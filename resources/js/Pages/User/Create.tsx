import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import UserForm from "./Form";
import axios from "axios";
import { UserType } from "./@types/user-type";

export default function Create({ roles }: any) {
  async function handleSubmit(data: UserType) {
    await axios.post(`/user`, data);
  }

  return (
    <UserForm
      initialData={{ email: "", name: "", password: "", roles: [] }}
      roles={roles}
      onSubmit={handleSubmit}
      submitButtonText="Simpan"
      successMessage="Data user berhasil ditambah"
      redirectTo="/user/create"
    />
  );
}

Create.layout = (page: any) => (
  <AuthenticatedLayout
    breadcrumbs={[
      { label: "Dashboard", href: "/" },
      { label: "Pengaturan", href: "#" },
      { label: "User", href: "/user" },
      { label: "Tambah", href: "#" },
    ]}
  >
    {page}
  </AuthenticatedLayout>
);
