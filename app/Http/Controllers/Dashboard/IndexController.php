<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Venue;
use App\Models\Review;
use App\Models\User;
use App\Models\Blog;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        // Overview Statistics
        $totalVenues = Venue::count();
        $totalBookings = Booking::count();
        $totalReviews = Review::count();
        $totalUsers = User::count();
        $totalAdmins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->count();
        $totalMembers = User::whereHas('roles', function ($query) {
            $query->where('name', 'member');
        })->count();
        $totalBlogs = Blog::count();

        // Booking Statistics
        $bookingsByStatus = Booking::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalRevenue = Booking::where('status', Booking::STATUS_COMPLETED)
            ->sum('total_price');

        // Top Rated Venues
        $topRatedVenues = Venue::where('average_rating', '>', 0)
            ->orderByDesc('average_rating')
            ->limit(5)
            ->get(['id', 'name', 'average_rating']);

        // Recent Bookings
        $recentBookings = Booking::with(['user', 'courtSchedule'])
            ->latest()
            ->limit(10)
            ->get(['id', 'booking_number', 'user_id', 'status', 'total_price', 'created_at']);

        // Average Ratings by Category
        $averageRatings = [
            'cleanliness' => Review::avg('cleanliness_rating'),
            'court_condition' => Review::avg('court_condition_rating'),
            'communication' => Review::avg('communication_rating'),
        ];

        // Monthly Booking Trend (last 6 months)
        $bookingTrend = Booking::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count, SUM(total_price) as revenue')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        return inertia('Dashboard/Page', [
            'statistics' => [
                'totalVenues' => $totalVenues,
                'totalBookings' => $totalBookings,
                'totalReviews' => $totalReviews,
                'totalUsers' => $totalUsers,
                'totalAdmins' => $totalAdmins,
                'totalMembers' => $totalMembers,
                'totalBlogs' => $totalBlogs,
                'totalRevenue' => $totalRevenue,
            ],
            'bookingsByStatus' => $bookingsByStatus,
            'topRatedVenues' => $topRatedVenues,
            'recentBookings' => $recentBookings,
            'averageRatings' => $averageRatings,
            'bookingTrend' => $bookingTrend,
        ]);
    }
}

