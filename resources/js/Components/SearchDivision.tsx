
import type React from "react"

import { Search, X } from "lucide-react"
import { Button } from "@/Components/ui/button"
import { Input } from "@/Components/ui/input"
import { useEffect, useState, useRef } from "react"
import axios from "axios"


type SearchDivisionProps = {
    onChange: (division: DivisionType) => void
    value?: DivisionType
    clearable?: boolean
}


export default function SearchDivision({ onChange, value, clearable }: SearchDivisionProps) {
    const [division, setDivision] = useState<DivisionType>({
        id: 0,
        code: "",
        name: "",
    })
    const [isLoading, setIsLoading] = useState(false)
    const [error, setError] = useState("")

    // Use a ref to track if the update is coming from internal state change
    const isInternalUpdate = useRef(false)
    // Store the previous value to compare
    const prevValueRef = useRef<DivisionType | undefined>(value)

    // Initialize from props - only when value changes from external source

    useEffect(() => {
        if (value) {
            setDivision({
                id: value.id || 0,
                code: value.code || "",
                name: value.name || "",
            })
        }
    }, [])
    // useEffect(() => {
    //     onChange(division)
    // }, [division])

    useEffect(() => {
        // Only update if the value is different from the previous one
        if (value && (prevValueRef.current?.id !== value.id || prevValueRef.current?.code !== value.code || prevValueRef.current?.name !== value.name)) {
            setDivision({
                id: value.id || 0,
                code: value.code || "",
                name: value.name || "",
            })
            prevValueRef.current = value
        }
    }, [value])

    const handleCodeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setDivision({ ...division, code: e.target.value })
        handleSearch(e.target.value)
        setError("")
    }

    const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setDivision({ ...division, name: e.target.value })
        handleSearch(e.target.value)
        setError("")
    }

    const searchByCode = async (code: string) => {
        if (!code.trim()) return

        setIsLoading(true)
        setError("")

        try {
            const { data } = await axios.get(`/admin/division/search-by-code?search=${code}`)

            if (data.data) {
                // Set the flag before updating state
                isInternalUpdate.current = true
                setDivision({
                    id: data.data.id,
                    code: data.data.code,
                    name: data.data.name,
                })
                onChange({
                    id: data.data.id,
                    code: data.data.code,
                    name: data.data.name,
                })
            } else {
                setError("Item not found")
            }
        } catch (err) {
            setError("Failed to search division")
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
            const { data } = await axios.get(`/admin/division/search-by-name?search=${name}`)

            if (data.data) {
                // Set the flag before updating state
                isInternalUpdate.current = true
                setDivision({
                    id: data.data.id,
                    code: data.data.code,
                    name: data.data.name,
                })
                onChange({
                    id: data.data.id,
                    code: data.data.code,
                    name: data.data.name,
                })
            } else {
                setError("Item not found")
            }
        } catch (err) {
            setError("Failed to search division")
            console.error(err)
        } finally {
            setIsLoading(false)
        }
    }

    const handleCodeKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === "Enter") {
            searchByCode(division.code!)
        }
    }

    const handleNameKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === "Enter") {
            searchByName(division.name!)
        }
    }

    const handleSearchButtonClick = () => {
        if (division.code) {
            searchByCode(division.code)
        } else if (division.name) {
            searchByName(division.name)
        }
    }



    const [list, setList] = useState<DivisionType[]>([])

    const handleSearch = (query: string) => {
        if (query.length < 2) {
            setList([])
            return
        }

        axios.get(`/admin/division/browse?search=${query}`)
            .then((response) => {
                setList(response.data.data)
            })
            .catch((error) => {
                console.error("Error fetching division:", error)
            })
    }

    function handleClear() {
        setDivision({
            id: '',
            code: "",
            name: "",
        })
        onChange({
            id: '',
            code: "",
            name: "",
        })
        setList([])
        setError("")
    }

    return (
        <div className="space-y-2 ">
            <div className="relative">

                <div className="flex">
                    <div className="w-1/4">
                        <Input
                            className="rounded-r-none "
                            placeholder="Kode Divisi"
                            value={division.code}
                            onChange={handleCodeChange}
                            onKeyDown={handleCodeKeyDown}
                            disabled={isLoading}
                        />
                    </div>
                    <div>
                        <Button
                            variant={"ghost"}
                            className="rounded-none border"
                            onClick={handleSearchButtonClick} disabled={isLoading}>
                            <Search className="h-4 w-4 " />
                        </Button>
                    </div>
                    <div className="w-3/4">
                        <Input
                            className="rounded-l-none "
                            placeholder="Nama Divisi"
                            value={division.name}
                            onChange={handleNameChange}
                            onKeyDown={handleNameKeyDown}
                            disabled={isLoading}
                        />
                    </div>
                    {clearable && (
                        <Button
                            onClick={handleClear}
                            variant={"outline"}
                        >
                            <X className="h-4 w-4" />
                        </Button>
                    )}
                </div>

                <div className={`absolute bg-blue-300 w-full p-2  ${list.length > 0 ? "block z-10 rounded shadow-sm" : "hidden"}`}>
                    <table className="w-full">
                        <thead>
                            <tr>
                                <th className="text-left">Kode</th>
                                <th className="text-left">Nama</th>
                            </tr>
                        </thead>
                        <tbody>
                            {list.map((item) => (
                                <tr
                                    key={item.id}
                                    className="cursor-pointer hover:bg-blue-200"
                                    onClick={() => {
                                        setDivision({
                                            id: item.id!,
                                            code: item.code,
                                            name: item.name,
                                        })
                                        onChange({
                                            id: item.id!,
                                            code: item.code,
                                            name: item.name,
                                        })
                                        setList([])
                                    }}
                                >
                                    <td>{item.code}</td>
                                    <td>{item.name}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {isLoading && <div className="text-sm text-muted-foreground">Searching...</div>}

                {error && <div className="text-sm text-destructive">{error}</div>}

                {/* {division.id && !isLoading && !error && (
                <div className="text-sm text-muted-foreground">
                    Division found: {division.name}
                </div>
            )} */}
            </div>
        </div>
    )
}

