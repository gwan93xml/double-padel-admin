"use client"

import type React from "react"

import { Search, Loader2, X } from "lucide-react"
import { Button } from "@/Components/ui/button"
import { Input } from "@/Components/ui/input"
import { Card, CardContent } from "@/Components/ui/card"
import { useEffect, useState, useRef, useCallback } from "react"
import axios from "axios"


type SearchBankAccountProps = {
  onChange: (bankAccount: BankAccountType) => void
  value?: BankAccountType
  clearable?: boolean
}

export default function SearchBankAccount({ onChange, value, clearable }: SearchBankAccountProps) {
  const [bankAccount, setBankAccount] = useState<BankAccountType>({
    id: "",
    account_number: "",
    account_name: "",
  })
  const [isLoading, setIsLoading] = useState(false)
  const [error, setError] = useState("")
  const [list, setList] = useState<BankAccountType[]>([])
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
  const account_numberInputRef = useRef<HTMLInputElement>(null)
  const account_nameInputRef = useRef<HTMLInputElement>(null)
  const [activeInput, setActiveInput] = useState<"account_number" | "account_name" | null>(null)

  // Initialize from props
  useEffect(() => {
    if (value && !isInternalUpdate.current) {
      setBankAccount({
        id: value.id || "",
        account_number: value.account_number || "",
        account_name: value.account_name || "",
      })
    }
  }, [value])

  useEffect(() => {
    // onChange(bankAccount)
  }, [bankAccount, onChange])

  useEffect(() => {
    // Restore focus after loading is complete
    if (!isLoading && !isLoadingMore && activeInput) {
      const timeoutId = setTimeout(() => {
        if (activeInput === "account_number" && account_numberInputRef.current) {
          account_numberInputRef.current.focus()
        } else if (activeInput === "account_name" && account_nameInputRef.current) {
          account_nameInputRef.current.focus()
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
    setActiveInput("account_number")
    setBankAccount({ ...bankAccount, account_number: newCode })
    setSearchQuery(newCode)
    debouncedSearch(newCode, true) // Use debounced search
    setError("")
  }

  const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const newName = e.target.value
    setActiveInput("account_name")
    setBankAccount({ ...bankAccount, account_name: newName })
    setSearchQuery(newName)
    debouncedSearch(newName, true) // Use debounced search
    setError("")
  }

  const searchByCode = async (account_number: string) => {
    if (!account_number.trim()) return

    setIsLoading(true)
    setError("")

    try {
      const { data } = await axios.get(`/admin/bank-account/search-by-account_number?search=${account_number}`)

      if (data.data) {
        isInternalUpdate.current = true
        setBankAccount({
          id: data.data.id,
          account_number: data.data.account_number,
          account_name: data.data.account_name,
        })
        onChange({
          id: data.data.id,
          account_number: data.data.account_number,
          account_name: data.data.account_name,
        })
        setIsDropdownOpen(false)
      } else {
        setError("BankAccount not found")
      }
    } catch (err) {
      setError("Failed to search bankAccount")
      console.error(err)
    } finally {
      setIsLoading(false)
    }
  }

  const searchByName = async (account_name: string) => {
    if (!account_name.trim()) return

    setIsLoading(true)
    setError("")

    try {
      const { data } = await axios.get(`/admin/bank-account/search-by-account_name?search=${account_name}`)

      if (data.data) {
        isInternalUpdate.current = true
        setBankAccount({
          id: data.data.id,
          account_number: data.data.account_number,
          account_name: data.data.account_name,
        })
        onChange({
          id: data.data.id,
          account_number: data.data.account_number,
          account_name: data.data.account_name,
        })
        setIsDropdownOpen(false)
      } else {
        setError("BankAccount not found")
      }
    } catch (err) {
      setError("Failed to search bankAccount")
      console.error(err)
    } finally {
      setIsLoading(false)
    }
  }

  const handleCodeKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Enter") {
      searchByCode(bankAccount.account_number ?? "")
      // Tutup dropdown setelah Enter
      setIsDropdownOpen(false)
      setList([])
    }
  }

  const handleNameKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Enter") {
      searchByName(bankAccount.account_name ?? "")
      // Tutup dropdown setelah Enter
      setIsDropdownOpen(false)
      setList([])
    }
  }

  const handleSearchButtonClick = () => {
    if (bankAccount.account_number) {
      searchByCode(bankAccount.account_number)
    } else if (bankAccount.account_name) {
      searchByName(bankAccount.account_name)
    }
  }

  const loadBankAccounts = useCallback(async (query: string, pageNum: number, reset = false) => {
    if (pageNum === 1) {
      setIsLoading(true)
    } else {
      setIsLoadingMore(true)
    }

    try {
      const { data } = await axios.get(`/admin/bank-account/browse?search=${query}&page=${pageNum}&limit=20`)

      if (reset || pageNum === 1) {
        setList(data.data || [])
      } else {
        setList((prev) => [...prev, ...(data.data || [])])
      }

      // Use next_page_url to determine if there's more data
      setHasMore(data.next_page_url !== null)
      setIsDropdownOpen(data.data && data.data.length > 0)
    } catch (error) {
      console.error("Error fetching bankAccounts:", error)
      setError("Failed to load bankAccounts")
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
        loadBankAccounts(query, reset ? 1 : page, reset)
      }, DEBOUNCE_DELAY)
    },
    [loadBankAccounts, page],
  )

  // Immediate search function (for Enter key press)
  const immediateSearch = useCallback(
    (query: string, reset = false) => {
      // Clear any pending debounced search
      if (debounceTimeoutRef.current) {
        clearTimeout(debounceTimeoutRef.current)
      }

      if (query.length < 2) {
        setList([])
        setIsDropdownOpen(false)
        setPage(1)
        setHasMore(true)
        return
      }

      if (reset) {
        setPage(1)
        setHasMore(true)
      }

      loadBankAccounts(query, reset ? 1 : page, reset)
    },
    [loadBankAccounts, page],
  )

  // Infinite scroll handler
  const handleScroll = useCallback(() => {
    if (!scrollContainerRef.current || isLoadingMore || !hasMore) return

    const { scrollTop, scrollHeight, clientHeight } = scrollContainerRef.current

    if (scrollTop + clientHeight >= scrollHeight - 5) {
      const query = bankAccount.account_number || bankAccount.account_name
      if (query && query.length >= 2) {
        const nextPage = page + 1
        setPage(nextPage)
        loadBankAccounts(query, nextPage, false)
      }
    }
  }, [isLoadingMore, hasMore, bankAccount.account_number, bankAccount.account_name, page, loadBankAccounts])

  useEffect(() => {
    const scrollContainer = scrollContainerRef.current
    if (scrollContainer) {
      scrollContainer.addEventListener("scroll", handleScroll)
      return () => scrollContainer.removeEventListener("scroll", handleScroll)
    }
  }, [handleScroll])

  const handleBankAccountSelect = (selectedBankAccount: BankAccountType) => {
    const currentActiveInput = activeInput // Store current active input

    setBankAccount({
      id: selectedBankAccount.id,
      account_number: selectedBankAccount.account_number,
      account_name: selectedBankAccount.account_name,
    })
    onChange({
      id: selectedBankAccount.id,
      account_number: selectedBankAccount.account_number,
      account_name: selectedBankAccount.account_name,
    })
    setList([])
    setIsDropdownOpen(false)
    setError("")

    // Restore focus to the previously active input
    setTimeout(() => {
      if (currentActiveInput === "account_number" && account_numberInputRef.current) {
        account_numberInputRef.current.focus()
      } else if (currentActiveInput === "account_name" && account_nameInputRef.current) {
        account_nameInputRef.current.focus()
      }
    }, 0)
  }

  const handleClear = () => {
    // Clear debounce timeout
    if (debounceTimeoutRef.current) {
      clearTimeout(debounceTimeoutRef.current)
    }

    setBankAccount({
      id: "",
      account_number: "",
      account_name: "",
    })
    onChange({
      id: "",
      account_number: "",
      account_name: "",
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
            ref={account_numberInputRef}
            className="border-0 rounded-none focus-visible:ring-0 focus-visible:ring-offset-0"
            placeholder="Nomor Rekening"
            value={bankAccount.account_number}
            onChange={handleCodeChange}
            onKeyDown={handleCodeKeyDown}
            // disabled={isLoading}
            onFocus={() => {
              setActiveInput("account_number")
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
            ref={account_nameInputRef}
            className="border-0 rounded-none focus-visible:ring-0 focus-visible:ring-offset-0"
            placeholder="Nama Rekening"
            value={bankAccount.account_name}
            onChange={handleNameChange}
            onKeyDown={handleNameKeyDown}
            // disabled={isLoading}
            onFocus={() => {
              setActiveInput("account_name")
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
            <Button variant="ghost" size="sm" className="rounded-none  px-3" onClick={handleClear}>
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
                    onClick={() => handleBankAccountSelect(item)}
                  >
                    <div className="text-sm font-medium truncate">{item.account_number}</div>
                    <div className="text-sm truncate col-span-3">{item.account_name}</div>
                  </div>
                ))}

                {isLoadingMore && (
                  <div className="flex items-center justify-center py-4">
                    <Loader2 className="h-4 w-4 animate-spin mr-2" />
                    <span className="text-sm text-muted-foreground">Loading more...</span>
                  </div>
                )}

                {!hasMore && list.length > 0 && (
                  <div className="text-center py-4 text-sm text-muted-foreground">No more bankAccounts to load</div>
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
