<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowUpRight, Briefcase, FileText, Target, TrendingUp, Zap } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { create } from '@/routes/briefs';
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

    <div class="space-y-6 p-4">
        <section class="relative overflow-hidden rounded-[3rem] bg-emerald-700 text-white shadow-2xl">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,theme(colors.white)/10,transparent_35%)]" />
            <div class="relative grid gap-8 px-6 py-10 lg:grid-cols-[1.4fr_1fr] lg:px-12 lg:py-16">
                <div class="space-y-6">
                    <div class="max-w-xl">
                        <p class="text-4xl font-semibold leading-tight lg:text-5xl">
                            Every great collaboration starts with the right brief.
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <Button as-child size="lg" class="rounded-full bg-white px-6 text-sm font-semibold text-emerald-900 shadow-sm hover:bg-slate-100">
                            <Link :href="create()" class="inline-flex items-center gap-3">
                                Create Brief
                                <ArrowUpRight class="size-4 text-emerald-900" />
                            </Link>
                        </Button>
                    </div>
                </div>

                <div class="relative grid h-full place-items-center overflow-hidden rounded-[3rem] bg-white p-8 shadow-xl">
                    <div class="absolute right-6 top-6 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-700 text-white shadow-md">
                        <ArrowUpRight class="size-5" />
                    </div>
                    <div class="flex h-full w-full flex-col items-center justify-center gap-4 rounded-[2.5rem] border border-slate-200 bg-white p-8 text-center shadow-sm">
                        <div class="rounded-3xl bg-slate-50 p-5 shadow-inner">
                            <FileText class="size-8 text-slate-900" />
                        </div>
                        <div class="text-6xl font-bold tracking-tight text-slate-950 lg:text-7xl">95</div>
                        <div class="text-lg font-medium text-slate-600">Brief Created</div>
                    </div>
                </div>
            </div>
        </section>

        <div class="space-y-4">
            <div class="flex items-center justify-between gap-4 rounded-3xl border border-border bg-card p-5 shadow-sm">
                <div>
                    <h2 class="text-lg font-semibold">Highlights</h2>
                    <p class="text-sm text-muted-foreground">Overview of your current brief and pitch metrics.</p>
                </div>
                <div class="inline-flex items-center gap-2 rounded-full border border-border bg-background px-4 py-2 text-sm text-muted-foreground">
                    <span>See all stats</span>
                    <ArrowUpRight class="size-4" />
                </div>
            </div>

            <div class="flex gap-4 overflow-x-auto pb-4">
                <div class="min-w-[18rem] snap-center rounded-[2rem] border border-border bg-white p-6 shadow-xl">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm uppercase tracking-[0.24em] text-muted-foreground">Active Briefs</p>
                            <p class="mt-4 text-4xl font-semibold">{{ stats.active_briefs }}</p>
                        </div>
                        <div class="rounded-3xl bg-emerald-700 p-3 text-white">
                            <Briefcase class="size-5" />
                        </div>
                    </div>
                </div>
                <div class="min-w-[18rem] snap-center rounded-[2rem] border border-border bg-white p-6 shadow-xl">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm uppercase tracking-[0.24em] text-muted-foreground">Win Rate</p>
                            <p class="mt-4 text-4xl font-semibold">{{ stats.win_rate }}%</p>
                        </div>
                        <div class="rounded-3xl bg-slate-900 p-3 text-white">
                            <TrendingUp class="size-5" />
                        </div>
                    </div>
                </div>
                <div class="min-w-[18rem] snap-center rounded-[2rem] border border-border bg-white p-6 shadow-xl">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm uppercase tracking-[0.24em] text-muted-foreground">Pending Pitches</p>
                            <p class="mt-4 text-4xl font-semibold">{{ stats.pending_pitches }}</p>
                        </div>
                        <div class="rounded-3xl bg-slate-900 p-3 text-white">
                            <Target class="size-5" />
                        </div>
                    </div>
                </div>
                <div class="min-w-[18rem] snap-center rounded-[2rem] border border-border bg-white p-6 shadow-xl">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm uppercase tracking-[0.24em] text-muted-foreground">Won / Lost</p>
                            <p class="mt-4 text-4xl font-semibold">{{ stats.won }} / {{ stats.lost }}</p>
                        </div>
                        <div class="rounded-3xl bg-slate-900 p-3 text-white">
                            <Zap class="size-5" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
