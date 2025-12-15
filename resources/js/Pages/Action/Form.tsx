import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import FormGroup from "@/Components/ui/form-group";
import { Input } from "@/Components/ui/input";
import { toast } from "@/hooks/use-toast";
import { router, useForm } from "@inertiajs/react";
import axios from "axios";
import { useState } from "react";

interface FormProps {
    initialData?: ActionType;
    onSubmit: (data: ActionType) => Promise<void>;
    submitButtonText: string;
    successMessage: string;
    redirectTo: string;
}

export default function ActionForm({
    initialData = { name: '' },
    onSubmit,
    submitButtonText,
    successMessage,
    redirectTo
}: FormProps) {
    const { data, setData, clearErrors, errors, setError } = useForm({
        ...initialData,
    } as ActionType);

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
                    <CardTitle>Form Aksi</CardTitle>
                    <CardDescription>Isikan data aksi dengan benar</CardDescription>
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
