





"use client"

import React from "react"
import { useState, useEffect, useRef, useCallback } from "react"
import { Button } from "@/Components/ui/button"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table"
import { Input } from "@/Components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card"
import { Badge } from "@/Components/ui/badge"
import { Separator } from "@/Components/ui/separator"
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/Components/ui/dropdown-menu"
import {
    ArrowDown,
    ArrowUp,
    ArrowUpDown,
    Download,
    ExternalLink,
    MoreVertical,
    Plus,
    Loader2,
    Trash2,
    Edit,
    Printer,
    Search,
    Calendar,
    Filter,
    RefreshCw,
    Database,
    Eye,
    FileText,
    ChevronRight,
    ChevronsRight,
    ChevronLeft,
    ChevronsLeft,
    Logs,
    List,
    Pin,
} from "lucide-react"
import axios from "axios"
import moment from "moment"
import { PromiseAlertDialog, showAlertDialog } from "./ui/promise-alert-dialog"
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "./ui/dialog"
import { toast } from "@/hooks/use-toast"
import FormGroup from "./ui/form-group"
import SearchDivision from "./SearchDivision"
import SelectAssetType from "./select-asset-type"
import AuditDialog from "./audit-dialog"
import { PromiseAlertDialogWithReason, showAlertDialogWithReason } from "./ui/promise-alert-dialog-with-reason"
import { useJournalDialog } from "@/hooks/use-journal-dialog"
import JournalDialog from "./JournalDialog"
import { Tooltip } from "./ui/tooltip"

type ColumnDef = {
    accessorKey: string
    header: string
    className?: string
    cell: (info: any) => React.ReactNode
}

type AdditionalFilter = {
    key: string
    label: string
    type: 'select' | 'input' | 'date' | 'dateRange' | 'number' | 'select-asset-type'
    options?: { value: string; label: string }[]
    placeholder?: string
    apiUrl?: string // For dynamic options from API
}

type DataTableProps = {
    columns: ColumnDef[]
    apiUrl: string
    dummyData?: any[]
    refreshTrigger?: number
    onCreate?: () => void
    onEdit?: (id: string) => void
    onDelete?: (id: string, reason?: string) => void
    onPrint?: (id: string) => void
    onExport?: () => void
    onDetail?: (id: string) => void
    onPrintDelivery?: (id: string) => void
    isFilterDate?: boolean
    isFilterDivision?: boolean
    canForceDelete?: boolean
    title?: string
    description?: string
    additionalFilters?: AdditionalFilter[]
    withReason?: boolean
    auditableType?: string
    canCreate?: boolean
    canUpdate?: boolean
    canDelete?: boolean
    transactionType?: string
    additionalActionButtons?: (row: any) => React.ReactNode
}

export default function EnhancedDataTable({
    columns,
    apiUrl,
    refreshTrigger,
    canCreate = false,
    canUpdate = false,
    canDelete = false,
    onCreate,
    onEdit,
    onDelete,
    onDetail,
    onExport,
    onPrint,
    onPrintDelivery,
    isFilterDate,
    isFilterDivision,
    canForceDelete = false,
    title = "Data Management",
    description = "Manage your data with advanced filtering and actions",
    additionalFilters = [],
    withReason = false,
    auditableType,
    transactionType,
    additionalActionButtons,
}: DataTableProps) {
    // Helper function to clear all localStorage for this table
    const clearAllLocalStorage = () => {
        const keys = Object.keys(localStorage)
        keys.forEach(key => {
            if (key.startsWith(`${apiUrl}.`)) {
                localStorage.removeItem(key)
            }
        })
    }
    const [data, setData] = useState<any[]>([])
    const [loading, setLoading] = useState(false)
    const [loadingMore, setLoadingMore] = useState(false)
    const [error, setError] = useState<string | null>(null)
    const [selectedItems, setSelectedItems] = useState<string[]>([])
    const [sortColumn, setSortColumn] = useState<string | null>(null)
    const [sortOrder, setSortOrder] = useState<"asc" | "desc">("asc")
    const [searchTerm, setSearchTerm] = useState("")
    const [hasMore, setHasMore] = useState(true)
    const [currentPage, setCurrentPage] = useState(1)
    const [totalRecords, setTotalRecords] = useState(0)
    const [printModalShow, setPrintModalShow] = useState(false)
    const [printModalUrl, setPrintModalUrl] = useState("")
    const [startDate, setStartDate] = useState<string | null>(null)
    const [endDate, setEndDate] = useState<string | null>(null)
    const [division, setDivision] = useState({
        id: "",
        code: "",
        name: "",
    })
    const [additionalFilterValues, setAdditionalFilterValues] = useState<{ [key: string]: any }>({})
    const [additionalFilterOptions, setAdditionalFilterOptions] = useState<{ [key: string]: any[] }>({})
    const [showFilters, setShowFilters] = useState(false)
    const [filtersLoaded, setFiltersLoaded] = useState(false)
    const { isOpen, hideJournal, showJournal } = useJournalDialog();
    const [transactionId, setTransactionId] = useState<string>();


    useEffect(() => {
        const getFiltersFromLocalStorage = () => {
            // Date filters
            const storedStartDate = localStorage.getItem(`${apiUrl}.startDate`) as string
            const storedEndDate = localStorage.getItem(`${apiUrl}.endDate`) as string
            if (storedStartDate) {
                setStartDate(storedStartDate)
            }
            if (storedEndDate) {
                setEndDate(storedEndDate)
            }

            // Search term
            const storedSearchTerm = localStorage.getItem(`${apiUrl}.searchTerm`)
            if (storedSearchTerm) {
                setSearchTerm(storedSearchTerm)
            }

            // Division filter
            const storedDivision = localStorage.getItem(`${apiUrl}.division`)
            if (storedDivision) {
                try {
                    const parsedDivision = JSON.parse(storedDivision)
                    setDivision(parsedDivision)
                } catch (error) {
                    console.error('Error parsing stored division:', error)
                }
            }

            // Additional filters
            const storedAdditionalFilters = localStorage.getItem(`${apiUrl}.additionalFilters`)
            if (storedAdditionalFilters) {
                try {
                    const parsedFilters = JSON.parse(storedAdditionalFilters)
                    setAdditionalFilterValues(parsedFilters)
                } catch (error) {
                    console.error('Error parsing stored additional filters:', error)
                }
            }

            // Sort settings
            const storedSortColumn = localStorage.getItem(`${apiUrl}.sortColumn`)
            const storedSortOrder = localStorage.getItem(`${apiUrl}.sortOrder`)
            if (storedSortColumn) {
                setSortColumn(storedSortColumn)
            }
            if (storedSortOrder && (storedSortOrder === 'asc' || storedSortOrder === 'desc')) {
                setSortOrder(storedSortOrder)
            }

            // Show filters state
            const storedShowFilters = localStorage.getItem(`${apiUrl}.showFilters`)
            if (storedShowFilters === 'true') {
                setShowFilters(true)
            }

            // Mark filters as loaded
            setFiltersLoaded(true)
        }
        getFiltersFromLocalStorage()
    }, [apiUrl])

    // Load options for additional filters from API
    useEffect(() => {
        const loadFilterOptions = async () => {
            const optionsPromises = additionalFilters
                .filter(filter => filter.apiUrl)
                .map(async filter => {
                    try {
                        const response = await axios.get(filter.apiUrl!)
                        return {
                            key: filter.key,
                            options: response.data
                        }
                    } catch (error) {
                        console.error(`Error loading options for ${filter.key}:`, error)
                        return {
                            key: filter.key,
                            options: []
                        }
                    }
                })

            const results = await Promise.all(optionsPromises)
            const optionsMap: { [key: string]: any[] } = {}
            results.forEach(result => {
                optionsMap[result.key] = result.options
            })
            setAdditionalFilterOptions(optionsMap)
        }

        if (additionalFilters.length > 0) {
            loadFilterOptions()
        }
    }, [additionalFilters])

    // Save filters to localStorage when they change
    useEffect(() => {
        if (startDate) {
            localStorage.setItem(`${apiUrl}.startDate`, startDate.toString())
        } else {
            localStorage.removeItem(`${apiUrl}.startDate`)
        }
    }, [startDate, apiUrl])

    useEffect(() => {
        if (endDate) {
            localStorage.setItem(`${apiUrl}.endDate`, endDate.toString())
        } else {
            localStorage.removeItem(`${apiUrl}.endDate`)
        }
    }, [endDate, apiUrl])

    useEffect(() => {
        if (searchTerm) {
            localStorage.setItem(`${apiUrl}.searchTerm`, searchTerm)
        } else {
            localStorage.removeItem(`${apiUrl}.searchTerm`)
        }
    }, [searchTerm, apiUrl])

    useEffect(() => {
        if (division.id || division.code || division.name) {
            localStorage.setItem(`${apiUrl}.division`, JSON.stringify(division))
        } else {
            localStorage.removeItem(`${apiUrl}.division`)
        }
    }, [division, apiUrl])

    useEffect(() => {
        const hasActiveFilters = Object.values(additionalFilterValues).some(v =>
            v !== '' && v !== null && v !== undefined && v !== 'all'
        )
        if (hasActiveFilters) {
            localStorage.setItem(`${apiUrl}.additionalFilters`, JSON.stringify(additionalFilterValues))
        } else {
            localStorage.removeItem(`${apiUrl}.additionalFilters`)
        }
    }, [additionalFilterValues, apiUrl])

    useEffect(() => {
        if (sortColumn) {
            localStorage.setItem(`${apiUrl}.sortColumn`, sortColumn)
        } else {
            localStorage.removeItem(`${apiUrl}.sortColumn`)
        }
    }, [sortColumn, apiUrl])

    useEffect(() => {
        localStorage.setItem(`${apiUrl}.sortOrder`, sortOrder)
    }, [sortOrder, apiUrl])

    useEffect(() => {
        localStorage.setItem(`${apiUrl}.showFilters`, showFilters.toString())
    }, [showFilters, apiUrl])

    const observer = useRef<IntersectionObserver>()
    const lastElementRef = useCallback(
        (node: HTMLTableRowElement) => {
            if (loading || loadingMore) return
            if (observer.current) observer.current.disconnect()
            observer.current = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting && hasMore) {
                    loadMoreData()
                }
            })
            if (node) observer.current.observe(node)
        },
        [loading, loadingMore, hasMore],
    )

    // Reset data when filters change (only after filters are loaded)
    useEffect(() => {
        if (!filtersLoaded) return // Don't fetch data until filters are loaded from localStorage

        setData([])
        setCurrentPage(1)
        setHasMore(true)
        fetchData(true)
    }, [apiUrl, sortColumn, sortOrder, searchTerm, refreshTrigger, startDate, endDate, division.id, additionalFilterValues, filtersLoaded])

    // Initial data load when component mounts and filters are loaded
    useEffect(() => {
        if (filtersLoaded) {
            setData([])
            setCurrentPage(1)
            setHasMore(true)
            fetchData(true)
        }
    }, [filtersLoaded])

    const fetchData = async (reset = false) => {
        if (reset) {
            setLoading(true)
        } else {
            setLoadingMore(true)
        }
        setError(null)

        try {
            const page = reset ? 1 : currentPage
            const queryParams = new URLSearchParams({
                take: "50",
                page: page.toString(),
                sort: sortColumn || "",
                order: sortOrder,
                search: searchTerm,
                division_id: isFilterDivision ? (division.id ? division.id : "") : "",
                start_date: isFilterDate ? (startDate ? moment(startDate).format("YYYY-MM-DD") : "") : "",
                end_date: isFilterDate ? (endDate ? moment(endDate).format("YYYY-MM-DD") : "") : "",
            })

            // Add additional filter values to query params
            Object.entries(additionalFilterValues).forEach(([key, value]) => {
                if (value !== null && value !== undefined && value !== '') {
                    queryParams.append(key, value.toString())
                }
            })

            const { data: response } = await axios.get(`${apiUrl}/list?${queryParams}`)

            if (reset) {
                setData(response.data)
            } else {
                setData((prevData) => [...prevData, ...response.data])
            }

            setTotalRecords(response.total)
            setHasMore(response.current_page < response.last_page)

            if (!reset) {
                setCurrentPage((prev) => prev + 1)
            } else {
                setCurrentPage(2)
            }
        } catch (err) {
            setError("An error occurred while fetching data")
        } finally {
            setLoading(false)
            setLoadingMore(false)
        }
    }

    const loadMoreData = () => {
        if (!loadingMore && hasMore) {
            fetchData(false)
        }
    }

    const handleSort = (column: string) => {
        if (sortColumn === column) {
            setSortOrder(sortOrder === "asc" ? "desc" : "asc")
        } else {
            setSortColumn(column)
            setSortOrder("asc")
        }
    }

    const handleSearch = (e: React.ChangeEvent<HTMLInputElement>) => {
        setSearchTerm(e.target.value)
    }

    const getSortIcon = (columnKey: string) => {
        if (sortColumn !== columnKey) {
            return <ArrowUpDown className="ml-2 h-4 w-4 text-gray-400" />
        }
        if (sortOrder === "asc") {
            return <ArrowUp className="ml-2 h-4 w-4 text-blue-600" />
        }
        if (sortOrder === "desc") {
            return <ArrowDown className="ml-2 h-4 w-4 text-blue-600" />
        }
        return <ArrowUpDown className="ml-2 h-4 w-4 text-gray-400" />
    }

    const handleRefresh = () => {
        setData([])
        setCurrentPage(1)
        setHasMore(true)
        fetchData(true)
    }

    const clearFilters = () => {
        setSearchTerm("")
        setDivision({ id: "", code: "", name: "" })
        setStartDate(null)
        setEndDate(null)
        setSortColumn(null)
        setSortOrder("asc")
        setAdditionalFilterValues({})
        setShowFilters(false)

        // Clear all localStorage for this table
        clearAllLocalStorage()

        // Set default values back
        localStorage.setItem(`${apiUrl}.sortOrder`, "asc")
        localStorage.setItem(`${apiUrl}.showFilters`, "false")

        // Keep filtersLoaded as true since we've manually set the values
        setFiltersLoaded(true)
    }

    const handleAdditionalFilterChange = (key: string, value: any) => {
        setAdditionalFilterValues(prev => ({
            ...prev,
            [key]: value
        }))
    }

    const renderAdditionalFilter = (filter: AdditionalFilter) => {
        const value = filter.type === 'select'
            ? (additionalFilterValues[filter.key] || 'all')
            : (additionalFilterValues[filter.key] || '')

        switch (filter.type) {
            case 'select':
                const options = filter.options || additionalFilterOptions[filter.key] || []
                return (
                    <FormGroup key={filter.key} label={filter.label}>
                        <Select
                            value={value}
                            onValueChange={(value) => handleAdditionalFilterChange(filter.key, value === "all" ? "" : value)}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder={filter.placeholder || `Pilih ${filter.label}`} />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{filter.placeholder || `All ${filter.label}`}</SelectItem>
                                {options.map((option: any) => (
                                    <SelectItem key={option.value} value={option.value ?? ''}>
                                        {option.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </FormGroup>
                )

            case 'input':
                return (
                    <FormGroup key={filter.key} label={filter.label}>
                        <Input
                            placeholder={filter.placeholder || `Enter ${filter.label}`}
                            value={value}
                            onChange={(e) => handleAdditionalFilterChange(filter.key, e.target.value)}
                        />
                    </FormGroup>
                )

            case 'number':
                return (
                    <FormGroup key={filter.key} label={filter.label}>
                        <Input
                            type="number"
                            placeholder={filter.placeholder || `Enter ${filter.label}`}
                            value={value}
                            onChange={(e) => handleAdditionalFilterChange(filter.key, e.target.value)}
                        />
                    </FormGroup>
                )

            case 'date':
                return (
                    <FormGroup key={filter.key} label={filter.label}>
                        <Input
                            type="date"
                            value={value}
                            onChange={(e) => handleAdditionalFilterChange(filter.key, e.target.value)}
                        />
                    </FormGroup>
                )

            case 'select-asset-type':
                return (
                    <FormGroup key={filter.key} label={filter.label}>
                        <SelectAssetType
                            value={additionalFilterValues[filter.key] ? {
                                id: additionalFilterValues[filter.key],
                                name: additionalFilterValues[filter.key + '_name'] || '',
                                description: additionalFilterValues[filter.key + '_description'] || ''
                            } : undefined}
                            onChange={(assetType) => {
                                handleAdditionalFilterChange(filter.key, assetType.id)
                                handleAdditionalFilterChange(filter.key + '_name', assetType.name)
                                handleAdditionalFilterChange(filter.key + '_description', assetType.description)
                            }}
                            clearable={true}
                        />
                    </FormGroup>
                )

            default:
                return null
        }
    }

    function handleForceDelete(id: string) {
        showAlertDialog({
            title: "Apakah Anda yakin?",
            description: "Tindakan ini tidak dapat dibatalkan. Ini akan menghapus data Anda secara permanen.",
            cancelText: "Batal",
            confirmText: "Ya, Hapus data",
        }).then(async (result) => {
            if (result) {
                try {
                    await axios.delete(`${apiUrl}/${id}/force-delete`)
                    setData((prevData) => prevData.filter((item) => item.id !== id))
                    toast({
                        title: "Data berhasil dihapus",
                        description: "Data telah dihapus secara permanen.",
                    })
                } catch (error) {
                    setError("An error occurred while deleting the data")
                }
            }
        })
    }

    if (error) {
        return (
            <Card className="border-red-200 dark:border-red-800">
                <CardContent className="flex flex-col items-center justify-center py-12">
                    <div className="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mb-4">
                        <Database className="h-8 w-8 text-red-600 dark:text-red-400" />
                    </div>
                    <h3 className="text-lg font-medium text-red-900 dark:text-red-100 mb-2">Error Loading Data</h3>
                    <p className="text-red-600 dark:text-red-400 text-center mb-4">{error}</p>
                    <Button
                        onClick={handleRefresh}
                        variant="outline"
                        className="border-red-200 text-red-700 hover:bg-red-50 bg-transparent"
                    >
                        <RefreshCw className="h-4 w-4 mr-2" />
                        Try Again
                    </Button>
                </CardContent>
            </Card>
        )
    }

    return (
        <>
            <div className="space-y-6 p-4 md:p-6 min-h-screen">
                {/* Header Section */}
                <div className="mb-8">
                    <div className="flex items-center gap-3 mb-2">
                        <div className="p-2  rounded-lg">
                            <Database className="h-6 w-6 " />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold">{title}</h1>
                            <p className="text-gray-600 dark:text-gray-400">{description}</p>
                        </div>
                    </div>
                </div>

                {/* Filters and Actions */}
                <Card className="shadow-lg  ">
                    <CardHeader className="border-b">
                        <CardTitle className="flex items-center gap-2">
                            <Filter className="h-5 w-5" />
                            Filters & Actions
                        </CardTitle>
                        <CardDescription className="">
                            Search, filter, and manage your data
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="p-6">
                        <div className="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
                            {/* Search */}
                            <div className="lg:col-span-3">
                                <label className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Search Data</label>
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                                    <Input
                                        placeholder="Search records..."
                                        value={searchTerm}
                                        onChange={handleSearch}
                                        className="pl-10 bg-white dark:bg-black"
                                    />
                                </div>
                            </div>
                            {isFilterDivision && (
                                <>
                                    <div className="lg:col-span-5">
                                        <FormGroup
                                            label="Divisi"
                                        >
                                            <SearchDivision
                                                onChange={(division: any) => {
                                                    setDivision(division)
                                                }}
                                                value={division}
                                                clearable
                                            />
                                        </FormGroup>
                                    </div>
                                </>
                            )}

                            {/* Date Filters */}
                            {isFilterDate && (
                                <>
                                    <div className="lg:col-span-2">
                                        <label className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                                            Start Date
                                        </label>
                                        <Input
                                            type="date"
                                            value={startDate ?? ""}
                                            onChange={(e: any) => setStartDate(e.target.value)}
                                            className=""
                                        />
                                    </div>
                                    <div className="lg:col-span-2">
                                        <label className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">End Date</label>
                                        <Input
                                            type="date"
                                            value={endDate ?? ""}
                                            onChange={(e: any) => setEndDate(e.target.value)}
                                            className=""
                                        />
                                    </div>
                                </>
                            )}

                            {/* Additional Filters */}
                            {additionalFilters.length > 0 && (
                                <div className="lg:col-span-12">
                                    <div className="flex items-center gap-2 mb-3">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => setShowFilters(!showFilters)}
                                            className="flex items-center gap-2"
                                        >
                                            <Filter className="h-4 w-4" />
                                            Additional Filters
                                            <Badge variant="secondary" className="ml-1">
                                                {Object.values(additionalFilterValues).filter(v => v !== '' && v !== null && v !== undefined && v !== 'all').length}
                                            </Badge>
                                        </Button>
                                    </div>

                                    {showFilters && (
                                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4  rounded-lg border">
                                            {additionalFilters.map(filter => renderAdditionalFilter(filter))}
                                        </div>
                                    )}
                                </div>
                            )}

                            {/* Action Buttons */}
                            <div className={`${isFilterDate ? "lg:col-span-4" : "lg:col-span-8"} flex gap-2`}>
                                <Button
                                    variant="secondary"
                                    onClick={clearFilters}
                                >
                                    <RefreshCw className="h-4 w-4 mr-2" />
                                    Clear
                                </Button>

                                {onExport && (
                                    <Button
                                        variant="outline"
                                        onClick={onExport}
                                    >
                                        <Download className="h-4 w-4 mr-2" />
                                        Export
                                    </Button>
                                )}

                                {canCreate ? (
                                    <Button

                                        onClick={onCreate}
                                    >
                                        <Plus className="h-4 w-4 mr-2" />
                                        Add New
                                    </Button>
                                ) : null}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Data Table */}
                <Card className="">
                    <CardHeader className="border-b border-gray-200 dark:border-gray-600">
                        <div className="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div>
                                <CardTitle className="text-xl text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                    <Database className="h-5 w-5" />
                                    Data Records
                                </CardTitle>
                                <CardDescription className="text-gray-600 dark:text-gray-400">
                                    {searchTerm && `Filtered by: "${searchTerm}" | `}
                                    {sortColumn && `Sorted by: ${sortColumn} (${sortOrder}) | `}
                                    Showing {data.length} of {totalRecords} records
                                </CardDescription>
                            </div>
                            <div className="flex items-center gap-2 mt-4 md:mt-0">
                                {hasMore && (
                                    <Badge
                                        variant="outline"
                                    >
                                        More available
                                    </Badge>
                                )}
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={handleRefresh}
                                    disabled={loading}
                                >
                                    <RefreshCw className={`h-4 w-4 mr-2 ${loading ? "animate-spin" : ""}`} />
                                    Refresh
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow className="">
                                        <TableHead className="w-[10px] font-semibold text-gray-900 dark:text-gray-100">
                                            <div className="flex items-center gap-2 p-2">
                                                <MoreVertical className="h-4 w-4" />
                                            </div>
                                        </TableHead>
                                        {columns.map((column) => (
                                            <TableHead
                                                key={column.accessorKey}
                                                className={`${column.className} font-semibold text-gray-900 dark:text-gray-100`}
                                            >
                                                <Button
                                                    variant="ghost"
                                                    onClick={() => handleSort(column.accessorKey)}
                                                    className={`w-full justify-between hover:bg-gray-100 dark:hover:bg-gray-600 ${sortColumn === column.accessorKey
                                                        ? "bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300"
                                                        : ""
                                                        }`}
                                                >
                                                    {column.header}
                                                    {getSortIcon(column.accessorKey)}
                                                </Button>
                                            </TableHead>
                                        ))}
                                        <TableHead className="font-semibold text-gray-900 dark:text-gray-100">

                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {loading && data.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={columns.length + 2} className="text-center py-12">
                                                <div className="flex flex-col items-center gap-4">
                                                    <div className="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                                        <Loader2 className="h-8 w-8 text-blue-600 dark:text-blue-400 animate-spin" />
                                                    </div>
                                                    <div>
                                                        <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Loading Data</h3>
                                                        <p className="text-gray-500 dark:text-gray-400">Please wait while we fetch your data...</p>
                                                    </div>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ) : data.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={columns.length + 2} className="text-center py-12">
                                                <div className="flex flex-col items-center gap-4">
                                                    <div className="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                                        <Database className="h-8 w-8 text-gray-400 dark:text-gray-500" />
                                                    </div>
                                                    <div>
                                                        <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No Data Found</h3>
                                                        <p className="text-gray-500 dark:text-gray-400">
                                                            {searchTerm ? `No records match "${searchTerm}"` : "No data available"}
                                                        </p>
                                                    </div>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        data.map((row, index) => (
                                            <TableRow
                                                key={index}
                                                ref={index === data.length - 1 ? lastElementRef : null}
                                                className={`
                          hover:bg-blue-50/50 dark:hover:bg-gray-700/50 transition-colors border-b border-gray-200 dark:border-gray-600
                          ${row.deleted_at ? "bg-red-50 dark:bg-red-900/20" : ""}
                          ${row.is_pinned ? "bg-yellow-200 dark:bg-yellow-900/20" : ""}
                        `}
                                                onDoubleClick={() => {
                                                    if (row.deleted_at) {
                                                        onDetail && onDetail(row.id)
                                                    } else {
                                                        onEdit && onEdit(row.id)

                                                    }
                                                }}
                                            >
                                                <TableCell className="">
                                                    <div className="relative flex items-center">

                                                        {row.deleted_at == null && (
                                                            <DropdownMenu>
                                                                <DropdownMenuTrigger asChild>
                                                                    <Button
                                                                        variant="ghost"
                                                                        size="sm"
                                                                        className="h-8 w-8 p-0 hover:bg-gray-100 dark:hover:bg-gray-600"
                                                                    >
                                                                        <MoreVertical className="h-4 w-4" />
                                                                    </Button>
                                                                </DropdownMenuTrigger>
                                                                <DropdownMenuContent align="end" className="w-48">
                                                                    {canUpdate ? (
                                                                        <DropdownMenuItem
                                                                            onClick={() => onEdit && onEdit(row.id)}
                                                                            className="flex items-center gap-2 cursor-pointer"
                                                                        >
                                                                            <Edit className="h-4 w-4" />
                                                                            Edit
                                                                            <a
                                                                                className="ml-auto hover:text-blue-500"
                                                                                target="_blank"
                                                                                onClick={(e) => e.stopPropagation()}
                                                                                href={`${apiUrl}/${row.id}/edit`}
                                                                                rel="noreferrer"
                                                                            >
                                                                                <ExternalLink className="h-4 w-4" />
                                                                            </a>
                                                                        </DropdownMenuItem>
                                                                    ) : null}
                                                                    {transactionType ? (
                                                                        <DropdownMenuItem
                                                                            onClick={() => {
                                                                                setTransactionId(row.id);
                                                                                showJournal(row.id, transactionType);
                                                                            }}
                                                                            className="flex items-center gap-2 cursor-pointer"
                                                                        >
                                                                            <List className="h-4 w-4" />
                                                                            Journal
                                                                        </DropdownMenuItem>
                                                                    ) : null}

                                                                    {onPrint && (
                                                                        <DropdownMenuItem
                                                                            onClick={() => {
                                                                                setPrintModalUrl(`${apiUrl}/${row.id}/print`)
                                                                                setPrintModalShow(true)
                                                                            }}
                                                                            className="flex items-center gap-2 cursor-pointer"
                                                                        >
                                                                            <Printer className="h-4 w-4" />
                                                                            Print
                                                                            <a
                                                                                className="ml-auto hover:text-blue-500"
                                                                                target="_blank"
                                                                                onClick={(e) => e.stopPropagation()}
                                                                                href={`${apiUrl}/${row.id}/print`}
                                                                                rel="noreferrer"
                                                                            >
                                                                                <ExternalLink className="h-4 w-4" />
                                                                            </a>
                                                                        </DropdownMenuItem>
                                                                    )}

                                                                    {onPrintDelivery && (
                                                                        <DropdownMenuItem
                                                                            onClick={() => {
                                                                                setPrintModalUrl(`${apiUrl}/${row.id}/print-delivery`)
                                                                                setPrintModalShow(true)
                                                                            }}
                                                                            className="flex items-center gap-2 cursor-pointer"
                                                                        >
                                                                            <FileText className="h-4 w-4" />
                                                                            Print Delivery
                                                                        </DropdownMenuItem>
                                                                    )}
                                                                    {additionalActionButtons && additionalActionButtons(row)}


                                                                    {canDelete ? (
                                                                        <>
                                                                            <Separator />
                                                                            <DropdownMenuItem
                                                                                onClick={async () => {
                                                                                    if (withReason) {
                                                                                        const { confirmed, reason } = await showAlertDialogWithReason({
                                                                                            title: "Apakah Anda yakin?",
                                                                                            description:
                                                                                                "Tindakan ini tidak dapat dibatalkan. Ini akan menghapus data Anda secara permanen.",
                                                                                            cancelText: "Batal",
                                                                                            confirmText: "Ya, Hapus data",
                                                                                        })
                                                                                        if (confirmed) {
                                                                                            onDelete!(row.id, reason)
                                                                                        }
                                                                                    } else {
                                                                                        const result = await showAlertDialog({
                                                                                            title: "Apakah Anda yakin?",
                                                                                            description:
                                                                                                "Tindakan ini tidak dapat dibatalkan. Ini akan menghapus data Anda secara permanen.",
                                                                                            cancelText: "Batal",
                                                                                            confirmText: "Ya, Hapus data",
                                                                                        })
                                                                                        if (result) {
                                                                                            onDelete!(row.id)
                                                                                        }

                                                                                    }
                                                                                }}
                                                                                className="flex items-center gap-2 cursor-pointer text-red-600 focus:text-red-600"
                                                                            >
                                                                                <Trash2 className="h-4 w-4" />
                                                                                Delete
                                                                            </DropdownMenuItem>
                                                                        </>
                                                                    ) : null}
                                                                </DropdownMenuContent>
                                                            </DropdownMenu>
                                                        )}

                                                        {row.deleted_at && canForceDelete && (
                                                            <Button
                                                                variant="destructive"
                                                                size="sm"
                                                                onClick={() => handleForceDelete(row.id)}
                                                                className="h-8 w-8 p-0"
                                                            >
                                                                <Trash2 className="h-4 w-4" />
                                                            </Button>
                                                        )}
                                                        {row.deleted_at && (
                                                            <Badge variant="destructive" className="text-xs">
                                                                Deleted
                                                            </Badge>
                                                        )}

                                                    </div>

                                                </TableCell>

                                                {columns.map((column) => (
                                                    <TableCell key={column.accessorKey} className={column.className}>
                                                        {column.cell(row)}
                                                    </TableCell>
                                                ))}

                                                <TableCell>
                                                    {auditableType ? (
                                                        <AuditDialog
                                                            auditableType={auditableType}
                                                            auditableId={row.id}
                                                            trigger={
                                                                <Button variant="outline" size="sm" >
                                                                    <Logs className="h-1 w-1 " />
                                                                </Button>
                                                            }
                                                        />
                                                    ) : (
                                                        <div className="space-y-1 text-xs">
                                                            <div className="flex items-center gap-2">
                                                                <Badge variant="outline" className="text-xs">
                                                                    Created
                                                                </Badge>
                                                                <span className="text-gray-600 dark:text-gray-400">{row.entry_by || "System"}</span>
                                                            </div>
                                                            <div className="text-gray-500 dark:text-gray-500">
                                                                {row.entry_at ? moment(row.entry_at).format("DD/MM/YYYY HH:mm") : "-"}
                                                            </div>
                                                            {row.last_edit_by && (
                                                                <>
                                                                    <div className="flex items-center gap-2">
                                                                        <Badge variant="outline" className="text-xs">
                                                                            Updated
                                                                        </Badge>
                                                                        <span className="text-gray-600 dark:text-gray-400">{row.last_edit_by}</span>
                                                                    </div>
                                                                    <div className="text-gray-500 dark:text-gray-500">
                                                                        {row.last_edit_at ? moment(row.last_edit_at).format("DD/MM/YYYY HH:mm") : "-"}
                                                                    </div>
                                                                </>
                                                            )}
                                                            {row.deleted_by && (
                                                                <>
                                                                    <div className="flex items-center gap-2">
                                                                        <Badge variant="outline" className="text-xs">
                                                                            Deleted
                                                                        </Badge>
                                                                        <span className="text-gray-600 dark:text-gray-400">{row.deleted_by}</span>
                                                                    </div>
                                                                    <div className="text-gray-500 dark:text-gray-500">
                                                                        {row.delete_reason ? `${row.delete_reason}` : "No reason provided"}
                                                                    </div>
                                                                    <div className="text-gray-500 dark:text-gray-500">
                                                                        {row.deleted_at ? moment(row.deleted_at).format("DD/MM/YYYY HH:mm") : "-"}
                                                                    </div>
                                                                </>
                                                            )}
                                                        </div>
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        {/* Loading more indicator */}
                        {loadingMore && (
                            <div className="flex items-center justify-center py-6 border-t border-gray-200 dark:border-gray-600">
                                <div className="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                    <Loader2 className="h-4 w-4 animate-spin" />
                                    Loading more data...
                                </div>
                            </div>
                        )}

                        {/* End of data indicator */}
                        {!hasMore && data.length > 0 && (
                            <div className="text-center py-6 border-t border-gray-200 dark:border-gray-600">
                                <div className="flex flex-col items-center gap-2">
                                    <Badge
                                        variant="outline"
                                        className="bg-green-50 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-700"
                                    >
                                        All data loaded
                                    </Badge>
                                    <p className="text-sm text-gray-500 dark:text-gray-400">
                                        Showing all {totalRecords.toLocaleString()} records
                                    </p>
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Print Modal */}
            {printModalShow && (
                <Dialog open={printModalShow} onOpenChange={setPrintModalShow}>
                    <DialogContent className="max-w-7xl max-h-[90vh] overflow-auto">
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2">
                                <Printer className="h-5 w-5" />
                                Print Preview
                            </DialogTitle>
                        </DialogHeader>
                        <div className="border rounded-lg overflow-hidden">
                            <iframe src={printModalUrl} className="w-full h-[80vh] border-0" title="Print Preview" />
                        </div>
                    </DialogContent>
                </Dialog>
            )}

            <PromiseAlertDialog />
            <PromiseAlertDialogWithReason />
            <JournalDialog
                open={isOpen}
                onOpenChange={hideJournal}
                transactionId={transactionId}
                transactionType={transactionType}
                title={"Jurnal " + transactionType}
            />
        </>
    )
}

interface PaginationProps {
    totalRecords: number;
    currentPage: number;
    totalPages: number;
    onPageChange: (page: number) => void;
}
export const Pagination: React.FC<PaginationProps> = ({ currentPage, totalPages, totalRecords, onPageChange }) => {
    const getPageNumbers = () => {
        const pageNumbers = [];
        const maxVisiblePages = 5;

        if (totalPages <= maxVisiblePages) {
            for (let i = 1; i <= totalPages; i++) {
                pageNumbers.push(i);
            }
        } else {
            if (currentPage <= 3) {
                for (let i = 1; i <= 4; i++) {
                    pageNumbers.push(i);
                }
                pageNumbers.push('...');
                pageNumbers.push(totalPages);
            } else if (currentPage >= totalPages - 2) {
                pageNumbers.push(1);
                pageNumbers.push('...');
                for (let i = totalPages - 3; i <= totalPages; i++) {
                    pageNumbers.push(i);
                }
            } else {
                pageNumbers.push(1);
                pageNumbers.push('...');
                for (let i = currentPage - 1; i <= currentPage + 1; i++) {
                    pageNumbers.push(i);
                }
                pageNumbers.push('...');
                pageNumbers.push(totalPages);
            }
        }

        return pageNumbers;
    };

    return (
        <div className="flex flex-col items-center space-y-2">
            <div className="flex items-center justify-center space-x-2">
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onPageChange(1)}
                    disabled={currentPage === 1}
                >
                    <ChevronsLeft className="h-4 w-4" />
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onPageChange(currentPage - 1)}
                    disabled={currentPage === 1}
                >
                    <ChevronLeft className="h-4 w-4" />
                </Button>
                {getPageNumbers().map((pageNumber, index) => (
                    <React.Fragment key={index}>
                        {pageNumber === '...' ? (
                            <span className="px-2">...</span>
                        ) : (
                            <Button
                                variant={currentPage === pageNumber ? "default" : "outline"}
                                size="sm"
                                onClick={() => onPageChange(pageNumber as number)}
                            >
                                {pageNumber}
                            </Button>
                        )}
                    </React.Fragment>
                ))}
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onPageChange(currentPage + 1)}
                    disabled={currentPage === totalPages}
                >
                    <ChevronRight className="h-4 w-4" />
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onPageChange(totalPages)}
                    disabled={currentPage === totalPages}
                >
                    <ChevronsRight className="h-4 w-4" />
                </Button>
            </div>
            <div className="text-sm text-muted-foreground">
                Total records: {totalRecords} | Page {currentPage} of {totalPages}
            </div>
        </div>
    );
};
