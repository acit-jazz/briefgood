<script setup lang="ts">
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';
import { FileDown, Sparkles, FileText, Brain, Building2, CheckCircle } from 'lucide-vue-next';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes';
import { analyze, index } from '@/routes/briefs';

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
        matched_services?: string[];
        recommendation_confidence?: number;
    }>;
    resourceAllocations?: Array<{
        id: string;
        resource_name?: string;
        estimated_hours?: number;
        estimated_workload_percent?: number;
        estimated_duration_days?: number;
    }>;
}>();

const form = useForm({
    advanced: false,
});

const contentRef = ref<HTMLElement | null>(null);

// Polling state
const pollingInterval = ref<ReturnType<typeof setInterval> | null>(null);
const currentStep = ref(0);
const isRerunning = ref(false);

const analysisSteps = [
    { id: 'parsing', label: 'Parsing brief document', icon: FileText },
    { id: 'analyzing', label: 'Analyzing content with AI', icon: Brain },
    { id: 'matching', label: 'Matching with business units', icon: Building2 },
    { id: 'generating', label: 'Generating recommendations', icon: Sparkles },
    { id: 'finalizing', label: 'Finalizing report', icon: CheckCircle },
];

const isProcessing = computed(() => {
    return props.brief.ai_status === 'pending' || props.brief.ai_status === 'processing';
});

const processingMessage = computed(() => {
    if (props.brief.ai_status === 'failed') {
        return 'Analysis failed';
    }

    const messages = [
        'Extracting information from the brief document...',
        'AI is analyzing the content and context...',
        'Matching requirements with business units...',
        'Generating personalized recommendations...',
        'Finalizing your analysis report...',
    ];

    return messages[currentStep.value] || messages[0];
});

const currentStepData = computed(() => analysisSteps[currentStep.value]);
const progressValue = computed(() => ((currentStep.value + 1) / analysisSteps.length) * 100);

function rerunAnalysis(advanced = false): void {
    isRerunning.value = true;
    form.advanced = advanced;
    form.post(analyze(props.brief.id).url, {
        preserveScroll: true,
    });
}

function startPolling(): void {
    if (pollingInterval.value) {
        return;
    }

    pollingInterval.value = setInterval(() => {
        // Rotate through steps for animation
        currentStep.value = (currentStep.value + 1) % analysisSteps.length;

        // Use Inertia's visit to reload data
        if (isProcessing.value || isRerunning.value) {
            router.visit(window.location.pathname, {
                method: 'get',
                only: ['brief', 'analysis', 'pitchAssignments'],
                preserveScroll: true,
                onFinish: () => {
                    // Stop polling if analysis is complete AND job is done
                    if (!isProcessing.value && pollingInterval.value) {
                        clearInterval(pollingInterval.value);
                        pollingInterval.value = null;
                        isRerunning.value = false;
                    }
                },
            });
        }
    }, 3000);
}

function stopPolling(): void {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
        pollingInterval.value = null;
    }
}

function shouldPoll(): boolean {
    return isProcessing.value || form.processing;
}

onMounted(() => {
    if (shouldPoll()) {
        startPolling();
    }
});

onUnmounted(() => {
    stopPolling();
});

// Watch for form processing state changes (Re-run button clicked)
watch(() => form.processing, (processing) => {
    if (processing && !pollingInterval.value) {
        startPolling();
    }
});

async function exportPDF(): Promise<void> {
    if (!contentRef.value) {
        return;
    }

    const canvas = await html2canvas(contentRef.value, {
        scale: 2,
        useCORS: true,
        backgroundColor: '#ffffff',
        onclone: (cloneDoc, el) => {
            // Replace oklch colors with rgb as html2canvas doesn't support oklch
            cloneDoc.querySelectorAll('*').forEach((htmlEl) => {
                const computedStyle = window.getComputedStyle(htmlEl);
                ['color', 'background-color', 'border-color', 'fill', 'stroke'].forEach((prop) => {
                    const val = computedStyle.getPropertyValue(prop);
                    if (val && val.includes('oklch')) {
                        htmlEl.style.setProperty(prop, '#000000');
                    }
                });
            });
            // Also remove Tailwind dark mode classes
            cloneDoc.querySelectorAll('[class*="dark:"]').forEach((htmlEl) => {
                htmlEl.classList.remove('dark:text-blue-300', 'dark:bg-blue-950/20', 'dark:prose-invert');
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
                    <Button
                        v-if="analysis"
                        size="sm"
                        variant="outline"
                        @click="exportPDF"
                    >
                        <FileDown class="mr-1 size-4" />
                        Export PDF
                    </Button>
                    <Badge>{{ brief.status_label }}</Badge>
                    <Badge variant="secondary">{{ brief.ai_status }}</Badge>
                </div>
            </div>

            <div ref="contentRef">
                <Card v-if="analysis && !form.processing" class="mb-5">
                    <CardHeader>
                        <CardTitle>Executive Summary</CardTitle>
                    </CardHeader>
                    <CardContent class="prose prose-sm dark:prose-invert max-w-none">
                        <p>{{ analysis.executive_summary }}</p>
                    </CardContent>
                </Card>

                <div
                    v-if="analysis && !form.processing"
                    class="grid gap-4 md:grid-cols-2"
                >
                    <Card>
                        <CardHeader><CardTitle class="text-sm">Brand Overview</CardTitle></CardHeader>
                        <CardContent class="prose prose-sm dark:prose-invert max-w-none" v-html="analysis.brand_overview"></CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle class="text-sm">Campaign Objective</CardTitle></CardHeader>
                        <CardContent class="prose prose-sm dark:prose-invert max-w-none" v-html="analysis.campaign_objective"></CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle class="text-sm">Target Audience</CardTitle></CardHeader>
                        <CardContent class="prose prose-sm dark:prose-invert max-w-none" v-html="analysis.target_audience"></CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle class="text-sm">Scope of Work</CardTitle></CardHeader>
                        <CardContent class="prose prose-sm dark:prose-invert max-w-none" v-html="analysis.scope_of_work"></CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle class="text-sm">Deliverables</CardTitle></CardHeader>
                        <CardContent class="prose prose-sm dark:prose-invert max-w-none" v-html="analysis.deliverables"></CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle class="text-sm">Timeline</CardTitle></CardHeader>
                        <CardContent class="prose prose-sm dark:prose-invert max-w-none" v-html="analysis.timeline"></CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle class="text-sm">Budget</CardTitle></CardHeader>
                        <CardContent class="prose prose-sm dark:prose-invert max-w-none" v-html="analysis.budget"></CardContent>
                    </Card>
                    <Card>
                        <CardHeader><CardTitle class="text-sm">Mandatory Requirements</CardTitle></CardHeader>
                        <CardContent class="prose prose-sm dark:prose-invert max-w-none" v-html="analysis.mandatory_requirements"></CardContent>
                    </Card>
                </div>
            </div>

            <Card v-if="isProcessing || form.processing">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Spinner class="size-4" />
                        AI Analysis in Progress
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="space-y-2">
                        <p class="text-sm font-medium">{{ processingMessage }}</p>
                        <Progress :value="progressValue" class="w-full" />
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="(step, index) in analysisSteps"
                            :key="step.id"
                            class="flex items-center gap-3 text-sm"
                            :class="index <= currentStep ? 'text-primary' : 'text-muted-foreground'"
                        >
                            <div
                                class="flex size-6 items-center justify-center rounded-full"
                                :class="index < currentStep ? 'bg-primary text-primary-foreground' : index === currentStep ? 'bg-primary/20' : 'bg-muted'"
                            >
                                <component
                                    :is="step.icon"
                                    v-if="index < currentStep"
                                    class="size-3"
                                />
                                <span v-else-if="index === currentStep" class="text-xs">{{ index + 1 }}</span>
                                <span v-else class="text-xs">{{ index + 1 }}</span>
                            </div>
                            <span :class="index === currentStep ? 'font-medium' : ''">
                                {{ step.label }}
                            </span>
                        </div>
                    </div>

                    <p class="text-xs text-muted-foreground text-center">
                        This usually takes 30-60 seconds. You can wait here or come back later.
                    </p>
                </CardContent>
            </Card>

            <Card v-if="!analysis && brief.ai_status === 'failed'">
                <CardContent class="py-8 text-center">
                    <p class="text-destructive font-medium mb-2">Analysis Failed</p>
                    <p class="text-sm text-muted-foreground">{{ brief.ai_error || 'An unknown error occurred.' }}</p>
                </CardContent>
            </Card>
        </div>

        <div class="space-y-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle class="text-sm">AI Insights</CardTitle>
                    <Button
                        class="cursor-pointer"
                        size="sm"
                        variant="outline"
                        :disabled="isRerunning || isProcessing"
                        @click="rerunAnalysis(false)"
                    >
                        <Spinner v-if="isRerunning || isProcessing" class="mr-1 size-4" />
                        <Sparkles v-else class="mr-1 size-4" />
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

            <Card v-if="analysis?.recommendations?.length && !form.processing">
                <CardHeader>
                    <CardTitle class="text-sm">AI Recommended Units</CardTitle>
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
                            <div class="flex flex-wrap gap-1 mt-1">
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

            <Card v-if="pitchAssignments.length && !form.processing">
                <CardHeader>
                    <CardTitle class="text-sm">Pitch Assignments</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div
                        v-for="pitch in pitchAssignments"
                        :key="pitch.id"
                        class="rounded-lg border p-3"
                    >
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="font-medium">{{ pitch.business_unit }}</span>
                                <span class="ml-2 text-xs text-muted-foreground">
                                    ({{ pitch.confidence }}% AI confidence)
                                </span>
                            </div>
                            <Badge variant="outline">{{ pitch.status }}</Badge>
                        </div>
                        <div v-if="pitch.matched_services?.length" class="space-y-1">
                            <span class="text-xs font-medium text-muted-foreground">Services to provide:</span>
                            <div class="flex flex-wrap gap-1 mt-1">
                                <Badge
                                    v-for="service in pitch.matched_services"
                                    :key="service"
                                    variant="secondary"
                                    class="text-xs"
                                >
                                    {{ service }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card v-if="resourceAllocations?.length && !form.processing">
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
        </div>
    </div>
</template>
