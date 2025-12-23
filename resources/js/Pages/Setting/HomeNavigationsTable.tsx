import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import ImageUploader from "@/Components/ImageUploader";
import { toast } from "@/hooks/use-toast";
import { Plus, Trash2 } from "lucide-react";

interface HomeNavigation {
    title: string;
    href: string;
    image: string;
    icon: string;
}

interface HomeNavigationsTableProps {
    value: HomeNavigation[];
    onChange: (navigations: HomeNavigation[]) => void;
}

export default function HomeNavigationsTable({ value = [], onChange }: HomeNavigationsTableProps) {
    const handleAdd = () => {
        const newNavigations = [...value, {
            title: '',
            href: '',
            image: '',
            icon: ''
        }];
        onChange(newNavigations);
    };

    const handleDelete = (index: number) => {
        const newNavigations = [...value];
        newNavigations.splice(index, 1);
        onChange(newNavigations);
    };

    const handleUpdate = (index: number, field: keyof HomeNavigation, newValue: string) => {
        const newNavigations = [...value];
        newNavigations[index] = {
            ...newNavigations[index],
            [field]: newValue
        };
        onChange(newNavigations);
    };

    return (
        <div className="space-y-3">
            <div className="border rounded-lg overflow-hidden">
                <table className="w-full">
                    <thead className="bg-muted">
                        <tr>
                            <th className="text-left p-2 font-medium text-sm">Title</th>
                            <th className="text-left p-2 font-medium text-sm">URL</th>
                            <th className="text-left p-2 font-medium text-sm">Icon</th>
                            <th className="text-left p-2 font-medium text-sm">Image</th>
                            <th className="text-center p-2 font-medium text-sm w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {value.map((nav, index) => (
                            <tr key={index} className="border-t">
                                <td className="p-2">
                                    <Input
                                        value={nav.title || ''}
                                        onChange={(e) => handleUpdate(index, 'title', e.target.value)}
                                        placeholder="Title"
                                        className="h-8"
                                    />
                                </td>
                                <td className="p-2">
                                    <Input
                                        value={nav.href || ''}
                                        onChange={(e) => handleUpdate(index, 'href', e.target.value)}
                                        placeholder="URL"
                                        className="h-8"
                                    />
                                </td>
                                <td className="p-2">
                                    <Input
                                        value={nav.icon || ''}
                                        onChange={(e) => handleUpdate(index, 'icon', e.target.value)}
                                        placeholder="Icon"
                                        className="h-8"
                                    />
                                </td>
                                <td className="p-2">
                                    <ImageUploader
                                        maxHeight={150}
                                        label=""
                                        initialValue={nav.image}
                                        onSuccess={(url) => {
                                            handleUpdate(index, 'image', url);
                                            toast({
                                                title: 'Sukses',
                                                description: 'Gambar berhasil diunggah',
                                            });
                                        }}
                                        onError={(error) => {
                                            toast({
                                                variant: 'destructive',
                                                title: 'Gagal',
                                                description: error,
                                            });
                                        }}
                                    />
                                </td>
                                <td className="p-2 text-center">
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        size="sm"
                                        onClick={() => handleDelete(index)}
                                    >
                                        <Trash2 className="w-4 h-4" />
                                    </Button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            
            <Button
                type="button"
                variant="outline"
                onClick={handleAdd}
                size="sm"
            >
                <Plus className="w-4 h-4 mr-2" />
                Tambah Navigation
            </Button>
        </div>
    );
}
