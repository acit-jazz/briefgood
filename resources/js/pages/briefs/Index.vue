<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
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
            <Card
                v-for="brief in briefs.data"
                :key="brief.id"
                class="transition-colors hover:border-primary/40"
            >
                <CardHeader class="flex flex-row items-start justify-between pb-2">
                    <div>
                        <CardTitle class="text-base">
                            <Link
                                :href="show(brief.id)"
                                class="hover:underline"
                            >
                                {{ brief.title }}
                            </Link>
                        </CardTitle>
                        <p class="text-sm text-muted-foreground">
                            {{ brief.client_name }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <Badge variant="outline">{{ brief.status_label }}</Badge>
                        <Badge variant="secondary">{{ brief.ai_status }}</Badge>
                    </div>
                </CardHeader>
                <CardContent>
                    <p
                        v-if="brief.deadline"
                        class="text-xs text-muted-foreground"
                    >
                        Deadline: {{ brief.deadline }}
                    </p>
                </CardContent>
            </Card>

            <p
                v-if="!briefs.data.length"
                class="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground"
            >
                No briefs yet. Upload your first client RFP to start AI analysis.
            </p>
        </div>
    </div>
</template>
