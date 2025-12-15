
import * as React from "react"
import { format } from "date-fns"
import { Calendar as CalendarIcon, Clock } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/Components/ui/button"
import { Calendar } from "@/Components/ui/calendar"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/Components/ui/popover"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/Components/ui/select"

interface DateTimePickerProps {
  value?: Date
  onChange?: (date: Date | undefined) => void
  placeholder?: string
}

export function DateTimePicker({ value, onChange, placeholder = "Pilih tanggal dan waktu" }: DateTimePickerProps) {
  const [date, setDate] = React.useState<Date | undefined>(value)
  const [hour, setHour] = React.useState<string | undefined>(value ? format(value, "HH") : undefined)
  const [minute, setMinute] = React.useState<string | undefined>(value ? format(value, "mm") : undefined)

  React.useEffect(() => {
    if (date && hour && minute) {
      const newDate = new Date(date)
      newDate.setHours(parseInt(hour, 10))
      newDate.setMinutes(parseInt(minute, 10))
      onChange?.(newDate)
    }
  }, [date, hour, minute, onChange])

  const hourOptions = React.useMemo(() => {
    return Array.from({ length: 24 }, (_, i) => i.toString().padStart(2, '0'))
  }, [])

  const minuteOptions = React.useMemo(() => {
    return Array.from({ length: 60 }, (_, i) => i.toString().padStart(2, '0'))
  }, [])

  return (
    <Popover>
      <PopoverTrigger asChild>
        <Button
          variant={"outline"}
          className={cn(
            "w-full justify-start text-left font-normal",
            !date && "text-muted-foreground"
          )}
        >
          <CalendarIcon className="mr-2 h-4 w-4" />
          {date ? format(date, "PPP") : <span>{placeholder}</span>}
          {hour && minute && <span className="ml-auto">{`${hour}:${minute}`}</span>}
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-auto p-0" align="start">
        <Calendar
          mode="single"
          selected={date}
          onSelect={setDate}
          initialFocus
        />
        <div className="p-3 border-t border-border">
          <div className="flex items-center space-x-2">
            <Clock className="h-4 w-4 opacity-50" />
            <Select value={hour} onValueChange={setHour}>
              <SelectTrigger className="w-[70px]">
                <SelectValue placeholder="Jam" />
              </SelectTrigger>
              <SelectContent>
                {hourOptions.map((option) => (
                  <SelectItem key={option} value={option}>
                    {option}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
            <span>:</span>
            <Select value={minute} onValueChange={setMinute}>
              <SelectTrigger className="w-[70px]">
                <SelectValue placeholder="Menit" />
              </SelectTrigger>
              <SelectContent>
                {minuteOptions.map((option) => (
                  <SelectItem key={option} value={option}>
                    {option}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        </div>
      </PopoverContent>
    </Popover>
  )
}

