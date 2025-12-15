import React, { useEffect, useState } from "react"
import { Checkbox } from "@/Components/ui/checkbox"
import { Button } from "@/Components/ui/button"
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from "@/Components/ui/command"
import { Popover, PopoverContent, PopoverTrigger } from "@/Components/ui/popover"
import { Check, X, ChevronDown } from "lucide-react"
import axios from "axios"


type WarehouseListProps = {
    selectedWarehouses?: WarehouseType[]
    onSelectionChange?: (warehouses: WarehouseType[]) => void
    placeholder?: string
    className?: string
}

export default function WarehouseList({
    selectedWarehouses = [],
    onSelectionChange,
    placeholder = "Cari warehouse...",
    className = ""
}: WarehouseListProps) {
    const [warehouses, setWarehouses] = useState<WarehouseType[]>([])
    const [filteredWarehouses, setFilteredWarehouses] = useState<WarehouseType[]>([])
    const [searchTerm, setSearchTerm] = useState("")
    const [isLoading, setIsLoading] = useState(false)
    const [selectedIds, setSelectedIds] = useState<Set<string | undefined>>(
        new Set(selectedWarehouses.map(w => w.id).filter((id): id is string | undefined => id !== undefined))
    )

    // Load warehouses on mount
    useEffect(() => {
        loadWarehouses()
    }, [])

    // Update selectedIds when selectedWarehouses prop changes
    useEffect(() => {
        setSelectedIds(new Set(selectedWarehouses.map(w => w.id).filter((id): id is string | undefined => id !== undefined)))
    }, [selectedWarehouses])

    // Filter warehouses based on search term
    useEffect(() => {
        if (!searchTerm.trim()) {
            setFilteredWarehouses(warehouses)
        } else {
            const filtered = warehouses.filter(warehouse =>
                warehouse.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
                warehouse.code?.toLowerCase().includes(searchTerm.toLowerCase())
            )
            setFilteredWarehouses(filtered)
        }
    }, [warehouses, searchTerm])

    const loadWarehouses = async () => {
        setIsLoading(true)
        try {
            const { data } = await axios.get('/admin/warehouse/list', {
                params: {
                    take: 100
                }
            })
            setWarehouses(data.data.map((item: any) => {
                return {
                    id: item.id,
                    name: item.name,
                    code: item.code
                }
            }) || [])
        } catch (error) {
            console.error('Failed to load warehouses:', error)
            setWarehouses([])
        } finally {
            setIsLoading(false)
        }
    }

    const handleWarehouseToggle = (warehouse: WarehouseType) => {
        if (!warehouse.id) return

        const newSelectedIds = new Set(selectedIds)
        if (newSelectedIds.has(warehouse.id)) {
            newSelectedIds.delete(warehouse.id)
        } else {
            newSelectedIds.add(warehouse.id)
        }

        setSelectedIds(newSelectedIds)

        // Convert selected IDs back to warehouse objects
        const selectedWarehousesList = warehouses.filter(w => newSelectedIds.has(w.id!))
        onSelectionChange?.(selectedWarehousesList)
    }

    const handleSelectAll = () => {
        const allIds = new Set(warehouses.map(w => w.id).filter((id): id is string | undefined => id !== undefined))
        setSelectedIds(allIds)
        onSelectionChange?.(warehouses)
    }

    const handleClearAll = () => {
        setSelectedIds(new Set())
        onSelectionChange?.([])
    }

    const isAllSelected = warehouses.length > 0 && selectedIds.size === warehouses.length
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
                            ? "Pilih warehouse..."
                            : `${selectedIds.size} warehouse dipilih`
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

                        {/* Warehouse List with Command */}
                        <Command className="rounded-lg">
                            <CommandInput
                                placeholder={placeholder}
                                value={searchTerm}
                                onValueChange={setSearchTerm}
                            />
                            <CommandList className="max-h-64">
                                <CommandEmpty>
                                    {isLoading ? 'Loading warehouses...' : searchTerm ? 'No warehouses found' : 'No warehouses available'}
                                </CommandEmpty>
                                <CommandGroup>
                                    {filteredWarehouses.map((warehouse) => (
                                        <CommandItem
                                            key={warehouse.id}
                                            onSelect={() => handleWarehouseToggle(warehouse)}
                                            className="flex items-center space-x-3 p-2 cursor-pointer"
                                        >
                                            <Checkbox
                                                checked={selectedIds.has(warehouse.id!)}
                                                onCheckedChange={() => handleWarehouseToggle(warehouse)}
                                                onClick={(e) => e.stopPropagation()}
                                            />
                                            <div className="flex-1">
                                                <div className="font-medium">{warehouse.name}</div>
                                                <div className="text-gray-500 text-xs">{warehouse.code}</div>
                                            </div>
                                            {selectedIds.has(warehouse.id!) && (
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