"use client"

import type React from "react"
import { Search, FileText, Loader2, X } from "lucide-react"
import { Button } from "@/Components/ui/button"
import { Input } from "@/Components/ui/input"
import { Card, CardContent } from "@/Components/ui/card"
import { Badge } from "@/Components/ui/badge"
import { useEffect, useState, useRef, useCallback } from "react"
import axios from "axios"
import { ChartOfAccountType } from "@/Pages/Chart_ofAccount/@types/chart-of-account-type"


type SearchChartOfAccountProps = {
  onChange: (chartOfAccount: ChartOfAccountType) => void
  clearable?: boolean
  value?: ChartOfAccountType
  nextRef?: React.RefObject<HTMLInputElement>
  accountCodeRef?: React.RefObject<HTMLInputElement>
}

export default function SearchChartOfAccount({ onChange, value, nextRef, accountCodeRef, clearable = true }: SearchChartOfAccountProps) {
  const [chartOfAccount, setChartOfAccount] = useState<ChartOfAccountType>({
    id: "",
    code: "",
    name: "",
  })
  const [isLoading, setIsLoading] = useState(false)
  const [error, setError] = useState("")
  const [list, setList] = useState<ChartOfAccountType[]>([])
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
      setChartOfAccount({
        id: value.id || "",
        code: value.code || "",
        name: value.name || "",
      })
    }
  }, [value])

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
          selectAccount(list[selectedIndex])
          console.log("Selected account:", list[selectedIndex])
          nextRef?.current?.focus() // Focus next input if available
        } else {
          // If no item is selected, perform search
          if (e.currentTarget === document.activeElement) {
            const target = e.currentTarget as HTMLInputElement
            if (target.placeholder === "Kode Akun") {
              searchByCode(chartOfAccount.code ?? "")
            } else {
              searchByName(chartOfAccount.name ?? "")
            }
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
    [showDropdown, list, selectedIndex, chartOfAccount.code, chartOfAccount.name],
  )

  const handleCodeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const newCode = e.target.value
    setChartOfAccount((prev) => ({ ...prev, code: newCode }))

    // Clear previous timeout
    if (searchTimeoutRef.current) {
      clearTimeout(searchTimeoutRef.current)
    }

    // Debounce search to prevent too many API calls
    searchTimeoutRef.current = setTimeout(() => {
      handleSearch(newCode)
    }, 300)
    setError("")
  }

  const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const newName = e.target.value
    setChartOfAccount((prev) => ({ ...prev, name: newName }))

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
    fetchAccounts(query, 1, true)
  }, [])

  const fetchAccounts = async (query: string, pageNum: number, reset = false) => {
    if (reset) {
      setIsLoading(true)
    } else {
      setIsLoadingMore(true)
    }

    try {
      const response = await axios.get(`/admin/chart-of-account/list`, {
        params: {
          search: query,
          children: 0,
          page: pageNum,
          limit: 10,
        },
      })

      const newAccounts = response.data.data || []

      if (reset) {
        setList(newAccounts)
      } else {
        setList((prev) => [...prev, ...newAccounts])
      }

      // Check if there are more accounts to load
      setHasMore(newAccounts.length === 10)
      setShowDropdown(newAccounts.length > 0 || (!reset && list.length > 0))
    } catch (error) {
      console.error("Error fetching chart of accounts:", error)
      setError("Failed to fetch accounts")
    } finally {
      setIsLoading(false)
      setIsLoadingMore(false)
    }
  }

  const loadMore = useCallback(() => {
    if (!isLoadingMore && hasMore && currentQuery) {
      const nextPage = page + 1
      setPage(nextPage)
      fetchAccounts(currentQuery, nextPage, false)
    }
  }, [isLoadingMore, hasMore, currentQuery, page])

  // Fixed scroll handler
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
      const { data } = await axios.get(`/admin/chart-of-account/search-by-code?search=${code}`)
      if (data.data) {
        const accountData = {
          id: data.data.id,
          code: data.data.code,
          name: data.data.name,
          description: data.data.description || "",
          type: data.data.type || "",
          parent_id: data.data.parent_id || "",
        }
        setChartOfAccount(accountData)
        onChange(accountData) // Call onChange directly for exact searches
        setShowDropdown(false)
      } else {
        setError("Account not found")
      }
    } catch (err) {
      setError("Failed to search account")
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
      const { data } = await axios.get(`/admin/chart-of-account/search-by-name?search=${name}`)
      if (data.data) {
        const accountData = {
          id: data.data.id,
          code: data.data.code,
          name: data.data.name,
          description: data.data.description || "",
          type: data.data.type || "",
          parent_id: data.data.parent_id || "",
        }
        setChartOfAccount(accountData)
        onChange(accountData) // Call onChange directly for exact searches
        setShowDropdown(false)
      } else {
        setError("Account not found")
      }
    } catch (err) {
      setError("Failed to search account")
      console.error(err)
    } finally {
      setIsLoading(false)
    }
  }

  const handleSearchButtonClick = () => {
    if (chartOfAccount.code) {
      searchByCode(chartOfAccount.code)
    } else if (chartOfAccount.name) {
      searchByName(chartOfAccount.name)
    }
  }

  const selectAccount = (selectedAccount: ChartOfAccountType) => {
    setChartOfAccount(selectedAccount)
    onChange(selectedAccount)
    setShowDropdown(false)
    setList([])
    setSelectedIndex(-1)
  }

  // Close dropdown when clicking outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
        setShowDropdown(false)
        setSelectedIndex(-1)
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
              placeholder="Kode Akun"
              ref={accountCodeRef}
              value={chartOfAccount.code}
              onChange={handleCodeChange}
              onKeyDown={handleKeyNavigation}
            />
          </div>
          <Button
            variant="ghost"
            size="sm"
            className="rounded-none border-l border-r px-3"
            onClick={handleSearchButtonClick}
            disabled={isLoading}
          >
            {isLoading ? <Loader2 className="h-4 w-4 animate-spin" /> : <Search className="h-4 w-4" />}
          </Button>
          <div className="flex-[3]">
            <Input
              className="border-0 rounded-none focus-visible:ring-0 focus-visible:ring-offset-0"
              placeholder="Nama Akun"
              value={chartOfAccount.name}
              onChange={handleNameChange}
              onKeyDown={handleKeyNavigation}
            />
          </div>
          {clearable  && (
            <Button
              variant="ghost"
              // size="sm"
              className="rounded-none px-3 h-full"
              onClick={() => {
                setChartOfAccount({ id: "", code: "", name: "" })
                onChange({ id: "", code: "", name: "" })
                setError("")
                accountCodeRef?.current?.focus()
              }}
            >
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
                  <div className="text-xs text-muted-foreground mb-2 px-2 sticky top-0 bg-white">
                    Chart of Accounts ({currentQuery}) - {list.length} accounts
                  </div>
                  {list.map((account, index) => (
                    <div
                      key={`${account.id}-${index}`}
                      ref={(el) => (itemRefs.current[index] = el)}
                      className={`flex items-center justify-between p-3 rounded-md cursor-pointer transition-colors ${selectedIndex === index ? "bg-primary/10 border border-primary/20" : "hover:bg-accent"
                        }`}
                      onClick={() => selectAccount(account)}
                      onMouseEnter={() => setSelectedIndex(index)}
                    >
                      <div className="flex items-center gap-3">
                        <div className="p-2 bg-primary/10 rounded-md">
                          <FileText className="h-4 w-4 text-primary" />
                        </div>
                        <div>
                          <div className="font-medium text-sm">{account.name}</div>
                          <div className="text-xs text-muted-foreground flex gap-2">
                            <span>{account.code}</span>
                          </div>
                        </div>
                      </div>
                      <div className="text-right">
                        <div className="text-sm font-medium">{account.code}</div>
                      </div>
                    </div>
                  ))}
                  {isLoadingMore && (
                    <div className="flex items-center justify-center p-4 bg-gray-50">
                      <Loader2 className="h-4 w-4 animate-spin mr-2" />
                      <span className="text-sm text-muted-foreground">Loading more accounts...</span>
                    </div>
                  )}
                  {!hasMore && list.length > 0 && (
                    <div className="text-center p-4 text-xs text-muted-foreground bg-gray-50 border-t">
                      ✅ All accounts loaded ({list.length} total)
                    </div>
                  )}
                  {list.length === 0 && !isLoading && (
                    <div className="text-center p-4 text-sm text-muted-foreground">No accounts found</div>
                  )}
                </div>
              </div>
            </CardContent>
          </Card>
        )}

        {error && <div className="text-sm text-destructive mt-2 p-2 bg-destructive/10 rounded-md">{error}</div>}

        {chartOfAccount.id && !isLoading && !error && (
          <Card className="mt-3">
            <CardContent className="p-4">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <div className="p-2 bg-primary/10 rounded-md">
                    <FileText className="h-5 w-5 text-primary" />
                  </div>
                  <div>
                    <div className="font-medium">{chartOfAccount.name}</div>
                    <div className="text-sm text-muted-foreground flex gap-2">
                      <span>{chartOfAccount.code}</span>
                    </div>
                  </div>
                </div>
                <div className="text-right">
                  <Badge variant="secondary" className="bg-blue-100 text-blue-800 hover:bg-blue-100">
                    Account
                  </Badge>
                </div>
              </div>
            </CardContent>
          </Card>
        )}
      </div>

      {/* Keyboard shortcuts help */}
      {showDropdown && list.length > 0 && (
        <div className="text-xs text-muted-foreground bg-muted/50 p-2 rounded-md">
          <div className="flex gap-4">
            <span>↑↓ Navigate</span>
            <span>Enter Select</span>
            <span>Esc Close</span>
          </div>
        </div>
      )}
    </div>
  )
}
