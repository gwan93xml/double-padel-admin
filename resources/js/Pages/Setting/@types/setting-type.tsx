import { ChartOfAccountType } from "@/Pages/Chart_ofAccount/@types/chart-of-account-type"

export type SettingType = {
    app_name?: string
    code?: string
    logo?: string
    bank_account?: BankAccountType
    bank_account_id?: string
    company_name?: string
    company_address?: string
    company_phone?: string
    vat_paid_to_vendor_chart_of_account_id?: string
    sales_tax_payable_chart_of_account_id?: string
    sales_chart_of_account_id?: string
    purchase_chart_of_account_id?: string
    receivable_chart_of_account_id?: string
    debt_chart_of_account_id?: string
    shipping_cost_chart_of_account_id?: string
    stamp_duty_chart_of_account_id?: string
    purchase_discount_chart_of_account_id?: string

    vat_paid_to_vendor_chart_of_account?: ChartOfAccountType
    sales_tax_payable_chart_of_account?: ChartOfAccountType
    sales_chart_of_account?: ChartOfAccountType
    purchase_chart_of_account?: ChartOfAccountType
    receivable_chart_of_account?: ChartOfAccountType
    debt_chart_of_account?: ChartOfAccountType
    shipping_cost_chart_of_account?: ChartOfAccountType
    stamp_duty_chart_of_account?: ChartOfAccountType
    purchase_discount_chart_of_account?: ChartOfAccountType
}