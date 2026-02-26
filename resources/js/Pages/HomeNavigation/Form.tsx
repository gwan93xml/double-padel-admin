import ImageUploader from "@/Components/ImageUploader";
import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import FormGroup from "@/Components/ui/form-group";
import { Input } from "@/Components/ui/input";
import { toast } from "@/hooks/use-toast";
import { router, useForm } from "@inertiajs/react";
import { useState } from "react";

interface FormProps {
    initialData?: HomeNavigationType;
    onSubmit: (data: HomeNavigationType) => Promise<void>;
    submitButtonText: string;
    successMessage: string;
    redirectTo: string;
}

export default function HomeNavigationForm({
    initialData = { icon: '', small_icon: '', name: '', url: '' },
    onSubmit,
    submitButtonText,
    successMessage,
    redirectTo,
}: FormProps) {
    const { data, setData, clearErrors, errors, setError } = useForm({
        ...initialData,
    } as HomeNavigationType);

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
                    <CardTitle>Form Home Navigation</CardTitle>
                    <CardDescription>Isikan data home navigation dengan benar</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="mb-4">
                        <FormGroup
                            label="Icon"
                            error={errors.icon}
                            required
                        >
                            <ImageUploader
                                maxHeight={400}
                                initialValue={data.icon}
                                onSuccess={(url) => {
                                    setData('icon', url);
                                }}
                                onError={(error) => {
                                    toast({
                                        variant: 'destructive',
                                        title: 'Gagal',
                                        description: error,
                                    });
                                }}
                            />
                        </FormGroup>
                    </div>
                    <div className="mb-4">
                        <FormGroup
                            label="Small Icon"
                            error={errors.small_icon}
                        >
                            <ImageUploader
                                maxHeight={200}
                                initialValue={data.small_icon}
                                onSuccess={(url) => {
                                    setData('small_icon', url);
                                }}
                                onError={(error) => {
                                    toast({
                                        variant: 'destructive',
                                        title: 'Gagal',
                                        description: error,
                                    });
                                }}
                            />
                        </FormGroup>
                    </div>
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
                    <div className="mb-4">
                        <FormGroup
                            label="URL"
                            error={errors.url}
                            required
                        >
                            <Input
                                type="text"
                                value={data.url}
                                onChange={(e) => {
                                    setData('url', e.target.value);
                                }}
                                placeholder="/contoh-link"
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
