import axios from "axios"
import { useEffect, useState } from "react"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select"
type SearchUnitProps = {
    onChange: (unit: string) => void
    value?: string
}
export default function SearchUnit({ value, onChange }: SearchUnitProps) {
    const [units, setUnits] = useState<UnitType[]>([])
    useEffect(() => {
        const fetchUnits = async () => {
            const { data } = await axios.get('/admin/unit/browse?no_pagination=true')
            setUnits(data.data)
        }
        fetchUnits()
    }, [])
    return (
        <>
            <Select
                onValueChange={(value) => {
                    onChange(value)
                }}
                value={value}
            >
                <SelectTrigger >
                    <SelectValue placeholder="Pilih Satuan" />
                </SelectTrigger>
                <SelectContent>
                    {units.map((unit) => (
                        <SelectItem value={unit.name} key={unit.name}>{unit.name}</SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </>
    )
}