<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import { index as pitchPipeline } from '@/routes/pitch-pipeline';

type Column = {
    status: string;
    items: Array<{
        id: string;
        confidence: number;
        business_unit?: string;
        brief: { id: string; title: string; client_name: string };
    }>;
};

defineProps<{
    columns: Column[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Pitch Pipeline', href: pitchPipeline() },
        ],
    },
});
</script>

<template>
    <Head title="Pitch Pipeline" />

    <div class="flex flex-col gap-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold">Pitch Pipeline</h1>
            <p class="text-sm text-muted-foreground">
                Kanban view of internal pitch assignments across business units.
            </p>
        </div>

        <div class="flex gap-4 overflow-x-auto pb-4">
            <div
                v-for="column in columns"
                :key="column.status"
                class="min-w-[280px] flex-1"
            >
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm capitalize">
                            {{ column.status.replace('_', ' ') }}
                            <span class="ml-2 text-muted-foreground">({{ column.items.length }})</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div
                            v-for="item in column.items"
                            :key="item.id"
                            class="rounded-lg border bg-card p-3 text-sm shadow-sm"
                        >
                            <p class="font-medium">{{ item.brief.title }}</p>
                            <p class="text-xs text-muted-foreground">{{ item.brief.client_name }}</p>
                            <p class="mt-2 text-xs">{{ item.business_unit }} · {{ item.confidence }}%</p>
                        </div>
                        <p
                            v-if="!column.items.length"
                            class="text-center text-xs text-muted-foreground py-4"
                        >
                            Empty
                        </p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
