"use client"

import type React from "react"
import { Search, Loader2, X } from "lucide-react"
import { Button } from "@/Components/ui/button"
import { Input } from "@/Components/ui/input"
import { Card, CardContent } from "@/Components/ui/card"
import { Badge } from "@/Components/ui/badge"
import { useEffect, useState, useRef, useCallback } from "react"
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
    const [assetType, setAssetType] = useState<AssetType>({
        id: "",
        name: "",
        description: "",
    })
    const [isLoading, setIsLoading] = useState(false)
    const [error, setError] = useState("")
    const [list, setList] = useState<AssetType[]>([])
    const [showDropdown, setShowDropdown] = useState(false)

    // Keyboard navigation state
    const [selectedIndex, setSelectedIndex] = useState(-1)

    // Infinite scroll states
    const [page, setPage] = useState(1)
    const [hasMore, setHasMore] = useState(true)
    const [isLoadingMore, setIsLoadingMore] = useState(false)
    const [currentQuery, setCurrentQuery] = useState("")

    const dropdownRef = useRef<HTMLDivElement>(null)
    const containerRef = useRef<HTMLDivElement>(null)
    const itemRefs = useRef<(HTMLDivElement | null)[]>([])
    const searchTimeoutRef = useRef<NodeJS.Timeout>()

    // Initialize from props
    useEffect(() => {
        if (value) {
            setAssetType({
                id: value.id || "",
                name: value.name || "",
                description: value.description || "",
            })
        }
    }, [])

    // Reset selected index when list changes
    useEffect(() => {
        setSelectedIndex(-1)
        itemRefs.current = []
    }, [list])

    // Scroll selected item into view
    const scrollToSelectedItem = useCallback((index: number) => {
        if (itemRefs.current[index] && dropdownRef.current) {
            const item = itemRefs.current[index]
            const container = dropdownRef.current

            if (item) {
                const itemRect = item.getBoundingClientRect()
                const containerRect = container.getBoundingClientRect()

                if (itemRect.bottom > containerRect.bottom) {
                    container.scrollTop += itemRect.bottom - containerRect.bottom
                } else if (itemRect.top < containerRect.top) {
                    container.scrollTop -= containerRect.top - itemRect.top
                }
            }
        }
    }, [])

    // Handle keyboard navigation
    const handleKeyDown = useCallback(
        (e: React.KeyboardEvent<HTMLInputElement>) => {
            if (!showDropdown) return

            switch (e.key) {
                case "ArrowDown":
                    e.preventDefault()
                    setSelectedIndex((prev) => {
                        const next = prev < list.length - 1 ? prev + 1 : 0
                        scrollToSelectedItem(next)
                        return next
                    })
                    break

                case "ArrowUp":
                    e.preventDefault()
                    setSelectedIndex((prev) => {
                        const next = prev > 0 ? prev - 1 : list.length - 1
                        scrollToSelectedItem(next)
                        return next
                    })
                    break

                case "Enter":
                    e.preventDefault()
                    if (selectedIndex >= 0 && list[selectedIndex]) {
                        selectAssetType(list[selectedIndex])
                    }
                    break

                case "Escape":
                    e.preventDefault()
                    setShowDropdown(false)
                    setSelectedIndex(-1)
                    break
            }
        },
        [showDropdown, list, selectedIndex, scrollToSelectedItem],
    )

    // Close dropdown when clicking outside
    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (
                containerRef.current &&
                !containerRef.current.contains(event.target as Node)
            ) {
                setShowDropdown(false)
                setSelectedIndex(-1)
            }
        }

        document.addEventListener("mousedown", handleClickOutside)
        return () => document.removeEventListener("mousedown", handleClickOutside)
    }, [])

    const fetchAssetTypes = async (query: string, pageNum: number = 1, reset: boolean = true) => {
        if (!query.trim() && pageNum === 1) return

        setIsLoading(true)
        setError("")

        try {
            const { data } = await axios.get('/admin/asset-type/list', {
                params: {
                    search: query,
                    page: pageNum,
                    take: 10,
                },
            })

            const newAssetTypes = data.data.data || []

            if (reset) {
                setList(newAssetTypes)
            } else {
                setList((prev) => [...prev, ...newAssetTypes])
            }

            // Check if there are more items to load
            setHasMore(data.data.next_page_url !== null)
            setShowDropdown(newAssetTypes.length > 0 || list.length > 0)
        } catch (error) {
            console.error("Error fetching asset types:", error)
            setError("Failed to fetch asset types")
        } finally {
            setIsLoading(false)
            setIsLoadingMore(false)
        }
    }

    const loadMore = useCallback(() => {
        if (!isLoadingMore && hasMore && currentQuery) {
            const nextPage = page + 1
            setPage(nextPage)
            fetchAssetTypes(currentQuery, nextPage, false)
        }
    }, [isLoadingMore, hasMore, currentQuery, page])

    // Fixed scroll handler with better detection
    const handleScroll = useCallback(
        (event: React.UIEvent<HTMLDivElement>) => {
            const target = event.currentTarget
            const { scrollTop, scrollHeight, clientHeight } = target

            // Trigger load more when within 50px of bottom
            if (scrollHeight - scrollTop - clientHeight < 50) {
                loadMore()
            }
        },
        [loadMore],
    )

    const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const newName = e.target.value
        setAssetType((prev) => ({ ...prev, name: newName }))

        // Clear previous timeout
        if (searchTimeoutRef.current) {
            clearTimeout(searchTimeoutRef.current)
        }

        // Debounce search to prevent too many API calls
        searchTimeoutRef.current = setTimeout(() => {
            if (newName.trim()) {
                setCurrentQuery(newName)
                setPage(1)
                fetchAssetTypes(newName, 1, true)
            } else {
                setList([])
                setShowDropdown(false)
            }
        }, 300)
    }

    const selectAssetType = (selectedAssetType: AssetType) => {
        setAssetType(selectedAssetType)
        onChange(selectedAssetType)
        setShowDropdown(false)
        setSelectedIndex(-1)

        // Focus next element if provided
        if (nextRef?.current) {
            nextRef.current.focus()
        }
    }

    const clearSelection = () => {
        setAssetType({
            id: "",
            name: "",
            description: "",
        })
        onChange({
            id: "",
            name: "",
            description: "",
        })
        setList([])
        setShowDropdown(false)
        setSelectedIndex(-1)
    }

    return (
        <div className="relative" ref={containerRef}>
            <div className="relative">
                <Input
                    type="text"
                    placeholder="Cari jenis asset..."
                    value={assetType.name}
                    onChange={handleNameChange}
                    onKeyDown={handleKeyDown}
                    className="pr-10"
                    autoComplete="off"
                />
                <div className="absolute inset-y-0 right-0 flex items-center pr-3">
                    {isLoading ? (
                        <Loader2 className="h-4 w-4 animate-spin text-gray-400" />
                    ) : assetType.id && clearable ? (
                        <button
                            type="button"
                            onClick={clearSelection}
                            className="text-gray-400 hover:text-gray-600"
                        >
                            <X className="h-4 w-4" />
                        </button>
                    ) : (
                        <Search className="h-4 w-4 text-gray-400" />
                    )}
                </div>
            </div>

            {error && (
                <div className="mt-1 text-sm text-red-600">{error}</div>
            )}

            {showDropdown && (
                <Card className="absolute z-50 w-full mt-1 max-h-60 overflow-hidden">
                    <CardContent className="p-0">
                        <div
                            ref={dropdownRef}
                            className="max-h-60 overflow-y-auto"
                            onScroll={handleScroll}
                        >
                            {list.map((item, index) => (
                                <div
                                    key={item.id}
                                    ref={(el) => (itemRefs.current[index] = el)}
                                    className={`p-3 cursor-pointer border-b last:border-b-0 hover:bg-gray-50 ${
                                        selectedIndex === index ? "bg-blue-50" : ""
                                    }`}
                                    onClick={() => selectAssetType(item)}
                                >
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <div className="font-medium">{item.name}</div>
                                            {item.description && (
                                                <div className="text-sm text-gray-500">
                                                    {item.description}
                                                </div>
                                            )}
                                        </div>
                                        {selectedIndex === index && (
                                            <Badge variant="secondary">Enter</Badge>
                                        )}
                                    </div>
                                </div>
                            ))}

                            {isLoadingMore && (
                                <div className="p-3 text-center text-gray-500">
                                    <Loader2 className="h-4 w-4 animate-spin inline mr-2" />
                                    Loading more...
                                </div>
                            )}

                            {!isLoadingMore && !hasMore && list.length > 0 && (
                                <div className="p-3 text-center text-gray-500">
                                    No more results
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>
            )}
        </div>
    )
}
