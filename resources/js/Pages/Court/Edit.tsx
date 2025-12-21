import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import CourtForm from "./Form";
import axios from "axios";
import { CourtType } from "./@types/court-type";

export default function Edit({ court, venues }: any) {
  async function handleSubmit(data: CourtType) {
    await axios.put(`/court/${data.id}`, data);
  }

  return (
    <CourtForm
      initialData={court}
      venues={venues}
      onSubmit={handleSubmit}
      submitButtonText="Ubah"
      successMessage="Data court berhasil diubah"
      redirectTo="/court"
    />
  );
}

Edit.layout = (page: any) => (
  <AuthenticatedLayout
    breadcrumbs={[
      { label: "Dashboard", href: "/" },
      { label: "Master Data", href: "#" },
      { label: "Court", href: "/court" },
      { label: "Edit", href: "#" },
    ]}
  >
    {page}
  </AuthenticatedLayout>
);
