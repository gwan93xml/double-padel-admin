import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import CourtScheduleForm from "./Form";
import axios from "axios";
import { CourtSchedule, Court, User } from "@/types/court-schedule-type";

interface Props {
    courtSchedule: CourtSchedule;
    courts: Court[];
    users: User[];
}

export default function Edit({ courtSchedule, courts, users }: Props) {
    async function handleSubmit(data: any) {
        await axios.put(`/court-schedule/${data.id}`, data);
    }

    return (
        <CourtScheduleForm
            initialData={courtSchedule}
            courts={courts}
            users={users}
            onSubmit={handleSubmit}
            submitButtonText="Ubah"
            successMessage="Data jadwal lapangan berhasil diubah"
            redirectTo="/court-schedule"
        />
    );
}

Edit.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Master Data', href: '#' },
            { label: 'Jadwal Lapangan', href: '/court-schedule' },
            { label: 'Edit', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
