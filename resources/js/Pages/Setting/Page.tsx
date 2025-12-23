'use client'

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Card, CardContent } from "@/Components/ui/card";
import FormGroup from "@/Components/ui/form-group";
import { useForm } from "@inertiajs/react";
import { Input } from "@/Components/ui/input";
import { Textarea } from "@/Components/ui/textarea";
import { Button } from "@/Components/ui/button";
import { toast } from "@/hooks/use-toast";
import ImageUploader from "@/Components/ImageUploader";
import { SettingType } from "./@types/setting-type";
import HomeNavigationsTable from "./HomeNavigationsTable";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/Components/ui/tabs";


export default function Page({ setting }: any) {
    const { data, setData, post, errors, processing } = useForm({
        ...setting
    } as SettingType)

    function handleSubmit() {
        post('/setting', {
            onSuccess: () => {
                toast({
                    title: 'Berhasil',
                    description: 'Pengaturan berhasil disimpan'
                })
            }
        })
    }
    return (
        <>
            <div className="p-3">
                <h2 className="text-2xl font-semibold mb-3">
                    Utama
                </h2>
                <Tabs defaultValue="general" className="w-full">
                    <TabsList>
                        <TabsTrigger value="general">Pengaturan Umum</TabsTrigger>
                        <TabsTrigger value="navigation">Home Navigation</TabsTrigger>
                    </TabsList>

                    <TabsContent value="general">
                        <Card>
                            <CardContent className="mt-3">
                                <FormGroup
                                    label="App. Name"
                                    required
                                    error={errors.app_name}
                                >
                                    <Input
                                        value={data.app_name}
                                        onChange={(e) => setData('app_name', e.target.value)}
                                    />
                                </FormGroup>
                                <FormGroup
                                    label="App. Title"
                                    required
                                    error={errors.app_title}
                                >
                                    <Input
                                        value={data.app_title}
                                        onChange={(e) => setData('app_title', e.target.value)}
                                    />
                                </FormGroup>
                                <FormGroup
                                    label="Nama Perusahaan"
                                    required
                                    error={errors.company_name}
                                >
                                    <Input
                                        value={data.company_name}
                                        onChange={(e) => setData('company_name', e.target.value)}
                                    />
                                </FormGroup>
                                <FormGroup
                                    label="Alamat"
                                    error={errors.address}
                                >
                                    <Textarea
                                        value={data.address}
                                        onChange={(e) => setData('address', e.target.value)}
                                    />
                                </FormGroup>
                                <FormGroup
                                    label="Booking URL"
                                    error={errors.booking_url}
                                >
                                    <Input
                                        value={data.booking_url}
                                        onChange={(e) => setData('booking_url', e.target.value)}
                                    />
                                </FormGroup>
                                <FormGroup
                                    label="Logo"
                                    error={errors.logo}
                                >
                                    <ImageUploader
                                        maxHeight={300}
                                        label=""
                                        initialValue={data.logo}
                                        onSuccess={(url) => {
                                            setData('logo', url);
                                            toast({
                                                title: 'Sukses',
                                                description: 'Logo berhasil diunggah',
                                            });
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
                                <FormGroup
                                    label="Favicon"
                                    error={errors.favicon}
                                >
                                    <ImageUploader
                                        maxHeight={300}
                                        label=""
                                        initialValue={data.favicon}
                                        onSuccess={(url) => {
                                            setData('favicon', url);
                                            toast({
                                                title: 'Sukses',
                                                description: 'Favicon berhasil diunggah',
                                            });
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
                                <FormGroup
                                    label="Home Hero Image"
                                    error={errors.home_hero_image}
                                >
                                    <ImageUploader
                                        maxHeight={300}
                                        label=""
                                        initialValue={data.home_hero_image}
                                        onSuccess={(url) => {
                                            setData('home_hero_image', url);
                                            toast({
                                                title: 'Sukses',
                                                description: 'Favicon berhasil diunggah',
                                            });
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
                                <Button
                                    onClick={handleSubmit}
                                    disabled={processing}
                                    className="mt-3"
                                >
                                    {processing ? 'Menyimpan...' : 'Simpan'}
                                </Button>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="navigation">
                        <Card>
                            <CardContent className="mt-3">
                                <FormGroup
                                    label="Home Navigations"
                                    error={errors.home_navigations}
                                >
                                    <HomeNavigationsTable
                                        value={data.home_navigations || []}
                                        onChange={(navigations) => setData('home_navigations', navigations)}
                                    />
                                </FormGroup>
                                <Button
                                    onClick={handleSubmit}
                                    disabled={processing}
                                    className="mt-3"
                                >
                                    {processing ? 'Menyimpan...' : 'Simpan'}
                                </Button>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </>
    )
}
Page.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/admin' },
            { label: 'Pengaturan', href: '#' },
            { label: 'Utama', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
