<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Sparkles, FileText, Brain, Building2, CheckCircle, FileDown, Pencil, X, Check } from 'lucide-vue-next';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Spinner } from '@/components/ui/spinner';
import { Tabs, TabsList, TabsContent, TabsTrigger } from '@/components/ui/tabs';
import HtmlEditor from '@/components/ui/html-editor.vue';
import { dashboard } from '@/routes';
import { analyze, index } from '@/routes/briefs';
import analysisRoutes from '@/routes/briefs/analysis';

type Analysis = {
    id: string;
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
    recommended_business_units?: Array<{
        name: string;
        confidence: number;
        reasoning?: string;
        services?: string[];
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

const editingField = ref<string | null>(null);
const editValue = ref('');
const editForm = useForm({
    field: '',
    value: '',
});

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

const progressValue = computed(() => ((currentStep.value + 1) / analysisSteps.length) * 100);

function rerunAnalysis(advanced = false): void {
    isRerunning.value = true;
    form.advanced = advanced;
    form.post(analyze(props.brief.id).url, {
        preserveScroll: true,
    });
}

function startEditing(field: string, value: string): void {
    editingField.value = field;
    editValue.value = value;
}

function cancelEditing(): void {
    editingField.value = null;
    editValue.value = '';
}

function saveEdit(): void {
    if (!editingField.value || !props.analysis) return;

    editForm.field = editingField.value;
    editForm.value = editValue.value;

    editForm.patch(analysisRoutes.update(props.brief.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            editingField.value = null;
            editValue.value = '';
            router.visit(window.location.pathname, {
                method: 'get',
                only: ['brief', 'analysis', 'pitchAssignments'],
                preserveScroll: true,
            });
        },
    });
}

function startPolling(): void {
    if (pollingInterval.value) {
        return;
    }

    pollingInterval.value = setInterval(() => {
        currentStep.value = (currentStep.value + 1) % analysisSteps.length;

        if (isProcessing.value || isRerunning.value) {
            router.visit(window.location.pathname, {
                method: 'get',
                only: ['brief', 'analysis', 'pitchAssignments'],
                preserveScroll: true,
                onFinish: () => {
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

watch(() => form.processing, (processing) => {
    if (processing && !pollingInterval.value) {
        startPolling();
    }
});

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
                        as-child
                    >
                        <Link :href="`/briefs/${brief.id}/preview`">
                            <FileDown class="mr-1 size-4" />
                            Preview PDF
                        </Link>
                    </Button>
                    <Badge>{{ brief.status_label }}</Badge>
                    <Badge variant="secondary">{{ brief.ai_status }}</Badge>
                </div>
            </div>

            <Card v-if="analysis && !isProcessing" class="mb-5">
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle>Executive Summary</CardTitle>
                    <Button
                        v-if="editingField !== 'executive_summary'"
                        size="sm"
                        variant="ghost"
                        @click="startEditing('executive_summary', analysis.executive_summary || '')"
                    >
                        <Pencil class="size-4" />
                    </Button>
                    <div v-else class="flex gap-1">
                        <Button size="sm" variant="ghost" @click="cancelEditing">
                            <X class="size-4" />
                        </Button>
                        <Button size="sm" variant="ghost" @click="saveEdit" :disabled="editForm.processing">
                            <Check class="size-4" />
                        </Button>
                    </div>
                </CardHeader>
                <CardContent class="prose prose-sm dark:prose-invert max-w-none">
                    <HtmlEditor
                        v-if="editingField === 'executive_summary'"
                        v-model="editValue"
                        class="min-h-[100px]"
                    />
                    <div v-else class="prose prose-sm dark:prose-invert max-w-none" v-html="analysis.executive_summary"></div>
                </CardContent>
            </Card>

            <Tabs v-if="analysis && !isProcessing" default-value="scope" class="space-y-4">
                <TabsList class="border-b">
                    <TabsTrigger value="scope">Scope</TabsTrigger>
                    <TabsTrigger value="campaign">Campaign</TabsTrigger>
                    <TabsTrigger value="additional">Additional Info</TabsTrigger>
                    <TabsTrigger value="ai-recommended-units">AI Recommended Units</TabsTrigger>
                    <TabsTrigger value="ai-recommended-resources">Recommended Resources</TabsTrigger>
                </TabsList>

                <TabsContent value="scope">
                    <!-- Scope of Work -->
                    <Card class="mb-4">
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle class="text-sm">Scope of Work</CardTitle>
                            <Button
                                v-if="editingField !== 'scope_of_work'"
                                size="sm"
                                variant="ghost"
                                @click="startEditing('scope_of_work', analysis.scope_of_work || '')"
                            >
                                <Pencil class="size-4" />
                            </Button>
                            <div v-else class="flex gap-1">
                                <Button size="sm" variant="ghost" @click="cancelEditing"><X class="size-4" /></Button>
                                <Button size="sm" variant="ghost" @click="saveEdit" :disabled="editForm.processing"><Check class="size-4" /></Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <HtmlEditor
                                v-if="editingField === 'scope_of_work'"
                                v-model="editValue"
                                class="min-h-[100px]"
                            />
                            <div v-else class="prose prose-sm dark:prose-invert max-w-none" v-html="analysis.scope_of_work"></div>
                        </CardContent>
                    </Card>
                    <!-- Deliverables -->
                    <Card class="mb-4">
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle class="text-sm">Deliverables</CardTitle>
                            <Button
                                v-if="editingField !== 'deliverables'"
                                size="sm"
                                variant="ghost"
                                @click="startEditing('deliverables', analysis.deliverables || '')"
                            >
                                <Pencil class="size-4" />
                            </Button>
                            <div v-else class="flex gap-1">
                                <Button size="sm" variant="ghost" @click="cancelEditing"><X class="size-4" /></Button>
                                <Button size="sm" variant="ghost" @click="saveEdit" :disabled="editForm.processing"><Check class="size-4" /></Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <HtmlEditor
                                v-if="editingField === 'deliverables'"
                                v-model="editValue"
                                class="min-h-[100px]"
                            />
                            <div v-else class="prose prose-sm dark:prose-invert max-w-none" v-html="analysis.deliverables"></div>
                        </CardContent>
                    </Card>
                    <!-- Mandatory Requirements -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle class="text-sm">Mandatory Requirements</CardTitle>
                            <Button
                                v-if="editingField !== 'mandatory_requirements'"
                                size="sm"
                                variant="ghost"
                                @click="startEditing('mandatory_requirements', analysis.mandatory_requirements || '')"
                            >
                                <Pencil class="size-4" />
                            </Button>
                            <div v-else class="flex gap-1">
                                <Button size="sm" variant="ghost" @click="cancelEditing"><X class="size-4" /></Button>
                                <Button size="sm" variant="ghost" @click="saveEdit" :disabled="editForm.processing"><Check class="size-4" /></Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <HtmlEditor
                                v-if="editingField === 'mandatory_requirements'"
                                v-model="editValue"
                                class="min-h-[100px]"
                            />
                            <div v-else class="prose prose-sm dark:prose-invert max-w-none" v-html="analysis.mandatory_requirements"></div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="campaign">
                    <!-- Target Audience -->
                    <Card class="mb-4">
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle class="text-sm">Target Audience</CardTitle>
                            <Button
                                v-if="editingField !== 'target_audience'"
                                size="sm"
                                variant="ghost"
                                @click="startEditing('target_audience', analysis.target_audience || '')"
                            >
                                <Pencil class="size-4" />
                            </Button>
                            <div v-else class="flex gap-1">
                                <Button size="sm" variant="ghost" @click="cancelEditing"><X class="size-4" /></Button>
                                <Button size="sm" variant="ghost" @click="saveEdit" :disabled="editForm.processing"><Check class="size-4" /></Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <HtmlEditor
                                v-if="editingField === 'target_audience'"
                                v-model="editValue"
                                class="min-h-[100px]"
                            />
                            <div v-else class="prose prose-sm dark:prose-invert max-w-none" v-html="analysis.target_audience"></div>
                        </CardContent>
                    </Card>
                    <!-- Timeline -->
                    <Card class="mb-4">
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle class="text-sm">Timeline</CardTitle>
                            <Button
                                v-if="editingField !== 'timeline'"
                                size="sm"
                                variant="ghost"
                                @click="startEditing('timeline', analysis.timeline || '')"
                            >
                                <Pencil class="size-4" />
                            </Button>
                            <div v-else class="flex gap-1">
                                <Button size="sm" variant="ghost" @click="cancelEditing"><X class="size-4" /></Button>
                                <Button size="sm" variant="ghost" @click="saveEdit" :disabled="editForm.processing"><Check class="size-4" /></Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <HtmlEditor
                                v-if="editingField === 'timeline'"
                                v-model="editValue"
                                class="min-h-[100px]"
                            />
                            <div v-else class="prose prose-sm dark:prose-invert max-w-none" v-html="analysis.timeline"></div>
                        </CardContent>
                    </Card>
                    <!-- Budget -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle class="text-sm">Budget</CardTitle>
                            <Button
                                v-if="editingField !== 'budget'"
                                size="sm"
                                variant="ghost"
                                @click="startEditing('budget', analysis.budget || '')"
                            >
                                <Pencil class="size-4" />
                            </Button>
                            <div v-else class="flex gap-1">
                                <Button size="sm" variant="ghost" @click="cancelEditing"><X class="size-4" /></Button>
                                <Button size="sm" variant="ghost" @click="saveEdit" :disabled="editForm.processing"><Check class="size-4" /></Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <HtmlEditor
                                v-if="editingField === 'budget'"
                                v-model="editValue"
                                class="min-h-[100px]"
                            />
                            <div v-else class="prose prose-sm dark:prose-invert max-w-none" v-html="analysis.budget"></div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="additional">
                    <!-- Brand Overview -->
                    <Card class="mb-4">
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle class="text-sm">Brand Overview</CardTitle>
                            <Button
                                v-if="editingField !== 'brand_overview'"
                                size="sm"
                                variant="ghost"
                                @click="startEditing('brand_overview', analysis.brand_overview || '')"
                            >
                                <Pencil class="size-4" />
                            </Button>
                            <div v-else class="flex gap-1">
                                <Button size="sm" variant="ghost" @click="cancelEditing"><X class="size-4" /></Button>
                                <Button size="sm" variant="ghost" @click="saveEdit" :disabled="editForm.processing"><Check class="size-4" /></Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <HtmlEditor
                                v-if="editingField === 'brand_overview'"
                                v-model="editValue"
                                class="min-h-[100px]"
                            />
                            <div v-else class="prose prose-sm dark:prose-invert max-w-none" v-html="analysis.brand_overview"></div>
                        </CardContent>
                    </Card>
                    <!-- Campaign Objective -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle class="text-sm">Campaign Objective</CardTitle>
                            <Button
                                v-if="editingField !== 'campaign_objective'"
                                size="sm"
                                variant="ghost"
                                @click="startEditing('campaign_objective', analysis.campaign_objective || '')"
                            >
                                <Pencil class="size-4" />
                            </Button>
                            <div v-else class="flex gap-1">
                                <Button size="sm" variant="ghost" @click="cancelEditing"><X class="size-4" /></Button>
                                <Button size="sm" variant="ghost" @click="saveEdit" :disabled="editForm.processing"><Check class="size-4" /></Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <HtmlEditor
                                v-if="editingField === 'campaign_objective'"
                                v-model="editValue"
                                class="min-h-[100px]"
                            />
                            <div v-else class="prose prose-sm dark:prose-invert max-w-none" v-html="analysis.campaign_objective"></div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="ai-recommended-units">
                    <Card v-if="analysis?.recommended_business_units?.length && !isProcessing">
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
                </TabsContent>

                <TabsContent value="ai-recommended-resources">
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
                </TabsContent>
            </Tabs>

            <Card v-if="!analysis || isProcessing">
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

            <Card v-if="pitchAssignments.length && !isProcessing">
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
        </div>
    </div>
</template>