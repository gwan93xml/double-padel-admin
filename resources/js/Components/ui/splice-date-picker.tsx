
import { useState, useEffect } from "react"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select"
import { Label } from "@/Components/ui/label"
import moment from "moment"

interface SpliceDatePickerProps {
  value: string
  onChange: (date: string) => void
  range?: number
}

export default function SpliceDatePicker({ value, onChange, range }: SpliceDatePickerProps) {
  const [day, setDay] = useState<string>("")
  const [month, setMonth] = useState<string>("")
  const [year, setYear] = useState<string>("")

  const months = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
  ]

  const currentYear = new Date().getFullYear()
  let years;
  if (range) {
    years = Array.from({ length: 8 }, (_, i) => (currentYear - 4 + i).toString())
  } else {
    years = Array.from({ length: 201 }, (_, i) => (currentYear - 100 + i).toString())
  }

  useEffect(() => {
    if (value) {
      const date = new Date(value)
      if (!isNaN(date.getTime())) {
        setDay(date.getDate().toString())
        setMonth((date.getMonth() + 1).toString())
        setYear(date.getFullYear().toString())
      }
    } else {
      setDay(moment().format('D'))
      setMonth(moment().format('M'))
      setYear(moment().format('Y'))
    }
  }, [value])

  useEffect(() => {
    if (day && month && year) {
      const date = new Date(parseInt(year), parseInt(month) - 1, parseInt(day))
      if (!isNaN(date.getTime())) {
        console.log()
        onChange(moment(date).format('YYYY-MM-DD'))
      }
    }
  }, [day, month, year])

  const getDaysInMonth = (month: number, year: number) => {
    return new Date(year, month, 0).getDate()
  }

  const generateDayOptions = () => {
    const daysInMonth = month && year ? getDaysInMonth(parseInt(month), parseInt(year)) : 31
    return Array.from({ length: daysInMonth }, (_, i) => i + 1)
  }

  return (
    <div className="space-y-4">
      <div className="grid grid-cols-3 gap-4">
        <div className="space-y-2">
          <Select value={day} onValueChange={setDay}>
            <SelectTrigger id="day">
              <SelectValue placeholder="Day" />
            </SelectTrigger>
            <SelectContent>
              {generateDayOptions().map((d) => (
                <SelectItem key={d} value={d.toString()}>
                  {d}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
        <div className="space-y-2">
          <Select value={month} onValueChange={setMonth}>
            <SelectTrigger id="month">
              <SelectValue placeholder="Month" />
            </SelectTrigger>
            <SelectContent>
              {months.map((m, index) => (
                <SelectItem key={m} value={(index + 1).toString()}>
                  {m}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
        <div className="space-y-2">
          <Select value={year} onValueChange={setYear}>
            <SelectTrigger id="year">
              <SelectValue placeholder="Year" />
            </SelectTrigger>
            <SelectContent>
              {years.map((y) => (
                <SelectItem key={y} value={y}>
                  {y}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
      </div>
    </div>
  )
}

