type ReviewType = {
    id?: string
    venue_id?: string
    user_id?: string
    cleanliness_rating?: number
    court_condition_rating?: number
    communication_rating?: number
    comment?: string
    venue?: {
        id: string
        name: string
    }
    user?: {
        id: string
        name: string
    }
}
