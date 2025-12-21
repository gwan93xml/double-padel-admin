import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import FormGroup from "@/Components/ui/form-group";
import { Input } from "@/Components/ui/input";
import { toast } from "@/hooks/use-toast";
import { router, useForm } from "@inertiajs/react";
import ImageUploader from "@/Components/ImageUploader";
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from "@/Components/ui/tabs";
import axios from "axios";
import { useState } from "react";
import { Trash2, Plus } from "lucide-react";

interface FormProps {
    initialData?: PaymentMethodType;
    onSubmit: (data: PaymentMethodType) => Promise<void>;
    submitButtonText: string;
    successMessage: string;
    redirectTo: string;
}

export default function PaymentMethodForm({
    initialData = { code: '', name: '', image: '', how_to_pay: [] },
    onSubmit,
    submitButtonText,
    successMessage,
    redirectTo
}: FormProps) {
    const { data, setData, clearErrors, errors, setError } = useForm({
        ...initialData,
    } as PaymentMethodType);

    const [processing, setProcessing] = useState(false);
    const [howToPay, setHowToPay] = useState<Array<{ channel: string; steps: string[] }>>(
        (initialData?.how_to_pay || []).map(item => ({
            channel: item.channel || '',
            steps: item.steps || []
        }))
    );
    const [activeTab, setActiveTab] = useState<string>(
        howToPay.length > 0 ? '0' : ''
    );

    async function handleSubmit() {
        clearErrors();
        setProcessing(true);
        try {
            await onSubmit({
                ...data,
                how_to_pay: howToPay,
            });
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

    const addPaymentChannel = () => {
        const newTab = { channel: '', steps: [] };
        const newHowToPay = [...howToPay, newTab];
        setHowToPay(newHowToPay);
        setActiveTab(String(newHowToPay.length - 1));
    };

    const removePaymentChannel = (index: number) => {
        const newHowToPay = howToPay.filter((_, i) => i !== index);
        setHowToPay(newHowToPay);
        if (activeTab === String(index)) {
            setActiveTab(newHowToPay.length > 0 ? '0' : '');
        }
    };

    const updateChannelName = (index: number, channel: string) => {
        const newHowToPay = [...howToPay];
        newHowToPay[index].channel = channel;
        setHowToPay(newHowToPay);
    };

    const addChannelStep = (channelIndex: number) => {
        const newHowToPay = [...howToPay];
        newHowToPay[channelIndex].steps = [...(newHowToPay[channelIndex].steps || []), ''];
        setHowToPay(newHowToPay);
    };

    const removeChannelStep = (channelIndex: number, stepIndex: number) => {
        const newHowToPay = [...howToPay];
        newHowToPay[channelIndex].steps = (newHowToPay[channelIndex].steps || []).filter(
            (_, i) => i !== stepIndex
        );
        setHowToPay(newHowToPay);
    };

    const updateChannelStep = (channelIndex: number, stepIndex: number, value: string) => {
        const newHowToPay = [...howToPay];
        if (!newHowToPay[channelIndex].steps) {
            newHowToPay[channelIndex].steps = [];
        }
        newHowToPay[channelIndex].steps[stepIndex] = value;
        setHowToPay(newHowToPay);
    };

    return (
        <div className="p-5">
            <Card>
                <CardHeader>
                    <CardTitle>Form Metode Pembayaran</CardTitle>
                    <CardDescription>Isikan data metode pembayaran dengan benar</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div className="col-span-2">
                            <FormGroup
                                label="Grup"
                                error={errors.group}
                                required
                            >
                                <Input
                                    type="text"
                                    value={data.group || ''}
                                    onChange={(e) => {
                                        setData('group', e.target.value);
                                    }}
                                    placeholder="Contoh: Bank, E-Wallet, dll"
                                />
                            </FormGroup>

                        </div>
                        <FormGroup
                            label="Kode"
                            error={errors.code}
                            required
                        >
                            <Input
                                type="text"
                                value={data.code || ''}
                                onChange={(e) => {
                                    setData('code', e.target.value);
                                }}
                                placeholder="Contoh: BCA, GOPAY, OVO"
                            />
                        </FormGroup>

                        <FormGroup
                            label="Nama"
                            error={errors.name}
                            required
                        >
                            <Input
                                type="text"
                                value={data.name || ''}
                                onChange={(e) => {
                                    setData('name', e.target.value);
                                }}
                                placeholder="Contoh: Bank Central Asia"
                            />
                        </FormGroup>

                        <FormGroup
                            label="Biaya Transaksi"
                            error={errors.transaction_fee}
                            required
                        >
                            <Input
                                type="number"
                                value={data.transaction_fee || ''}
                                onChange={(e) => {
                                    setData('transaction_fee', e.target.value ? parseInt(e.target.value) : 0);
                                }}
                                placeholder="Contoh: 5000"
                            />
                        </FormGroup>
                    </div>

                    <div className="grid grid-cols-1 gap-4 mt-4">
                        <FormGroup
                            label="Gambar/Icon"
                            error={errors.image}
                        >
                            <ImageUploader
                                maxHeight={300}
                                label=""
                                initialValue={data.image}
                                onSuccess={(url) => {
                                    setData('image', url);
                                    toast({
                                        title: 'Sukses',
                                        description: 'Gambar berhasil diunggah',
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

                        <div>
                            <div className="flex items-center justify-between mb-4">
                                <h3 className="text-lg font-semibold">Cara Pembayaran</h3>
                                <Button
                                    size="sm"
                                    onClick={addPaymentChannel}
                                    type="button"
                                >
                                    <Plus className="w-4 h-4 mr-2" />
                                    Tambah Channel
                                </Button>
                            </div>

                            {howToPay.length === 0 ? (
                                <div className="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                                    <p className="text-gray-500">Belum ada channel pembayaran. Klik tombol di atas untuk menambahkan.</p>
                                </div>
                            ) : (
                                <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
                                    <TabsList className="grid w-full gap-2 grid-flow-col overflow-x-auto justify-start h-auto">
                                        {howToPay.map((channel, index) => (
                                            <div key={index} className="flex items-center gap-2">
                                                <TabsTrigger value={String(index)}>
                                                    {channel.channel || `Channel ${index + 1}`}
                                                </TabsTrigger>
                                                {howToPay.length > 1 && (
                                                    <Button
                                                        size="sm"
                                                        variant="ghost"
                                                        onClick={() => removePaymentChannel(index)}
                                                        type="button"
                                                        className="h-8 w-8 p-0"
                                                    >
                                                        <Trash2 className="w-3 h-3" />
                                                    </Button>
                                                )}
                                            </div>
                                        ))}
                                    </TabsList>

                                    {howToPay.map((channel, channelIndex) => (
                                        <TabsContent key={channelIndex} value={String(channelIndex)} className="space-y-4 mt-4">
                                            <FormGroup
                                                label="Nama Channel"
                                                required
                                            >
                                                <Input
                                                    type="text"
                                                    value={channel.channel || ''}
                                                    onChange={(e) =>
                                                        updateChannelName(channelIndex, e.target.value)
                                                    }
                                                    placeholder="Contoh: ATM, Mobile Banking, Teller"
                                                />
                                            </FormGroup>

                                            <div>
                                                <div className="flex items-center justify-between mb-4">
                                                    <label className="block text-sm font-semibold">Langkah Pembayaran</label>
                                                    <Button
                                                        size="sm"
                                                        onClick={() => addChannelStep(channelIndex)}
                                                        type="button"
                                                    >
                                                        <Plus className="w-4 h-4 mr-2" />
                                                        Tambah Langkah
                                                    </Button>
                                                </div>

                                                {(!channel.steps || channel.steps.length === 0) ? (
                                                    <div className="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                                        <p className="text-gray-500 text-sm">Belum ada langkah. Klik tombol di atas untuk menambahkan.</p>
                                                    </div>
                                                ) : (
                                                    <div className="space-y-2">
                                                        {channel.steps.map((step, stepIndex) => (
                                                            <div
                                                                key={stepIndex}
                                                                className="border border-gray-200 rounded-lg p-3 flex gap-3"
                                                            >
                                                                <div className="flex-1">
                                                                    <label className="block text-xs font-semibold text-gray-600 mb-1">
                                                                        Langkah {stepIndex + 1}
                                                                    </label>
                                                                    <Input
                                                                        type="text"
                                                                        value={step}
                                                                        onChange={(e) =>
                                                                            updateChannelStep(channelIndex, stepIndex, e.target.value)
                                                                        }
                                                                        placeholder="Contoh: Buka aplikasi BCA..."
                                                                    />
                                                                </div>
                                                                <div className="flex items-center">
                                                                    <Button
                                                                        size="sm"
                                                                        variant="destructive"
                                                                        onClick={() => removeChannelStep(channelIndex, stepIndex)}
                                                                        type="button"
                                                                        className="h-9"
                                                                    >
                                                                        <Trash2 className="w-4 h-4" />
                                                                    </Button>
                                                                </div>
                                                            </div>
                                                        ))}
                                                    </div>
                                                )}
                                            </div>
                                        </TabsContent>
                                    ))}
                                </Tabs>
                            )}
                        </div>
                    </div>

                    <Button
                        onClick={handleSubmit}
                        disabled={processing}
                        className="mt-6"
                    >
                        {processing ? `${submitButtonText}...` : submitButtonText}
                    </Button>
                </CardContent>
            </Card>
        </div>
    );
}
