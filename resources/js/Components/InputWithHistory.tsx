import { useCallback, useEffect, useRef, useState } from "react";
import { Input } from "./ui/input";
import { debounce, set } from "lodash";
import axios from "axios";

interface InputWithHistoryProps {
    value: string;
    onChange: (value: string) => void;
    endpoint: string
    historyColumns: string[];
    placeholder?: string;
    searchColumn?: string;
}
export default function InputWithHistory({
    value,
    onChange,
    endpoint,
    historyColumns,
    placeholder,
    searchColumn = 'invoice_number'
}: InputWithHistoryProps) {
    const [items, setItems] = useState<any[]>([]);
    const [active, setActive] = useState<boolean>(false);
    const fetchHistory = useCallback(debounce(async (value) => {
        if (!active) return;
        try {
            const response = await axios.get(endpoint, {
                headers: {
                    'Content-Type': 'application/json',
                },
                params: {
                    [searchColumn]: value
                }
            });
            if (!active) return;
            setItems(response.data.data);
        } catch (error) {
            setItems([]);
        }
    }, 1000), [endpoint, value, active]);

    useEffect(() => {
        fetchHistory(value);
        return () => {
            fetchHistory.cancel();
        };
    }, [value, fetchHistory]);
    return (
        <>
            <div className="relative">
                <Input
                    type="text"
                    value={value}
                    placeholder={placeholder}
                    onChange={(e) => {
                        onChange(e.target.value);
                    }}
                    onFocus={() => {
                        setActive(true);
                        if (value) {
                            fetchHistory(value);
                        } else {
                            setItems([]);
                        }
                    }}
                    onBlur={() => {
                        setActive(false);
                        setItems([]);
                    }}
                />
                {items.length > 0 && (
                    <ul className="absolute z-10 bg-white dark:bg-black border border-gray-300 dark:border-gray-700 rounded shadow-lg mt-1 w-full max-h-96 overflow-y-scroll">
                        {items.map((item, index) => (
                            <li
                                key={index}
                                className="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-900 cursor-pointer flex gap-x-2"
                            >
                                {historyColumns?.map((column, colIndex) => (
                                    <span key={colIndex} className={`block ${colIndex === 0 ? 'font-bold' : ''}`}>
                                        {item[column]}
                                    </span>
                                ))}
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </>
    )
}