import { useEffect, useState } from "react"
type IsStockProps = {
    value: boolean
    onChange: (value: boolean) => void
}
export default function IsStock({ value, onChange }: IsStockProps) {
    console.log(value)
    const [isStock, setIsStock] = useState(value)
    useEffect(() => {
        setIsStock(value)
    }, [])
    useEffect(() => {
        onChange(isStock)
    }, [isStock])
    return (
        <div className="flex border border-gray-200 shadow-sm rounded justify-stretch">
            <div className={`flex-1 p-2 text-center border-r cursor-pointer font-bold " ${isStock ? 'bg-blue-600 rounded-l text-white' : ''}`}
                onClick={() => {
                    setIsStock(true)
                }}
            >
                STOCK
            </div>
            <div className={`flex-1 p-2 text-center cursor-pointer font-bold ${!isStock ? 'bg-blue-600 rounded-r text-white' : ''}`}
                onClick={() => {
                    setIsStock(false)
                }}
            >
                NON STOCK
            </div>
        </div>
    )
}