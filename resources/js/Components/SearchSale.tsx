"use client"

import type React from "react"
import { Search, Receipt, Loader2, X } from "lucide-react"
import { Button } from "@/Components/ui/button"
import { Input } from "@/Components/ui/input"
import { Card, CardContent } from "@/Components/ui/card"
import { Badge } from "@/Components/ui/badge"
import { useEffect, useState, useRef, useCallback } from "react"
import axios from "axios"
import { SaleType } from "@/Pages/Sale/@types/sale-type"


type SearchSaleProps = {
    onChange: (sale: SaleType) => void
    value?: SaleType
    nextRef?: React.RefObject<HTMLInputElement>
    saleNumberRef?: React.RefObject<HTMLInputElement>
    clearable?: boolean
}

export default function SearchSale({
    onChange,
    value,
    nextRef,
    saleNumberRef,
    clearable = false,

}: SearchSaleProps) {
    const [sale, setSale] = useState<SaleType>({
        id: "",
        number: "",
        no: "",
        sale_date: "",
        total_amount: "0",
        payment_method: "",
    })
    const [isLoading, setIsLoading] = useState(false)
    const [error, setError] = useState("")
    const [list, setList] = useState<SaleType[]>([])
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
            setSale({
                id: value.id || "",
                number: value.no || "",
                no: value.purchase_order_number || "",
                sale_date: value.sale_date || "",
                total_amount: value.total_amount || "0",
                payment_status: value.payment_status || "",
                payment_method: value.payment_method || "",
                customer: value.customer || undefined,
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
                    selectSale(list[selectedIndex])
                    console.log("Selected sale:", list[selectedIndex])
                    nextRef?.current?.focus() // Focus next input if available
                } else {
                    // If no item is selected, perform search
                    if (e.currentTarget === document.activeElement) {
                        searchByNumber(sale.no ?? "")
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
        [showDropdown, list, selectedIndex, sale.no],
    )

    const handleNumberChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const newNumber = e.target.value
        setSale((prev) => ({ ...prev, number: newNumber }))

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
        fetchSales(query, 1, true)
    }, [])

    const fetchSales = async (query: string, pageNum: number, reset = false) => {
        if (reset) {
            setIsLoading(true)
        } else {
            setIsLoadingMore(true)
        }

        try {
            const response = await axios.get(`/admin/sale/list`, {
                params: {
                    search: query,
                    page: pageNum,
                    limit: 10,
                },
            })

            const newSales = response.data.data || []

            if (reset) {
                setList(newSales)
            } else {
                setList((prev) => [...prev, ...newSales])
            }

            // Check if there are more items to load
            setHasMore(response.data.next_page_url !== null)
            setShowDropdown(newSales.length > 0 || list.length > 0)
        } catch (error) {
            console.error("Error fetching sales:", error)
            setError("Failed to fetch sales")
        } finally {
            setIsLoading(false)
            setIsLoadingMore(false)
        }
    }

    const loadMore = useCallback(() => {
        if (!isLoadingMore && hasMore && currentQuery) {
            const nextPage = page + 1
            setPage(nextPage)
            fetchSales(currentQuery, nextPage, false)
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
            const { data } = await axios.get(`/admin/sale/list?search=${number}`)

            if (data.data && data.data.length > 0) {
                const foundSale = data.data[0]
                setSale(foundSale)
                onChange(foundSale)
                setShowDropdown(false)
            } else {
                setError("Sale not found")
            }
        } catch (err) {
            setError("Failed to search sale")
            console.error(err)
        } finally {
            setIsLoading(false)
        }
    }

    const selectSale = async (selectedSale: SaleType) => {
        try {
            const { data: responseData } = await axios.get(`/admin/sale/${selectedSale.id}`)
            setSale(responseData.data)
            onChange(responseData.data)
            setShowDropdown(false)
            setList([])
        } catch (error) {
            console.error("Error selecting sale:", error)
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


    // Focus input when sale is cleared (when user clicks "Ubah")
    useEffect(() => {
        if (!sale.id) {
            // Small delay to ensure input is fully rendered
            const timer = setTimeout(() => {
                if (saleNumberRef?.current) {
                    saleNumberRef.current.focus()
                }
            }, 50)
            return () => clearTimeout(timer)
        }
    }, [sale.id, saleNumberRef])

    const getPaymentStatusBadge = (status: string) => {
        switch (status?.toLowerCase()) {
            case 'paid':
                return <Badge variant="secondary" className="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">Lunas</Badge>
            case 'partial':
                return <Badge variant="secondary" className="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">Sebagian</Badge>
            case 'unpaid':
                return <Badge variant="secondary" className="bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">Belum Bayar</Badge>
            default:
                return <Badge variant="secondary">{status || 'Unknown'}</Badge>
        }
    }


    return (
        <div className="space-y-3" ref={containerRef}>
            {!sale.id ? (
                <div className="relative">
                    <div className="flex gap-0 border rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-ring">
                        <Input
                            className="border-0 rounded-none focus-visible:ring-0 focus-visible:ring-offset-0"
                            placeholder="Nomor Penjualan"
                            ref={saleNumberRef}
                            onChange={handleNumberChange}
                            onKeyDown={handleKeyNavigation}
                        />
                        <Button
                            variant="ghost"
                            size="sm"
                            className="rounded-none border-l px-3"
                            onClick={() => searchByNumber(sale.no ?? '')}
                            disabled={isLoading}
                        >
                            {isLoading ? <Loader2 className="h-4 w-4 animate-spin" /> : <Search className="h-4 w-4" />}
                        </Button>
                        {clearable && (sale.id || sale.no) && (
                            <Button
                                variant="ghost"
                                size="sm"
                                className="rounded-none border-l px-3"
                                onClick={() => {
                                    setSale({ id: undefined })
                                    setList([])
                                    setShowDropdown(false)
                                    setError("")
                                    onChange({ id: "" })
                                    saleNumberRef?.current?.focus()
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
                                            Search Results ({currentQuery}) - {list.length} sales
                                        </div>

                                        {list.map((listSale, index) => (
                                            <div
                                                key={`${listSale.id}-${index}`}
                                                ref={(el) => (itemRefs.current[index] = el)}
                                                className={`flex items-center justify-between p-3 rounded-md cursor-pointer transition-colors ${selectedIndex === index ? "bg-primary/10 border border-primary/20" : "hover:bg-accent"
                                                    }`}
                                                onClick={() => selectSale(listSale)}
                                                onMouseEnter={() => setSelectedIndex(index)}
                                            >
                                                <div className="flex items-center gap-3">
                                                    <div className="p-2 bg-primary/10 rounded-md">
                                                        <Receipt className="h-4 w-4 text-primary" />
                                                    </div>
                                                    <div>
                                                        <div className="font-medium text-sm">{listSale.no}</div>
                                                        <div className="font-medium text-sm">{listSale.purchase_order_number}</div>
                                                        <div className="font-medium text-sm">{listSale.sales_order_number}</div>
                                                        <div className="text-xs text-muted-foreground">{listSale?.customer?.name || 'No Customer'}</div>
                                                        <div className="text-xs text-muted-foreground">
                                                            {listSale.sale_date}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div className="text-right">
                                                    <div className="text-sm font-medium">
                                                        Rp {Number.parseInt(listSale?.total_amount ?? "0").toLocaleString("id-ID")}
                                                    </div>
                                                    <div className="mt-1">
                                                        {getPaymentStatusBadge(listSale?.payment_status ?? "unpaid")}
                                                    </div>
                                                </div>
                                            </div>
                                        ))}

                                        {isLoadingMore && (
                                            <div className="flex items-center justify-center p-4 bg-gray-50 dark:bg-gray-950">
                                                <Loader2 className="h-4 w-4 animate-spin mr-2" />
                                                <span className="text-sm text-muted-foreground">Loading more sales...</span>
                                            </div>
                                        )}

                                        {!hasMore && list.length > 0 && (
                                            <div className="text-center p-4 text-xs text-muted-foreground bg-gray-50 dark:bg-gray-950 border-t">
                                                ✅ All sales loaded ({list.length} total)
                                            </div>
                                        )}

                                        {list.length === 0 && !isLoading && (
                                            <div className="text-center p-4 text-sm text-muted-foreground">No sales found</div>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {error && <div className="text-sm text-destructive mt-2 p-2 bg-destructive/10 rounded-md">{error}</div>}
                </div>
            ) : null}

            {sale.id && !isLoading && !error && (
                <Card className="mt-3">
                    <CardContent className="p-4">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-3">
                                <div className="p-2 bg-primary/10 rounded-md">
                                    <Receipt className="h-5 w-5 text-primary" />
                                </div>
                                <div>
                                    <div className="font-medium text-sm">{sale.no}</div>
                                    <div className="font-medium text-sm">{sale.purchase_order_number}</div>
                                    <div className="font-medium text-sm">{sale.sales_order_number}</div>
                                    <div className="text-sm text-muted-foreground">{sale.customer?.name || 'No Customer'}</div>
                                    <div className="text-xs text-muted-foreground">
                                        {sale.sale_date ? new Date(sale.sale_date).toLocaleDateString('id-ID') : ''}
                                    </div>
                                </div>
                            </div>
                            <div className="flex items-center gap-3">
                                <div className="text-right">
                                    {getPaymentStatusBadge(sale?.payment_status ?? 'unpaid')}
                                    <div className="text-sm font-medium mt-1">
                                        Rp {Number.parseInt(sale?.total_amount ?? "0").toLocaleString("id-ID")}
                                    </div>
                                    {sale.payment_method && (
                                        <div className="text-xs text-muted-foreground mt-1">
                                            {sale.payment_method}
                                        </div>
                                    )}
                                </div>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => {
                                        setSale({
                                            id: "",
                                            number: "",
                                            no: "",
                                            sale_date: "",
                                            total_amount: "0",
                                            payment_method: "",
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