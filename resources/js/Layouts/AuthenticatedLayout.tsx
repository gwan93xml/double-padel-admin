import { AppSidebar } from '@/Components/AppSidebar';
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator } from '@/Components/ui/breadcrumb';
import { Separator } from '@/Components/ui/separator';
import { SidebarInset, SidebarProvider, SidebarTrigger } from '@/Components/ui/sidebar';
import { Toaster } from '@/Components/ui/toaster';
import { Link } from '@inertiajs/react';
import React, { PropsWithChildren, ReactNode, useState } from 'react';
import { Header } from './Header';
type BreadcrumbItemType = {
    label: string;
    href: string;
}
export default function AuthenticatedLayout({
    breadcrumbs,
    children,
}: PropsWithChildren<
    {
        breadcrumbs: BreadcrumbItemType[],
    }
>) {
    return (
        <>
            <SidebarProvider  >
                <div className="flex w-full overflow-hidden">
                    <AppSidebar />
                    <SidebarInset className='flex flex-col flex-1 overflow-hidden'>
                        <Header
                            breadcrumbs={breadcrumbs}
                            showClock={true}
                        />
                        {children}
                        <Toaster />
                    </SidebarInset>
                </div>
            </SidebarProvider >
        </>
    );
}
