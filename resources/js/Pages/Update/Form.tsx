import CKEditorComponent from "@/Components/CKEditorComponent";
import ImageUploader from "@/Components/ImageUploader";
import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import FormGroup from "@/Components/ui/form-group";
import { Input } from "@/Components/ui/input";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { toast } from "@/hooks/use-toast";
import { router, useForm } from "@inertiajs/react";
import { useState } from "react";

interface FormProps {
    initialData?: UpdateType;
    onSubmit: (data: UpdateType) => Promise<void>;
    submitButtonText: string;
    successMessage: string;
    redirectTo: string;
    categories?: UpdateCategoryType[];
}

export default function UpdateForm({
    initialData = { update_category_id: '', title: '', body: '', image: '' },
    onSubmit,
    submitButtonText,
    successMessage,
    redirectTo,
    categories = [],
}: FormProps) {
    const { data, setData, clearErrors, errors, setError } = useForm({
        ...initialData,
    } as UpdateType);

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
                    <CardTitle>Form Update</CardTitle>
                    <CardDescription>Isikan data update dengan benar</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="mb-4">
                        <FormGroup
                            label="Kategori"
                            error={errors.update_category_id}
                            required
                        >
                            <Select value={data.update_category_id?.toString()} onValueChange={(value) => setData('update_category_id', value)}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih kategori" />
                                </SelectTrigger>
                                <SelectContent>
                                    {categories.map((category) => (
                                        <SelectItem key={category.id} value={category?.id?.toString() ?? ''}>
                                            {category.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </FormGroup>
                    </div>
                    <div className="mb-4">
                        <FormGroup
                            label="Judul"
                            error={errors.title}
                            required
                        >
                            <Input
                                type="text"
                                value={data.title}
                                onChange={(e) => {
                                    setData('title', e.target.value);
                                }}
                            />
                        </FormGroup>
                    </div>
                    <div className="mb-4">
                        <FormGroup
                            label="Body"
                            error={errors.body}
                            required
                        >
                            <CKEditorComponent
                                value={data.body || ''}
                                onChange={(body) => setData('body', body)}
                                error={errors.body}
                                placeholder="Tulis update di sini..."
                            />
                        </FormGroup>
                    </div>
                    <div className="mb-4">
                        <FormGroup
                            error={errors.image}
                        >
                            <ImageUploader
                                maxHeight={400}
                                initialValue={data.image}
                                onSuccess={(url) => {
                                    setData('image', url);
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
