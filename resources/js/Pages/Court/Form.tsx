import { Button } from "@/Components/ui/button";
import FormGroup from "@/Components/ui/form-group";
import { Input } from "@/Components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/Components/ui/select";
import ImageUploader from "@/Components/ImageUploader";
import { toast } from "@/hooks/use-toast";
import { router, useForm } from "@inertiajs/react";
import axios from "axios";
import { useState } from "react";
import { CourtType } from "./@types/court-type";

interface CourtFormProps {
  initialData?: Partial<CourtType>;
  venues: any[];
  onSubmit: (data: CourtType) => Promise<void>;
  submitButtonText: string;
  successMessage: string;
  redirectTo: string;
}

export default function CourtForm({
  initialData = {
    venue_id: 0,
    name: "",
    court_type: "",
    price_per_hour: 0,
    capacity: 4,
    status: "available",
    image: null,
  },
  venues,
  onSubmit,
  submitButtonText,
  successMessage,
  redirectTo,
}: CourtFormProps) {
  const { data, setData, clearErrors, errors, setError } = useForm({
    ...initialData,
  } as CourtType);

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

  const statusOptions = [
    { value: "available", label: "Tersedia" },
    { value: "maintenance", label: "Maintenance" },
    { value: "closed", label: "Ditutup" },
  ];

  return (
    <div className="p-5">
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-x-4 gap-y-4">
        <FormGroup label="Venue" error={errors.venue_id} required>
          <Select value={data.venue_id.toString()} onValueChange={(value) => setData("venue_id", parseInt(value))}>
            <SelectTrigger>
              <SelectValue placeholder="Pilih Venue" />
            </SelectTrigger>
            <SelectContent>
              {venues.map((venue) => (
                <SelectItem key={venue.id} value={venue.id.toString()}>
                  {venue.name}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </FormGroup>

        <FormGroup label="Nama Court" error={errors.name} required>
          <Input
            type="text"
            value={data.name}
            onChange={(e) => {
              setData("name", e.target.value);
            }}
            placeholder="Contoh: Court A"
          />
        </FormGroup>

        <FormGroup label="Tipe Court" error={errors.court_type} required>
          <Input
            type="text"
            value={data.court_type}
            onChange={(e) => {
              setData("court_type", e.target.value);
            }}
            placeholder="Contoh: Indoor, Outdoor"
          />
        </FormGroup>

        <FormGroup label="Harga per Jam" error={errors.price_per_hour} required>
          <Input
            type="number"
            value={data.price_per_hour}
            onChange={(e) => {
              setData("price_per_hour", parseInt(e.target.value));
            }}
            placeholder="Contoh: 150000"
          />
        </FormGroup>

        <FormGroup label="Kapasitas" error={errors.capacity} required>
          <Input
            type="number"
            value={data.capacity}
            onChange={(e) => {
              setData("capacity", parseInt(e.target.value));
            }}
            placeholder="Contoh: 4"
            min="1"
          />
        </FormGroup>

        <FormGroup label="Status" error={errors.status} required>
          <Select value={data.status} onValueChange={(value) => setData("status", value as "available" | "maintenance" | "closed")}>
            <SelectTrigger>
              <SelectValue placeholder="Pilih Status" />
            </SelectTrigger>
            <SelectContent>
              {statusOptions.map((option) => (
                <SelectItem key={option.value} value={option.value}>
                  {option.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </FormGroup>

        <FormGroup label="Gambar" error={errors.image}>
          <ImageUploader
          initialValue={data.image}
            label=""
            onSuccess={(url) => {
              setData("image", url);
              toast({
                title: "Sukses",
                description: "Gambar berhasil diunggah",
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
