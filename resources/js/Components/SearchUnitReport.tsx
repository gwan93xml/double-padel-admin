import axios from "axios"
import { useEffect, useState } from "react"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select"
type SearchUnitReportProps = {
    onChange1: (unit: string) => void
    onChange2: (unit: string) => void
    value1?: string
    value2?: string
    unit?: string
}
export default function SearchUnitReport({ value1, value2, onChange1, onChange2, unit }: SearchUnitReportProps) {
    const [units, setUnits] = useState<SubUnitType[]>([])
    useEffect(() => {
        const fetchUnits = async () => {
            const { data } = await axios.get(`/admin/unit/${unit}/by-unit`)
            setUnits(data.data.sub_units)
        }
        fetchUnits()
    }, [unit])
    return (
        <>
            <div className="flex gap-x-2">
                <Select
                    onValueChange={(value) => {
                        onChange1(value)
                    }}
                    value={value1}
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
                <Select
                    onValueChange={(value) => {
                        onChange2(value)
                    }}
                    value={value2}
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
            </div>
        </>
    )
}