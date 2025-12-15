"use client"

import type React from "react"
import { Search, Package, Loader2 } from "lucide-react"
import { Button } from "@/Components/ui/button"
import { Input } from "@/Components/ui/input"
import { Card, CardContent } from "@/Components/ui/card"
import { Badge } from "@/Components/ui/badge"
import { useEffect, useState, useRef, useCallback } from "react"
import axios from "axios"

type Item = {
    id: string
    item_name: string
    price: string
    unit: string
    description?: string
}

type SearchItemNonStockProps = {
    onChange: (item: Item) => void
    value?: Item
    nextRef?: React.RefObject<HTMLInputElement>
    itemCodeRef?: React.RefObject<HTMLInputElement>
    itemNameRef?: React.RefObject<HTMLInputElement>
    source?: string // Optional source prop for search
}

export default function SearchItemNonStock({
    onChange,
    value,
    nextRef,
    itemCodeRef,
    itemNameRef,
    source

}: SearchItemNonStockProps) {
    const [item, setItem] = useState<Item>({
        id: "",
        item_name: "",
        price: "0",
        unit: "",
        description: "",
    })
    const [isLoading, setIsLoading] = useState(false)
    const [error, setError] = useState("")
    const [list, setList] = useState<Item[]>([])
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
            setItem({
                id: value.id || "",
                item_name: value.item_name || "",
                price: value.price || "0",
                unit: value.unit || "",
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
                const itemTop = item.offsetTop
                const itemBottom = itemTop + item.offsetHeight
                const containerTop = container.scrollTop
                const containerBottom = containerTop + container.clientHeight

                if (itemTop < containerTop) {
                    container.scrollTop = itemTop - 8
                } else if (itemBottom > containerBottom) {
                    container.scrollTop = itemBottom - container.clientHeight + 8
                }
            }
        }
    }, [])


    const handleKeyNavigation = useCallback((e: React.KeyboardEvent) => {
        if (!showDropdown || list.length === 0) {
            if (e.key === "Enter") {
                nextRef?.current?.focus() // Focus next input if available
            }
            return
        }

        switch (e.key) {
            case "ArrowDown":
                e.preventDefault()
                setSelectedIndex((prev) => {
                    const newIndex = prev < list.length - 1 ? prev + 1 : 0
                    setTimeout(() => scrollToSelectedItem(newIndex), 0)
                    return newIndex
                })
                break

            case "ArrowUp":
                e.preventDefault()
                setSelectedIndex((prev) => {
                    const newIndex = prev > 0 ? prev - 1 : list.length - 1
                    setTimeout(() => scrollToSelectedItem(newIndex), 0)
                    return newIndex
                })
                break

            case "Enter":
                e.preventDefault()
                if (selectedIndex >= 0 && selectedIndex < list.length) {
                    selectItem(list[selectedIndex])
                    console.log("Selected item:", list[selectedIndex])
                    nextRef?.current?.focus() // Focus next input if available
                } else {
                    // If no item is selected, perform search
                    if (e.currentTarget === document.activeElement) {
                        const target = e.currentTarget as HTMLInputElement
                        searchByCode(item.item_name ?? "")
                    }
                }
                break

            case "Escape":
                e.preventDefault()
                setShowDropdown(false)
                setSelectedIndex(-1)
                break
        }
    },
        [showDropdown, list, selectedIndex, item.item_name],
    )


    const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const newName = e.target.value
        setItem((prev) => ({ ...prev, item_name: newName }))

        onChange({
            ...item,
            item_name: newName,
            id: "", // Clear ID to indicate new search
            price: "0", // Reset price on name change
            unit: "", // Reset unit on name change
            description: "", // Reset description on name change
        })
        // Clear previous timeout
        if (searchTimeoutRef.current) {
            clearTimeout(searchTimeoutRef.current)
        }

        // Debounce search to prevent too many API calls
        searchTimeoutRef.current = setTimeout(() => {
            handleSearch(newName)
        }, 300)
        setError("")
    }


    const handleSearch = useCallback((query: string) => {
        if (query.length < 2) {
            setList([])
            setShowDropdown(false)
            setCurrentQuery("")
            setPage(1)
            setHasMore(true)
            return
        }

        setCurrentQuery(query)
        setPage(1)
        setHasMore(true)
        fetchItems(query, 1, true)
    }, [])

    const fetchItems = async (query: string, pageNum: number, reset = false) => {
        if (reset) {
            setIsLoading(true)
        } else {
            setIsLoadingMore(true)
        }

        try {
            const response = await axios.get(`/admin/item-non-stock/browse`, {
                params: {
                    search: query,
                    page: pageNum,
                    limit: 10,
                    source: source || "purchase", // Default to purchase if no source provided
                },
            })

            const newItems = response.data.data || []

            if (reset) {
                setList(newItems)
            } else {
                setList((prev) => [...prev, ...newItems])
            }

            // Check if there are more items to load
            setHasMore(response.data.next_page_url !== null)
            setShowDropdown(newItems.length > 0 || list.length > 0)
        } catch (error) {
            console.error("Error fetching items:", error)
            setError("Failed to fetch items")
        } finally {
            setIsLoading(false)
            setIsLoadingMore(false)
        }
    }

    const loadMore = useCallback(() => {
        if (!isLoadingMore && hasMore && currentQuery) {
            const nextPage = page + 1
            setPage(nextPage)
            fetchItems(currentQuery, nextPage, false)
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

    const searchByCode = async (code: string) => {
        if (!code.trim()) return

        setIsLoading(true)
        setError("")

        try {
            const { data } = await axios.get(`/admin/item-non-stock/search-by-code?search=${code}&source=${source}`)
            if (data.data) {
                setItem({
                    id: data.data.id,
                    item_name: data.data.item_name,
                    price: data.data.price,
                    unit: data.data.unit,
                    description: data.data.description,
                })
                onChange({
                    id: data.data.id,
                    price: data.data.price,
                    item_name: data.data.item_name,
                    unit: data.data.unit,
                    description: data.data.description,
                })
                setShowDropdown(false)
            } else {
                setError("Item not found")
            }
        } catch (err) {
            setError("Failed to search item")
            console.error(err)
        } finally {
            setIsLoading(false)
        }
    }

    const selectItem = async (selectedItem: Item) => {
        try {

            setItem({
                id: selectedItem.id,
                item_name: selectedItem.item_name,
                price: selectedItem.price,
                unit: selectedItem.unit,
                description: selectedItem.description,
            })
            onChange({
                id: selectedItem.id,
                item_name: selectedItem.item_name,
                price: selectedItem.price,
                unit: selectedItem.unit,
                description: selectedItem.description,
            })
            setShowDropdown(false)
            setList([])
        } catch (error) {
            console.error("Error selecting item:", error)
        }
    }

    // Close dropdown when clicking outside
    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
                setShowDropdown(false)
            }
        }

        if (showDropdown) {
            document.addEventListener("mousedown", handleClickOutside)
            return () => document.removeEventListener("mousedown", handleClickOutside)
        }
    }, [showDropdown])


    // Cleanup timeout on unmount
    useEffect(() => {
        return () => {
            if (searchTimeoutRef.current) {
                clearTimeout(searchTimeoutRef.current)
            }
        }
    }, [])


    return (
        <div className="space-y-3" ref={containerRef}>
            <div className="relative">
                <div className="flex gap-0 border rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-ring">
                    <div className="flex-1">
                        <Input
                            className="border-0 rounded-none focus-visible:ring-0 focus-visible:ring-offset-0"
                            placeholder="Item"
                            ref={itemCodeRef}
                            value={item.item_name}
                            onChange={handleNameChange}
                            onKeyDown={handleKeyNavigation}
                        />
                    </div>
                </div>

                {/* Fixed dropdown with native scroll */}
                {showDropdown && (
                    <Card className="absolute top-full left-0 right-0 z-[9999] mt-1 shadow-xl border-2">
                        <CardContent className="p-0">
                            {/* Native scrollable div with infinite scroll */}
                            <div
                                ref={dropdownRef}
                                className="max-h-64 overflow-y-auto overscroll-contain"
                                onScroll={handleScroll}
                                style={{
                                    scrollbarWidth: "thin",
                                    scrollbarColor: "#cbd5e1 #f1f5f9",
                                }}
                            >
                                <div className="p-2">
                                    <div className="text-xs text-muted-foreground mb-2 px-2 sticky top-0 bg-white dark:bg-black">
                                        Search Results ({currentQuery}) - {list.length} items
                                    </div>

                                    {list.map((listItem, index) => (
                                        <div
                                            key={`${listItem.id}-${index}`}
                                            ref={(el) => (itemRefs.current[index] = el)}
                                            className={`flex items-center justify-between p-3 rounded-md cursor-pointer transition-colors ${selectedIndex === index ? "bg-primary/10 border border-primary/20" : "hover:bg-accent"
                                                }`}
                                            onClick={() => selectItem(listItem)}
                                            onMouseEnter={() => setSelectedIndex(index)}
                                        >
                                            <div className="flex items-center gap-3">
                                                <div className="p-2 bg-primary/10 rounded-md">
                                                    <Package className="h-4 w-4 text-primary" />
                                                </div>
                                                <div>
                                                    <div className="font-medium text-sm">{listItem.item_name}</div>
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <div className="text-sm font-medium">
                                                    Rp {Number.parseInt(listItem.price).toLocaleString("id-ID")}
                                                </div>
                                            </div>
                                        </div>
                                    ))}

                                    {isLoadingMore && (
                                        <div className="flex items-center justify-center p-4 bg-gray-50 dark:bg-gray-950">
                                            <Loader2 className="h-4 w-4 animate-spin mr-2" />
                                            <span className="text-sm text-muted-foreground">Loading more items...</span>
                                        </div>
                                    )}

                                    {!hasMore && list.length > 0 && (
                                        <div className="text-center p-4 text-xs text-muted-foreground bg-gray-50 dark:bg-gray-950 border-t">
                                            ✅ All items loaded ({list.length} total)
                                        </div>
                                    )}

                                    {list.length === 0 && !isLoading && (
                                        <div className="text-center p-4 text-sm text-muted-foreground">No items found</div>
                                    )}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {error && <div className="text-sm text-destructive mt-2 p-2 bg-destructive/10 rounded-md">{error}</div>}

                {item.id && !isLoading && !error && (
                    <Card className="mt-3">
                        <CardContent className="p-4">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-3">
                                    <div className="p-2 bg-primary/10 rounded-md">
                                        <Package className="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <div className="font-medium">{item.item_name}</div>
                                        {item.description && <div className="text-xs text-muted-foreground mt-1">{item.description}</div>}
                                    </div>
                                </div>
                                <div className="text-right">
                                    <div className="text-sm text-muted-foreground mt-1">
                                        Rp {Number.parseInt(item.price).toLocaleString("id-ID")}
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </div>
    )
}
