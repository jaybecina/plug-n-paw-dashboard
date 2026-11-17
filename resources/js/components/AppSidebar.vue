<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Coffee,
    FolderGit2,
    Globe,
    LayoutDashboard,
    MapPinned,
    Search,
    Stethoscope,
    TrendingUp,
} from '@lucide/vue';
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
import { clinics, dashboard, searches, supporters, traffic } from '@/routes';
import leads from '@/routes/leads';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    { title: 'Overview', href: dashboard(), icon: LayoutDashboard },
    { title: 'Traffic', href: traffic(), icon: TrendingUp },
    { title: 'Vet searches', href: searches(), icon: Search },
    { title: 'Clinic leads', href: leads.index(), icon: MapPinned },
    { title: 'Clinics', href: clinics(), icon: Stethoscope },
    { title: 'Supporters', href: supporters(), icon: Coffee },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Live website',
        href: 'https://plug-n-paw-web.vercel.app',
        icon: Globe,
    },
    {
        title: 'Source code',
        href: 'https://github.com/jaybecina/plug-n-paw-dashboard',
        icon: FolderGit2,
    },
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
