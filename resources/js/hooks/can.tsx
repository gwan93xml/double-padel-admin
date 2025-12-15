import { usePage } from "@inertiajs/react";

export default function can(permission: string | string[] | undefined) {
    if (!permission) {
        return true;
    }
    const props = usePage().props as any;
    const roles = props.auth.user.roles;
    const permissions = roles.flatMap((role: any) => role.permissions.map((permission: any) => permission.name));

    if (Array.isArray(permission)) {
        //check if one of the permission is in the permissions
        return permission.some((perm) => permissions.includes(perm));
    }
    return permissions.includes(permission);
}