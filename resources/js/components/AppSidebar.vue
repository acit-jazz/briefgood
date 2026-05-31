<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BookOpen, UploadCloudIcon, LayoutGrid, BriefcaseBusiness } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { index as briefsIndex, create as briefsCreate } from '@/routes/briefs';
import { index as businessUnitsIndex } from '@/routes/business-units';
import { index as pitchPipelineIndex } from '@/routes/pitch-pipeline';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Briefs',
        href: briefsIndex(),
        icon: BookOpen,
    },
    {
        title: 'Upload Brief',
        href: briefsCreate(),
        icon: UploadCloudIcon,
    },
    {
        title: 'Business Units',
        href: businessUnitsIndex(),
        icon: BriefcaseBusiness,
    },
    {
        title: 'Pitch Pipeline',
        href: pitchPipelineIndex(),
        icon: BriefcaseBusiness,
    },
];

const footerNavItems: NavItem[] = [
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
