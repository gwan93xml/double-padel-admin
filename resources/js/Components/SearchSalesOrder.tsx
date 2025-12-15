
import type React from "react"

import { Folder, Search } from "lucide-react"
import { Button } from "@/Components/ui/button"
import { Input } from "@/Components/ui/input"
import { useEffect, useState, useRef } from "react"
import axios from "axios"
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "./ui/dialog"
import { Table, TableBody, TableHead, TableHeader, TableRow } from "./ui/table"
import { set } from "lodash"
import CurrencyFormatter from "./ui/currency-formatter"
import moment from "moment"
import { SalesOrderType } from "@/Pages/SalesOrder/@types/sales-order-type"


type SearchSalesOrderProps = {
    customerId?: string | number
    onChange: (salesOrder: SalesOrderType) => void
    value?: SalesOrderType
}


export default function SearchSalesOrder({ 
    onChange, 
    value,
    customerId = "",


}: SearchSalesOrderProps) {
    const [salesOrder, setSalesOrder] = useState<SalesOrderType>({
        id: "",
        number: "",
    })
    const [isLoading, setIsLoading] = useState(false)
    const [error, setError] = useState("")

    // Use a ref to track if the update is coming from internal state change
    const isInternalUpdate = useRef(false)
    // Store the previous value to compare
    const prevValueRef = useRef<SalesOrderType | undefined>(value)

    // Initialize from props - only when value changes from external source

    useEffect(() => {
        if (value) {
            setSalesOrder({
                id: value.id || "",
                number: value.number || "",
            })
        }
    }, [])
    useEffect(() => {
        onChange(salesOrder)
    }, [salesOrder])

    const handleCodeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setSalesOrder({ ...salesOrder, number: e.target.value })
        setError("")
    }


    const searchByNumber = async (number: string) => {
        if (!number.trim()) return

        setIsLoading(true)
        setError("")

        try {
            const { data } = await axios.get(`/admin/sales-order/search-by-number?search=${number}`)

            if (data.data) {
                // Set the flag before updating state
                isInternalUpdate.current = true
                setSalesOrder({
                    id: data.data.id,
                    number: data.data.number,
                })
            } else {
                setError("Sales order not found")
            }
        } catch (err) {
            setError("Failed to search sales order")
            console.error(err)
        } finally {
            setIsLoading(false)
        }
    }


    const handleNumberKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === "Enter") {
            searchByNumber(salesOrder.number!)
        }
    }

    const handleSearchButtonClick = () => {
        if (salesOrder.number) {
            searchByNumber(salesOrder.number)
        }
    }

    const [browseOpen, setBrowseOpen] = useState(false)
    const [queryBrowse, setQueryBrowse] = useState("")
    const [browseSalesOrders, setBrowseSalesOrders] = useState<SalesOrderType[]>([])
    const handleBrowse = () => {
        setBrowseOpen(true)
        handleSearchBrowse()
    }

    const handleSearchBrowse = async () => {
        try {
            const responseData = await axios.get(`/admin/sales-order/list?search=${queryBrowse}&customer_id=${customerId}`)
            setBrowseSalesOrders(responseData.data.data)
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
                        placeholder="Nomor Sales Order"
                        value={salesOrder.number}
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

            {salesOrder.id && !isLoading && !error && (
                <div className="text-sm text-muted-foreground">
                    Sales Order found: {salesOrder.number}
                </div>
            )}
            <Dialog open={browseOpen} onOpenChange={setBrowseOpen}>
                <DialogContent className="min-w-[70%]">
                    <DialogHeader>
                        <DialogTitle>Browse Sales Order</DialogTitle>
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
                            {browseSalesOrders.map((salesOrder) => (
                                <TableRow
                                    key={salesOrder.id}
                                    onClick={() => {
                                        setSalesOrder(salesOrder)
                                        setBrowseOpen(false)
                                    }}
                                    className="cursor-pointer hover:bg-muted"
                                >
                                    <TableHead>
                                        {salesOrder.number}
                                    </TableHead>
                                    <TableHead>
                                        {salesOrder.purchase_order_number}
                                    </TableHead>
                                    <TableHead>
                                        {salesOrder.division?.name}
                                    </TableHead>
                                    <TableHead>
                                        {moment(salesOrder.date).format("DD/MM/YYYY")}
                                    </TableHead>
                                    <TableHead>
                                        {salesOrder.customer?.name}
                                    </TableHead>
                                    <TableHead>
                                        <CurrencyFormatter
                                            amount={parseFloat(salesOrder.total ?? '0')}
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

