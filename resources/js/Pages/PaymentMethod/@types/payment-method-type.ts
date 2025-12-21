type PaymentMethodType = {
    id?: number
    group?: string
    code?: string
    name?: string
    image?: string
    transaction_fee?: number
    how_to_pay?: Array<{
        channel?: string
        steps?: string[]
    }>
}
