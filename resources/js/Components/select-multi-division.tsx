import { useState } from "react";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select";
import { Button } from "./ui/button";
import { Plus, Trash } from "lucide-react";

interface SelectMultiDivisionProps {
    divisions: DivisionType[];
    onChange?: (selectedDivisions: DivisionType[]) => void;
}
export default function SelectMultiDivision({ divisions, onChange }: SelectMultiDivisionProps) {
    const modifiedDivisions = [{ code: 'GABUNGAN', name: 'GABUNGAN', id: 0 }, ...divisions];
    const [selectedDivisions, setSelectedDivisions] = useState<DivisionType[]>([]);
    const [selectValue, setSelectValue] = useState<string>("");
    const handleSelectChange = (value: string) => {
        const division = modifiedDivisions.find(d => d.id === parseInt(value));
        if (division && !selectedDivisions.some(d => d.id === division.id)) {
            const newSelected = [...selectedDivisions, division];
            setSelectedDivisions(newSelected);
            onChange?.(newSelected);
        }
        setSelectValue("");
    };
    const handleRemoveDivision = (division: DivisionType) => {
        const newSelected = selectedDivisions.filter(d => d.id !== division.id);
        setSelectedDivisions(newSelected);
        onChange?.(newSelected);
    }
    return (
        <div className="flex flex-wrap gap-2">
            <Select onValueChange={handleSelectChange} value={selectValue}>
                <SelectTrigger>
                    <SelectValue placeholder="Pilih Divisi" />
                </SelectTrigger>
                <SelectContent>
                    {modifiedDivisions.map((division) => (
                        <SelectItem
                            key={division.id}
                            value={`${division.id}`}
                        >
                            {division.name}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            {selectedDivisions.map((division) => (
                <div key={division.id} className="flex items-center border px-2 py-1 rounded gap-x-2">
                    <Button
                        variant="destructive"
                        size="sm"
                        onClick={handleRemoveDivision.bind(null, division)}
                    >
                        <Trash className="h-4 w-4" />
                    </Button>
                    <span>{division.name}</span>
                </div>
            ))}
        </div>
    );
}