<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $companies = 0;
        $warehouses = 0;
        $divisions = 0;
        $customers = 0;
        $vendors = 0;
        $items = 0;

        return inertia("Dashboard/Page", [
            'counts' => [
                'companies' => $companies,
                'warehouses' => $warehouses,
                'divisions' => $divisions,
                'customers' => $customers,
                'vendors' => $vendors,
                'items' => $items,
            ],
        ]);
    }
}
