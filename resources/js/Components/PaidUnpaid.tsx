import { useEffect, useState } from "react"
type PaidUnpaidProps = {
    value: string
    onChange: (value: string) => void
}
export default function PaidUnpaid({ value, onChange }: PaidUnpaidProps) {
    const [paidUnpaid, setPaidUnpaid] = useState(value as string)
    useEffect(() => {
        setPaidUnpaid(value)
    }, [])
    useEffect(() => {
        onChange(paidUnpaid)
    }, [paidUnpaid])
    return (
        <div className="flex border border-gray-200 shadow-sm rounded justify-stretch">
            <div className={`flex-1 p-2 text-center border-r cursor-pointer font-bold " ${paidUnpaid == 'paid' ? 'bg-blue-600 rounded-l text-white' : ''}`}
                onClick={() => {
                    setPaidUnpaid('paid')
                }}
            >
                Dibayar
            </div>
            <div className={`flex-1 p-2 text-center cursor-pointer font-bold ${paidUnpaid != 'paid' ? 'bg-blue-600 rounded-r text-white' : ''}`}
                onClick={() => {
                    setPaidUnpaid('unpaid')
                }}
            >
                Belum Dibayar
            </div>
        </div>
    )
}