import { useEffect, useState } from "react"
type CashCreditProps = {
    value: string
    onChange: (value: string) => void
}
export default function CashCredit({ value, onChange }: CashCreditProps) {
    const [cashCredit, setCashCredit] = useState(value)
    useEffect(() => {
        setCashCredit(value)
    }, [])
    useEffect(() => {
        onChange(cashCredit)
    }, [cashCredit])
    return (
        <div className="flex border border-gray-200 shadow-sm rounded justify-stretch">
            <div className={`flex-1 py-1 px-2 text-center border-r cursor-pointer  " ${cashCredit == 'Cash' ? 'bg-blue-600 rounded-l text-white' : ''}`}
                onClick={() => {
                    setCashCredit('Cash')
                }}
            >
                CASH
            </div>
            <div className={`flex-1 py-1 px-2 text-center cursor-pointer  ${cashCredit == 'Credit' ? 'bg-blue-600 rounded-r text-white' : ''}`}
                onClick={() => {
                    setCashCredit('Credit')
                }}
            >
                CREDIT
            </div>
        </div>
    )
}