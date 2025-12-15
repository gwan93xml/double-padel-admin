"use client"

import { SidebarTrigger } from "@/Components/ui/sidebar"
import { Separator } from "@/Components/ui/separator"
import {
    Breadcrumb,
    BreadcrumbItem as UIBreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/Components/ui/breadcrumb"
import { Link } from "@inertiajs/react"
// import { SimpleThemeToggle } from "./simple-theme-toggle"
import { Badge } from "@/Components/ui/badge"
import { Clock, Bell, AlertTriangle, Calendar } from "lucide-react"
import { useState, useEffect } from "react"
import { ThemeToggle } from "@/Components/theme-toggle"
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu"
import { Button } from "@/Components/ui/button"
import CurrencyFormatter from "@/Components/ui/currency-formatter"
import axios from "axios"
import moment from "moment"

interface OutdatedReceivable {
    id: number;
    customer_id: number;
    sale_id: number;
    amount: number;
    due_date: string;
    purchase_order_number: string;
    date: string;
    paid_amount: number;
    remaining_amount: number;
    status: string;
    notes: string;
    division_id: number;
}

interface HeaderProps {
    breadcrumbs: { label: string; href: string }[]
    showClock?: boolean
    variant?: "dropdown" | "simple"
}

export function Header({ breadcrumbs, showClock = false, variant = "dropdown" }: HeaderProps) {
    const [currentTime, setCurrentTime] = useState(new Date())
    const [outdatedReceivables, setOutdatedReceivables] = useState<OutdatedReceivable[]>([])
    const [isLoading, setIsLoading] = useState(false)

    useEffect(() => {
        if (showClock) {
            const timer = setInterval(() => {
                setCurrentTime(new Date())
            }, 1000)

            return () => clearInterval(timer)
        }
    }, [showClock])

    return (
        <header className="flex h-16 shrink-0 items-center gap-2 border-b px-4 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            {/* Left Section */}
            <div className="flex items-center gap-2">
                <SidebarTrigger className="-ml-1" />
                <Separator orientation="vertical" className="mr-2 h-4" />

                {/* Breadcrumbs */}
                <Breadcrumb>
                    <BreadcrumbList>
                        {breadcrumbs.map((breadcrumb, index) => {
                            if (index === breadcrumbs.length - 1) {
                                return (
                                    <UIBreadcrumbItem key={index}>
                                        <BreadcrumbPage className="font-medium">{breadcrumb.label}</BreadcrumbPage>
                                    </UIBreadcrumbItem>
                                )
                            } else {
                                return (
                                    <span key={index} className="flex items-center">
                                        <UIBreadcrumbItem>
                                            <BreadcrumbLink href={breadcrumb.href} asChild>
                                                <Link
                                                    href={breadcrumb.href}
                                                    className="transition-colors hover:text-foreground/80 text-foreground/60"
                                                >
                                                    {breadcrumb.label}
                                                </Link>
                                            </BreadcrumbLink>
                                        </UIBreadcrumbItem>
                                        <BreadcrumbSeparator />
                                    </span>
                                )
                            }
                        })}
                    </BreadcrumbList>
                </Breadcrumb>
            </div>

            {/* Right Section */}
            <div className="flex items-center gap-2 ml-auto">
                {/* Outdated Receivables Notification */}
                {outdatedReceivables.length > 0 && (
                    <>
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button 
                                    variant="ghost" 
                                    size="sm" 
                                    className="relative hover:bg-red-50 hover:text-red-700"
                                >
                                    <Bell className="h-4 w-4" />
                                    {outdatedReceivables.length > 0 && (
                                        <Badge 
                                            variant="destructive" 
                                            className="absolute -top-1 -right-1 h-5 w-5 rounded-full p-0 flex items-center justify-center text-xs"
                                        >
                                            {outdatedReceivables.length > 99 ? '99+' : outdatedReceivables.length}
                                        </Badge>
                                    )}
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" className="w-80">
                                <DropdownMenuLabel className="flex items-center gap-2">
                                    <AlertTriangle className="h-4 w-4 text-red-500" />
                                    Piutang Jatuh Tempo ({outdatedReceivables.length})
                                </DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                
                                {isLoading ? (
                                    <DropdownMenuItem disabled>
                                        <span className="text-sm text-muted-foreground">Memuat...</span>
                                    </DropdownMenuItem>
                                ) : (
                                    <>
                                        {/* Summary */}
                                        <div className="px-2 py-2">
                                            <div className="bg-red-50 p-2 rounded text-xs">
                                                <p className="font-medium text-red-800">
                                                    Total: <CurrencyFormatter 
                                                        amount={outdatedReceivables.reduce((sum, item) => sum + item.remaining_amount, 0)} 
                                                    />
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <DropdownMenuSeparator />
                                        
                                        {/* List of outdated receivables (max 5 items) */}
                                        <div className="max-h-64 overflow-y-auto">
                                            {outdatedReceivables.slice(0, 5).map((receivable) => {
                                                const daysPastDue = moment().diff(moment(receivable.due_date), 'days')
                                                return (
                                                    <DropdownMenuItem key={receivable.id} className="flex-col items-start p-3 cursor-default" asChild>
                                                        <Link href={`/admin/receivable/${receivable.id}/edit`}>
                                                        <div className="w-full">
                                                            <div className="flex justify-between items-start mb-1">
                                                                <span className="font-medium text-sm truncate">
                                                                    {receivable.purchase_order_number}
                                                                </span>
                                                                <Badge 
                                                                    variant={daysPastDue > 30 ? "destructive" : "secondary"}
                                                                    className="text-xs ml-2 shrink-0"
                                                                >
                                                                    {daysPastDue}h
                                                                </Badge>
                                                            </div>
                                                            <div className="flex justify-between items-center">
                                                                <span className="text-xs text-muted-foreground flex items-center gap-1">
                                                                    <Calendar className="h-3 w-3" />
                                                                    {moment(receivable.due_date).format('DD/MM/YY')}
                                                                </span>
                                                                <span className="text-xs font-medium text-red-600">
                                                                    <CurrencyFormatter amount={receivable.remaining_amount} />
                                                                </span>
                                                            </div>
                                                        </div>
                                                        </Link>
                                                    </DropdownMenuItem>
                                                )
                                            })}
                                        </div>
                                        
                                        {outdatedReceivables.length > 5 && (
                                            <>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem className="text-center text-xs text-muted-foreground cursor-default">
                                                    +{outdatedReceivables.length - 5} piutang lainnya
                                                </DropdownMenuItem>
                                            </>
                                        )}
                                        
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem asChild>
                                            <Link 
                                                href="/admin/receivable" 
                                                className="w-full text-center text-xs font-medium text-blue-600 hover:text-blue-800"
                                            >
                                                Lihat Semua Piutang
                                            </Link>
                                        </DropdownMenuItem>
                                    </>
                                )}
                            </DropdownMenuContent>
                        </DropdownMenu>
                        <Separator orientation="vertical" className="h-4" />
                    </>
                )}

                {/* Optional Clock */}
                {showClock && (
                    <>
                        <Badge variant="outline" className="hidden sm:flex items-center gap-1 px-2 py-1">
                            <Clock className="h-3 w-3" />
                            <span className="text-xs font-mono">
                                {currentTime.toLocaleTimeString("id-ID", {
                                    hour: "2-digit",
                                    minute: "2-digit",
                                    second: "2-digit",
                                })}
                            </span>
                        </Badge>
                        <Separator orientation="vertical" className="h-4" />
                    </>
                )}
                <ThemeToggle />
            </div>
        </header>
    )
}
