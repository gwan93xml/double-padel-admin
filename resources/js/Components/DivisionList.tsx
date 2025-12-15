import React, { useEffect, useState } from "react"
import { Checkbox } from "@/Components/ui/checkbox"
import { Button } from "@/Components/ui/button"
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from "@/Components/ui/command"
import { Popover, PopoverContent, PopoverTrigger } from "@/Components/ui/popover"
import { Check, X, ChevronDown } from "lucide-react"
import axios from "axios"


type DivisionListProps = {
    selectedDivisions?: DivisionType[]
    onSelectionChange?: (divisions: DivisionType[]) => void
    placeholder?: string
    className?: string
}

export default function DivisionList({
    selectedDivisions = [],
    onSelectionChange,
    placeholder = "Cari divisi...",
    className = ""
}: DivisionListProps) {
    const [divisions, setDivisions] = useState<DivisionType[]>([])
    const [filteredDivisions, setFilteredDivisions] = useState<DivisionType[]>([])
    const [searchTerm, setSearchTerm] = useState("")
    const [isLoading, setIsLoading] = useState(false)
    const [selectedIds, setSelectedIds] = useState<Set<string | number>>(
        new Set(selectedDivisions.map(d => d.id).filter((id): id is string | number => id !== undefined))
    )

    // Load divisions on mount
    useEffect(() => {
        loadDivisions()
    }, [])

    // Update selectedIds when selectedDivisions prop changes
    useEffect(() => {
        setSelectedIds(new Set(selectedDivisions.map(d => d.id).filter((id): id is string | number => id !== undefined)))
    }, [selectedDivisions])

    // Filter divisions based on search term
    useEffect(() => {
        if (!searchTerm.trim()) {
            setFilteredDivisions(divisions)
        } else {
            const filtered = divisions.filter(division =>
                division.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
                division.code?.toLowerCase().includes(searchTerm.toLowerCase())
            )
            setFilteredDivisions(filtered)
        }
    }, [divisions, searchTerm])

    const loadDivisions = async () => {
        setIsLoading(true)
        try {
            const { data } = await axios.get('/admin/division/list', {
                params: {
                    take: 100
                }
            })
            setDivisions(data.data.map((item: any) => {
                return {
                    id: item.id,
                    name: item.name,
                    code: item.code
                }
            }) || [])
        } catch (error) {
            console.error('Failed to load divisions:', error)
            setDivisions([])
        } finally {
            setIsLoading(false)
        }
    }

    const handleDivisionToggle = (division: DivisionType) => {
        if (!division.id) return

        const newSelectedIds = new Set(selectedIds)
        if (newSelectedIds.has(division.id)) {
            newSelectedIds.delete(division.id)
        } else {
            newSelectedIds.add(division.id)
        }

        setSelectedIds(newSelectedIds)

        // Convert selected IDs back to division objects
        const selectedDivisionsList = divisions.filter(d => newSelectedIds.has(d.id!))
        onSelectionChange?.(selectedDivisionsList)
    }

    const handleSelectAll = () => {
        const allIds = new Set(divisions.map(d => d.id).filter((id): id is string | number => id !== undefined))
        setSelectedIds(allIds)
        onSelectionChange?.(divisions)
    }

    const handleClearAll = () => {
        setSelectedIds(new Set())
        onSelectionChange?.([])
    }

    const isAllSelected = divisions.length > 0 && selectedIds.size === divisions.length
    const isNoneSelected = selectedIds.size === 0

    return (
        <div className={className}>
            <Popover>
                <PopoverTrigger asChild>
                    <Button
                        variant="outline"
                        role="combobox"
                        className="w-full justify-between"
                    >
                        {selectedIds.size === 0
                            ? "Pilih divisi..."
                            : `${selectedIds.size} divisi dipilih`
                        }
                        <ChevronDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                    </Button>
                </PopoverTrigger>
                <PopoverContent className="w-full p-0" align="start">
                    <div className="p-2">
                        {/* Actions */}
                        <div className="flex items-center gap-2 mb-2">
                            <Button
                                type="button"
                                size="sm"
                                onClick={handleSelectAll}
                                disabled={isAllSelected}
                            >
                                <Check className="h-4 w-4 mr-1" />
                                All
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={handleClearAll}
                                disabled={isNoneSelected}
                            >
                                <X className="h-4 w-4 mr-1" />
                                Clear
                            </Button>
                        </div>

                        {/* Division List with Command */}
                        <Command className="rounded-lg">
                            <CommandInput
                                placeholder={placeholder}
                                value={searchTerm}
                                onValueChange={setSearchTerm}
                            />
                            <CommandList className="max-h-64">
                                <CommandEmpty>
                                    {isLoading ? 'Loading divisions...' : searchTerm ? 'No divisions found' : 'No divisions available'}
                                </CommandEmpty>
                                <CommandGroup>
                                    {filteredDivisions.map((division) => (
                                        <CommandItem
                                            key={division.id}
                                            onSelect={() => handleDivisionToggle(division)}
                                            className="flex items-center space-x-3 p-2 cursor-pointer"
                                        >
                                            <Checkbox
                                                checked={selectedIds.has(division.id!)}
                                                onCheckedChange={() => handleDivisionToggle(division)}
                                                onClick={(e) => e.stopPropagation()}
                                            />
                                            <div className="flex-1">
                                                <div className="font-medium">{division.name}</div>
                                                <div className="text-gray-500 text-xs">{division.code}</div>
                                            </div>
                                            {selectedIds.has(division.id!) && (
                                                <Check className="h-4 w-4 text-green-600" />
                                            )}
                                        </CommandItem>
                                    ))}
                                </CommandGroup>
                            </CommandList>
                        </Command>
                    </div>
                </PopoverContent>
            </Popover>

        </div>
    )
}