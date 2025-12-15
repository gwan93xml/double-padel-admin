import axios from "axios"
import { useEffect, useState } from "react"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select"
import { ItemCategoryType } from "@/Pages/ItemCategory/@types/item-category-type"
type SearchItemCategoryProps = {
    onChange: (itemCategory: string) => void
    value?: string
}
export default function SearchItemCategory({ value, onChange }: SearchItemCategoryProps) {
    const [itemCategories, setItemCategories] = useState<ItemCategoryType[]>([])
    useEffect(() => {
        const fetchUnits = async () => {
            const { data } = await axios.get('/admin/item-category/list?no_pagination=true')
            setItemCategories(data.data)
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
                    <SelectValue placeholder="Pilih Kategori" />
                </SelectTrigger>
                <SelectContent>
                    {itemCategories.map((itemCategory) => (
                        <SelectItem value={itemCategory.id!} key={itemCategory.id}>{itemCategory.name}</SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </>
    )
}