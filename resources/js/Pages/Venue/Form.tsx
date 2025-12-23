import { Button } from "@/Components/ui/button";
import FormGroup from "@/Components/ui/form-group";
import { Input } from "@/Components/ui/input";
import ImageUploader from "@/Components/ImageUploader";
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from "@/Components/ui/tabs";
import { toast } from "@/hooks/use-toast";
import { router, useForm } from "@inertiajs/react";
import axios from "axios";
import { useState } from "react";
import { Trash2, Plus } from "lucide-react";
import { VenueType } from "./@types/venue-type";
import { Textarea } from "@/Components/ui/textarea";

interface Facility {
    id?: number;
    name: string;
    icon: string;
}

interface VenuePhoto {
    id?: number;
    file: string;
    is_primary: boolean;
}

interface VenueFormProps {
    initialData?: Partial<VenueType>;
    initialFacilities?: Facility[];
    initialPhotos?: VenuePhoto[];
    onSubmit: (data: VenueType) => Promise<void>;
    submitButtonText: string;
    successMessage: string;
    redirectTo: string;
}

export default function VenueForm({
    initialData = {
        slug: "",
        name: "",
        description: "",
        province: "",
        city: "",
        address: "",
        latitude: 0,
        longitude: 0,
        min_price: null,
        max_price: null,
        average_rating: null,
    },
    initialFacilities = [],
    initialPhotos = [],
    onSubmit,
    submitButtonText,
    successMessage,
    redirectTo,
}: VenueFormProps) {
    const { data, setData, clearErrors, errors, setError } = useForm({
        ...initialData,
    } as VenueType);

    const [processing, setProcessing] = useState(false);
    const [facilities, setFacilities] = useState<Facility[]>(initialFacilities);
    const [photos, setPhotos] = useState<VenuePhoto[]>(initialPhotos);

    async function handleSubmit() {
        clearErrors();
        setProcessing(true);
        try {
            const submitData = {
                ...data,
                facilities,
                photos,
            };
            await onSubmit(submitData as any);
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

    const addFacility = () => {
        setFacilities([...facilities, { name: "", icon: "" }]);
    };

    const removeFacility = (index: number) => {
        setFacilities(facilities.filter((_, i) => i !== index));
    };

    const updateFacility = (index: number, field: keyof Facility, value: string) => {
        const newFacilities = [...facilities];
        newFacilities[index] = { ...newFacilities[index], [field]: value };
        setFacilities(newFacilities);
    };

    const addPhoto = () => {
        setPhotos([...photos, { file: "", is_primary: photos.length === 0 }]);
    };

    const removePhoto = (index: number) => {
        const newPhotos = photos.filter((_, i) => i !== index);
        // If removed photo was primary and there are still photos, set first as primary
        if (photos[index].is_primary && newPhotos.length > 0) {
            newPhotos[0].is_primary = true;
        }
        setPhotos(newPhotos);
    };

    const updatePhoto = (index: number, field: keyof VenuePhoto, value: any) => {
        const newPhotos = [...photos];
        if (field === "is_primary" && value) {
            // Unset primary for all others
            newPhotos.forEach((photo) => (photo.is_primary = false));
        }
        newPhotos[index] = { ...newPhotos[index], [field]: value };
        setPhotos(newPhotos);
    };

    return (
        <div className="p-5">
            <Tabs defaultValue="basic" className="w-full">
                <TabsList className="grid w-full grid-cols-3">
                    <TabsTrigger value="basic">Informasi Dasar</TabsTrigger>
                    <TabsTrigger value="photos">Photo</TabsTrigger>
                    <TabsTrigger value="facilities">Fasilitas</TabsTrigger>
                </TabsList>

                <TabsContent value="basic" className="space-y-4">
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-x-4 gap-y-4">
                        <div className="lg:col-span-2">
                            <FormGroup label="Nama" error={errors.name} required>
                                <Input
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => {
                                        setData("name", e.target.value);
                                    }}
                                    placeholder="Nama venue"
                                />
                            </FormGroup>
                        </div>

                        <div className="lg:col-span-2">
                            <FormGroup label="Deskripsi" error={errors.description} required>
                                <Textarea
                                    value={data.description}
                                    onChange={(e) => {
                                        setData("description", e.target.value);
                                    }}
                                    placeholder="Deskripsi venue"
                                    rows={4}
                                />
                            </FormGroup>
                        </div>

                        <FormGroup label="Provinsi" error={errors.province} required>
                            <Input
                                type="text"
                                value={data.province}
                                onChange={(e) => {
                                    setData("province", e.target.value);
                                }}
                                placeholder="Contoh: Jakarta"
                            />
                        </FormGroup>

                        <FormGroup label="Kota/Kabupaten" error={errors.city} required>
                            <Input
                                type="text"
                                value={data.city}
                                onChange={(e) => {
                                    setData("city", e.target.value);
                                }}
                                placeholder="Contoh: Jakarta Selatan"
                            />
                        </FormGroup>

                        <div className="lg:col-span-2">
                            <FormGroup label="Alamat" error={errors.address} required>
                                <Textarea
                                    value={data.address}
                                    onChange={(e) => {
                                        setData("address", e.target.value);
                                    }}
                                    placeholder="Alamat lengkap venue"
                                    rows={3}
                                />
                            </FormGroup>
                        </div>

                        <FormGroup label="Latitude" error={errors.latitude} required>
                            <Input
                                type="number"
                                step="0.00000001"
                                value={data.latitude}
                                onChange={(e) => {
                                    setData("latitude", parseFloat(e.target.value));
                                }}
                                placeholder="Contoh: -6.27511"
                            />
                        </FormGroup>

                        <FormGroup label="Longitude" error={errors.longitude} required>
                            <Input
                                type="number"
                                step="0.00000001"
                                value={data.longitude}
                                onChange={(e) => {
                                    setData("longitude", parseFloat(e.target.value));
                                }}
                                placeholder="Contoh: 106.79313"
                            />
                        </FormGroup>

                        <FormGroup label="Harga Minimum" error={errors.min_price}>
                            <Input
                                type="number"
                                step="0.01"
                                value={data.min_price ?? ""}
                                onChange={(e) => {
                                    setData("min_price", e.target.value ? parseFloat(e.target.value) : null);
                                }}
                                placeholder="Harga minimum (opsional)"
                            />
                        </FormGroup>

                        <FormGroup label="Harga Maksimum" error={errors.max_price}>
                            <Input
                                type="number"
                                step="0.01"
                                value={data.max_price ?? ""}
                                onChange={(e) => {
                                    setData("max_price", e.target.value ? parseFloat(e.target.value) : null);
                                }}
                                placeholder="Harga maksimum (opsional)"
                            />
                        </FormGroup>

                        <FormGroup label="Rating Rata-rata" error={errors.average_rating}>
                            <Input
                                type="number"
                                step="0.01"
                                min="0"
                                max="5"
                                value={data.average_rating ?? ""}
                                onChange={(e) => {
                                    setData("average_rating", e.target.value ? parseFloat(e.target.value) : null);
                                }}
                                placeholder="Rating 0-5 (opsional)"
                            />
                        </FormGroup>
                    </div>
                </TabsContent>

                <TabsContent value="photos" className="space-y-4">
                    <div className="space-y-4">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-semibold">Daftar Photo</h3>
                            <Button
                                size="sm"
                                onClick={addPhoto}
                                type="button"
                            >
                                <Plus className="w-4 h-4 mr-2" />
                                Tambah Photo
                            </Button>
                        </div>

                        {photos.length === 0 ? (
                            <div className="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                                <p className="text-gray-500">Belum ada photo. Klik tombol di atas untuk menambahkan.</p>
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {photos.map((photo, index) => (
                                    <div
                                        key={index}
                                        className="border border-gray-200 rounded-lg p-4 space-y-3"
                                    >
                                        <div className="flex items-start justify-between">
                                            <div className="flex-1">
                                                <label className="block text-sm font-semibold mb-2">
                                                    Photo
                                                </label>
                                                <ImageUploader
                                                    maxHeight={200}
                                                    label=""
                                                    initialValue={photo.file}
                                                    onSuccess={(url) => {
                                                        updatePhoto(index, "file", url);
                                                        toast({
                                                            title: "Sukses",
                                                            description: "Photo berhasil diunggah",
                                                        });
                                                    }}
                                                    onError={(error) => {
                                                        toast({
                                                            variant: "destructive",
                                                            title: "Gagal",
                                                            description: error,
                                                        });
                                                    }}
                                                />
                                            </div>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <label className="flex items-center space-x-2 cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    checked={photo.is_primary}
                                                    onChange={(e) =>
                                                        updatePhoto(index, "is_primary", e.target.checked)
                                                    }
                                                    className="w-4 h-4 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                                                />
                                                <span className="text-sm font-medium">Jadikan Photo Utama</span>
                                            </label>
                                            <Button
                                                size="sm"
                                                variant="destructive"
                                                onClick={() => removePhoto(index)}
                                                type="button"
                                            >
                                                <Trash2 className="w-4 h-4 mr-2" />
                                                Hapus
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </TabsContent>

                <TabsContent value="facilities" className="space-y-4">
                    <div className="space-y-4">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-semibold">Daftar Fasilitas</h3>
                            <Button
                                size="sm"
                                onClick={addFacility}
                                type="button"
                            >
                                <Plus className="w-4 h-4 mr-2" />
                                Tambah Fasilitas
                            </Button>
                        </div>

                        {facilities.length === 0 ? (
                            <div className="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                                <p className="text-gray-500">Belum ada fasilitas. Klik tombol di atas untuk menambahkan.</p>
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {facilities.map((facility, index) => (
                                    <div
                                        key={index}
                                        className="border border-gray-200 rounded-lg p-4 space-y-3"
                                    >
                                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                            <div>
                                                <label className="block text-sm font-semibold mb-2">
                                                    Nama Fasilitas
                                                </label>
                                                <Input
                                                    type="text"
                                                    value={facility.name}
                                                    onChange={(e) =>
                                                        updateFacility(index, "name", e.target.value)
                                                    }
                                                    placeholder="Contoh: WiFi, Parkir, Toilet"
                                                />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-semibold mb-2">
                                                    Icon/Gambar
                                                </label>
                                                <ImageUploader
                                                    maxHeight={100}
                                                    label=""
                                                    initialValue={facility.icon}
                                                    onSuccess={(url) => {
                                                        updateFacility(index, "icon", url);
                                                        toast({
                                                            title: "Sukses",
                                                            description: "Icon berhasil diunggah",
                                                        });
                                                    }}
                                                    onError={(error) => {
                                                        toast({
                                                            variant: "destructive",
                                                            title: "Gagal",
                                                            description: error,
                                                        });
                                                    }}
                                                />
                                            </div>
                                        </div>
                                        <div className="flex justify-end">
                                            <Button
                                                size="sm"
                                                variant="destructive"
                                                onClick={() => removeFacility(index)}
                                                type="button"
                                            >
                                                <Trash2 className="w-4 h-4 mr-2" />
                                                Hapus
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </TabsContent>
            </Tabs>

            <div className="mt-6">
                <Button disabled={processing} onClick={handleSubmit} className="w-full lg:w-auto">
                    {processing ? `${submitButtonText}...` : submitButtonText}
                </Button>
            </div>
        </div>
    );
}
