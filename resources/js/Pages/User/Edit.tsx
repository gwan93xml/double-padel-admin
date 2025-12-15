import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import UserForm from "./Form";
import axios from "axios";
import { UserType } from "./@types/user-type";

export default function Edit({ user, roles }: any) {
  async function handleSubmit(data: UserType) {
    await axios.put(`/user/${data.id}`, data);
  }

  return (
    <UserForm
      initialData={user}
      roles={roles}
      onSubmit={handleSubmit}
      submitButtonText="Ubah"
      successMessage="Data user berhasil diubah"
      redirectTo="/user"
      passwordPlaceholder="Kosongkan jika tidak ingin mengubah password"
    />
  );
}

Edit.layout = (page: any) => (
  <AuthenticatedLayout
    breadcrumbs={[
      { label: "Dashboard", href: "/" },
      { label: "Pengaturan", href: "#" },
      { label: "User", href: "/user" },
      { label: "Edit", href: "#" },
    ]}
  >
    {page}
  </AuthenticatedLayout>
);
