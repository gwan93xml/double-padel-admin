"use client";

import React, { useState, useEffect, useRef, Fragment, useCallback } from "react";
import axios from "axios";
import {
    Table,
    TableHeader,
    TableHead,
    TableBody,
    TableRow,
    TableCell,
    TableFooter,
} from "@/Components/ui/table";
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
} from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import {
    FileText,
    FileSpreadsheet,
    ListIcon,
    Filter,
    Search,
} from "lucide-react";

export interface ColumnDef<T> {
    header: string;
    renderCell: (item: T, index: number) => React.ReactNode;
    key: string;
    renderFooter?: (items: T[]) => React.ReactNode;
    align?: "left" | "right";
    width?: string;
    isMerged?: boolean;
    mergeKey?: keyof T | string; // Property path to use for merging
}

export interface DetailPropsInline<T, D> {
    rowKey: (item: T, idx: number) => string;
    getDetails: (item: T) => D[];
    columns: ColumnDef<D>[];
}

export interface ReportListProps<F, T, D = never> {
    title: string;
    description?: string;
    fetchUrl: string;
    exportPdfUrl?: (filters: F) => string;
    exportExcelUrl?: (filters: F) => string;
    initialFilters: F;
    renderFilter: (
        filters: F,
        setFilters: React.Dispatch<React.SetStateAction<F>>,
        onSearch: () => void
    ) => React.ReactNode;
    columns: ColumnDef<T>[];
    detailInline?: DetailPropsInline<T, D>;
    isMerged?: boolean;
}

export function ReportList<F, T, D = never>(props: ReportListProps<F, T, D>) {
    const {
        title,
        description,
        fetchUrl,
        exportPdfUrl,
        exportExcelUrl,
        initialFilters,
        renderFilter,
        columns,
        detailInline,
        isMerged,
    } = props;

    const [filters, setFilters] = useState<F>(initialFilters);
    const [items, setItems] = useState<T[]>([]);
    const [page, setPage] = useState(1);
    const [hasMore, setHasMore] = useState(true);
    const containerRef = useRef<HTMLDivElement>(null);
    const loadingRef = useRef(false);
    const [loading, setLoading] = useState(false)

    // Helper function to check if cell should be empty (same as previous row)
    const shouldHideCell = useCallback((rowIndex: number, colKey: string) => {
        if (!isMerged || rowIndex === 0) return false;

        const column = columns.find(col => col.key === colKey);
        if (!column?.isMerged) return false;

        const currentItem = items[rowIndex];
        const previousItem = items[rowIndex - 1];

        // Get values to compare
        const getValue = (item: T, col: ColumnDef<T>) => {
            if (col.mergeKey) {
                // Handle nested properties like "vendor.name"
                const keys = String(col.mergeKey).split('.');
                let value: any = item;
                for (const key of keys) {
                    value = value?.[key];
                }
                return value;
            }

            // Fallback: try to extract from rendered content
            const rendered = col.renderCell(item, rowIndex);

            // If it's a string or number, use it directly
            if (typeof rendered === 'string' || typeof rendered === 'number') {
                return rendered;
            }

            // If it's a React element, try to extract text content
            if (React.isValidElement(rendered)) {
                const children = rendered.props.children;
                if (typeof children === 'string' || typeof children === 'number') {
                    return children;
                }
                return JSON.stringify(children);
            }

            // Fallback to string representation
            return String(rendered);
        };

        const currentValue = getValue(currentItem, column);
        const previousValue = getValue(previousItem, column);

        return currentValue === previousValue;
    }, [items, columns, isMerged]);

    const loadPage = useCallback(async (pageToLoad = 1, reset = false) => {
        if (loadingRef.current) return;
        if (!reset && !hasMore) return;
        setLoading(true)
        loadingRef.current = true;
        const currentPage = reset ? 1 : pageToLoad;
        try {
            const { data } = await axios.get(fetchUrl, {
                params: { ...filters, page: currentPage },
            });
            const newData: T[] = data.data ?? [];
            setItems(prev => (reset ? newData : [...prev, ...newData]));
            setHasMore(data.next_page_url !== null);
            setPage(data.current_page);
        } catch (e) {
            console.error("Error loading report:", e);
        } finally {
            loadingRef.current = false;
            setLoading(false);

        }
    }, [page, filters, fetchUrl, hasMore]);


    const onScroll = () => {
        if (!containerRef.current || loadingRef.current || !hasMore) return;
        const { scrollTop, scrollHeight, clientHeight } = containerRef.current;
        if (scrollTop + clientHeight >= scrollHeight - 100) {
            loadPage(page + 1);
        }
    };

    const handleSearch = () => {
        loadPage(1, true);
    };

    useEffect(() => {
        loadPage(1, true);
    }, []);

    useEffect(() => {
        const c = containerRef.current;
        if (c) {
            c.addEventListener("scroll", onScroll);
            return () => c.removeEventListener("scroll", onScroll);
        }
    }, [items, loading, hasMore]);

    return (
        <div
            ref={containerRef}
            className="h-screen overflow-y-auto p-4 md:p-6 bg-gradient-to-br "
        >
            {/* Header */}
            <div className="mb-8">
                <div className="flex items-center gap-3 mb-2">
                    <div className="p-2 rounded-lg">
                        <ListIcon className="h-6 w-6" />
                    </div>
                    <div>
                        <h1 className="text-3xl font-bold ">{title}</h1>
                        {description && <p className="">{description}</p>}
                    </div>
                </div>
            </div>

            {/* Filter */}
            <Card className="mb-6 shadow-lg ">
                <CardHeader className="bg-gradient-to-r border-b">
                    <CardTitle className="flex items-center gap-2">
                        <Filter className="h-5 w-5" /> Filter Laporan
                    </CardTitle>
                    <CardDescription className="">
                        Sesuaikan filter untuk menampilkan data
                    </CardDescription>
                </CardHeader>
                <CardContent className="p-6">
                    {renderFilter(filters, setFilters, handleSearch)}
                </CardContent>
            </Card>

            {/* Export Buttons */}
            {items.length > 0 && (
                <div className="mb-4 flex justify-end gap-2">
                    {exportPdfUrl && (
                        <Button
                            variant="outline" size="sm" asChild
                            className=""
                        >
                            <a href={exportPdfUrl(filters)} target="_blank" rel="noreferrer">
                                <FileText className="h-4 w-4 mr-1" /> PDF
                            </a>
                        </Button>
                    )}
                    {exportExcelUrl && (
                        <Button
                            variant="outline" size="sm" asChild
                            className=""
                        >
                            <a href={exportExcelUrl(filters)} target="_blank" rel="noreferrer">
                                <FileSpreadsheet className="h-4 w-4 mr-1" /> Excel
                            </a>
                        </Button>
                    )}
                </div>
            )
            }

            {/* Data Table */}
            <Card className="shadow-lg ">
                <CardHeader className=" border-b">
                    <CardTitle className="text-xl flex items-center gap-2">
                        <ListIcon className="h-5 w-5" /> Detail
                    </CardTitle>
                </CardHeader>
                <CardContent className="p-0">
                    <div className="overflow-x-auto">
                        <Table>
                            {/* Master Header */}
                            <TableHeader>
                                <TableRow className="bg-gradient-to-r ">
                                    {columns.map(col => (
                                        <TableHead
                                            key={`header-${col.key}`}
                                            className={col.align === "right" ? "text-right" : ""}
                                        >
                                            {col.header}
                                        </TableHead>
                                    ))}
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                {items.map((item, idx) => {
                                    const masterKey = detailInline
                                        ? detailInline.rowKey(item, idx)
                                        : `row-${idx}`;

                                    return (
                                        <Fragment key={`master-${idx}-${masterKey}`}>
                                            {/* Master Row */}
                                            <TableRow>
                                                {columns.map(col => {
                                                    const shouldHide = shouldHideCell(idx, col.key);

                                                    return (
                                                        <TableCell
                                                            key={`${masterKey}-c-${col.key}`}
                                                            className={col.align === "right" ? "text-right" : ""}
                                                        >
                                                            {shouldHide ? null : col.renderCell(item, idx)}
                                                        </TableCell>
                                                    );
                                                })}
                                            </TableRow>

                                            {/* Nested Detail Table */}
                                            {detailInline && (
                                                <TableRow>
                                                    <TableCell colSpan={columns.length} className="p-0">
                                                        <div className="pl-8">
                                                            <Table>
                                                                {/* Detail Header */}
                                                                <TableHeader>
                                                                    <TableRow className="bg-gray-100">
                                                                        {detailInline.columns.map(dc => (
                                                                            <TableHead
                                                                                key={`${masterKey}-d-h-${dc.key}`}
                                                                                className={dc.align === "right" ? "text-right" : ""}
                                                                            >
                                                                                {dc.header}
                                                                            </TableHead>
                                                                        ))}
                                                                    </TableRow>
                                                                </TableHeader>
                                                                {/* Detail Body */}
                                                                <TableBody>
                                                                    {detailInline.getDetails(item).map((d, j) => (
                                                                        <TableRow key={`${masterKey}-d-${j}`} className="bg-white">
                                                                            {detailInline.columns.map(dc => (
                                                                                <TableCell
                                                                                    key={`${masterKey}-d-${j}-c-${dc.key}`}
                                                                                    className={`${dc.width ?? ""} ${dc.align === "right" ? "text-right" : ""}`}
                                                                                >
                                                                                    {dc.renderCell(d, j)}
                                                                                </TableCell>
                                                                            ))}
                                                                        </TableRow>
                                                                    ))}
                                                                </TableBody>
                                                            </Table>
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            )}
                                        </Fragment>
                                    );
                                })}
                            </TableBody>

                            {/* Master Footer */}
                            <TableFooter>
                                <TableRow className="bg-gradient-to-r  border-t-2">
                                    {columns.map(col => (
                                        <TableCell
                                            key={`footer-${col.key}`}
                                            className={col.align === "right" ? "text-right font-bold" : "font-bold"}
                                        >
                                            {col.renderFooter ? col.renderFooter(items) : null}
                                        </TableCell>
                                    ))}
                                </TableRow>
                            </TableFooter>
                        </Table>

                        {loading && (
                            <div className="p-4 text-center text-gray-500">Memuat data…</div>
                        )}
                        {!hasMore && !loading && (
                            <div className="p-4 text-center text-gray-400">Tidak ada lagi data.</div>
                        )}
                    </div>
                </CardContent>
            </Card>
        </div >
    );
}
