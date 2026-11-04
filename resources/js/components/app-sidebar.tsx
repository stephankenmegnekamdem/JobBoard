import { Link, usePage } from '@inertiajs/react';
import { Briefcase, FolderGit2, LayoutGrid, Shield } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { TeamSwitcher } from '@/components/team-switcher';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

export function AppSidebar() {
    const { homeUrl, auth } = usePage().props;

    const mainNavItems: NavItem[] = auth.user.is_admin
        ? [
              { title: 'Admin', href: '/admin', icon: Shield },
              { title: 'Job board', href: '/', icon: Briefcase },
          ]
        : [
              { title: 'Dashboard', href: homeUrl, icon: LayoutGrid },
              { title: 'Job board', href: '/', icon: Briefcase },
          ];

    const footerNavItems: NavItem[] = [
        {
            title: 'Source code',
            href: 'https://github.com/stephankenmegnekamdem/JobBoard',
            icon: FolderGit2,
        },
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={homeUrl} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <TeamSwitcher />
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
