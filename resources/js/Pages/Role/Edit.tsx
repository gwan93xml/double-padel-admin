"use client"

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout"
import RoleForm from "./Form"
import axios from "axios"
import { Shield } from "lucide-react"

interface ModuleType {
  id: string
  name: string
  slug: string
  actions: Array<{
    name: string
  }>
}

export default function Edit({ role, modules }: { modules: ModuleType[]; role: any }) {
  async function handleSubmit(data: any) {
    await axios.put(`/role/${data.id}`, data)
  }

  return (
    <AuthenticatedLayout
      breadcrumbs={[
        { label: "Dashboard", href: "/" },
        { label: "Pengaturan", href: "#" },
        { label: "Role", href: "/role" },
        { label: "Edit", href: "#" },
      ]}
    >
      <RoleForm
        initialData={role}
        modules={modules}
        onSubmit={handleSubmit}
        submitButtonText="Simpan Perubahan"
        successMessage="Data role berhasil diubah"
        redirectTo="/role"
        headerTitle="Edit Role"
        headerDescription="Kelola hak akses dan permission untuk role"
        headerIcon={<Shield className="h-6 w-6 text-white" />}
        headerColor="bg-gradient-to-r from-blue-600 to-blue-700"
        showQuickActions={false}
        showBackButton={false}
      />
    </AuthenticatedLayout>
  )
}
