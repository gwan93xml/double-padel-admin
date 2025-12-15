"use client"

import type React from "react"
import { useEffect, useState } from "react"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select"
import axios from "axios"

type AssetType = {
    id: string
    name: string
    description?: string
}

type SelectAssetTypeProps = {
    onChange: (assetType: AssetType) => void
    value?: AssetType
    nextRef?: React.RefObject<HTMLInputElement>
    clearable?: boolean
}

export default function SelectAssetType({
    onChange,
    value,
    nextRef,
    clearable = false,
}: SelectAssetTypeProps) {
    const [assetTypes, setAssetTypes] = useState<AssetType[]>([])
    const [isLoading, setIsLoading] = useState(false)
    const [error, setError] = useState("")

    // Load all asset types on component mount
    useEffect(() => {
        const fetchAssetTypes = async () => {
            setIsLoading(true)
            setError("")

            try {
                const { data } = await axios.get('/admin/asset-type/list', {
                    params: {
                        take: 1000, // Load all asset types
                    },
                })

                const assetTypesData = data.data || []
                setAssetTypes(assetTypesData)
            } catch (error) {
                console.error("Error fetching asset types:", error)
                setError("Failed to fetch asset types")
                setAssetTypes([])
            } finally {
                setIsLoading(false)
            }
        }

        fetchAssetTypes()
    }, [])

    const handleValueChange = (selectedId: string) => {
        if (selectedId === "clear" && clearable) {
            onChange({
                id: "",
                name: "",
                description: "",
            })
            return
        }

        const selectedAssetType = assetTypes.find(type => type.id === selectedId)
        if (selectedAssetType) {
            onChange(selectedAssetType)

            // Focus next element if provided
            if (nextRef?.current) {
                nextRef.current.focus()
            }
        }
    }

    const getCurrentValue = () => {
        if (!value || !value.id) return ""
        return value.id
    }

    if (error) {
        return (
            <div className="text-sm text-red-600 p-2 border border-red-200 rounded">
                {error}
            </div>
        )
    }

    return (
        <Select
            value={getCurrentValue()}
            onValueChange={handleValueChange}
            disabled={isLoading}
        >
            <SelectTrigger>
                <SelectValue placeholder={isLoading ? "Loading..." : "Pilih jenis asset"} />
            </SelectTrigger>
            <SelectContent>
                {clearable && (
                    <SelectItem value="clear">
                        <span className="text-gray-500">Semua Tipe Aset</span>
                    </SelectItem>
                )}
                {assetTypes.map((assetType) => (
                    <SelectItem key={assetType.id} value={assetType.id}>
                        <div className="flex flex-col">
                            <span className="font-medium">{assetType.name}</span>
                            {assetType.description && (
                                <span className="text-sm text-gray-500">{assetType.description}</span>
                            )}
                        </div>
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    )
}
