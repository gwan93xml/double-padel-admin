import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import FormGroup from "@/Components/ui/form-group";
import { Input } from "@/Components/ui/input";
import ImageUploader from "@/Components/ImageUploader";
import CKEditorComponent from "@/Components/CKEditorComponent";
import StarRating from "@/Components/StarRating";
import { toast } from "@/hooks/use-toast";
import { router, useForm } from "@inertiajs/react";
import axios from "axios";
import { useState } from "react";

interface FormProps {
    initialData?: TestimonyType;
    onSubmit: (data: TestimonyType) => Promise<void>;
    submitButtonText: string;
    successMessage: string;
    redirectTo: string;
}

export default function TestimonyForm({
    initialData = { name: '', title: '', content: '', image: '', rating: 5 },
    onSubmit,
    submitButtonText,
    successMessage,
    redirectTo
}: FormProps) {
    const { data, setData, clearErrors, errors, setError } = useForm({
        ...initialData,
    } as TestimonyType);

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
                    <CardTitle>Form Testimoni</CardTitle>
                    <CardDescription>Isikan data testimoni dengan benar</CardDescription>
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
                                placeholder="Tulis testimoni di sini..."
                            />
                        </FormGroup>
                    </div>
                    <div className="mb-4">
                        <FormGroup
                            error={errors.image}
                        >
                            <ImageUploader
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
                            label="Rating"
                            error={errors.rating}
                            required
                        >
                            <StarRating
                                value={data.rating || 5}
                                onChange={(rating) => setData('rating', rating)}
                                error={errors.rating}
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
