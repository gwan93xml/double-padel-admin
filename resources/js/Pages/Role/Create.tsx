"use client"

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout"
import RoleForm from "./Form"
import axios from "axios"
import { Plus } from "lucide-react"

interface ModuleType {
  id: string
  name: string
  slug: string
  actions: Array<{
    name: string
  }>
}

export default function Create({ modules }: { modules: ModuleType[] }) {
  async function handleSubmit(data: any) {
    await axios.post(`/role`, data)
  }

  return (
    <AuthenticatedLayout
      breadcrumbs={[
        { label: "Dashboard", href: "/" },
        { label: "Pengaturan", href: "#" },
        { label: "Role", href: "/role" },
        { label: "Tambah", href: "#" },
      ]}
    >
      <RoleForm
        initialData={{ name: "", permissions: [] }}
        modules={modules}
        onSubmit={handleSubmit}
        submitButtonText="Simpan Role"
        successMessage="Data role berhasil ditambah"
        redirectTo="/role"
        headerTitle="Tambah Role Baru"
        headerDescription="Buat role baru dengan hak akses dan permission yang sesuai"
        headerIcon={<Plus className="h-6 w-6 text-white" />}
        headerColor="bg-green-600"
        showQuickActions={true}
        showBackButton={true}
      />
    </AuthenticatedLayout>
  )
}
