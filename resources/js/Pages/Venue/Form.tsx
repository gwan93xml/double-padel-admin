import { Button } from "@/Components/ui/button";
import FormGroup from "@/Components/ui/form-group";
import { Input } from "@/Components/ui/input";
import { toast } from "@/hooks/use-toast";
import { router, useForm } from "@inertiajs/react";
import axios from "axios";
import { useState } from "react";
import { VenueType } from "./@types/venue-type";

interface VenueFormProps {
  initialData?: Partial<VenueType>;
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
  onSubmit,
  submitButtonText,
  successMessage,
  redirectTo,
}: VenueFormProps) {
  const { data, setData, clearErrors, errors, setError } = useForm({
    ...initialData,
  } as VenueType);

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
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-x-4 gap-y-4">
        <FormGroup label="Slug" error={errors.slug} required>
          <Input
            type="text"
            value={data.slug}
            onChange={(e) => {
              setData("slug", e.target.value);
            }}
            placeholder="Contoh: grand-ballroom"
          />
        </FormGroup>

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

        <div className="lg:col-span-2">
          <FormGroup label="Deskripsi" error={errors.description} required>
            <textarea
              value={data.description}
              onChange={(e) => {
                setData("description", e.target.value);
              }}
              placeholder="Deskripsi venue"
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
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
            <textarea
              value={data.address}
              onChange={(e) => {
                setData("address", e.target.value);
              }}
              placeholder="Alamat lengkap venue"
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
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

        <div className="lg:col-span-2">
          <Button disabled={processing} onClick={handleSubmit}>
            {processing ? `${submitButtonText}...` : submitButtonText}
          </Button>
        </div>
      </div>
    </div>
  );
}
