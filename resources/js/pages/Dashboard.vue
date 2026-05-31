<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Briefcase, Target, TrendingUp, Zap } from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { create, index, show } from '@/routes/briefs';
import { dashboard } from '@/routes';

type Stats = {
    active_briefs: number;
    won: number;
    lost: number;
    win_rate: number;
    pending_pitches: number;
};

type RecentBrief = {
    id: string;
    title: string;
    client_name: string;
    status: string;
    deadline?: string | null;
};

defineProps<{
    stats: Stats;
    pipeline: Record<string, number>;
    recentBriefs: RecentBrief[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">BriefGood</h1>
                <p class="text-sm text-muted-foreground">
                    AI-powered brief standardization & pitch management
                </p>
            </div>
            <Link
                :href="create()"
                class="text-sm font-medium text-primary hover:underline"
            >
                + Upload Brief
            </Link>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium">Active Briefs</CardTitle>
                    <Briefcase class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ stats.active_briefs }}</div>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium">Win Rate</CardTitle>
                    <TrendingUp class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ stats.win_rate }}%</div>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium">Pending Pitches</CardTitle>
                    <Target class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ stats.pending_pitches }}</div>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium">Won / Lost</CardTitle>
                    <Zap class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ stats.won }} / {{ stats.lost }}</div>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Recent Briefs</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3">
                    <Link
                        v-for="brief in recentBriefs"
                        :key="brief.id"
                        :href="show(brief.id)"
                        class="flex items-center justify-between rounded-lg border p-3 text-sm hover:bg-muted/50"
                    >
                        <div>
                            <p class="font-medium">{{ brief.title }}</p>
                            <p class="text-xs text-muted-foreground">{{ brief.client_name }}</p>
                        </div>
                        <span class="text-xs capitalize text-muted-foreground">{{ brief.status }}</span>
                    </Link>
                    <Link
                        :href="index()"
                        class="block text-center text-sm text-primary hover:underline"
                    >
                        View all briefs
                    </Link>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Pipeline by Status</CardTitle>
                </CardHeader>
                <CardContent class="space-y-2">
                    <div
                        v-for="(count, status) in pipeline"
                        :key="status"
                        class="flex items-center justify-between text-sm"
                    >
                        <span class="capitalize">{{ status }}</span>
                        <span class="font-medium">{{ count }}</span>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
