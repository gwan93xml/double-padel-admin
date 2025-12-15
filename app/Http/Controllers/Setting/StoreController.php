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
            'app_name' => 'required',
            'code' => 'required',
            'company_name' => 'required',
            'company_address' => 'required',
            'company_phone' => 'required',
            'vat_paid_to_vendor_chart_of_account_id' => 'required',
            'sales_tax_payable_chart_of_account_id' => 'required',
            'sales_chart_of_account_id' => 'required',
            'purchase_chart_of_account_id' => 'required',
            'receivable_chart_of_account_id' => 'required',
            'debt_chart_of_account_id' => 'required',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'app_name.required' => 'Kolom nama aplikasi harus diisi',
            'code.required' => 'Kolom kode harus diisi',
            'company_name.required' => 'Kolom nama perusahaan harus diisi',
            'company_address.required' => 'Kolom alamat perusahaan harus diisi',
            'company_phone.required' => 'Kolom telepon perusahaan harus diisi',
            'vat_paid_to_vendor_chart_of_account_id.required' => 'Kolom akun pembayaran PPN ke vendor harus diisi',
            'sales_tax_payable_chart_of_account_id.required' => 'Kolom akun pajak penjualan yang harus dibayar harus diisi',
            'sales_chart_of_account_id.required' => 'Kolom akun penjualan harus diisi',
            'purchase_chart_of_account_id.required' => 'Kolom akun pembelian harus diisi',
            'receivable_chart_of_account_id.required' => 'Kolom akun piutang harus diisi',
            'debt_chart_of_account_id.required' => 'Kolom akun hutang harus diisi',
        ]);
        DB::beginTransaction();
        try {
            $setting = Setting::first();
            if ($setting) {
                $setting->update([
                    ...$request->all(),
                    'logo' => $request->hasFile('logo') ? $request->file('logo')->store('logo', 'public') : $setting->logo,
                ]);
            } else {
                Setting::create([
                    ...$request->all(),
                    'logo' => $request->hasFile('logo') ? $request->file('logo')->store('logo', 'public') : null,
                ]);
            }
            DB::commit();
            return back();
        } catch (Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }
}
