<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Sparkles } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { analyze, index } from '@/routes/briefs';
import { dashboard } from '@/routes';

type Analysis = {
    executive_summary?: string;
    brand_overview?: string;
    campaign_objective?: string;
    target_audience?: string;
    scope_of_work?: string;
    deliverables?: string;
    timeline?: string;
    budget?: string;
    mandatory_requirements?: string;
    pitch_complexity_score?: number;
    ai_confidence_score?: number;
    ai_reasoning?: string;
    recommendations?: Array<{
        business_unit_name: string;
        confidence: number;
        reasoning?: string;
        matched_services?: string[];
    }>;
};

type Brief = {
    id: string;
    title: string;
    client_name: string;
    status_label: string;
    ai_status: string;
    ai_error?: string | null;
};

const props = defineProps<{
    brief: Brief;
    analysis: Analysis | null;
    pitchAssignments: Array<{
        id: string;
        status: string;
        confidence: number;
        business_unit?: string;
    }>;
}>();

function rerunAnalysis(advanced = false): void {
    router.post(analyze(props.brief.id).url, { advanced });
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Briefs', href: index() },
            { title: 'Analysis', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="brief.title" />

    <div class="grid gap-6 p-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold">{{ brief.title }}</h1>
                    <p class="text-muted-foreground">{{ brief.client_name }}</p>
                </div>
                <div class="flex gap-2">
                    <Badge>{{ brief.status_label }}</Badge>
                    <Badge variant="secondary">{{ brief.ai_status }}</Badge>
                </div>
            </div>

            <Card v-if="analysis">
                <CardHeader>
                    <CardTitle>Executive Summary</CardTitle>
                </CardHeader>
                <CardContent class="prose prose-sm dark:prose-invert max-w-none">
                    <p>{{ analysis.executive_summary }}</p>
                </CardContent>
            </Card>

            <div
                v-if="analysis"
                class="grid gap-4 md:grid-cols-2"
            >
                <Card>
                    <CardHeader><CardTitle class="text-sm">Scope of Work</CardTitle></CardHeader>
                    <CardContent class="text-sm whitespace-pre-wrap">{{ analysis.scope_of_work }}</CardContent>
                </Card>
                <Card>
                    <CardHeader><CardTitle class="text-sm">Deliverables</CardTitle></CardHeader>
                    <CardContent class="text-sm whitespace-pre-wrap">{{ analysis.deliverables }}</CardContent>
                </Card>
            </div>

            <Card v-else>
                <CardContent class="py-8 text-center text-sm text-muted-foreground">
                    <p v-if="brief.ai_error" class="text-destructive">{{ brief.ai_error }}</p>
                    <p v-else>AI analysis is processing. Refresh shortly.</p>
                </CardContent>
            </Card>
        </div>

        <div class="space-y-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle class="text-sm">AI Insights</CardTitle>
                    <Button
                        size="sm"
                        variant="outline"
                        @click="rerunAnalysis(false)"
                    >
                        <Sparkles class="mr-1 size-4" />
                        Re-run
                    </Button>
                </CardHeader>
                <CardContent
                    v-if="analysis"
                    class="space-y-3 text-sm"
                >
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Confidence</span>
                        <span class="font-medium">{{ analysis.ai_confidence_score }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Complexity</span>
                        <span class="font-medium">{{ analysis.pitch_complexity_score }}/10</span>
                    </div>
                    <p class="text-muted-foreground">{{ analysis.ai_reasoning }}</p>
                </CardContent>
            </Card>

            <Card v-if="analysis?.recommendations?.length">
                <CardHeader>
                    <CardTitle class="text-sm">Recommended Units</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div
                        v-for="rec in analysis.recommendations"
                        :key="rec.business_unit_name"
                        class="rounded-lg border p-3"
                    >
                        <div class="flex justify-between font-medium">
                            <span>{{ rec.business_unit_name }}</span>
                            <span>{{ rec.confidence }}%</span>
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">{{ rec.reasoning }}</p>
                    </div>
                </CardContent>
            </Card>

            <Card v-if="pitchAssignments.length">
                <CardHeader>
                    <CardTitle class="text-sm">Pitch Assignments</CardTitle>
                </CardHeader>
                <CardContent class="space-y-2 text-sm">
                    <div
                        v-for="pitch in pitchAssignments"
                        :key="pitch.id"
                        class="flex justify-between"
                    >
                        <span>{{ pitch.business_unit }}</span>
                        <Badge variant="outline">{{ pitch.status }}</Badge>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
