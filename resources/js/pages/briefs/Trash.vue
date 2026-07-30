<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ArrowLeft, Search, RotateCcw } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { dashboard } from '@/routes';
import { trash, restore, index as briefsIndex } from '@/routes/briefs';

type BriefItem = {
    id: string;
    title: string;
    client_name: string;
    status: string;
    status_label: string;
    ai_status: string;
    deadline?: string | null;
    creator?: { name: string };
    deleted_at: string;
};

const page = usePage();
const auth = computed(() => page.props.auth);
const props = defineProps<{
    briefs: { data: BriefItem[]; current_page: number; last_page: number };
    filters: Record<string, string>;
}>();

const search = ref(props.filters.search || '');

watch(search, (newSearch) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(trash().url, { search: newSearch }, { preserveState: true, replace: true });
    }, 300);
});

let searchTimeout: ReturnType<typeof setTimeout>;

function goToPage(pageNum: number) {
    router.get(trash().url + '?page=' + pageNum, { search: search.value }, { preserveState: true });
}

function restoreBrief(briefId: string) {
    if (confirm('Restore this brief from trash?')) {
        router.post(restore(briefId).url, {}, { preserveScroll: true });
    }
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Briefs', href: briefsIndex() },
            { title: 'Trash', href: trash() },
        ],
    },
});
</script>

<template>
    <Head title="Briefs Trash" />

    <div class="space-y-6 p-4">
        <div class="flex items-center gap-4">
            <Button as-child variant="outline" size="sm">
                <Link :href="briefsIndex()" class="inline-flex items-center gap-2">
                    <ArrowLeft class="size-4" />
                    Back to Briefs
                </Link>
            </Button>
            <h1 class="text-2xl font-semibold">Trash</h1>
        </div>

        <div class="grid gap-4">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-3 w-6/12 rounded-2xl border border-border bg-background px-3 py-2 shadow-sm">
                    <Search class="size-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        placeholder="Search trashed briefs by title or client"
                        class="border-0 bg-transparent px-0 py-0 text-sm shadow-none"
                    />
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
                                <th class="px-5 py-4">Owner</th>
                                <th class="px-5 py-4">Deleted At</th>
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
                                <td class="px-5 py-4 text-sm text-muted-foreground">
                                    {{ brief.creator?.name }}
                                </td>
                                <td class="px-5 py-4 text-sm text-muted-foreground">
                                    {{ brief.deleted_at }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <Button size="sm" variant="default" @click="restoreBrief(brief.id)" class="bg-[#1C7A56] hover:bg-[#1C7A56]/90">
                                            <RotateCcw class="size-4 mr-1" />
                                            Restore
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="!briefs.data.length" class="rounded-b-3xl border-t border-border bg-background p-8 text-center text-sm text-muted-foreground">
                    No trashed briefs found.
                </div>
            </CardContent>
        </Card>
    </div>
</template>
