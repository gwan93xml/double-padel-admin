
"use client"

import type React from "react"
import { Search, Receipt, Loader2, X } from "lucide-react"
import { Button } from "@/Components/ui/button"
import { Input } from "@/Components/ui/input"
import { Card, CardContent } from "@/Components/ui/card"
import { useEffect, useState, useRef, useCallback } from "react"
import axios from "axios"
import moment from "moment"
import { PurchaseType } from "@/Pages/Purchase/@types/purchase-type"

type SearchPurchaseProps = {
    onChange: (purchase: PurchaseType) => void
    value?: PurchaseType
    nextRef?: React.RefObject<HTMLInputElement>
    purchaseNumberRef?: React.RefObject<HTMLInputElement>
    clearable?: boolean
    vendorId?: string
}

export default function SearchPurchase({
    onChange,
    value,
    nextRef,
    purchaseNumberRef,
    clearable = false,
    vendorId
}: SearchPurchaseProps) {
    const [purchase, setPurchase] = useState<PurchaseType>({
        id: "",
        number: "",
        purchase_date: "",
        total_amount: "0",
    })
    const [isLoading, setIsLoading] = useState(false)
    const [error, setError] = useState("")
    const [list, setList] = useState<PurchaseType[]>([])
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
            setPurchase({
                id: value.id || "",
                number: value.number || "",
                purchase_date: value.purchase_date || "",
                total_amount: value.total_amount || "0",
                vendor: value.vendor,
                division: value.division,
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
                    selectPurchase(list[selectedIndex])
                    nextRef?.current?.focus() // Focus next input if available
                } else {
                    // If no item is selected, perform search
                    if (e.currentTarget === document.activeElement) {
                        searchByNumber(purchase.number ?? "")
                    }
                }
                break

            case "Escape":
                e.preventDefault()
                setShowDropdown(false)
                setSelectedIndex(-1)
                break
        }
    }, [showDropdown, list, selectedIndex, purchase.number])

    const handleNumberChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const newNumber = e.target.value
        setPurchase((prev) => ({ ...prev, number: newNumber }))

        // Clear previous timeout
        if (searchTimeoutRef.current) {
            clearTimeout(searchTimeoutRef.current)
        }

        // Debounce search to prevent too many API calls
        searchTimeoutRef.current = setTimeout(() => {
            handleSearch(newNumber)
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
        fetchPurchases(query, 1, true)
    }, [])

    const fetchPurchases = async (query: string, pageNum: number, reset = false) => {
        if (reset) {
            setIsLoading(true)
        } else {
            setIsLoadingMore(true)
        }

        try {
            const response = await axios.get(`/admin/purchase/list`, {
                params: {
                    search: query,
                    page: pageNum,
                    limit: 10,
                    vendor_id: vendorId,
                },
            })

            const newPurchases = response.data.data || []

            if (reset) {
                setList(newPurchases)
            } else {
                setList((prev) => [...prev, ...newPurchases])
            }

            // Check if there are more items to load
            setHasMore(response.data.next_page_url !== null)
            setShowDropdown(newPurchases.length > 0 || list.length > 0)
        } catch (error) {
            console.error("Error fetching purchases:", error)
            setError("Failed to fetch purchases")
        } finally {
            setIsLoading(false)
            setIsLoadingMore(false)
        }
    }

    const loadMore = useCallback(() => {
        if (!isLoadingMore && hasMore && currentQuery) {
            const nextPage = page + 1
            setPage(nextPage)
            fetchPurchases(currentQuery, nextPage, false)
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

    const searchByNumber = async (number: string) => {
        if (!number.trim()) return

        setIsLoading(true)
        setError("")

        try {
            const { data } = await axios.get(`/admin/purchase/list?search=${number}`)

            if (data.data && data.data.length > 0) {
                const foundPurchase = data.data[0]
                setPurchase(foundPurchase)
                onChange(foundPurchase)
                setShowDropdown(false)
            } else {
                setError("Purchase not found")
            }
        } catch (err) {
            setError("Failed to search purchase")
            console.error(err)
        } finally {
            setIsLoading(false)
        }
    }

    const selectPurchase = async (selectedPurchase: PurchaseType) => {
        try {
            const { data: responseData } = await axios.get(`/admin/purchase/${selectedPurchase.id}`)
            setPurchase(responseData.data)
            onChange(responseData.data)
            setShowDropdown(false)
            setList([])
        } catch (error) {
            console.error("Error selecting purchase:", error)
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

    // Focus input when purchase is cleared (when user clicks "Ubah")
    useEffect(() => {
        if (!purchase.id) {
            // Small delay to ensure input is fully rendered
            const timer = setTimeout(() => {
                if (purchaseNumberRef?.current) {
                    purchaseNumberRef.current.focus()
                }
            }, 50)
            return () => clearTimeout(timer)
        }
    }, [purchase.id, purchaseNumberRef])

    return (
        <div className="space-y-3" ref={containerRef}>
            {!purchase.id ? (
                <div className="relative">
                    <div className="flex gap-0 border rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-ring">
                        <Input
                            className="border-0 rounded-none focus-visible:ring-0 focus-visible:ring-offset-0"
                            placeholder="Nomor Purchase"
                            ref={purchaseNumberRef}
                            onChange={handleNumberChange}
                            onKeyDown={handleKeyNavigation}
                        />
                        <Button
                            variant="ghost"
                            size="sm"
                            className="rounded-none border-l px-3"
                            onClick={() => searchByNumber(purchase.number ?? '')}
                            disabled={isLoading}
                        >
                            {isLoading ? <Loader2 className="h-4 w-4 animate-spin" /> : <Search className="h-4 w-4" />}
                        </Button>
                        {clearable && (purchase.id || purchase.number) && (
                            <Button
                                variant="ghost"
                                size="sm"
                                className="rounded-none border-l px-3"
                                onClick={() => {
                                    setPurchase({ id: undefined })
                                    setList([])
                                    setShowDropdown(false)
                                    setError("")
                                    onChange({ id: "" })
                                    purchaseNumberRef?.current?.focus()
                                }}>
                                <X className="h-4 w-4" />
                            </Button>
                        )}
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
                                            Search Results ({currentQuery}) - {list.length} purchases
                                        </div>

                                        {list.map((listPurchase, index) => (
                                            <div
                                                key={`${listPurchase.id}-${index}`}
                                                ref={(el) => (itemRefs.current[index] = el)}
                                                className={`flex items-center justify-between p-3 rounded-md cursor-pointer transition-colors ${selectedIndex === index ? "bg-primary/10 border border-primary/20" : "hover:bg-accent"
                                                    }`}
                                                onClick={() => selectPurchase(listPurchase)}
                                                onMouseEnter={() => setSelectedIndex(index)}
                                            >
                                                <div className="flex items-center gap-3">
                                                    <div className="p-2 bg-primary/10 rounded-md">
                                                        <Receipt className="h-4 w-4 text-primary" />
                                                    </div>
                                                    <div>
                                                        <div className="font-medium text-sm">{listPurchase.number}</div>
                                                        <div className="text-xs text-muted-foreground">{listPurchase?.vendor?.name || 'No Vendor'}</div>
                                                        <div className="text-xs text-muted-foreground">
                                                            {listPurchase.purchase_date ? moment(listPurchase.purchase_date).format("DD/MM/YYYY") : ''}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div className="text-right">
                                                    <div className="text-sm font-medium">
                                                        Rp {Number.parseInt(listPurchase?.total_amount ?? "0").toLocaleString("id-ID")}
                                                    </div>
                                                    <div className="text-xs text-muted-foreground mt-1">
                                                        {listPurchase?.division?.name || 'No Division'}
                                                    </div>
                                                </div>
                                            </div>
                                        ))}

                                        {isLoadingMore && (
                                            <div className="flex items-center justify-center p-4 bg-gray-50 dark:bg-gray-950">
                                                <Loader2 className="h-4 w-4 animate-spin mr-2" />
                                                <span className="text-sm text-muted-foreground">Loading more purchases...</span>
                                            </div>
                                        )}

                                        {!hasMore && list.length > 0 && (
                                            <div className="text-center p-4 text-xs text-muted-foreground bg-gray-50 dark:bg-gray-950 border-t">
                                                ✅ All purchases loaded ({list.length} total)
                                            </div>
                                        )}

                                        {list.length === 0 && !isLoading && (
                                            <div className="text-center p-4 text-sm text-muted-foreground">No purchases found</div>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {error && <div className="text-sm text-destructive mt-2 p-2 bg-destructive/10 rounded-md">{error}</div>}
                </div>
            ) : null}

            {purchase.id && !isLoading && !error && (
                <Card className="mt-3">
                    <CardContent className="p-4">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-3">
                                <div className="p-2 bg-primary/10 rounded-md">
                                    <Receipt className="h-5 w-5 text-primary" />
                                </div>
                                <div>
                                    <div className="font-medium text-sm">{purchase.number}</div>
                                    <div className="text-sm text-muted-foreground">{purchase.vendor?.name || 'No Vendor'}</div>
                                    <div className="text-xs text-muted-foreground">
                                        {purchase.purchase_date ? moment(purchase.purchase_date).format("DD/MM/YYYY") : ''}
                                    </div>
                                </div>
                            </div>
                            <div className="flex items-center gap-3">
                                <div className="text-right">
                                    <div className="text-sm font-medium">
                                        Rp {Number.parseInt(purchase?.total_amount ?? "0").toLocaleString("id-ID")}
                                    </div>
                                    <div className="text-xs text-muted-foreground mt-1">
                                        {purchase?.division?.name || 'No Division'}
                                    </div>
                                </div>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => {
                                        setPurchase({
                                            id: "",
                                            number: "",
                                            purchase_date: "",
                                            total_amount: "0",
                                        })
                                        setList([])
                                        setShowDropdown(false)
                                        setError("")
                                        onChange({ id: "" })
                                    }}
                                    className="ml-2"
                                >
                                    <X className="h-4 w-4 mr-1" />
                                    Ubah
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            )}
        </div>
    )
}

