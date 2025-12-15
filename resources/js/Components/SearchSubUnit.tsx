import axios from "axios"
import { forwardRef, useEffect, useImperativeHandle, useRef, useState } from "react"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select"

type SearchSubUnitProps = {
  onChange: (unit: string) => void
  value?: string
  itemId?: string
  nextRef?: React.RefObject<HTMLInputElement>
}

// Forward ref ke SelectTrigger
const SearchSubUnit = forwardRef<HTMLButtonElement, SearchSubUnitProps>(
  ({ value, onChange, itemId }, ref) => {
    const [subUnits, setSubUnits] = useState<UnitType[]>([])
    const triggerRef = useRef<HTMLButtonElement>(null)

    useImperativeHandle(ref, () => triggerRef.current!, [])

    useEffect(() => {
      const fetchUnits = async () => {
        const { data } = await axios.get(`/admin/item/${itemId}`)
        setSubUnits(data.data.units)
      }
      if (itemId) {
        fetchUnits()
      }
    }, [itemId])

    return (
      <Select onValueChange={onChange} value={value}>
        <SelectTrigger ref={triggerRef}>
          <SelectValue placeholder="Pilih Satuan" />
        </SelectTrigger>
        <SelectContent>
          {subUnits.map((unit) => (
            <SelectItem value={unit.name} key={unit.name}>
              {unit.name}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
    )
  }
)

export default SearchSubUnit
