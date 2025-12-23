
export type SettingType = {
    app_name?: string
    app_title?: string
    company_name?: string
    logo?: string
    favicon?: string
    address?: string
    booking_url?: string
    home_navigations?: Array<{
        title: string
        href: string
        image: string
        icon: string
    }>
    home_hero_image?: string
}