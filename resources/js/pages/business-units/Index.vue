<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Pencil, Plus } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { create, edit, index } from '@/routes/business-units';
import  AnimationButton  from '@/components/ui/button/AnimationButton.vue';
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

          <div class="w-fit mx-auto lg:mx-0 flex items-center">
              <AnimationButton
              data-aos="fade-up" data-aos-anchor-placement="top-bottom" data-aos-delay="900"
                :href="create().url" size="40" color="#1C7A56" 
              >
                <span class="text-white">Create Unit</span>
              </AnimationButton>
              <AnimationButton
              data-aos="fade-up" data-aos-anchor-placement="top-bottom" data-aos-delay="900"
                :href="create().url" size="40" color="#E7BA33" 
                :isSquare="true"
              >
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path
                      d="M5 15L15 5"
                      stroke="#000"
                      stroke-width="2"
                      stroke-linecap="round"
                    />
                    <path
                      d="M6.875 5H15V13.125"
                      stroke="#000"
                      stroke-width="2"
                      stroke-linecap="round"
                    />
                  </svg>
              </AnimationButton>
          </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <Card
                v-for="unit in businessUnits.data"
                :key="unit.id"
                class="relative shadow-none border border-[#ECEFF3] overflow-visible"
            >
                <CardHeader class="flex flex-row items-center">
                    <div>
                        <img
                            :src="unit.logo_url"
                            alt="Logo"
                            class="w-10 h-10 rounded-full object-cover mr-3"
                        />  
                    </div>
                    <div>
                        <CardTitle>{{ unit.name }}</CardTitle>
                        <p class="text-xs text-muted-foreground">
                            {{ unit.category?.name }}
                        </p>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="border-12 size-18 rounded-full absolute -right-4 -top-4 border-[#fafafa]"
                        as-child
                    >
                        <Link :href="edit(unit.id)">
                            <Pencil class="size-4" />
                        </Link>
                    </Button>
                </CardHeader>
                <CardContent class="space-y-3">
                    <p class="text-sm text-muted-foreground line-clamp-2 mb-5">
                        {{ unit.description }}
                    </p>
                    <h4 class="text-xs uppercase">Core Capabilities</h4>
                    <div class="flex flex-wrap gap-1">
                        <Badge
                            v-for="service in unit.services?.slice(0, 3)"
                            :key="service.name"
                            variant="secondary"
                            class="text-sm py-1 px-3 rounded-md bg-[#F8FAFC] border-[#E2E8F0]"
                        >
                            {{ service.name }}
                        </Badge>
                    </div>
                    <div class="pt-2 border-t mt-4">
                       <small class="text-xs"> <strong>{{ unit.pic?.name || '-' }}</strong> <br> 
                            <span class="text-gray-500">{{ unit.pic?.email || '-' }}</span>
                        </small>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
