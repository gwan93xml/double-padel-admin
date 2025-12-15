import axios from "axios"
import { useEffect, useState } from "react"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select"
type SearchTypeOfTaxProps = {
    onChange: (typeOfTax: string) => void
    value?: string
}
export default function SearchTypeOfTax({ value, onChange }: SearchTypeOfTaxProps) {
    const [typeOfTaxes, setTypeOfTaxes] = useState<Type_ofTaxType[]>([])
    useEffect(() => {
        const fetchUnits = async () => {
            const { data } = await axios.get('/admin/type-of-tax/list?no_pagination=true')
            setTypeOfTaxes(data.data)
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
                    <SelectValue placeholder="PJK" />
                </SelectTrigger>
                <SelectContent>
                    {typeOfTaxes.map((typeOfTax) => (
                        <SelectItem value={typeOfTax.id!} key={typeOfTax.id}>{typeOfTax.name}</SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </>
    )
}