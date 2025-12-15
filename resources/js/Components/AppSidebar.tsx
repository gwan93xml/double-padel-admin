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
    ShieldIcon,
    ShoppingCartIcon,
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
        permission: ['read-member', 'read-bank-account', 'read-company', 'read-division', 'read-warehouse', 'read-customer', 'read-vendor', 'read-item-category', 'read-item', 'read-type-of-tax', 'read-unit'],
        children: [
            {
                name: "Member",
                link: "/member",
                permission: 'read-member',
            },
            {
                name: "Rekening Bank",
                link: "/admin/bank-account",
                permission: 'read-bank-account',
            },

            {
                name: "Perusahaan",
                link: "/admin/company",
                permission: 'read-company',
            },

            {
                name: "Divisi",
                link: "/admin/division",
                permission: 'read-division',
            },
            {
                name: "Gudang",
                link: "/admin/warehouse",
                permission: 'read-warehouse',
            },
            {
                name: "Pelanggan",
                link: "/admin/customer",
                permission: 'read-customer',
            },
            {
                name: "Vendor",
                link: "/admin/vendor",
                permission: 'read-vendor',

            },
            {
                name: "Satuan",
                link: "/admin/unit",
                permission: 'read-unit',
            },
            {
                name: "Kategori Item",
                link: "/admin/item-category",
                permission: 'read-item-category',
            },
            {
                name: "Item",
                link: "/admin/item",
                permission: 'read-item',
            },
            {
                name: "Item Non Stok",
                link: "/admin/item-non-stock",
                permission: 'read-item-non-stock',
            },
            {
                name: "Jenis Pajak",
                link: "/admin/type-of-tax",
                permission: 'read-type-of-tax',
            },
        ]
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
                            <div >
                                <img
                                    src={`/storage/${setting.logo}`}
                                    className="items-center justify-center h-[80px]" />
                            </div>

                            <div className="flex flex-col gap-0.5 leading-none">
                                <span className="font-semibold">{setting.app_name}</span>
                                <span className="">v1.0.0</span>
                            </div>
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
