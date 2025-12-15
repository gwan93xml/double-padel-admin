
import type React from "react"

import { Search } from "lucide-react"
import { Button } from "@/Components/ui/button"
import { Input } from "@/Components/ui/input"
import { useEffect, useState, useRef } from "react"
import axios from "axios"


type SearchCompanyProps = {
    onChange: (company: CompanyType) => void
    value?: CompanyType
}


export default function SearchCompany({ onChange, value }: SearchCompanyProps) {
    const [company, setCompany] = useState<CompanyType>({
        id: "",
        code: "",
        name: "",
    })
    const [isLoading, setIsLoading] = useState(false)
    const [error, setError] = useState("")

    // Use a ref to track if the update is coming from internal state change
    const isInternalUpdate = useRef(false)
    // Store the previous value to compare
    const prevValueRef = useRef<CompanyType | undefined>(value)

    // Initialize from props - only when value changes from external source

    useEffect(() => {
        if (value) {
            setCompany({
                id: value.id || "",
                code: value.code || "",
                name: value.name || "",
            })
        }
    }, [])
    useEffect(() => {
        onChange(company)
    }, [company])

    const handleCodeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setCompany({ ...company, code: e.target.value })
        handleSearch(e.target.value)
        setError("")
    }

    const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setCompany({ ...company, name: e.target.value })
        handleSearch(e.target.value)
        setError("")
    }

    const searchByCode = async (code: string) => {
        if (!code.trim()) return

        setIsLoading(true)
        setError("")

        try {
            const { data } = await axios.get(`/admin/company/search-by-code?search=${code}`)

            if (data.data) {
                // Set the flag before updating state
                isInternalUpdate.current = true
                setCompany({
                    id: data.data.id,
                    code: data.data.code,
                    name: data.data.name,
                })
            } else {
                setError("Item not found")
            }
        } catch (err) {
            setError("Failed to search company")
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
            const { data } = await axios.get(`/admin/company/search-by-name?search=${name}`)

            if (data.data) {
                // Set the flag before updating state
                isInternalUpdate.current = true
                setCompany({
                    id: data.data.id,
                    code: data.data.code,
                    name: data.data.name,
                })
            } else {
                setError("Item not found")
            }
        } catch (err) {
            setError("Failed to search company")
            console.error(err)
        } finally {
            setIsLoading(false)
        }
    }

    const handleCodeKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === "Enter") {
            searchByCode(company.code!)
        }
    }

    const handleNameKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === "Enter") {
            searchByName(company.name!)
        }
    }

    const handleSearchButtonClick = () => {
        if (company.code) {
            searchByCode(company.code)
        } else if (company.name) {
            searchByName(company.name)
        }
    }

    const [list, setList] = useState<CompanyType[]>([])

    const handleSearch = (query: string) => {
        if (query.length < 2) {
            setList([])
            return
        }

        axios.get(`/admin/company/list?search=${query}`)
            .then((response) => {
                setList(response.data.data)
            })
            .catch((error) => {
                console.error("Error fetching company:", error)
            })
    }

    return (
        <div className="space-y-2 ">
            <div className="relative">
                <div className="flex">
                    <div className="w-1/4">
                        <Input
                            className="rounded-r-none"
                            placeholder="Kode Perusahaan"
                            value={company.code}
                            onChange={handleCodeChange}
                            onKeyDown={handleCodeKeyDown}
                            disabled={isLoading}
                        />
                    </div>
                    <div>
                        <Button className="rounded-none" onClick={handleSearchButtonClick} disabled={isLoading}>
                            <Search className="h-4 w-4" />
                        </Button>
                    </div>
                    <div className="w-3/4">
                        <Input
                            className="rounded-l-none"
                            placeholder="Nama Perusahaan"
                            value={company.name}
                            onChange={handleNameChange}
                            onKeyDown={handleNameKeyDown}
                            disabled={isLoading}
                        />
                    </div>
                </div>


                <div className={`absolute bg-blue-300 w-full p-2  ${list.length > 0 ? "block z-10 shadow-sm rounded" : "hidden"}`}>
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
                                        setCompany({
                                            id: item.id,
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

                {company.id && !isLoading && !error && (
                    <div className="text-sm text-muted-foreground">
                        Company found: {company.name}
                    </div>
                )}
            </div>
        </div>
    )
}

