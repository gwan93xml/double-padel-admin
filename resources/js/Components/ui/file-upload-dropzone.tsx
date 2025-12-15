"use client"
import { useCallback, useState, useEffect } from "react"
import { useDropzone } from "react-dropzone"
import { UploadCloud, FileIcon, X, Video, ImageIcon, CheckCircle, AlertCircle, Download, Eye } from "lucide-react"
import { cn } from "@/lib/utils"
import { Button } from "@/Components/ui/button"
import { Badge } from "@/Components/ui/badge"
import { Progress } from "@/Components/ui/progress"

interface FileWithPreview extends File {
  preview: string
}

interface FileUploadDropzoneProps {
  initialFile?: string | File
  onChange?: (file: FileWithPreview | null) => void
  fileType?: "image" | "video" | "all"
  maxSize?: number // in MB
  className?: string
}

export function FileUploadDropzone({
  initialFile,
  onChange,
  fileType = "all",
  maxSize = 10,
  className,
}: FileUploadDropzoneProps) {
  const [file, setFile] = useState<FileWithPreview | null>(null)
  const [error, setError] = useState<string | null>(null)
  const [isLoading, setIsLoading] = useState(false)
  const [uploadProgress, setUploadProgress] = useState(0)
  const [previewMode, setPreviewMode] = useState(false)

  useEffect(() => {
    if (initialFile && typeof initialFile === "string") {
      setIsLoading(true)
      fetch(`/storage/${initialFile}`)
        .then((response) => {
          if (!response.ok) {
            throw new Error("Network response was not ok")
          }
          return response.blob()
        })
        .then((blob) => {
          const file = new File([blob], `/storage/${initialFile}`.split("/").pop() || "file", { type: blob.type })
          const fileWithPreview = Object.assign(file, { preview: `/storage/${initialFile}` }) as FileWithPreview
          setFile(fileWithPreview)
          onChange?.(fileWithPreview)
          setIsLoading(false)
        })
        .catch((error) => {
          console.error("Error fetching initial file:", error)
          setError("Failed to load initial file. Please try uploading manually.")
          setIsLoading(false)
        })
    }
  }, [initialFile, onChange])

  const onDrop = useCallback(
    (acceptedFiles: File[], rejectedFiles: any[]) => {
      if (rejectedFiles.length > 0) {
        const rejection = rejectedFiles[0]
        if (rejection.errors[0]?.code === "file-too-large") {
          setError(`File terlalu besar. Maksimal ${maxSize}MB`)
        } else if (rejection.errors[0]?.code === "file-invalid-type") {
          setError(
            `Tipe file tidak didukung. Hanya ${fileType === "image" ? "gambar" : fileType === "video" ? "video" : "file"} yang diperbolehkan.`,
          )
        } else {
          setError("File tidak valid")
        }
        return
      }

      if (acceptedFiles.length) {
        setIsLoading(true)
        setUploadProgress(0)

        // Simulate upload progress
        const progressInterval = setInterval(() => {
          setUploadProgress((prev) => {
            if (prev >= 100) {
              clearInterval(progressInterval)
              setIsLoading(false)
              return 100
            }
            return prev + 10
          })
        }, 100)

        const newFile = Object.assign(acceptedFiles[0], {
          preview: URL.createObjectURL(acceptedFiles[0]),
        }) as FileWithPreview

        setFile(newFile)
        onChange?.(newFile)
        setError(null)
      }
    },
    [onChange, maxSize, fileType],
  )

  const { getRootProps, getInputProps, isDragActive, isDragReject } = useDropzone({
    onDrop,
    accept:
      fileType === "image"
        ? { "image/*": [".png", ".jpg", ".jpeg", ".gif", ".webp"] }
        : fileType === "video"
          ? { "video/*": [".mp4", ".avi", ".mov", ".wmv", ".flv"] }
          : undefined,
    multiple: false,
    maxSize: maxSize * 1024 * 1024, // Convert MB to bytes
  })

  const removeFile = useCallback(() => {
    setFile(null)
    onChange?.(null)
    setError(null)
    setUploadProgress(0)
  }, [onChange])

  const formatFileSize = (bytes: number) => {
    if (bytes === 0) return "0 Bytes"
    const k = 1024
    const sizes = ["Bytes", "KB", "MB", "GB"]
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Number.parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i]
  }

  const getFileTypeIcon = (file: FileWithPreview) => {
    if (file.type.startsWith("image/")) {
      return <ImageIcon className="w-5 h-5 text-blue-500 dark:text-blue-400" />
    } else if (file.type.startsWith("video/")) {
      return <Video className="w-5 h-5 text-purple-500 dark:text-purple-400" />
    } else {
      return <FileIcon className="w-5 h-5 text-gray-500 dark:text-gray-400" />
    }
  }

  const getAcceptedFormats = () => {
    switch (fileType) {
      case "image":
        return "PNG, JPG, JPEG, GIF, WEBP"
      case "video":
        return "MP4, AVI, MOV, WMV, FLV"
      default:
        return "Semua format file"
    }
  }

  useEffect(() => {
    return () => {
      if (file) {
        URL.revokeObjectURL(file.preview)
      }
    }
  }, [file])

  return (
    <div className={cn("w-full mx-auto space-y-4", className)}>
      <div
        {...getRootProps()}
        className={cn(
          "relative flex flex-col items-center justify-center w-full min-h-64 border-2 border-dashed rounded-xl cursor-pointer overflow-hidden transition-all duration-300 ease-in-out",
          // Light mode states
          isDragActive && !isDragReject ? "border-blue-400 bg-blue-50 scale-105" : "",
          isDragReject ? "border-red-400 bg-red-50" : "",
          !isDragActive && !isDragReject ? "border-gray-300 hover:border-gray-400 hover:bg-gray-50" : "",
          file ? "border-green-400 bg-green-50" : "",
          // Dark mode states
          "dark:border-gray-600",
          isDragActive && !isDragReject ? "dark:border-blue-500 dark:bg-black" : "",
          isDragReject ? "dark:border-red-500 dark:bg-black" : "",
          !isDragActive && !isDragReject ? "dark:hover:border-gray-500 dark:hover:bg-gray-800/50" : "",
          file ? "dark:border-green-500 dark:bg-green-950/50" : "",
          "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-blue-400 dark:focus-visible:ring-offset-gray-900",
        )}
      >
        <input {...getInputProps()} />

        {/* Loading State */}
        {isLoading && (
          <div className="absolute inset-0 bg-white dark:bg-black bg-opacity-90 dark:bg-opacity-90 flex flex-col items-center justify-center z-10">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 dark:border-blue-400 mb-4"></div>
            <p className="text-sm text-gray-600 dark:text-gray-300 mb-2">Memproses file...</p>
            <div className="w-48">
              <Progress value={uploadProgress} className="h-2" />
            </div>
          </div>
        )}

        {!file ? (
          <div className="text-center p-8">
            <div className="mb-4">
              <UploadCloud
                className={cn(
                  "w-16 h-16 mx-auto transition-colors duration-300",
                  isDragActive ? "text-blue-500 dark:text-blue-400" : "text-gray-400 dark:text-gray-500",
                )}
              />
            </div>

            {isDragActive ? (
              <div className="space-y-2">
                <p className="text-lg font-medium text-blue-600 dark:text-blue-400">Lepaskan file di sini</p>
                <p className="text-sm text-blue-500 dark:text-blue-400">File akan diupload secara otomatis</p>
              </div>
            ) : (
              <div className="space-y-3">
                <div>
                  <p className="text-lg font-medium text-gray-700 dark:text-gray-200 mb-1">Drag & drop file di sini</p>
                  <p className="text-sm text-gray-500 dark:text-gray-400">
                    atau <span className="text-blue-500 dark:text-blue-400 font-medium">klik untuk memilih file</span>
                  </p>
                </div>

                <div className="flex flex-col items-center space-y-2 pt-2">
                  <Badge
                    variant="secondary"
                    className="text-xs bg-gray-100 dark:bg-black text-gray-700 dark:text-gray-300"
                  >
                    Format: {getAcceptedFormats()}
                  </Badge>
                  <Badge
                    variant="outline"
                    className="text-xs border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400"
                  >
                    Maksimal: {maxSize}MB
                  </Badge>
                </div>
              </div>
            )}

            {error && (
              <div className="mt-4 p-3 bg-red-50 dark:bg-black border border-red-200 dark:border-red-800 rounded-lg">
                <div className="flex items-center space-x-2">
                  <AlertCircle className="w-4 h-4 text-red-500 dark:text-red-400" />
                  <p className="text-sm text-red-600 dark:text-red-400">{error}</p>
                </div>
              </div>
            )}
          </div>
        ) : (
          <div className="w-full h-full p-6">
            {/* File Preview */}
            <div className="bg-white dark:bg-black rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 space-y-4">
              {/* File Header */}
              <div className="flex items-center justify-between">
                <div className="flex items-center space-x-3">
                  {getFileTypeIcon(file)}
                  <div>
                    <p className="font-medium text-gray-900 dark:text-gray-100 truncate max-w-[200px]">{file.name}</p>
                    <p className="text-sm text-gray-500 dark:text-gray-400">{formatFileSize(file.size)}</p>
                  </div>
                </div>

                <div className="flex items-center space-x-2">
                  <Badge
                    variant="default"
                    className="bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300 border-green-200 dark:border-green-800"
                  >
                    <CheckCircle className="w-3 h-3 mr-1" />
                    Uploaded
                  </Badge>

                  <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    onClick={(e) => {
                      e.stopPropagation()
                      removeFile()
                    }}
                    className="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-black"
                  >
                    <X className="w-4 h-4" />
                  </Button>
                </div>
              </div>

              {/* File Preview */}
              {file.type.startsWith("image/") && (
                <div className="relative">
                  <img
                    src={file.preview || "/placeholder.svg"}
                    alt={file.name}
                    className="w-full h-48 object-cover rounded-lg border border-gray-200 dark:border-gray-600"
                  />
                  <div className="absolute top-2 right-2 flex space-x-1">
                    <Button
                      type="button"
                      variant="secondary"
                      size="sm"
                      onClick={(e) => {
                        e.stopPropagation()
                        setPreviewMode(true)
                      }}
                      className="bg-white dark:bg-black bg-opacity-90 dark:bg-opacity-90 hover:bg-opacity-100 dark:hover:bg-opacity-100 border border-gray-200 dark:border-gray-600"
                    >
                      <Eye className="w-4 h-4" />
                    </Button>
                  </div>
                </div>
              )}

              {file.type.startsWith("video/") && (
                <div className="relative">
                  <video
                    src={file.preview}
                    className="w-full h-48 object-cover rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700"
                    controls
                  />
                </div>
              )}

              {/* File Actions */}
              <div className="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700">
                <div className="text-xs text-gray-500 dark:text-gray-400">Uploaded successfully</div>
                <div className="flex space-x-2">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={(e) => {
                      e.stopPropagation()
                      // Handle download
                    }}
                    className="border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                  >
                    <Download className="w-4 h-4 mr-1" />
                    Download
                  </Button>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Preview Modal */}
      {previewMode && file && file.type.startsWith("image/") && (
        <div className="fixed inset-0 bg-black bg-opacity-75 dark:bg-black dark:bg-opacity-85 flex items-center justify-center z-50">
          <div className="relative max-w-4xl max-h-4xl p-4">
            <img
              src={file.preview || "/placeholder.svg"}
              alt={file.name}
              className="max-w-full max-h-full object-contain rounded-lg"
            />
            <Button
              variant="secondary"
              size="sm"
              onClick={() => setPreviewMode(false)}
              className="absolute top-4 right-4 bg-white dark:bg-black text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700"
            >
              <X className="w-4 h-4" />
            </Button>
          </div>
        </div>
      )}
    </div>
  )
}
