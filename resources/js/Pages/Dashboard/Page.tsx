import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { 
    Building2, 
    ShoppingCart, 
    MessageSquare, 
    Users, 
    User, 
    BookOpen, 
    DollarSign, 
    Star,
    TrendingUp
} from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { BarChart, Bar, CartesianGrid, XAxis, YAxis, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import { ChartContainer, ChartTooltip, ChartTooltipContent, type ChartConfig } from '@/components/ui/chart';

type PageProps = {
    statistics: {
        totalVenues: number;
        totalBookings: number;
        totalReviews: number;
        totalUsers: number;
        totalAdmins: number;
        totalMembers: number;
        totalBlogs: number;
        totalRevenue: number;
    };
    bookingsByStatus: Record<string, number>;
    topRatedVenues: Array<{ id: string; name: string; average_rating: number }>;
    recentBookings: Array<{
        id: string;
        booking_number: string;
        user_id: string;
        status: string;
        total_price: number;
        created_at: string;
        user?: { name: string };
    }>;
    averageRatings: {
        cleanliness: number | null;
        court_condition: number | null;
        communication: number | null;
    };
    bookingTrend: Array<{
        month: string;
        count: number;
        revenue: number;
    }>;
};

const statusColors: Record<string, string> = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'confirmed': 'bg-blue-100 text-blue-800',
    'completed': 'bg-green-100 text-green-800',
    'cancelled': 'bg-red-100 text-red-800',
};

export default function Page({ 
    statistics, 
    bookingsByStatus, 
    topRatedVenues, 
    recentBookings,
    averageRatings,
    bookingTrend
}: PageProps) {
    const statCards = [
        {
            icon: <Building2 className="h-5 w-5" />,
            title: 'Total Venue',
            value: statistics.totalVenues,
            subtitle: 'Venue terdaftar',
            color: 'emerald',
            href: '/venue',
        },
        {
            icon: <ShoppingCart className="h-5 w-5" />,
            title: 'Total Booking',
            value: statistics.totalBookings,
            subtitle: 'Pemesanan',
            color: 'blue',
            href: '/booking',
        },
        {
            icon: <MessageSquare className="h-5 w-5" />,
            title: 'Total Review',
            value: statistics.totalReviews,
            subtitle: 'Review pelanggan',
            color: 'purple',
            href: '/review',
        },
        {
            icon: <Users className="h-5 w-5" />,
            title: 'Total User',
            value: statistics.totalUsers,
            subtitle: 'User terdaftar',
            color: 'amber',
            href: '/user',
        },
        {
            icon: <User className="h-5 w-5" />,
            title: 'Admin',
            value: statistics.totalAdmins,
            subtitle: 'Admin pengguna',
            color: 'rose',
            href: '#',
        },
        {
            icon: <User className="h-5 w-5" />,
            title: 'Member',
            value: statistics.totalMembers,
            subtitle: 'Member pengguna',
            color: 'cyan',
            href: '#',
        },
        {
            icon: <BookOpen className="h-5 w-5" />,
            title: 'Total Blog',
            value: statistics.totalBlogs,
            subtitle: 'Blog dipublikasikan',
            color: 'indigo',
            href: '/blog',
        },
        {
            icon: <DollarSign className="h-5 w-5" />,
            title: 'Total Revenue',
            value: `Rp ${(statistics.totalRevenue / 1000000).toFixed(2)}M`,
            subtitle: 'Dari booking selesai',
            color: 'green',
            href: '#',
        },
    ];

    const colorClasses: Record<string, { bg: string; text: string; border: string }> = {
        emerald: { bg: 'bg-emerald-50 dark:bg-emerald-900/10', text: 'text-emerald-700 dark:text-emerald-400', border: 'border-emerald-100 dark:border-emerald-900' },
        blue: { bg: 'bg-blue-50 dark:bg-blue-900/10', text: 'text-blue-700 dark:text-blue-400', border: 'border-blue-100 dark:border-blue-900' },
        purple: { bg: 'bg-purple-50 dark:bg-purple-900/10', text: 'text-purple-700 dark:text-purple-400', border: 'border-purple-100 dark:border-purple-900' },
        amber: { bg: 'bg-amber-50 dark:bg-amber-900/10', text: 'text-amber-700 dark:text-amber-400', border: 'border-amber-100 dark:border-amber-900' },
        rose: { bg: 'bg-rose-50 dark:bg-rose-900/10', text: 'text-rose-700 dark:text-rose-400', border: 'border-rose-100 dark:border-rose-900' },
        cyan: { bg: 'bg-cyan-50 dark:bg-cyan-900/10', text: 'text-cyan-700 dark:text-cyan-400', border: 'border-cyan-100 dark:border-cyan-900' },
        indigo: { bg: 'bg-indigo-50 dark:bg-indigo-900/10', text: 'text-indigo-700 dark:text-indigo-400', border: 'border-indigo-100 dark:border-indigo-900' },
        green: { bg: 'bg-green-50 dark:bg-green-900/10', text: 'text-green-700 dark:text-green-400', border: 'border-green-100 dark:border-green-900' },
    };

    return (
        <div className="h-full p-5 space-y-6">
            {/* Statistics Cards */}
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                {statCards.map((stat, idx) => {
                    const colors = colorClasses[stat.color as keyof typeof colorClasses];
                    return (
                        <Card key={idx} className={`overflow-hidden ${colors.border}`}>
                            <CardHeader className={`${colors.bg} pb-2`}>
                                <CardTitle className={`${colors.text} flex items-center gap-2 text-sm`}>
                                    {stat.icon}
                                    {stat.title}
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="pt-4">
                                <div className="text-2xl font-bold text-slate-900 dark:text-slate-50">{stat.value}</div>
                                <p className="text-xs text-slate-500 dark:text-slate-400">{stat.subtitle}</p>
                            </CardContent>
                        </Card>
                    );
                })}
            </div>

            {/* Two Column Layout */}
            <div className="grid gap-6 lg:grid-cols-2">
                {/* Booking by Status */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <TrendingUp className="h-5 w-5" />
                            Status Booking
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-3">
                            {Object.entries(bookingsByStatus).map(([status, count]) => (
                                <div key={status} className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <span className={`px-2 py-1 rounded-full text-xs font-medium ${statusColors[status] || 'bg-gray-100'}`}>
                                            {status.charAt(0).toUpperCase() + status.slice(1)}
                                        </span>
                                    </div>
                                    <span className="font-semibold text-slate-900 dark:text-slate-50">{count}</span>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Average Ratings */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Star className="h-5 w-5" />
                            Rating Rata-rata
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            <div>
                                <div className="flex justify-between mb-1">
                                    <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Kebersihan</span>
                                    <span className="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                        {averageRatings?.cleanliness || 'N/A'}/5
                                    </span>
                                </div>
                                <div className="w-full bg-gray-200 rounded-full h-2">
                                    <div 
                                        className="bg-yellow-400 h-2 rounded-full" 
                                        style={{ width: `${((averageRatings.cleanliness || 0) / 5) * 100}%` }}
                                    ></div>
                                </div>
                            </div>
                            <div>
                                <div className="flex justify-between mb-1">
                                    <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Kondisi Lapangan</span>
                                    <span className="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                        {averageRatings.court_condition || 'N/A'}/5
                                    </span>
                                </div>
                                <div className="w-full bg-gray-200 rounded-full h-2">
                                    <div 
                                        className="bg-blue-400 h-2 rounded-full" 
                                        style={{ width: `${((averageRatings.court_condition || 0) / 5) * 100}%` }}
                                    ></div>
                                </div>
                            </div>
                            <div>
                                <div className="flex justify-between mb-1">
                                    <span className="text-sm font-medium text-slate-700 dark:text-slate-300">Komunikasi</span>
                                    <span className="text-sm font-semibold text-slate-900 dark:text-slate-50">
                                        {/* {averageRatings.communication?.toFixed(1) || 'N/A'}/5 */}
                                    </span>
                                </div>
                                <div className="w-full bg-gray-200 rounded-full h-2">
                                    <div 
                                        className="bg-green-400 h-2 rounded-full" 
                                        style={{ width: `${((averageRatings.communication || 0) / 5) * 100}%` }}
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Top Rated Venues */}
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <Star className="h-5 w-5" />
                        Top 5 Venue Terbaik
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="space-y-3">
                        {topRatedVenues.length > 0 ? (
                            topRatedVenues.map((venue) => (
                                <div key={venue.id} className="flex items-center justify-between p-2 bg-slate-50 dark:bg-slate-900/20 rounded-lg">
                                    <span className="font-medium text-slate-900 dark:text-slate-50">{venue.name}</span>
                                    <span className="flex items-center gap-1">
                                        {'⭐'.repeat(Math.round(venue.average_rating))}
                                        <span className="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                            {venue.average_rating.toFixed(1)}/5
                                        </span>
                                    </span>
                                </div>
                            ))
                        ) : (
                            <p className="text-slate-500 dark:text-slate-400">Belum ada venue dengan rating</p>
                        )}
                    </div>
                </CardContent>
            </Card>

            {/* Recent Bookings */}
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <ShoppingCart className="h-5 w-5" />
                        10 Booking Terbaru
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th className="text-left py-2 px-2 font-semibold text-slate-700 dark:text-slate-300">No Booking</th>
                                    <th className="text-left py-2 px-2 font-semibold text-slate-700 dark:text-slate-300">Pelanggan</th>
                                    <th className="text-left py-2 px-2 font-semibold text-slate-700 dark:text-slate-300">Status</th>
                                    <th className="text-left py-2 px-2 font-semibold text-slate-700 dark:text-slate-300">Total</th>
                                    <th className="text-left py-2 px-2 font-semibold text-slate-700 dark:text-slate-300">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-200 dark:divide-slate-700">
                                {recentBookings.map((booking) => (
                                    <tr key={booking.id} className="hover:bg-slate-50 dark:hover:bg-slate-900/20">
                                        <td className="py-2 px-2 text-slate-900 dark:text-slate-50">{booking.booking_number}</td>
                                        <td className="py-2 px-2 text-slate-900 dark:text-slate-50">{booking.user?.name || '-'}</td>
                                        <td className="py-2 px-2">
                                            <span className={`px-2 py-1 rounded text-xs font-medium ${statusColors[booking.status] || 'bg-gray-100'}`}>
                                                {booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}
                                            </span>
                                        </td>
                                        <td className="py-2 px-2 font-semibold text-slate-900 dark:text-slate-50">
                                            Rp {(booking.total_price / 1000).toFixed(0)}K
                                        </td>
                                        <td className="py-2 px-2 text-slate-600 dark:text-slate-400">
                                            {new Date(booking.created_at).toLocaleDateString('id-ID')}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            {/* Booking Trend */}
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <TrendingUp className="h-5 w-5" />
                        Trend Booking 6 Bulan Terakhir
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    {bookingTrend.length > 0 ? (
                        <ResponsiveContainer width="100%" height={300}>
                            <BarChart data={bookingTrend} margin={{ top: 20, right: 30, left: 0, bottom: 20 }}>
                                <CartesianGrid strokeDasharray="3 3" stroke="rgba(0,0,0,0.1)" />
                                <XAxis 
                                    dataKey="month" 
                                    tick={{ fill: 'currentColor', fontSize: 12 }}
                                    axisLine={{ stroke: 'rgba(0,0,0,0.1)' }}
                                />
                                <YAxis 
                                    yAxisId="left"
                                    tick={{ fill: 'currentColor', fontSize: 12 }}
                                    axisLine={{ stroke: 'rgba(0,0,0,0.1)' }}
                                    label={{ value: 'Jumlah Booking', angle: -90, position: 'insideLeft' }}
                                />
                                <YAxis 
                                    yAxisId="right" 
                                    orientation="right"
                                    tick={{ fill: 'currentColor', fontSize: 12 }}
                                    axisLine={{ stroke: 'rgba(0,0,0,0.1)' }}
                                    label={{ value: 'Revenue (Juta)', angle: 90, position: 'insideRight' }}
                                />
                                <Tooltip 
                                    contentStyle={{ 
                                        backgroundColor: 'rgba(255, 255, 255, 0.95)', 
                                        border: '1px solid rgba(0,0,0,0.1)',
                                        borderRadius: '8px'
                                    }}
                                    formatter={(value, name) => {
                                        if (name === 'revenue') {
                                            return [`Rp ${(value / 1000000).toFixed(2)}M`, 'Revenue'];
                                        }
                                        return [value, 'Booking'];
                                    }}
                                />
                                <Legend />
                                <Bar yAxisId="left" dataKey="count" fill="#10b981" name="Jumlah Booking" radius={[8, 8, 0, 0]} />
                                <Bar yAxisId="right" dataKey="revenue" fill="#3b82f6" name="Revenue" radius={[8, 8, 0, 0]} />
                            </BarChart>
                        </ResponsiveContainer>
                    ) : (
                        <p className="text-slate-500 dark:text-slate-400 text-center py-8">Tidak ada data booking</p>
                    )}
                </CardContent>
            </Card>
        </div>
    );
}

Page.layout = (page: any) => <AuthenticatedLayout
    breadcrumbs={[
        { label: 'Dashboard', href: '/dashboard' },
    ]}
>{page}</AuthenticatedLayout>;
