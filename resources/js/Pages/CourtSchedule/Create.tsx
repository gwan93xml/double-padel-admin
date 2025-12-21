import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import CourtScheduleForm from "./Form";
import axios from "axios";
import { Court, User } from "@/types/court-schedule-type";

interface Props {
    courts: Court[];
    users: User[];
}

export default function Create({ courts, users }: Props) {
    async function handleSubmit(data: any) {
        await axios.post(`/court-schedule`, data);
    }

    return (
        <CourtScheduleForm
            courts={courts}
            users={users}
            onSubmit={handleSubmit}
            submitButtonText="Simpan"
            successMessage="Data jadwal lapangan berhasil ditambah"
            redirectTo="/court-schedule"
        />
    );
}

Create.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Master Data', href: '#' },
            { label: 'Jadwal Lapangan', href: '/court-schedule' },
            { label: 'Tambah', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
