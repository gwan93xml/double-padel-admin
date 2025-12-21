import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import CourtForm from "./Form";
import axios from "axios";
import { CourtType } from "./@types/court-type";

export default function Create({ venues }: any) {
  async function handleSubmit(data: CourtType) {
    await axios.post(`/court`, data);
  }

  return (
    <CourtForm
      initialData={{
        venue_id: 0,
        name: "",
        court_type: "",
        price_per_hour: 0,
        capacity: 4,
        status: "available",
        image: null,
      }}
      venues={venues}
      onSubmit={handleSubmit}
      submitButtonText="Simpan"
      successMessage="Data court berhasil ditambah"
      redirectTo="/court/create"
    />
  );
}

Create.layout = (page: any) => (
  <AuthenticatedLayout
    breadcrumbs={[
      { label: "Dashboard", href: "/" },
      { label: "Master Data", href: "#" },
      { label: "Court", href: "/court" },
      { label: "Tambah", href: "#" },
    ]}
  >
    {page}
  </AuthenticatedLayout>
);
