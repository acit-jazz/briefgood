<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Pencil, Plus } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { create, edit, index } from '@/routes/business-units';
import { dashboard } from '@/routes';

type BusinessUnit = {
    id: string;
    name: string;
    description?: string;
    is_active: boolean;
    category?: { name: string };
    services?: Array<{ name: string }>;
};

defineProps<{
    businessUnits: { data: BusinessUnit[] };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Business Units', href: index() },
        ],
    },
});
</script>

<template>
    <Head title="Business Units" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Business Units</h1>
                <p class="text-sm text-muted-foreground">
                    Manage agency units, services, and PIC assignments.
                </p>
            </div>
            <Button as-child>
                <Link :href="create()">
                    <Plus class="mr-2 size-4" />
                    Add Unit
                </Link>
            </Button>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <Card
                v-for="unit in businessUnits.data"
                :key="unit.id"
            >
                <CardHeader class="flex flex-row items-start justify-between">
                    <div>
                        <CardTitle>{{ unit.name }}</CardTitle>
                        <p class="text-xs text-muted-foreground">
                            {{ unit.category?.name }}
                        </p>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        as-child
                    >
                        <Link :href="edit(unit.id)">
                            <Pencil class="size-4" />
                        </Link>
                    </Button>
                </CardHeader>
                <CardContent class="space-y-3">
                    <p class="text-sm text-muted-foreground line-clamp-2">
                        {{ unit.description }}
                    </p>
                    <div class="flex flex-wrap gap-1">
                        <Badge
                            v-for="service in unit.services?.slice(0, 3)"
                            :key="service.name"
                            variant="secondary"
                            class="text-xs"
                        >
                            {{ service.name }}
                        </Badge>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
