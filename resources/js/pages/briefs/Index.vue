<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Eye, Plus } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { create, index, show } from '@/routes/briefs';
import { dashboard } from '@/routes';

type BriefItem = {
    id: string;
    title: string;
    client_name: string;
    status: string;
    status_label: string;
    ai_status: string;
    deadline?: string | null;
};

defineProps<{
    briefs: { data: BriefItem[] };
    filters: Record<string, string>;
}>();

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

    <div class="flex flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Briefs</h1>
                <p class="text-sm text-muted-foreground">
                    Client RFPs and standardized pitch opportunities.
                </p>
            </div>
            <Button as-child>
                <Link :href="create()">
                    <Plus class="mr-2 size-4" />
                    Upload Brief
                </Link>
            </Button>
        </div>

        <div class="grid gap-4">

            <table class="min-w-full divide-y divide-border">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium uppercase">PROJECT NAME</th>
                        <th class="px-4 py-3 text-left text-sm font-medium uppercase">Client</th>
                        <th class="px-4 py-3 text-left text-sm font-medium uppercase">Assignee</th>
                        <th class="px-4 py-3 text-left text-sm font-medium uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-sm font-medium uppercase">OWNER</th>
                        <th class="px-4 py-3 text-left text-sm font-medium uppercase">deadline</th>
                        <th class="px-4 py-3 text-right text-sm font-medium uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border bg-white">
                    <tr
                        v-for="brief in briefs.data"
                        :key="brief.id"
                        class="hover:bg-muted/50"
                    >
                        <td class="px-4 py-3 text-sm font-medium">{{ brief.title }}</td>
                        <td class="px-4 py-3 text-sm text-muted-foreground">{{ brief.client_name }}</td>
                        <td class="px-4 py-3 text-sm text-muted-foreground">

                            <div v-if="brief.latest_analysis && brief.latest_analysis.recommended_business_units">
                                <div
                                    v-for="(unit, index) in brief.latest_analysis.recommended_business_units"
                                    :key="index"
                                    class="inline-flex items-center rounded-full bg-muted px-2 py-1 text-xs font-medium text-muted-foreground mr-1 mb-1"
                                >
                                    {{ unit.name }}
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                        <Badge variant="outline">{{ brief.status_label }}</Badge>
                        <Badge variant="secondary">{{ brief.ai_status }}</Badge>
                        </td>
                        <td class="px-4 py-3 text-sm text-muted-foreground">
                            {{ brief.creator?.name }}
                        </td>
                        <td class="px-4 py-3 text-sm text-muted-foreground">
                            {{ brief.deadline }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <Button size="sm" variant="outline" as-child>
                                    <Link :href="show(brief.id)">
                                        <Eye class="mr-1 size-3" />
                                        View Details
                                    </Link>
                                </Button>
                                <button
                                    @click="goToEdit(brief.id)"
                                    class="rounded p-1 hover:bg-muted"
                                    title="Edit"
                                >
                                    <Pencil class="size-4 text-muted-foreground" />
                                </button>
                                <button
                                    @click="deletebrief(brief.id)"
                                    class="rounded p-1 hover:bg-muted"
                                    title="Delete"
                                >
                                    <Trash2 class="size-4 text-destructive" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p
                v-if="!briefs.data.length"
                class="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground"
            >
                No briefs yet. Upload your first client RFP to start AI analysis.
            </p>
        </div>
    </div>
</template>
