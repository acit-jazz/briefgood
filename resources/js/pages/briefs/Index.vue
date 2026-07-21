<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { Eye, Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import AnimationButton from '@/components/ui/button/AnimationButton.vue';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { dashboard } from '@/routes';
import { create, index, show } from '@/routes/briefs';

type BriefItem = {
    id: string;
    title: string;
    client_name: string;
    status: string;
    status_label: string;
    ai_status: string;
    deadline?: string | null;
    creator?: { name: string };
    pitch_assignments?: Array<{ id: string; business_unit_id: string; business_unit: { id: string; name: string; logo_url: string } }>;
    latest_analysis?: {
        recommended_business_units?: Array<{ name: string }>;
    };
};

const page = usePage();
const auth = computed(() => page.props.auth);
const props = defineProps<{
    briefs: { data: BriefItem[]; current_page: number; last_page: number };
    filters: Record<string, string>;
}>();

// Search and filter state
const search = ref(props.filters.search || '');
const activeStatus = ref(props.filters.status || '');

// Role check helpers
const canCreateBrief = computed(() => {
    const role = auth.value?.user?.role;

    return role === 'super_admin' || role === 'group_admin';
});

// Watch for URL changes and update local state
watch(() => props.filters, (newFilters) => {
    search.value = newFilters.search || '';
    activeStatus.value = newFilters.status || '';
}, { immediate: true });

// Debounced search
let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, (newSearch) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(index().url, { search: newSearch, status: activeStatus.value }, { preserveState: true, replace: true });
    }, 300);
});

function setStatusFilter(status: string) {
    activeStatus.value = status;
    router.get(index().url, { search: search.value, status: status || '' }, { preserveState: true, replace: true });
}

function goToPage(pageNum: number) {
    router.get(index().url + '?page=' + pageNum, { search: search.value, status: activeStatus.value }, { preserveState: true });
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Briefs', href: index() },
        ],
    },
});
</script>

<template>
    <Head title="Briefs" />

    <div class="space-y-6 p-4 pb-26">
          <div v-if="canCreateBrief" class="w-fit mx-auto lg:mx-0 flex items-center fixed bottom-5 right-5">
              <AnimationButton
              data-aos="fade-up" data-aos-anchor-placement="top-bottom" data-aos-delay="900"
                :href="create().url" class="mt-10" color="#1C7A56"
              >
                <span class="text-white">Create Brief</span>
              </AnimationButton>
              <AnimationButton
              data-aos="fade-up" data-aos-anchor-placement="top-bottom" data-aos-delay="900"
                :href="create().url" class="mt-10" color="#E7BA33"
                :isSquare="true"
              >
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path
                      d="M5 15L15 5"
                      stroke="#000"
                      stroke-width="2"
                      stroke-linecap="round"
                    />
                    <path
                      d="M6.875 5H15V13.125"
                      stroke="#000"
                      stroke-width="2"
                      stroke-linecap="round"
                    />
                  </svg>
              </AnimationButton>
          </div>

        <div class="grid gap-4 ">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-3 w-6/12 rounded-2xl border border-border bg-background px-3 py-2 shadow-sm">
                    <Search class="size-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        placeholder="Search briefs by title, client, or assignee"
                        class="border-0 bg-transparent px-0 py-0 text-sm shadow-none"
                    />
                </div>
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <button
                        @click="setStatusFilter('')"
                        class="rounded-full px-3 py-2 transition-colors"
                        :class="activeStatus === '' ? 'bg-[#1C7A56] text-white' : 'bg-muted hover:bg-muted/80'"
                    >
                        All
                    </button>
                    <button
                        @click="setStatusFilter('new')"
                        class="rounded-full px-3 py-2 transition-colors"
                        :class="activeStatus === 'new' ? 'bg-[#1C7A56] text-white' : 'bg-muted hover:bg-muted/80'"
                    >
                        New
                    </button>
                    <button
                        @click="setStatusFilter('reviewing')"
                        class="rounded-full px-3 py-2 transition-colors"
                        :class="activeStatus === 'reviewing' ? 'bg-[#1C7A56] text-white' : 'bg-muted hover:bg-muted/80'"
                    >
                        Reviewing
                    </button>
                    <button
                        @click="setStatusFilter('assigned')"
                        class="rounded-full px-3 py-2 transition-colors"
                        :class="activeStatus === 'assigned' ? 'bg-[#1C7A56] text-white' : 'bg-muted hover:bg-muted/80'"
                    >
                        Assigned
                    </button>
                    <button
                        @click="setStatusFilter('completed')"
                        class="rounded-full px-3 py-2 transition-colors"
                        :class="activeStatus === 'completed' ? 'bg-[#1C7A56] text-white' : 'bg-muted hover:bg-muted/80'"
                    >
                        Completed
                    </button>
                </div>
            </div>
        </div>

        <Card class="overflow-hidden rounded-3xl border border-border shadow-sm">
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border">
                        <thead class="bg-muted/70 text-left text-xs uppercase tracking-[0.16em] text-muted-foreground">
                            <tr>
                                <th class="px-5 py-4">Project Name</th>
                                <th class="px-5 py-4">Client</th>
                                <th class="px-5 py-4">Assignee</th>
                                <th class="px-5 py-4">Owner</th>
                                <th class="px-5 py-4">Deadline</th>
                                <th class="px-5 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border bg-background">
                            <tr
                                v-for="brief in briefs.data"
                                :key="brief.id"
                                class="transition-colors duration-150 hover:bg-muted/50"
                            >
                                <td class="px-5 py-4">
                                    <div class="font-medium text-foreground">{{ brief.title }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-muted-foreground">
                                    {{ brief.client_name }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center">
                                        <template v-if="brief.pitch_assignments && brief.pitch_assignments.length">
                                            <div
                                                v-for="(unit, index) in brief.pitch_assignments"
                                                :key="unit.id || index"
                                                class="relative"
                                                :style="{ marginLeft: index > 0 ? '-12px' : '0', zIndex: brief.pitch_assignments.length - index }"
                                            >
                                                <img
                                                    :src="unit.business_unit.logo_url"
                                                    alt="Logo"
                                                    class="h-10 w-10 rounded-full object-cover border-2 border-background"
                                                    :title="unit.business_unit.name"
                                                />
                                            </div>
                                        </template>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-sm text-muted-foreground">
                                    {{ brief.creator?.name }}
                                </td>
                                <td class="px-5 py-4 text-sm text-muted-foreground">
                                    {{ brief.deadline }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <Button as-child size="sm" variant="outline">
                                        <Link :href="show(brief.id)" class="inline-flex items-center gap-2">
                                            <Eye class="size-4" />
                                            View Details
                                        </Link>
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="!briefs.data.length" class="rounded-b-3xl border-t border-border bg-background p-8 text-center text-sm text-muted-foreground">
                    No briefs found.
                </div>
            </CardContent>
        </Card>
    </div>
</template>
