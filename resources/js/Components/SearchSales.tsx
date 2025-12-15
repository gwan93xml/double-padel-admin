
import type React from "react"

import { Folder, Search } from "lucide-react"
import { Button } from "@/Components/ui/button"
import { Input } from "@/Components/ui/input"
import { useEffect, useState, useRef } from "react"
import axios from "axios"
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "./ui/dialog"
import { Table, TableBody, TableHead, TableHeader, TableRow } from "./ui/table"
import moment from "moment"
import CurrencyFormatter from "./ui/currency-formatter"
import { SaleType } from "@/Pages/Sale/@types/sale-type"


type SearchSalesProps = {
    onChange: (purchase: SaleType) => void
    value?: SaleType
    vendorId?: string
}


export default function SearchSales({ onChange, value, vendorId }: SearchSalesProps) {
    const [purchase, setSales] = useState<SaleType>({
        id: "",
        number: "",
    })
    const [isLoading, setIsLoading] = useState(false)
    const [error, setError] = useState("")

    // Use a ref to track if the update is coming from internal state change
    const isInternalUpdate = useRef(false)
    // Store the previous value to compare
    const prevValueRef = useRef<SaleType | undefined>(value)

    // Initialize from props - only when value changes from external source

    useEffect(() => {
        if (value) {
            setSales({
                id: value.id || "",
                number: value.number || "",
            })
        }
    }, [])
    useEffect(() => {
        onChange(purchase)
    }, [purchase])

    const handleCodeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setSales({ ...purchase, number: e.target.value })
        setError("")
    }


    const searchByNumber = async (number: string) => {
        if (!number.trim()) return

        setIsLoading(true)
        setError("")

        try {
            const { data } = await axios.get(`/admin/purchase/search-by-number?search=${number}`)

            if (data.data) {
                // Set the flag before updating state
                isInternalUpdate.current = true
                setSales({
                    id: data.data.id,
                    number: data.data.number,
                })
            } else {
                setError("Sales order not found")
            }
        } catch (err) {
            setError("Failed to search purchase order")
            console.error(err)
        } finally {
            setIsLoading(false)
        }
    }


    const handleNumberKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === "Enter") {
            searchByNumber(purchase.number!)
        }
    }

    const handleSearchButtonClick = () => {
        if (purchase.number) {
            searchByNumber(purchase.number)
        }
    }

    const [browseOpen, setBrowseOpen] = useState(false)
    const [queryBrowse, setQueryBrowse] = useState("")
    const [browseSaless, setBrowseSaless] = useState<SaleType[]>([])
    const handleBrowse = () => {
        setBrowseOpen(true)
        handleSearchBrowse()
    }

    const handleSearchBrowse = async () => {
        try {
            const responseData = await axios.get(`/admin/purchase/list?search=${queryBrowse}&vendor_id=${vendorId}`)
            setBrowseSaless(responseData.data.data)
        } catch (err) {
            console.error(err)
        }
    }
    useEffect(() => {
        handleSearchBrowse()
    }, [])

    return (
        <div className="space-y-2">
            <div className="flex">
                <div className="w-full">
                    <Input
                        className="rounded-r-none"
                        placeholder="Nomor Sales "
                        value={purchase.number}
                        onChange={handleCodeChange}
                        onKeyDown={handleNumberKeyDown}
                        disabled={isLoading}
                    />
                </div>
                <div className="flex">
                    <Button className="rounded-none" onClick={handleSearchButtonClick} disabled={isLoading}>
                        <Search className="h-4 w-4" />
                    </Button>

                    <Button className="rounded-none" onClick={handleBrowse} disabled={isLoading}>
                        <Folder className="h-4 w-4" />
                    </Button>
                </div>
            </div>

            {isLoading && <div className="text-sm text-muted-foreground">Searching...</div>}

            {error && <div className="text-sm text-destructive">{error}</div>}

            <Dialog open={browseOpen} onOpenChange={setBrowseOpen}>
                <DialogContent className="min-w-[70%]">
                    <DialogHeader>
                        <DialogTitle>Browse Sales </DialogTitle>
                    </DialogHeader>
                    <div className="flex">
                        <Input
                            className="rounded-r-none"
                            value={queryBrowse}
                            onChange={(e) => setQueryBrowse(e.target.value)}
                        />
                        <Button
                            className="rounded-l-none"
                            onClick={handleSearchBrowse}
                        >
                            <Search className="h-4 w-4" />
                        </Button>
                    </div>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>
                                    NOMOR
                                </TableHead>
                                <TableHead>
                                    NOMOR PO
                                </TableHead>
                                <TableHead>
                                    DIVISI
                                </TableHead>
                                <TableHead>
                                    TANGGAL
                                </TableHead>
                                <TableHead>
                                    PELANGGAN
                                </TableHead>
                                <TableHead>
                                    TOTAL
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {browseSaless.map((purchase) => (
                                <TableRow
                                    key={purchase.id}
                                    onClick={() => {
                                        setSales(purchase)
                                        setBrowseOpen(false)
                                    }}
                                    className="cursor-pointer hover:bg-muted"
                                >
                                    <TableHead>
                                        {purchase.number}
                                    </TableHead>
                                    <TableHead>
                                        {purchase.division?.name}
                                    </TableHead>
                                    <TableHead>
                                        {moment(purchase.sale_date).format("DD/MM/YYYY")}
                                    </TableHead>
                                    <TableHead>
                                        {purchase.customer?.name}
                                    </TableHead>
                                    <TableHead>
                                        <CurrencyFormatter
                                            amount={parseFloat(purchase.total_amount ?? '0')}
                                        />
                                    </TableHead>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </DialogContent>
            </Dialog>
        </div>
    )
}

