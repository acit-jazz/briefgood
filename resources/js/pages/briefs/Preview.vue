<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';
import { ArrowLeft, Download } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

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
};

const props = defineProps<{
    brief: Brief;
    analysis: Analysis;
    pitchAssignments: Array<{
        id: string;
        status: string;
        confidence: number;
        business_unit?: string;
        matched_services?: string[];
    }>;
    resourceAllocations?: Array<{
        id: string;
        resource_name?: string;
        estimated_hours?: number;
        estimated_workload_percent?: number;
        estimated_duration_days?: number;
    }>;
}>();

const contentRef = ref<HTMLElement | null>(null);
const isExporting = ref(false);

async function exportPDF(): Promise<void> {
    if (!contentRef.value) return;

    isExporting.value = true;

    try {
        const canvas = await html2canvas(contentRef.value, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff',
            onclone: (cloneDoc) => {
                cloneDoc.querySelectorAll('*').forEach((el) => {
                    const htmlEl = el as HTMLElement;
                    const computedStyle = window.getComputedStyle(htmlEl);
                    ['color', 'background-color', 'border-color', 'fill', 'stroke'].forEach((prop) => {
                        const val = computedStyle.getPropertyValue(prop);
                        if (val && val.includes('oklch')) {
                            htmlEl.style.setProperty(prop, '#000000');
                        }
                    });

                    if (htmlEl.classList?.contains('rounded-lg') || htmlEl.tagName === 'CARD') {
                        htmlEl.style.pageBreakInside = 'avoid';
                        htmlEl.style.breakInside = 'avoid';
                        htmlEl.style.pageBreakBefore = 'avoid';
                    }
                });
                cloneDoc.querySelectorAll('[class*="dark:"]').forEach((el) => {
                    el.classList.remove('dark:text-blue-300', 'dark:bg-blue-950/20', 'dark:prose-invert');
                });
            },
        });

        const imgData = canvas.toDataURL('image/jpeg', 0.98);
        const pdf = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4',
        });

        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = pdf.internal.pageSize.getHeight();
        const imgWidth = canvas.width;
        const imgHeight = canvas.height;
        const ratio = pdfWidth / (imgWidth / 2);
        const height = (imgHeight / 2) * ratio;

        let y = 0;

        if (height <= pdfHeight) {
            pdf.addImage(imgData, 'JPEG', 0, 0, pdfWidth, height);
        } else {
            let remaining = height;

            while (remaining > 0) {
                pdf.addImage(imgData, 'JPEG', 0, -y, pdfWidth, height);
                y += pdfHeight;
                remaining -= pdfHeight;

                if (remaining > 0) {
                    pdf.addPage();
                }
            }
        }

        pdf.save(`brief-analysis-${props.brief.id}.pdf`);
    } finally {
        isExporting.value = false;
    }
}

function goBack(): void {
    router.visit(`/briefs/${props.brief.id}`);
}
</script>

<template>
    <Head :title="`Preview: ${brief.title}`" />

    <!-- Header Bar -->
    <div class="sticky top-0 z-50 flex items-center justify-between border-b bg-background px-6 py-3">
        <div class="flex items-center gap-4">
            <Button variant="ghost" size="sm" @click="goBack">
                <ArrowLeft class="mr-1 size-4" />
                Back
            </Button>
            <h1 class="text-lg font-semibold">PDF Preview: {{ brief.title }}</h1>
        </div>
        <Button
            size="sm"
            @click="exportPDF"
            :disabled="isExporting"
        >
            <Download class="mr-1 size-4" />
            {{ isExporting ? 'Exporting...' : 'Download PDF' }}
        </Button>
    </div>

    <!-- PDF Content Preview -->
    <div class="flex justify-center bg-muted p-6">
        <div class="w-full max-w-[210mm] bg-background shadow-lg">
            <div ref="contentRef" class="p-12">
                <!-- Header -->
                <div class="mb-8 border-b pb-6">
                    <h1 class="text-3xl font-bold text-gray-900">{{ brief.title }}</h1>
                    <p class="mt-2 text-lg text-gray-600">{{ brief.client_name }}</p>
                </div>

                <!-- Executive Summary -->
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>Executive Summary</CardTitle>
                    </CardHeader>
                    <CardContent v-html="analysis.executive_summary">
                    </CardContent>
                </Card>

                <!-- Analysis Grid -->
                <div class="mb-6 grid gap-4 ">
                    <Card>
                        <CardHeader><CardTitle class="text-sm">Brand Overview</CardTitle></CardHeader>
                        <CardContent class="text-sm" v-html="analysis.brand_overview"></CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle class="text-sm">Campaign Objective</CardTitle></CardHeader>
                        <CardContent class="text-sm" v-html="analysis.campaign_objective"></CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle class="text-sm">Target Audience</CardTitle></CardHeader>
                        <CardContent class="text-sm" v-html="analysis.target_audience"></CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle class="text-sm">Scope of Work</CardTitle></CardHeader>
                        <CardContent class="text-sm" v-html="analysis.scope_of_work"></CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle class="text-sm">Deliverables</CardTitle></CardHeader>
                        <CardContent class="text-sm" v-html="analysis.deliverables"></CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle class="text-sm">Timeline</CardTitle></CardHeader>
                        <CardContent class="text-sm" v-html="analysis.timeline"></CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle class="text-sm">Budget</CardTitle></CardHeader>
                        <CardContent class="text-sm" v-html="analysis.budget"></CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle class="text-sm">Mandatory Requirements</CardTitle></CardHeader>
                        <CardContent class="text-sm" v-html="analysis.mandatory_requirements"></CardContent>
                    </Card>
                </div>

                <!-- AI Insights -->
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>AI Insights</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Confidence</span>
                            <span class="font-medium">{{ analysis.ai_confidence_score }}%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Complexity</span>
                            <span class="font-medium">{{ analysis.pitch_complexity_score }}/10</span>
                        </div>
                        <p class="text-sm text-muted-foreground">{{ analysis.ai_reasoning }}</p>
                    </CardContent>
                </Card>

                <!-- Recommended Units -->
                <Card v-if="analysis.recommendations?.length" class="mb-6">
                    <CardHeader>
                        <CardTitle>AI Recommended Units</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div
                            v-for="rec in analysis.recommendations"
                            :key="rec.business_unit_name"
                            class="rounded-lg border p-4"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-semibold">{{ rec.business_unit_name }}</span>
                                <Badge variant="secondary">{{ rec.confidence }}% Match</Badge>
                            </div>
                            <p class="text-xs text-muted-foreground mb-3">{{ rec.reasoning }}</p>
                            <div v-if="rec.matched_services?.length" class="space-y-1">
                                <span class="text-xs font-medium text-muted-foreground">Matched Services:</span>
                                <div class="flex flex-wrap gap-1">
                                    <Badge
                                        v-for="service in rec.matched_services"
                                        :key="service"
                                        variant="outline"
                                        class="text-xs"
                                    >
                                        {{ service }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Pitch Assignments -->
                <Card v-if="analysis?.recommended_business_units?.length && !isProcessing" class="mb-6">
                    <CardHeader>
                        <CardTitle class="text-sm">AI Recommended Units</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div
                            v-for="rec in analysis.recommended_business_units"
                            :key="rec.name"
                            class="rounded-lg border p-4"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-semibold">{{ rec.name }}</span>
                                <Badge variant="secondary">{{ rec.confidence }}% Match</Badge>
                            </div>
                            <p class="text-xs text-muted-foreground mb-3">{{ rec.reasoning }}</p>
                            <div v-if="rec.services?.length" class="space-y-1">
                                <span class="text-xs font-medium text-muted-foreground">Matched Services:</span>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    <Badge
                                        v-for="service in rec.services"
                                        :key="service"
                                        variant="outline"
                                        class="text-xs"
                                    >
                                        {{ service }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card v-if="resourceAllocations?.length && !isProcessing">
                    <CardHeader>
                        <CardTitle class="text-sm">Recommended Resources</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div
                                v-for="resource in resourceAllocations"
                                :key="resource.id"
                                class="flex items-center justify-between py-2 border-b last:border-0"
                            >
                                <div>
                                    <span class="font-medium text-sm">{{ resource.resource_name }}</span>
                                    <div class="text-xs text-muted-foreground mt-1">
                                        <span v-if="resource.estimated_hours">{{ resource.estimated_hours }} hours</span>
                                        <span v-if="resource.estimated_workload_percent"> • {{ resource.estimated_workload_percent }}% workload</span>
                                        <span v-if="resource.estimated_duration_days"> • {{ resource.estimated_duration_days }} days</span>
                                    </div>
                                </div>
                                <Badge variant="outline" class="text-xs">
                                    {{ resource.estimated_duration_days ?? '-' }} days
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <!-- Footer -->
                <div class="mt-8 border-t pt-4 text-center text-xs text-gray-400">
                    Generated by BriefGood AI Analysis
                </div>
            </div>
        </div>
    </div>
</template>
