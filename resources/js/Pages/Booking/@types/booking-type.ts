export type RoleType = {
    id?: string
    name?: string
    guard_name?: string
}

export type BookingType = {
    id?: string
    status?: 'pending' | 'confirmed' | 'cancelled' | 'completed'
    booking_number?: string
}