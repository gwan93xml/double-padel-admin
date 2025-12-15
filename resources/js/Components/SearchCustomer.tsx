"use client"

import type React from "react"
import { Search, X, Loader2 } from "lucide-react"
import { Button } from "@/Components/ui/button"
import { Input } from "@/Components/ui/input"
import { Card, CardContent } from "@/Components/ui/card"
import { useEffect, useState, useRef, useCallback } from "react"
import axios from "axios"


type SearchCustomerProps = {
    onChange: (customer: CustomerType) => void
    value?: CustomerType
    clearable?: boolean
}

export default function SearchCustomer({ onChange, value, clearable }: SearchCustomerProps) {
    const [customer, setCustomer] = useState<CustomerType>({
        id: "",
        code: "",
        name: "",
        phone: "",
    })
    const [isLoading, setIsLoading] = useState(false)
    const [error, setError] = useState("")
    const [list, setList] = useState<CustomerType[]>([])
    const [isDropdownOpen, setIsDropdownOpen] = useState(false)
    const [page, setPage] = useState(1)
    const [hasMore, setHasMore] = useState(true)
    const [isLoadingMore, setIsLoadingMore] = useState(false)

    // Add debounce state and ref
    const [searchQuery, setSearchQuery] = useState("")
    const debounceTimeoutRef = useRef<NodeJS.Timeout | null>(null)
    const DEBOUNCE_DELAY = 200 // 300ms delay

    const dropdownRef = useRef<HTMLDivElement>(null)
    const scrollContainerRef = useRef<HTMLDivElement>(null)
    const isInternalUpdate = useRef(false)
    const codeInputRef = useRef<HTMLInputElement>(null)
    const nameInputRef = useRef<HTMLInputElement>(null)
    const [activeInput, setActiveInput] = useState<"code" | "name" | null>(null)

    // Initialize from props
    useEffect(() => {
        if (value) {
            setCustomer({
                id: value.id || "",
                code: value.code || "",
                name: value.name || "",
                phone: value.phone || "",
            })
        }
    }, [value])

    // useEffect(() => {
    //     onChange(customer)
    // }, [customer, onChange])

    useEffect(() => {
        // Restore focus after loading is complete
        if (!isLoading && !isLoadingMore && activeInput) {
            const timeoutId = setTimeout(() => {
                if (activeInput === "code" && codeInputRef.current) {
                    codeInputRef.current.focus()
                } else if (activeInput === "name" && nameInputRef.current) {
                    nameInputRef.current.focus()
                }
            }, 0)

            return () => clearTimeout(timeoutId)
        }
    }, [isLoading, isLoadingMore, activeInput])

    // Close dropdown when clicking outside
    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
                setIsDropdownOpen(false)
            }
        }

        document.addEventListener("mousedown", handleClickOutside)
        return () => document.removeEventListener("mousedown", handleClickOutside)
    }, [])

    const handleCodeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const newCode = e.target.value
        setActiveInput("code")
        setCustomer({ ...customer, code: newCode })
        setSearchQuery(newCode)
        debouncedSearch(newCode, true) // Use debounced search
        setError("")
    }

    const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const newName = e.target.value
        setActiveInput("name")
        setCustomer({ ...customer, name: newName })
        setSearchQuery(newName)
        debouncedSearch(newName, true) // Use debounced search
        setError("")
    }

    const searchByCode = async (code: string) => {
        if (!code.trim()) return

        setIsLoading(true)
        setError("")

        try {
            const { data } = await axios.get(`/admin/customer/search-by-code?search=${code}`)
            if (data.data) {
                isInternalUpdate.current = true
                setCustomer({
                    id: data.data.id,
                    code: data.data.code,
                    name: data.data.name,
                    phone: data.data.phone,
                })
                onChange({
                    id: data.data.id,
                    code: data.data.code,
                    name: data.data.name,
                    phone: data.data.phone,
                })

                setIsDropdownOpen(false)
            } else {
                setError("Customer not found")
            }
        } catch (err) {
            setError("Failed to search customer")
            console.error(err)
        } finally {
            setIsLoading(false)
        }
    }

    const searchByName = async (name: string) => {
        if (!name.trim()) return

        setIsLoading(true)
        setError("")

        try {
            const { data } = await axios.get(`/admin/customer/search-by-name?search=${name}`)

            if (data.data) {
                isInternalUpdate.current = true
                onChange({
                    id: data.data.id,
                    code: data.data.code,
                    name: data.data.name,
                    phone: data.data.phone,
                })
                setIsDropdownOpen(false)
            } else {
                setError("Customer not found")
            }
        } catch (err) {
            setError("Failed to search customer")
            console.error(err)
        } finally {
            setIsLoading(false)
        }
    }

    const handleCodeKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === "Enter") {
            searchByCode(customer.code ?? "")
            // Tutup dropdown setelah Enter
            setIsDropdownOpen(false)
            setList([])
        }
    }

    const handleNameKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === "Enter") {
            searchByName(customer.name ?? "")
            // Tutup dropdown setelah Enter
            setIsDropdownOpen(false)
            setList([])
        }
    }

    const handleSearchButtonClick = () => {
        if (customer.code) {
            searchByCode(customer.code)
        } else if (customer.name) {
            searchByName(customer.name)
        }
    }

    const loadCustomers = useCallback(async (query: string, pageNum: number, reset = false) => {
        if (pageNum === 1) {
            setIsLoading(true)
        } else {
            setIsLoadingMore(true)
        }

        try {
            const { data } = await axios.get(`/admin/customer/browse?search=${query}&page=${pageNum}&limit=20`)

            if (reset || pageNum === 1) {
                setList(data.data || [])
            } else {
                setList((prev) => [...prev, ...(data.data || [])])
            }

            setHasMore(data.next_page_url !== null)
            setIsDropdownOpen(data.data && data.data.length > 0)
        } catch (error) {
            console.error("Error fetching customers:", error)
            setError("Failed to load customers")
        } finally {
            setIsLoading(false)
            setIsLoadingMore(false)
        }
    }, [])

    const debouncedSearch = useCallback(
        (query: string, reset = false) => {
            // Clear existing timeout
            if (debounceTimeoutRef.current) {
                clearTimeout(debounceTimeoutRef.current)
            }

            // If query is too short, clear results immediately
            if (query.length < 2) {
                setList([])
                setIsDropdownOpen(false)
                setPage(1)
                setHasMore(true)
                return
            }

            // Set new timeout for debounced search
            debounceTimeoutRef.current = setTimeout(() => {
                if (reset) {
                    setPage(1)
                    setHasMore(true)
                }
                loadCustomers(query, reset ? 1 : page, reset)
            }, DEBOUNCE_DELAY)
        },
        [loadCustomers, page],
    )

    // Infinite scroll handler
    const handleScroll = useCallback(() => {
        if (!scrollContainerRef.current || isLoadingMore || !hasMore) return

        const { scrollTop, scrollHeight, clientHeight } = scrollContainerRef.current

        if (scrollTop + clientHeight >= scrollHeight - 5) {
            const query = activeInput === "code" ? customer.code : customer.name
            if (query && query.length >= 2) {
                const nextPage = page + 1
                setPage(nextPage)
                loadCustomers(query, nextPage, false)
            }
        }
    }, [isLoadingMore, hasMore, customer.code, customer.name, page, loadCustomers])

    useEffect(() => {
        const scrollContainer = scrollContainerRef.current
        if (scrollContainer) {
            scrollContainer.addEventListener("scroll", handleScroll)
            return () => scrollContainer.removeEventListener("scroll", handleScroll)
        }
    }, [handleScroll])

    const handleCustomerSelect = (selectedCustomer: CustomerType) => {
        const currentActiveInput = activeInput // Store current active input
        onChange({
            id: selectedCustomer.id,
            code: selectedCustomer.code,
            name: selectedCustomer.name,
            phone: selectedCustomer.phone,
        })
        setList([])
        setIsDropdownOpen(false)
        setError("")

        // Restore focus to the previously active input
        setTimeout(() => {
            if (currentActiveInput === "code" && codeInputRef.current) {
                codeInputRef.current.focus()
            } else if (currentActiveInput === "name" && nameInputRef.current) {
                nameInputRef.current.focus()
            }
        }, 0)
    }

    const handleClear = () => {
        // Clear debounce timeout
        if (debounceTimeoutRef.current) {
            clearTimeout(debounceTimeoutRef.current)
        }

        setCustomer({
            id: "",
            code: "",
            name: "",
            phone: "",
        })
        onChange({
            id: "",
            code: "",
            name: "",
            phone: "",
        })
        setSearchQuery("")
        setList([])
        setIsDropdownOpen(false)
        setError("")
        setPage(1)
        setHasMore(true)
    }

    // Cleanup timeout on unmount
    useEffect(() => {
        return () => {
            if (debounceTimeoutRef.current) {
                clearTimeout(debounceTimeoutRef.current)
            }
        }
    }, [])

    return (
        <div className="space-y-2 relative" ref={dropdownRef}>
            <div className="flex items-center space-x-0 border rounded-lg overflow-hidden bg-background">
                <div className="flex-1 min-w-0">
                    <Input
                        ref={codeInputRef}
                        className="border-0 rounded-none focus-visible:ring-0 focus-visible:ring-offset-0"
                        placeholder="Kode Customer"
                        value={customer.code}
                        onChange={handleCodeChange}
                        onKeyDown={handleCodeKeyDown}
                        // disabled={isLoading}
                        onFocus={() => {
                            setActiveInput("code")
                            if (list.length > 0) setIsDropdownOpen(true)
                        }}
                        onBlur={() => {
                            // Small delay to allow dropdown selection
                            setTimeout(() => setActiveInput(null), 150)
                        }}
                    />
                </div>

                <div className="border-l border-r">
                    <Button
                        variant="ghost"
                        size="sm"
                        className="rounded-none px-3"
                        onClick={handleSearchButtonClick}
                        disabled={isLoading}
                    >
                        {isLoading ? <Loader2 className="h-4 w-4 animate-spin" /> : <Search className="h-4 w-4" />}
                    </Button>
                </div>

                <div className="flex-[2] min-w-0">
                    <Input
                        ref={nameInputRef}
                        className="border-0 rounded-none focus-visible:ring-0 focus-visible:ring-offset-0"
                        placeholder="Nama Customer"
                        value={customer.name}
                        onChange={handleNameChange}
                        onKeyDown={handleNameKeyDown}
                        // disabled={isLoading}
                        onFocus={() => {
                            setActiveInput("name")
                            if (list.length > 0) setIsDropdownOpen(true)
                        }}
                        onBlur={() => {
                            // Small delay to allow dropdown selection
                            setTimeout(() => setActiveInput(null), 150)
                        }}
                    />
                </div>

                {clearable && (
                    <div className="border-l">
                        <Button variant="ghost" size="sm" className="rounded-none px-3" onClick={handleClear}>
                            <X className="h-4 w-4" />
                        </Button>
                    </div>
                )}
            </div>

            {/* Dropdown */}
            {isDropdownOpen && list.length > 0 && (
                <Card className="absolute top-full left-0 right-0 z-50 mt-1 shadow-lg border">
                    <CardContent className="p-0">
                        <div ref={scrollContainerRef} className="max-h-64 overflow-y-auto">
                            <div className="sticky top-0 bg-muted/50 border-b px-4 py-2">
                                <div className="grid grid-cols-2 gap-4 text-sm font-medium text-muted-foreground">
                                    <div>Kode</div>
                                    <div>Nama</div>
                                </div>
                            </div>

                            <div className="divide-y">
                                {list.map((item, index) => (
                                    <div
                                        key={`${item.id}-${index}`}
                                        className="grid grid-cols-4 gap-4 px-4 py-3 cursor-pointer hover:bg-muted/50 transition-colors"
                                        onClick={() => handleCustomerSelect(item)}
                                    >
                                        <div className="text-sm font-medium truncate">{item.code}</div>
                                        <div className="text-sm truncate col-span-3">{item.name}</div>
                                    </div>
                                ))}

                                {isLoadingMore && (
                                    <div className="flex items-center justify-center py-4">
                                        <Loader2 className="h-4 w-4 animate-spin mr-2" />
                                        <span className="text-sm text-muted-foreground">Loading more...</span>
                                    </div>
                                )}

                                {!hasMore && list.length > 0 && (
                                    <div className="text-center py-4 text-sm text-muted-foreground">No more customers to load</div>
                                )}
                            </div>
                        </div>
                    </CardContent>
                </Card>
            )}

            {/* Status Messages */}
            {error && <div className="text-sm text-destructive bg-destructive/10 px-3 py-2 rounded-md">{error}</div>}


        </div>
    )
}
