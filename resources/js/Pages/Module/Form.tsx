import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import { Checkbox } from "@/Components/ui/checkbox";
import FormGroup from "@/Components/ui/form-group";
import { Input } from "@/Components/ui/input";
import { toast } from "@/hooks/use-toast";
import { router, useForm } from "@inertiajs/react";
import axios from "axios";
import { useState } from "react";

interface ModuleFormProps {
    initialData?: ModuleType;
    actions: ActionType[];
    onSubmit: (data: ModuleType) => Promise<void>;
    submitButtonText: string;
    successMessage: string;
    redirectTo: string;
}

export default function ModuleForm({
    initialData = { name: '', actions: [] },
    actions,
    onSubmit,
    submitButtonText,
    successMessage,
    redirectTo,
}: ModuleFormProps) {
    const { data, setData, clearErrors, errors, setError } = useForm({
        ...initialData,
        actions: initialData.actions?.map((action: any) =>
            typeof action === 'object' ? action.id : action
        ) || [],
    } as ModuleType);

    const [processing, setProcessing] = useState(false);

    async function handleSubmit() {
        clearErrors();
        setProcessing(true);
        try {
            await onSubmit(data);
            toast({
                title: 'Sukses',
                description: successMessage,
            });
            router.visit(redirectTo);
        } catch (error: any) {
            if (error.response?.status === 422) {
                const errors = error.response.data.errors;
                setError(errors);
            } else {
                toast({
                    variant: 'destructive',
                    title: 'Gagal',
                    description: error.response?.data?.message || 'Terjadi kesalahan',
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
                    <CardTitle>Form Module</CardTitle>
                    <CardDescription>Isikan data module dengan benar</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="mb-4">
                        <FormGroup
                            label="Nama"
                            error={errors.name}
                            required
                        >
                            <Input
                                type="text"
                                value={data.name}
                                onChange={(e) => {
                                    setData('name', e.target.value);
                                }}
                            />
                        </FormGroup>
                        <FormGroup
                            label="Aksi"
                            error={errors.actions}
                        >
                            <div className="grid grid-cols-1 gap-x-4 gap-y-4">
                                <div className="flex items-center space-x-2 pb-2 border-b">
                                    <Checkbox
                                        id="check-all"
                                        checked={data.actions?.length === actions.length && actions.length > 0}
                                        onCheckedChange={(checked) => {
                                            if (checked) {
                                                setData('actions', actions.map((a) => a.id!));
                                            } else {
                                                setData('actions', []);
                                            }
                                        }}
                                    />
                                    <label
                                        htmlFor="check-all"
                                        className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer"
                                    >
                                        Pilih Semua
                                    </label>
                                </div>
                                {actions.map((action) => (
                                    <div key={action.id} className="flex items-center space-x-2">
                                        <Checkbox
                                            id={action.name}
                                            checked={data.actions?.includes(action.id!)}
                                            onCheckedChange={(checked) => {
                                                if (checked) {
                                                    setData('actions', [...(data.actions || []), action.id!]);
                                                } else {
                                                    setData(
                                                        'actions',
                                                        data.actions?.filter((id: any) => id !== action.id!)
                                                    );
                                                }
                                            }}
                                        />
                                        <label
                                            htmlFor={action.name}
                                            className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                        >
                                            {action.name}
                                        </label>
                                    </div>
                                ))}
                            </div>
                        </FormGroup>
                    </div>

                    <Button
                        disabled={processing}
                        onClick={handleSubmit}
                    >
                        {processing ? `${submitButtonText}...` : submitButtonText}
                    </Button>
                </CardContent>
            </Card>
        </div>
    );
}
