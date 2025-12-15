import { Button } from "@/Components/ui/button";
import { Checkbox } from "@/Components/ui/checkbox";
import FormGroup from "@/Components/ui/form-group";
import { Input } from "@/Components/ui/input";
import { toast } from "@/hooks/use-toast";
import { router, useForm } from "@inertiajs/react";
import { useState } from "react";
import { UserType } from "./@types/user-type";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";

interface UserFormProps {
    initialData?: Partial<UserType>;
    roles: any[];
    onSubmit: (data: UserType) => Promise<void>;
    submitButtonText: string;
    successMessage: string;
    redirectTo: string;
    passwordPlaceholder?: string;
}

export default function UserForm({
    initialData = {
        email: "",
        name: "",
        password: "",
        roles: [],
    },
    roles,
    onSubmit,
    submitButtonText,
    successMessage,
    redirectTo,
    passwordPlaceholder,
}: UserFormProps) {
    const { data, setData, clearErrors, errors, setError } = useForm({
        ...initialData,
        roles: initialData.roles?.map((role: any) => (typeof role === "object" ? role.id : role)) || [],
    } as UserType);

    const [processing, setProcessing] = useState(false);

    async function handleSubmit() {
        clearErrors();
        setProcessing(true);
        try {
            await onSubmit(data);
            toast({
                title: "Sukses",
                description: successMessage,
            });
            router.visit(redirectTo);
        } catch (error: any) {
            if (error.response?.status === 422) {
                const errors = error.response.data.errors;
                setError(errors);
            } else {
                toast({
                    variant: "destructive",
                    title: "Gagal",
                    description: error.response?.data?.message || "Terjadi kesalahan",
                });
            }
        } finally {
            setProcessing(false);
        }
    }

    return (
        <div className="p-5">
            <Card>
                <CardHeader>
                    <CardTitle>Form User</CardTitle>
                    <CardDescription>Kelola data user aplikasi</CardDescription>
                </CardHeader>
                <CardContent>

                    <div className="mb-4">
                        <FormGroup label="Username/Email" error={errors.email} required>
                            <Input
                                type="text"
                                value={data.email}
                                onChange={(e) => {
                                    setData("email", e.target.value);
                                }}
                            />
                        </FormGroup>
                        <FormGroup label="Nama" error={errors.name} required>
                            <Input
                                type="text"
                                value={data.name}
                                onChange={(e) => {
                                    setData("name", e.target.value);
                                }}
                            />
                        </FormGroup>
                        <FormGroup label="Password" error={errors.password} required>
                            <Input
                                type="text"
                                value={data.password}
                                placeholder={passwordPlaceholder}
                                onChange={(e) => {
                                    setData("password", e.target.value);
                                }}
                            />
                        </FormGroup>
                        <FormGroup label="Roles" error={errors.roles} required>
                            <div className="grid grid-cols-1 gap-x-4 gap-y-4">
                                {roles.map((role: any) => (
                                    <div key={role.id} className="flex items-center space-x-2">
                                        <Checkbox
                                            id={role.name}
                                            checked={data.roles?.includes(role.id!)}
                                            onCheckedChange={(checked) => {
                                                if (checked) {
                                                    setData("roles", [...(data.roles || []), role.id!]);
                                                } else {
                                                    setData(
                                                        "roles",
                                                        data.roles?.filter((id: any) => id !== role.id!)
                                                    );
                                                }
                                            }}
                                        />
                                        <label
                                            htmlFor={role.name}
                                            className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                        >
                                            {role.name}
                                        </label>
                                    </div>
                                ))}
                            </div>
                        </FormGroup>
                    </div>
                    <Button disabled={processing} onClick={handleSubmit}>
                        {processing ? `${submitButtonText}...` : submitButtonText}
                    </Button>
                </CardContent>
            </Card>
        </div>
    );
}
