<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Debt;
use App\Models\PayDebt;
use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string',
            'app_title' => 'required|string',
            'company_name' => 'required|string',
            'address' => 'nullable|string',
            'logo' => 'nullable|string',
            'favicon' => 'nullable|string',
            'home_hero_image' => 'nullable|string',
            'home_navigations' => 'nullable|array',
            'booking_url' => 'nullable|string',
        ], [
            'app_name.required' => 'Kolom nama aplikasi harus diisi',
            'app_title.required' => 'Kolom judul aplikasi harus diisi',
            'company_name.required' => 'Kolom nama perusahaan harus diisi',
        ]);
        DB::beginTransaction();
        try {
            $setting = Setting::first();
            if ($setting) {
                $setting->update([
                    'app_name' => $request->app_name,
                    'app_title' => $request->app_title,
                    'company_name' => $request->company_name,
                    'address' => $request->address,
                    'logo' => $request->logo ?? $setting->logo,
                    'favicon' => $request->favicon ?? $setting->favicon,
                    'home_hero_image' => $request->home_hero_image,
                    'home_navigations' => $request->home_navigations,
                    'booking_url' => $request->booking_url,
                ]);
            } else {
                Setting::create([
                    'app_name' => $request->app_name,
                    'app_title' => $request->app_title,
                    'company_name' => $request->company_name,
                    'address' => $request->address,
                    'logo' => $request->logo,
                    'favicon' => $request->favicon,
                    'home_hero_image' => $request->home_hero_image,
                    'home_navigations' => $request->home_navigations,
                    'booking_url' => $request->booking_url,
                ]);
            }
            DB::commit();
            return back();
        } catch (Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }
}
