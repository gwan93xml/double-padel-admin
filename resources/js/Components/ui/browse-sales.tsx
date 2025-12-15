
import * as SelectPrimitive from "@radix-ui/react-select"
import { Check, ChevronDown, FolderIcon } from 'lucide-react'

import { cn } from "@/lib/utils"
import { ScrollArea } from "@radix-ui/react-scroll-area"
import { Button } from "./button"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "./dialog"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "./table"
import { Input } from "./input"
import { useEffect, useState } from "react"
import axios from "axios"
import moment from "moment"
import CurrencyFormatter from "./currency-formatter"
import { Pagination } from "../DataTable"


interface BrowseSalesProps extends React.ComponentPropsWithoutRef<typeof SelectPrimitive.Root> {
    remoteConfig: RemoteConfig
    placeholder?: string
    value?: string
    label?: string
    onChange: any,
    initialSelected?: SelectedProps | null
}
interface SelectedProps {
    value: string
    label: string
}
export default function BrowseSales({ value, label, remoteConfig, onChange, initialSelected }: BrowseSalesProps) {
    const [selected, setSelected] = useState<SelectedProps | null>(initialSelected ?? null)
    const [data, setData] = useState<any[]>([])
    const [sortColumn, setSortColumn] = useState<string | null>(null)
    const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>('asc')
    const [searchTerm, setSearchTerm] = useState('')
    const [currentPage, setCurrentPage] = useState(1)
    const [totalPages, setTotalPages] = useState(1)
    const [totalRecords, setTotalRecords] = useState(0)
    const [dialogOpen, setDialogOpen] = useState(false)
    function renderValue() {
        if (selected) {
            return selected.label;
        }
        return (
            <div className="text-gray-600 ">
                {`Pilih ${label}`}
            </div>
        );
    }


    const handleSearch = (e: React.ChangeEvent<HTMLInputElement>) => {
        setSearchTerm(e.target.value)
        setCurrentPage(1)
    }

    useEffect(() => {
        fetchData()
    }, [remoteConfig.url, sortColumn, sortOrder, searchTerm, currentPage])

    const fetchData = async () => {
        // setLoading(true)
        // setError(null)
        try {

            const queryParams = new URLSearchParams({
                page: currentPage.toString(),
                sort: sortColumn || '',
                order: sortOrder,
                search: searchTerm,
            })

            const { data } = await axios.get(`${remoteConfig.url}?${queryParams}`)
            // if (!response.ok) throw new Error('Failed to fetch data')
            // // const result = await response.json()
            setData(data.data)
            setTotalPages(data.last_page)
            setTotalRecords(data.total)
        } catch (err) {
            // setError('An error occurred while fetching data')
        } finally {
            // setLoading(false)
        }
    }


    const handlePageChange = (newPage: number) => {
        setCurrentPage(newPage)
    }
    return (
        <>
            <div className="shadow-sm rounded border  flex p-2 font-light cursor-pointer h-9" onClick={() => setDialogOpen(true)}>
                <div className="flex-1 text-sm">
                    {renderValue()}
                </div>
                <button type="button" >
                    <FolderIcon className="h-4" />
                </button>
            </div>
            <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
                <DialogContent className="md:max-w-[70%] ">
                    <DialogHeader>
                        <DialogTitle>Data Penjualan</DialogTitle>
                        <DialogDescription>
                            Silahkan pilih data penjualan yang ingin di retur
                        </DialogDescription>
                    </DialogHeader>

                    <>
                        <Input
                            placeholder="Silahkan cari penjualan..."
                            value={searchTerm}
                            onChange={handleSearch}
                        />
                        <div className="overflow-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>
                                            No.SO.
                                        </TableHead>
                                        <TableHead>
                                            Tanggal
                                        </TableHead>
                                        <TableHead>
                                            Customer
                                        </TableHead>
                                        <TableHead>
                                            Metode Pembayaran
                                        </TableHead>
                                        <TableHead>
                                            Subtotal
                                        </TableHead>
                                        <TableHead>
                                            Pajak
                                        </TableHead>
                                        <TableHead>
                                            Total
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {data.map((item, key) => (
                                        <TableRow>
                                            <TableCell>
                                                <button type="button" className="underline text-blue-600"
                                                    onClick={() => {
                                                        const newValue = {
                                                            label: `${item.sales_order_number} - ${moment(item.sale_date).format('DD/MM/YYYY')} - ${item.customer.name}`,
                                                            value: item.id
                                                        }
                                                        setSelected(newValue)
                                                        onChange(newValue)
                                                        setDialogOpen(false)
                                                    }}
                                                >
                                                    {item.sales_order_number}
                                                </button>
                                            </TableCell>
                                            <TableCell>
                                                {moment(item.sale_date).format('DD/MM/YYYY')}
                                            </TableCell>
                                            <TableCell>
                                                {item.customer.name}
                                            </TableCell>
                                            <TableCell>
                                                {item.payment_method}
                                            </TableCell>
                                            <TableCell>
                                                <CurrencyFormatter
                                                    amount={item.subtotal}
                                                />
                                            </TableCell>
                                            <TableCell>
                                                <CurrencyFormatter
                                                    amount={item.tax}
                                                />
                                            </TableCell>
                                            <TableCell>
                                                <CurrencyFormatter
                                                    amount={item.total_amount}
                                                />
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                        <Pagination
                            currentPage={currentPage}
                            totalPages={totalPages}
                            onPageChange={handlePageChange}
                            totalRecords={totalRecords}
                        />
                    </>
                </DialogContent>
            </Dialog>
        </>
    )
}