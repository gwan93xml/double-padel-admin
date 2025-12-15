
import { Check, ChevronsUpDown } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/Components/ui/button"
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from "@/Components/ui/command"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/Components/ui/popover"
import { useState } from "react"
import { Badge } from "./badge"

const frameworks = [
  {
    value: "next.js",
    label: "Next.js",
  },
  {
    value: "sveltekit",
    label: "SvelteKit",
  },
  {
    value: "nuxt.js",
    label: "Nuxt.js",
  },
  {
    value: "remix",
    label: "Remix",
  },
  {
    value: "astro",
    label: "Astro",
  },
]

export function MultiSelectSearch() {
  const [open, setOpen] = useState(false)
  const [value, setValue] = useState("")
  const [values, setValues] = useState([] as string[])
  console.log(values)
  return (
    <Popover open={open} onOpenChange={setOpen} >
      <PopoverTrigger asChild>
        <div>
          <Button
            type="button"
            variant="outline"
            role="combobox"
            aria-expanded={open}
            className="w-full justify-between"
          >
            <div className="flex gap-x-1">
              {values.map((value, index) => {
                if (index < 2) {
                  return (
                    <Badge>
                      {value}
                    </Badge>
                  )
                } else if (index == 2) {
                  return (
                    <Badge>
                      +{values.length - 2} more
                    </Badge>
                  )
                }
              })}
              {values.length === 0 && (
                <span className="text-muted-foreground">Select framework</span>
              )}
            </div>
            <ChevronsUpDown className="opacity-50" />
          </Button>
        </div>
      </PopoverTrigger>
      <PopoverContent className="w-full p-0">
        <Command>
          <CommandInput placeholder="Search framework..." />
          <CommandList>
            <CommandEmpty>No framework found.</CommandEmpty>
            <CommandGroup>
              {frameworks.map((framework) => (
                <CommandItem
                  key={framework.value}
                  value={framework.value}
                  onSelect={(currentValue) => {
                    setValue(currentValue === value ? "" : currentValue)
                    setValues((prev: any) => {
                      if (prev.includes(currentValue)) {
                        return prev.filter((v: any) => v !== currentValue)
                      }
                      return [...prev, currentValue]
                    })

                  }}
                >
                  {framework.label}
                  <Check
                    className={cn(
                      "ml-auto",
                      values.includes(framework.value) ? 'opacity-100' : 'opacity-0'
                    )}
                  />
                </CommandItem>
              ))}
            </CommandGroup>
          </CommandList>
        </Command>
      </PopoverContent>
    </Popover>
  )
}
