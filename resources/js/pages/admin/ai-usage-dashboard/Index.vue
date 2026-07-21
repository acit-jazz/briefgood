<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Brain, Coins, FileText, Zap } from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type Stats = {
    total_analyses: number;
    total_cost_usd: number;
    total_tokens: number;
    total_prompt_tokens: number;
    total_completion_tokens: number;
};

type ByModel = {
    model_used: string;
    analysis_count: number;
    total_prompt_tokens: number;
    total_completion_tokens: number;
    total_tokens: number;
    total_cost_usd: number;
};

type RecentAnalysis = {
    id: string;
    brief_title: string;
    model_used: string;
    prompt_tokens: number;
    completion_tokens: number;
    total_tokens: number;
    cost_usd: number;
    created_at: string;
};

defineProps<{
    stats: Stats;
    byModel: ByModel[];
    recentAnalyses: RecentAnalysis[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'AI Usage', href: '#' },
        ],
    },
});

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 6,
        maximumFractionDigits: 6,
    }).format(value || 0);
}

function formatNumber(value: number): string {
    return new Intl.NumberFormat('en-US').format(value || 0);
}
</script>

<template>
    <Head title="AI Usage Dashboard" />

    <div class="space-y-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold">AI Usage Dashboard</h1>
            <p class="text-muted-foreground">Track token usage and costs across AI brief analyses.</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">Total Analyses</CardTitle>
                    <FileText class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatNumber(stats.total_analyses) }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">Total Cost</CardTitle>
                    <Coins class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatCurrency(stats.total_cost_usd) }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">Total Tokens</CardTitle>
                    <Zap class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatNumber(stats.total_tokens) }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">Avg Cost / Analysis</CardTitle>
                    <Brain class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ stats.total_analyses > 0 ? formatCurrency(stats.total_cost_usd / stats.total_analyses) : '$0.00' }}
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- By Model Breakdown -->
        <Card>
            <CardHeader>
                <CardTitle>Usage by Model</CardTitle>
            </CardHeader>
            <CardContent>
                <div v-if="byModel.length" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="px-4 py-3 text-left font-medium">Model</th>
                                <th class="px-4 py-3 text-right font-medium">Analyses</th>
                                <th class="px-4 py-3 text-right font-medium">Prompt Tokens</th>
                                <th class="px-4 py-3 text-right font-medium">Completion Tokens</th>
                                <th class="px-4 py-3 text-right font-medium">Total Tokens</th>
                                <th class="px-4 py-3 text-right font-medium">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="row in byModel" :key="row.model_used">
                                <td class="px-4 py-3 font-medium">{{ row.model_used }}</td>
                                <td class="px-4 py-3 text-right">{{ formatNumber(row.analysis_count) }}</td>
                                <td class="px-4 py-3 text-right">{{ formatNumber(row.total_prompt_tokens) }}</td>
                                <td class="px-4 py-3 text-right">{{ formatNumber(row.total_completion_tokens) }}</td>
                                <td class="px-4 py-3 text-right">{{ formatNumber(row.total_tokens) }}</td>
                                <td class="px-4 py-3 text-right font-medium">{{ formatCurrency(row.total_cost_usd) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-muted-foreground text-center py-8">No data available.</p>
            </CardContent>
        </Card>

        <!-- Recent Analyses -->
        <Card>
            <CardHeader>
                <CardTitle>Recent Analyses</CardTitle>
            </CardHeader>
            <CardContent>
                <div v-if="recentAnalyses.length" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="px-4 py-3 text-left font-medium">Brief</th>
                                <th class="px-4 py-3 text-left font-medium">Model</th>
                                <th class="px-4 py-3 text-right font-medium">Prompt Tokens</th>
                                <th class="px-4 py-3 text-right font-medium">Completion Tokens</th>
                                <th class="px-4 py-3 text-right font-medium">Total Tokens</th>
                                <th class="px-4 py-3 text-right font-medium">Cost</th>
                                <th class="px-4 py-3 text-left font-medium">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="analysis in recentAnalyses" :key="analysis.id">
                                <td class="px-4 py-3 font-medium">{{ analysis.brief_title || 'N/A' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-secondary px-2 py-1 text-xs font-medium">
                                        {{ analysis.model_used }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">{{ formatNumber(analysis.prompt_tokens) }}</td>
                                <td class="px-4 py-3 text-right">{{ formatNumber(analysis.completion_tokens) }}</td>
                                <td class="px-4 py-3 text-right">{{ formatNumber(analysis.total_tokens) }}</td>
                                <td class="px-4 py-3 text-right font-medium">{{ formatCurrency(analysis.cost_usd) }}</td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ analysis.created_at ? new Date(analysis.created_at).toLocaleDateString() : 'N/A' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-muted-foreground text-center py-8">No analyses recorded yet.</p>
            </CardContent>
        </Card>
    </div>
</template>
