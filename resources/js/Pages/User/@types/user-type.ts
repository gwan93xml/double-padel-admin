export type RoleType = {
    id?: string
    name?: string
    guard_name?: string
}

export type UserType = {
    id?: string
    user_type?: 'admin' | 'member'
    email?: string
    name?: string
    password?: string
    roles?: RoleType[]
}