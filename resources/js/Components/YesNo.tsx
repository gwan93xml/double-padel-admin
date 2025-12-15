import { useEffect, useState } from "react"
type YesNoProps = {
    value: boolean
    onChange: (value: boolean) => void
}
export default function YesNo({ value, onChange }: YesNoProps) {
    const [yesNo, setYesNo] = useState(value as boolean)
    useEffect(() => {
        setYesNo(value)
    }, [])
    useEffect(() => {
        onChange(yesNo)
    }, [yesNo])
    return (
        <div className="flex border border-gray-200 shadow-sm rounded justify-stretch">
            <div className={`flex-1 p-2 text-center border-r cursor-pointer font-bold " ${yesNo ? 'bg-blue-600 rounded-l text-white' : ''}`}
                onClick={() => {
                    setYesNo(true)
                }}
            >
                YA
            </div>
            <div className={`flex-1 p-2 text-center cursor-pointer font-bold ${!yesNo ? 'bg-blue-600 rounded-r text-white' : ''}`}
                onClick={() => {
                    setYesNo(false)
                }}
            >
                TIDAK
            </div>
        </div>
    )
}