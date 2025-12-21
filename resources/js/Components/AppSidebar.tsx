import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupContent,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from "@/Components/ui/sidebar";
import { Link, usePage } from "@inertiajs/react";
import {
    BanknoteIcon,
    BookIcon,
    BookOpenCheckIcon,
    ChevronRight,
    DatabaseIcon,
    GaugeIcon,
    NewspaperIcon,
    ShieldIcon,
    ShoppingCartIcon,
    Star,
} from "lucide-react";
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from "./ui/collapsible";
import can from "@/hooks/can";
import { NavUser } from "./nav-user";
const navList = [
    {
        name: "Dashboard",
        icon: <GaugeIcon />,
        link: "/",
    },
    {
        name: "Master File",
        icon: <DatabaseIcon />,
        link: "#",
        permission: ['read-member', 'read-venue', 'read-court', 'read-court-schedule', 'read-payment-method'],
        children: [
            {
                name: "Member",
                link: "/member",
                permission: 'read-member',
            },
            {
                name: "Venue",
                link: "/venue",
                permission: 'read-venue',
            },
            {
                name: "Lapangan",
                link: "/court",
                permission: 'read-court',
            },
            {
                name: "Jadwal Lapangan",
                link: "/court-schedule",
                permission: 'read-court-schedule',
            },
            {
                name: "Metode Pembayaran",
                link: "/payment-method",
                permission: 'read-payment-method',
            },
        ]
    },
    {
        name: "Transaksi",
        icon: <ShoppingCartIcon />,
        link: "#",
        permission: ['read-booking', 'read-venue', 'read-court', 'read-court-schedule', 'read-payment-method'],
        children: [
            {
                name: "Booking",
                link: "/booking",
                permission: 'read-booking',
            },
        ]
    },
    {
        name: "CMS",
        icon: <NewspaperIcon />,
        link: "#",
        permission: ['read-blog-category', 'read-blog', 'read-testimony'],
        children: [
            {
                name: "Kategori Blog",
                link: "/blog-category",
                permission: 'read-blog-category',
            },
            {
                name: "Blog",
                link: "/blog",
                permission: 'read-blog',
            },
            {
                name: "Testimoni",
                link: "/testimony",
                permission: 'read-testimony',
            }
        ]
    },
    {
        name: "Review",
        icon: <Star />,
        link: "/review",
        permission: ['read-review']
    },
    {
        name: "Pengaturan",
        icon: <BookOpenCheckIcon />,
        link: "/",
        children: [
            {
                name: "Utama",
                link: "/setting",
                permission: 'read-setting',
            },
            {
                name: "Aksi",
                link: "/action",
                permission: 'read-action',
            },
            {
                name: "Module",
                link: "/module",
                permission: 'read-module',
            },
            {
                name: "Role",
                link: "/role",
                permission: 'read-role',
            },
            {
                name: "User",
                link: "/user",
                permission: 'read-user',
            },
            {
                name: "Whitelist IP",
                icon: <ShieldIcon />,
                link: "/whitelist-ip",
                permission: 'read-whitelist-ip', // Using same permission as user management for now
            },
        ],
    },
];

export function AppSidebar() {
    const { user } = usePage().props.auth as any;
    const setting = usePage().props.setting as any;
    return (
        <Sidebar>
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            size="lg"
                            className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground h-[100px]"
                        >
                            <div className="p-12 bg-black dark:bg-transparent rounded-lg">
                                <img
                                    src={`/assets/images/logo.png`}
                                    className="rounded" />
                            </div>

                            {/* <div className="flex flex-col gap-0.5 leading-none">
                                <span className="font-semibold">Double Padel</span>
                                <span className="">v1.0.0</span>
                            </div> */}
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>
            <SidebarContent>
                <SidebarGroup>
                    <SidebarGroupContent>
                        <SidebarMenu>
                            {navList.map((nav: any, index) => {
                                if (nav.children && can(nav.permission)) {
                                    return (
                                        <Collapsible
                                            key={index}
                                            className={`group/collapsible`}
                                        >
                                            <SidebarMenuItem>
                                                <CollapsibleTrigger asChild>
                                                    <SidebarMenuButton>
                                                        {nav.icon}
                                                        <span>{nav.name}</span>
                                                        <ChevronRight
                                                            className={`ml-auto transition-transform group-data-[state=open]/collapsible:rotate-90`}
                                                        />
                                                    </SidebarMenuButton>
                                                </CollapsibleTrigger>
                                                <CollapsibleContent>
                                                    <SidebarMenuSub>
                                                        {nav.children.map(
                                                            (child: any, index: number) => {
                                                                return can(child.permission) ? (
                                                                    <SidebarMenuSubItem
                                                                        key={index}
                                                                    >
                                                                        <SidebarMenuSubButton
                                                                            asChild
                                                                        >
                                                                            <Link href={child.link}>
                                                                                {child.name}
                                                                            </Link>
                                                                        </SidebarMenuSubButton>
                                                                    </SidebarMenuSubItem>
                                                                ) : null
                                                            }
                                                        )}
                                                    </SidebarMenuSub>
                                                </CollapsibleContent>
                                            </SidebarMenuItem>
                                        </Collapsible>
                                    )
                                } else {
                                    return can(nav.permission) ? (
                                        <SidebarMenuItem key={index}>
                                            <SidebarMenuButton asChild>
                                                <Link href={nav.link}>
                                                    {nav.icon}
                                                    <span>{nav.name}</span>
                                                </Link>
                                            </SidebarMenuButton>
                                        </SidebarMenuItem>
                                    ) : null
                                }
                            })}
                        </SidebarMenu>
                    </SidebarGroupContent>
                </SidebarGroup>
            </SidebarContent>
            <SidebarFooter>
                <NavUser user={user} />
            </SidebarFooter>
        </Sidebar>
    );
}
