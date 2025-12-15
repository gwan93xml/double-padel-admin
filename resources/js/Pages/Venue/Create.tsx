import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import VenueForm from "./Form";
import axios from "axios";
import { VenueType } from "./@types/venue-type";

export default function Create() {
  async function handleSubmit(data: VenueType) {
    await axios.post(`/venue`, data);
  }

  return (
    <VenueForm
      initialData={{
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
      }}
      onSubmit={handleSubmit}
      submitButtonText="Simpan"
      successMessage="Data venue berhasil ditambah"
      redirectTo="/venue/create"
    />
  );
}

Create.layout = (page: any) => (
  <AuthenticatedLayout
    breadcrumbs={[
      { label: "Dashboard", href: "/" },
      { label: "Master Data", href: "#" },
      { label: "Venue", href: "/venue" },
      { label: "Tambah", href: "#" },
    ]}
  >
    {page}
  </AuthenticatedLayout>
);
