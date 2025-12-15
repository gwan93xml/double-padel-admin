import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import StatsCard from './Components/StatsCard';
import { BanknoteIcon, BookIcon, BookOpenCheckIcon, Building2, DatabaseIcon, DollarSign, Layers, Package, ShoppingCartIcon, SquareSlash, StoreIcon, TablePropertiesIcon, ThermometerIcon, Truck, Users, Warehouse } from 'lucide-react';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { Link } from '@inertiajs/react';
import PurchaseChart from './Components/PurchaseChart';
import SalesChart from './Components/SalesChart';
interface StatsCardProps {
    icon: JSX.Element;
    title: string;
    subtitle: string;
    iconBackground: string;
}
type PageProps = {
    counts: {
        companies: number;
        divisions: number;
        warehouses: number;
        customers: number;
        vendors: number;
        items: number;
    }
}

export default function Page({ counts }: PageProps) {
    return (

        <div className="h-full p-5 ">
            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Card className="overflow-hidden border-emerald-100 dark:border-emerald-900">
                    <CardHeader className="bg-emerald-50 dark:bg-emerald-900/10 pb-2">
                        <CardTitle className="text-emerald-700 dark:text-emerald-400 flex items-center gap-2">
                            <Building2 className="h-5 w-5" />
                            Perusahaan
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="pt-4">
                        <div className="text-3xl font-bold text-slate-900 dark:text-slate-50">{counts.companies}</div>
                        <p className="text-xs text-slate-500 dark:text-slate-400">Total perusahaan terdaftar</p>
                    </CardContent>
                    <CardFooter className="border-t p-2">
                        <Link
                            href="/admin/company"
                            className="text-xs text-emerald-600 dark:text-emerald-400 hover:underline"
                        >
                            Lihat detail
                        </Link>
                    </CardFooter>
                </Card>

                <Card className="overflow-hidden border-purple-100 dark:border-purple-900">
                    <CardHeader className="bg-purple-50 dark:bg-purple-900/10 pb-2">
                        <CardTitle className="text-purple-700 dark:text-purple-400 flex items-center gap-2">
                            <Layers className="h-5 w-5" />
                            Divisi
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="pt-4">
                        <div className="text-3xl font-bold text-slate-900 dark:text-slate-50">{counts.divisions}</div>
                        <p className="text-xs text-slate-500 dark:text-slate-400">Total divisi aktif</p>
                    </CardContent>
                    <CardFooter className="border-t p-2">
                        <Link
                            href="/admin/division"
                            className="text-xs text-purple-600 dark:text-purple-400 hover:underline"
                        >
                            Lihat detail
                        </Link>
                    </CardFooter>
                </Card>

                <Card className="overflow-hidden border-amber-100 dark:border-amber-900">
                    <CardHeader className="bg-amber-50 dark:bg-amber-900/10 pb-2">
                        <CardTitle className="text-amber-700 dark:text-amber-400 flex items-center gap-2">
                            <Warehouse className="h-5 w-5" />
                            Gudang
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="pt-4">
                        <div className="text-3xl font-bold text-slate-900 dark:text-slate-50">{counts.warehouses}</div>
                        <p className="text-xs text-slate-500 dark:text-slate-400">Total gudang operasional</p>
                    </CardContent>
                    <CardFooter className="border-t p-2">
                        <Link
                            href="/admin/warehouse"
                            className="text-xs text-amber-600 dark:text-amber-400 hover:underline"
                        >
                            Lihat detail
                        </Link>
                    </CardFooter>
                </Card>

                <Card className="overflow-hidden border-blue-100 dark:border-blue-900">
                    <CardHeader className="bg-blue-50 dark:bg-blue-900/10 pb-2">
                        <CardTitle className="text-blue-700 dark:text-blue-400 flex items-center gap-2">
                            <Users className="h-5 w-5" />
                            Pelanggan
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="pt-4">
                        <div className="text-3xl font-bold text-slate-900 dark:text-slate-50">{counts.customers}</div>
                        <p className="text-xs text-slate-500 dark:text-slate-400">Total pelanggan aktif</p>
                    </CardContent>
                    <CardFooter className="border-t p-2">
                        <Link
                            href="/admin/customer"
                            className="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                        >
                            Lihat detail
                        </Link>
                    </CardFooter>
                </Card>

                <Card className="overflow-hidden border-rose-100 dark:border-rose-900">
                    <CardHeader className="bg-rose-50 dark:bg-rose-900/10 pb-2">
                        <CardTitle className="text-rose-700 dark:text-rose-400 flex items-center gap-2">
                            <Truck className="h-5 w-5" />
                            Vendor
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="pt-4">
                        <div className="text-3xl font-bold text-slate-900 dark:text-slate-50">{counts.vendors}</div>
                        <p className="text-xs text-slate-500 dark:text-slate-400">Total vendor terdaftar</p>
                    </CardContent>
                    <CardFooter className="border-t p-2">
                        <Link
                            href="/admin/vendor"
                            className="text-xs text-rose-600 dark:text-rose-400 hover:underline"
                        >
                            Lihat detail
                        </Link>
                    </CardFooter>
                </Card>

                <Card className="overflow-hidden border-cyan-100 dark:border-cyan-900">
                    <CardHeader className="bg-cyan-50 dark:bg-cyan-900/10 pb-2">
                        <CardTitle className="text-cyan-700 dark:text-cyan-400 flex items-center gap-2">
                            <Package className="h-5 w-5" />
                            Item
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="pt-4">
                        <div className="text-3xl font-bold text-slate-900 dark:text-slate-50">{counts.items}</div>
                        <p className="text-xs text-slate-500 dark:text-slate-400">Total item dalam katalog</p>
                    </CardContent>
                    <CardFooter className="border-t p-2">
                        <Link
                            href="/admin/item"
                            className="text-xs text-cyan-600 dark:text-cyan-400 hover:underline"
                        >
                            Lihat detail
                        </Link>
                    </CardFooter>
                </Card>
            </div>
            <div className="mt-6">
                <SalesChart />
            </div>
            <div className="mt-6">
                <PurchaseChart />
            </div>

        </div>
    );
}

Page.layout = (page: any) => <AuthenticatedLayout
    breadcrumbs={[
        { label: 'Dashboard', href: '/dashboard' },
    ]}
>{page}</AuthenticatedLayout>;
