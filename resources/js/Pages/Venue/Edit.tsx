import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import VenueForm from "./Form";
import axios from "axios";
import { VenueType } from "./@types/venue-type";

export default function Edit({ venue }: any) {
  async function handleSubmit(data: VenueType) {
    await axios.put(`/venue/${data.id}`, data);
  }

  return (
    <VenueForm
      initialData={venue}
      onSubmit={handleSubmit}
      submitButtonText="Ubah"
      successMessage="Data venue berhasil diubah"
      redirectTo="/venue"
    />
  );
}

Edit.layout = (page: any) => (
  <AuthenticatedLayout
    breadcrumbs={[
      { label: "Dashboard", href: "/" },
      { label: "Master Data", href: "#" },
      { label: "Venue", href: "/venue" },
      { label: "Edit", href: "#" },
    ]}
  >
    {page}
  </AuthenticatedLayout>
);
