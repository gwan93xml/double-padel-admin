import React, { useState } from "react";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Badge } from "@/Components/ui/badge";
import { X, Plus } from "lucide-react";

interface IpAddressInputProps {
    value: string[];
    onChange: (value: string[]) => void;
    placeholder?: string;
    error?: string;
    disabled?: boolean;
}

export default function IpAddressInput({
    value = [],
    onChange,
    placeholder = "Masukkan IP Address",
    error,
    disabled = false
}: IpAddressInputProps) {
    const [currentInput, setCurrentInput] = useState("");

    // Validate IP address format
    const isValidIpAddress = (ip: string): boolean => {
        const ipRegex = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
        return ipRegex.test(ip.trim());
    };

    // Validate CIDR notation (optional)
    const isValidCidr = (cidr: string): boolean => {
        const cidrRegex = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\/(?:[0-9]|[1-2][0-9]|3[0-2])$/;
        return cidrRegex.test(cidr.trim());
    };

    const isValidInput = (input: string): boolean => {
        const trimmed = input.trim();
        return isValidIpAddress(trimmed) || isValidCidr(trimmed) || trimmed === "*";
    };

    const handleAddIp = () => {
        const trimmedInput = currentInput.trim();

        if (!trimmedInput) return;

        if (!isValidInput(trimmedInput)) {
            // You could add toast notification here
            return;
        }

        if (value.includes(trimmedInput)) {
            // IP already exists
            setCurrentInput("");
            return;
        }

        const newValue = [...value, trimmedInput];
        onChange(newValue);
        setCurrentInput("");
    };

    const handleRemoveIp = (ipToRemove: string) => {
        const newValue = value.filter(ip => ip !== ipToRemove);
        onChange(newValue);
    };

    const handleKeyPress = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleAddIp();
        }
    };

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setCurrentInput(e.target.value);
    };

    return (
        <div className="space-y-3">
            {/* Input field with add button */}
            <div className="flex gap-2">
                <div className="flex-1">
                    <Input
                        type="text"
                        value={currentInput}
                        onChange={handleInputChange}
                        onKeyPress={handleKeyPress}
                        placeholder={placeholder}
                        disabled={disabled}
                        className={error ? "border-destructive" : ""}
                    />
                </div>
                <Button
                    type="button"
                    onClick={handleAddIp}
                    disabled={disabled || !currentInput.trim() || !isValidInput(currentInput)}
                    size="sm"
                    variant="outline"
                >
                    <Plus className="h-4 w-4" />
                </Button>
            </div>

            {/* Display added IP addresses */}
            {value.length > 0 && (
                <div className="space-y-2">
                    <div className="text-sm text-muted-foreground">
                        IP Addresses ({value.length}):
                    </div>
                    <div className="flex flex-wrap gap-2">
                        {value.map((ip, index) => (
                            <Badge
                                key={index}
                                variant="secondary"
                                className="flex items-center gap-1 pr-1"
                            >
                                <span className="font-mono text-xs">{ip}</span>
                                {!disabled && (
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        className="h-4 w-4 p-0 hover:bg-destructive hover:text-destructive-foreground"
                                        onClick={() => handleRemoveIp(ip)}
                                    >
                                        <X className="h-3 w-3" />
                                    </Button>
                                )}
                            </Badge>
                        ))}
                    </div>
                </div>
            )}

            {/* Error message */}
            {error && (
                <div className="text-sm text-destructive">
                    {error}
                </div>
            )}

            {/* Helper text */}
            <div className="text-xs text-muted-foreground">
                Format yang didukung: 192.168.1.1, 192.168.1.0/24, atau * (untuk semua IP)
            </div>
        </div>
    );
}