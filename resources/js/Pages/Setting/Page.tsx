'use client'

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Card, CardContent } from "@/Components/ui/card";
import FormGroup from "@/Components/ui/form-group";
import { useForm } from "@inertiajs/react";
import { Input } from "@/Components/ui/input";
import { Textarea } from "@/Components/ui/textarea";
import { Button } from "@/Components/ui/button";
import { toast } from "@/hooks/use-toast";
import { FileUploadDropzone } from "@/Components/ui/file-upload-dropzone";
import SearchChartOfAccount from "@/Components/SearchChartOfAccount";
import { SettingType } from "./@types/setting-type";


export default function Page({ setting }: any) {
    const { data, setData, post, errors, processing } = useForm({
        ...setting
    } as SettingType)

    function handleSubmit() {
        post('/admin/setting', {
            onSuccess: () => {
                toast({
                    title: 'Berhasil',
                    description: 'Pengaturan berhasil disimpan'
                })
            }
        })
    }
    return (
        <>
            <div className="p-3">
                <h2 className="text-2xl font-semibold mb-3">
                    Utama
                </h2>
                <Card>
                    <CardContent className="mt-3">
                        <FormGroup
                            label="App. Name"
                            required
                            error={errors.app_name}
                        >
                            <Input
                                value={data.app_name}
                                onChange={(e) => setData('app_name', e.target.value)}
                            />
                        </FormGroup>
                        <FormGroup
                            label="Kode"
                            required
                            error={errors.code}
                        >
                            <Input
                                value={data.code}
                                onChange={(e) => setData('code', e.target.value)}
                            />
                        </FormGroup>
                        <FormGroup
                            label="Nama"
                            required
                        >
                            <Input
                                value={data.company_name}
                                onChange={(e) => setData('company_name', e.target.value)}
                            />
                        </FormGroup>
                        <FormGroup
                            label="Alamat"
                            required
                        >
                            <Textarea
                                value={data.company_address}
                                onChange={(e) => setData('company_address', e.target.value)}
                            />
                        </FormGroup>
                        <FormGroup
                            label="Telepon"
                            required
                        >
                            <Input
                                type="tel"
                                value={data.company_phone}
                                onChange={(e) => setData('company_phone', e.target.value)}
                            />
                        </FormGroup>
                        <FormGroup
                            label="Akun Pajak Masukan"
                            required
                            name="vat_paid_to_vendor_chart_of_account_id"
                        >
                            <SearchChartOfAccount
                                value={{
                                    id: data.vat_paid_to_vendor_chart_of_account_id ?? '',
                                    code: data.vat_paid_to_vendor_chart_of_account?.code ?? '',
                                    name: data.vat_paid_to_vendor_chart_of_account?.name ?? ''
                                }}
                                onChange={(chartOfAccount: any) => {
                                    setData({
                                        ...data,
                                        vat_paid_to_vendor_chart_of_account_id: chartOfAccount.id,
                                        vat_paid_to_vendor_chart_of_account: chartOfAccount
                                    })
                                }}
                            />
                        </FormGroup>
                        <FormGroup
                            label="Akun Pajak Keluaran"
                            required
                            name="sales_tax_payable_chart_of_account_id"
                        >
                            <SearchChartOfAccount
                                value={{
                                    id: data.sales_tax_payable_chart_of_account_id ?? '',
                                    name: data.sales_tax_payable_chart_of_account?.name ?? '',
                                    code: data.sales_tax_payable_chart_of_account?.code ?? ''
                                }}
                                onChange={(chartOfAccount: any) => {
                                    setData({
                                        ...data,
                                        sales_tax_payable_chart_of_account_id: chartOfAccount.id,
                                        sales_tax_payable_chart_of_account: chartOfAccount
                                    })
                                }}
                            />
                        </FormGroup>
                        <FormGroup
                            label="Akun Penjualan"
                            required
                            name="sales_chart_of_account_id"
                        >
                            <SearchChartOfAccount
                                value={{
                                    id: data.sales_chart_of_account_id ?? '',
                                    name: data.sales_chart_of_account?.name ?? '',
                                    code: data.sales_chart_of_account?.code ?? ''
                                }}
                                onChange={(chartOfAccount: any) => {
                                    setData({
                                        ...data,
                                        sales_chart_of_account_id: chartOfAccount.id,
                                        sales_chart_of_account: chartOfAccount
                                    })
                                }}
                            />
                        </FormGroup>
                        <FormGroup
                            label="Akun Pembelian"
                            required
                            name="purchase_chart_of_account_id"
                        >
                            <SearchChartOfAccount
                                value={{
                                    id: data.purchase_chart_of_account_id ?? '',
                                    name: data.purchase_chart_of_account?.name ?? '',
                                    code: data.purchase_chart_of_account?.code ?? ''
                                }}
                                onChange={(chartOfAccount: any) => {
                                    setData({
                                        ...data,
                                        purchase_chart_of_account_id: chartOfAccount.id,
                                        purchase_chart_of_account: chartOfAccount
                                    })
                                }}
                            />
                        </FormGroup>
                        <FormGroup
                            label="Akun Piutang"
                            required
                            name="receivable_chart_of_account_id"
                        >
                            <SearchChartOfAccount
                                value={{
                                    id: data.receivable_chart_of_account_id ?? '',
                                    name: data.receivable_chart_of_account?.name ?? '',
                                    code: data.receivable_chart_of_account?.code ?? ''
                                }}
                                onChange={(chartOfAccount: any) => {
                                    setData({
                                        ...data,
                                        receivable_chart_of_account_id: chartOfAccount.id,
                                        receivable_chart_of_account: chartOfAccount
                                    })
                                }}
                            />
                        </FormGroup>
                        <FormGroup
                            label="Akun Hutang"
                            required
                            name="debt_chart_of_account_id"
                        >
                            <SearchChartOfAccount
                                value={{
                                    id: data.debt_chart_of_account_id ?? '',
                                    name: data.debt_chart_of_account?.name ?? '',
                                    code: data.debt_chart_of_account?.code ?? ''
                                }}
                                onChange={(chartOfAccount: any) => {
                                    setData({
                                        ...data,
                                        debt_chart_of_account_id: chartOfAccount.id,
                                        debt_chart_of_account: chartOfAccount
                                    })
                                }}
                            />
                        </FormGroup>
                        <FormGroup
                            label="Akun Biaya Kirim"
                            required
                            name="shipping_cost_chart_of_account_id"
                        >
                            <SearchChartOfAccount
                                value={{
                                    id: data.shipping_cost_chart_of_account_id ?? '',
                                    name: data.shipping_cost_chart_of_account?.name ?? '',
                                    code: data.shipping_cost_chart_of_account?.code ?? ''
                                }}
                                onChange={(chartOfAccount: any) => {
                                    setData({
                                        ...data,
                                        shipping_cost_chart_of_account_id: chartOfAccount.id,
                                        shipping_cost_chart_of_account: chartOfAccount
                                    })
                                }}
                            />
                        </FormGroup>


                        <FormGroup
                            label="Akun Diskon Pembelian"
                            required
                            name="purchase_discount_chart_of_account_id"
                        >
                            <SearchChartOfAccount
                                value={{
                                    id: data.purchase_discount_chart_of_account_id ?? '',
                                    name: data.purchase_discount_chart_of_account?.name ?? '',
                                    code: data.purchase_discount_chart_of_account?.code ?? ''
                                }}
                                onChange={(chartOfAccount: any) => {
                                    setData({
                                        ...data,
                                        purchase_discount_chart_of_account_id: chartOfAccount.id,
                                        purchase_discount_chart_of_account: chartOfAccount
                                    })
                                }}
                            />
                        </FormGroup>
                        <FormGroup
                            label="Akun Biaya Materai"
                            required
                            name="stamp_duty_chart_of_account_id"
                        >
                            <SearchChartOfAccount
                                value={{
                                    id: data.stamp_duty_chart_of_account_id ?? '',
                                    name: data.stamp_duty_chart_of_account?.name ?? '',
                                    code: data.stamp_duty_chart_of_account?.code ?? ''
                                }}
                                onChange={(chartOfAccount: any) => {
                                    setData({
                                        ...data,
                                        stamp_duty_chart_of_account_id: chartOfAccount.id,
                                        stamp_duty_chart_of_account: chartOfAccount
                                    })
                                }}
                            />
                        </FormGroup>

                        <FormGroup
                            label="Logo"
                            required
                        >
                            <FileUploadDropzone
                                fileType='image'
                                onChange={(file: any) => {
                                    setData('logo', file)
                                }}
                                initialFile={ data.logo}
                            />
                        </FormGroup>
                        <Button
                            onClick={handleSubmit}
                            disabled={processing}
                            className="mt-3"
                        >
                            {processing ? 'Menyimpan...' : 'Simpan'}
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </>
    )
}
Page.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/admin' },
            { label: 'Pengaturan', href: '#' },
            { label: 'Utama', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
