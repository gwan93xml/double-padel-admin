import { useState, useRef, useEffect } from "react";
import { Upload, X, Check } from "lucide-react";
import { Button } from "@/Components/ui/button";
import axios from "axios";

interface ImageUploaderProps {
    onSuccess?: (url: string, filename: string) => void;
    onError?: (error: string) => void;
    className?: string;
    label?: string;
    initialValue?: string | null;
    maxHeight?: number;
}

export default function ImageUploader({
    onSuccess,
    onError,
    className = "",
    label = "Upload Gambar",
    initialValue = null,
    maxHeight,
}: ImageUploaderProps) {
    const [preview, setPreview] = useState<string | null>(initialValue);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [success, setSuccess] = useState(false);
    const [isInitial, setIsInitial] = useState(!!initialValue);
    const fileInputRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        if (initialValue) {
            setPreview(initialValue);
            setIsInitial(true);
        }
    }, [initialValue]);

    const handleFileSelect = async (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) return;

        // Reset states
        setError(null);
        setSuccess(false);
        setIsInitial(false);

        // Show preview
        const reader = new FileReader();
        reader.onload = (event) => {
            setPreview(event.target?.result as string);
        };
        reader.readAsDataURL(file);

        // Upload file
        await uploadFile(file);
    };

    const uploadFile = async (file: File) => {
        setIsLoading(true);

        try {
            const formData = new FormData();
            formData.append("image", file);

            const response = await axios.post("/upload/image", formData, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            setSuccess(true);
            onSuccess?.(response.data.data.url, response.data.data.filename);

            // Reset after 2 seconds
            setTimeout(() => {
                setSuccess(false);
                setPreview(response.data.data.url);
                setIsInitial(false);
                if (fileInputRef.current) {
                    fileInputRef.current.value = "";
                }
            }, 2000);
        } catch (err) {
            const errorMessage =
                axios.isAxiosError(err)
                    ? err.response?.data?.message || "Gagal mengunggah gambar"
                    : err instanceof Error
                        ? err.message
                        : "Gagal mengunggah gambar";
            setError(errorMessage);
            onError?.(errorMessage);
            setPreview(null);
        } finally {
            setIsLoading(false);
        }
    };

    const handleDragOver = (e: React.DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        e.currentTarget.classList.add("bg-blue-50", "border-blue-300");
    };

    const handleDragLeave = (e: React.DragEvent<HTMLDivElement>) => {
        e.currentTarget.classList.remove("bg-blue-50", "border-blue-300");
    };

    const handleDrop = async (e: React.DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        e.currentTarget.classList.remove("bg-blue-50", "border-blue-300");

        const file = e.dataTransfer.files?.[0];
        if (!file) return;

        // Reset states
        setError(null);
        setSuccess(false);
        setIsInitial(false);

        // Show preview
        const reader = new FileReader();
        reader.onload = (event) => {
            setPreview(event.target?.result as string);
        };
        reader.readAsDataURL(file);

        // Upload file
        await uploadFile(file);
    };

    return (
        <div className={className}>
            {label && <label className="block text-sm font-semibold mb-2">{label}</label>}

            <input
                ref={fileInputRef}
                type="file"
                accept="image/*"
                onChange={handleFileSelect}
                className="hidden"
            />

            {!preview ? (
                <div
                    onClick={() => fileInputRef.current?.click()}
                    onDragOver={handleDragOver}
                    onDragLeave={handleDragLeave}
                    onDrop={handleDrop}
                    className="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer transition-colors hover:border-blue-400 hover:bg-blue-50"
                >
                    <Upload className="w-12 h-12 text-gray-400 mx-auto mb-3" />
                    <p className="text-sm font-semibold text-gray-700 mb-1">
                        Drag gambar di sini atau klik untuk memilih
                    </p>
                    <p className="text-xs text-gray-500">
                        Format: JPEG, PNG, JPG, GIF, WebP (Max 5MB)
                    </p>
                </div>
            ) : (
                <div className="space-y-3">
                    <div className="relative w-full aspect-video rounded-lg overflow-hidden border border-gray-200 bg-gray-100"
                        style={maxHeight ? { maxHeight: `${maxHeight}px` } : {}}
                    >
                        <img
                            src={preview}
                            alt="Preview"
                            className="h-full object-center"
                            style={maxHeight ? { maxHeight: `${maxHeight}px` } : {}}
                        />
                    </div>

                    {isLoading && (
                        <div className="flex items-center justify-center gap-2 text-blue-600">
                            <div className="w-4 h-4 border-2 border-blue-200 border-t-blue-600 rounded-full animate-spin" />
                            <span className="text-sm font-medium">Mengunggah...</span>
                        </div>
                    )}

                    {error && (
                        <div className="flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg p-3">
                            <X className="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" />
                            <div className="flex-1">
                                <p className="text-sm font-medium text-red-800">Upload Gagal</p>
                                <p className="text-xs text-red-700 mt-1">{error}</p>
                            </div>
                        </div>
                    )}

                    {success && (
                        <div className="flex items-center gap-2 bg-green-50 border border-green-200 rounded-lg p-3">
                            <Check className="w-5 h-5 text-green-600" />
                            <span className="text-sm font-medium text-green-800">
                                Gambar berhasil diunggah
                            </span>
                        </div>
                    )}

                    {!isLoading && (
                        <div className="flex gap-2">
                            <Button
                                variant="outline"
                                onClick={() => {
                                    setPreview(initialValue || null);
                                    setIsInitial(!!initialValue);
                                    setError(null);
                                    setSuccess(false);
                                    if (fileInputRef.current) {
                                        fileInputRef.current.value = "";
                                    }
                                }}
                                className="flex-1"
                            >
                                {isInitial ? "Batalkan Perubahan" : "Batal"}
                            </Button>
                            <Button
                                onClick={() => fileInputRef.current?.click()}
                                className="flex-1"
                            >
                                Pilih Gambar Lain
                            </Button>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
