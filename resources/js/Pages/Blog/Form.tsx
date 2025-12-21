import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import FormGroup from "@/Components/ui/form-group";
import { Input } from "@/Components/ui/input";
import ImageUploader from "@/Components/ImageUploader";
import CKEditorComponent from "@/Components/CKEditorComponent";
import { toast } from "@/hooks/use-toast";
import { router, useForm } from "@inertiajs/react";
import axios from "axios";
import { useState } from "react";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";

interface FormProps {
    initialData?: BlogType;
    onSubmit: (data: BlogType) => Promise<void>;
    submitButtonText: string;
    successMessage: string;
    redirectTo: string;
    categories?: BlogCategoryType[];
}

export default function BlogForm({
    initialData = { category_id: '', title: '', content: '', image: '', tags: '' },
    onSubmit,
    submitButtonText,
    successMessage,
    redirectTo,
    categories = []
}: FormProps) {
    const { data, setData, clearErrors, errors, setError } = useForm({
        ...initialData,
    } as BlogType);

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
                    <CardTitle>Form Blog</CardTitle>
                    <CardDescription>Isikan data blog dengan benar</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="mb-4">
                        <FormGroup
                            label="Kategori"
                            error={errors.category_id}
                            required
                        >
                            <Select value={data.category_id?.toString()} onValueChange={(value) => setData('category_id', value)}>
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
                            label="Konten"
                            error={errors.content}
                            required
                        >
                            <CKEditorComponent
                                value={data.content || ""}
                                onChange={(content) => setData('content', content)}
                                error={errors.content}
                                placeholder="Tulis konten blog di sini..."
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
                    <div className="mb-4">
                        <FormGroup
                            label="Tag"
                            error={errors.tags}
                        >
                            <Input
                                type="text"
                                value={data.tags}
                                onChange={(e) => {
                                    setData('tags', e.target.value);
                                }}
                                placeholder="Pisahkan dengan koma"
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
