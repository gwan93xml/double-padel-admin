import React, { useState, useEffect } from 'react'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/Components/ui/dialog"
import { Button } from "@/Components/ui/button"
import { Badge } from "@/Components/ui/badge"
import { ScrollArea } from "@/Components/ui/scroll-area"
import { Separator } from "@/Components/ui/separator"
import {
  History,
  User,
  Calendar,
  Clock,
  FileText,
  Eye,
  AlertCircle,
  CheckCircle,
  XCircle,
  Edit,
  Trash2,
  Plus,
  ChevronDown,
  ChevronRight,
} from "lucide-react"
import moment from "moment"
import axios from "axios"

interface AuditLog {
  id: number
  user_type: string
  user_id: number
  event: 'created' | 'updated' | 'deleted' | 'restored'
  auditable_type: string
  auditable_id: number
  old_values: Record<string, any>
  new_values: Record<string, any>
  url: string
  ip_address: string
  user_agent: string
  tags: string | null
  delete_reason: string | null
  created_at: string
  updated_at: string
  user?: {
    id: number
    name: string
    email: string
  }
}

interface AuditDialogProps {
  auditableType: string
  auditableId: number
  trigger?: React.ReactNode
  title?: string
  description?: string
}

const AuditDialog: React.FC<AuditDialogProps> = ({
  auditableType,
  auditableId,
  trigger,
  title = "Riwayat Perubahan",
  description = "Lihat semua perubahan yang telah dilakukan pada data ini"
}) => {
  const [open, setOpen] = useState(false)
  const [auditLogs, setAuditLogs] = useState<AuditLog[]>([])
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [expandedItems, setExpandedItems] = useState<Set<number>>(new Set())

  const fetchAuditLogs = async () => {
    setLoading(true)
    setError(null)
    try {
            const response = await axios.get('/admin/audits', {
        params: {
          auditable_type: auditableType,
          auditable_id: auditableId,
        }
      })
      setAuditLogs(response.data.data || [])
    } catch (err: any) {
      setError(err.response?.data?.message || 'Gagal memuat audit logs')
      console.error('Error fetching audit logs:', err)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    if (open) {
      fetchAuditLogs()
      setExpandedItems(new Set()) // Reset expanded items when dialog opens
    }
  }, [open, auditableType, auditableId])

  const getEventIcon = (event: string) => {
    switch (event) {
      case 'created':
        return <Plus className="h-4 w-4 text-green-600" />
      case 'updated':
        return <Edit className="h-4 w-4 text-blue-600" />
      case 'deleted':
        return <Trash2 className="h-4 w-4 text-red-600" />
      case 'restored':
        return <CheckCircle className="h-4 w-4 text-green-600" />
      default:
        return <FileText className="h-4 w-4 text-gray-600" />
    }
  }

  const getEventColor = (event: string) => {
    switch (event) {
      case 'created':
        return 'bg-green-50 text-green-700 border-green-200'
      case 'updated':
        return 'bg-blue-50 text-blue-700 border-blue-200'
      case 'deleted':
        return 'bg-red-50 text-red-700 border-red-200'
      case 'restored':
        return 'bg-green-50 text-green-700 border-green-200'
      default:
        return 'bg-gray-50 text-gray-700 border-gray-200'
    }
  }

  const formatFieldName = (field: string) => {
    return field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
  }

  const formatValue = (value: any) => {
    if (value === null || value === undefined) return '-'
    if (typeof value === 'boolean') return value ? 'Ya' : 'Tidak'
    if (typeof value === 'object') return JSON.stringify(value, null, 2)
    if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}/.test(value)) {
      return moment(value).format('DD/MM/YYYY HH:mm')
    }
    return String(value)
  }

  const toggleAllExpanded = () => {
    if (expandedItems.size === auditLogs.length) {
      // All expanded, collapse all
      setExpandedItems(new Set())
    } else {
      // Not all expanded, expand all
      setExpandedItems(new Set(auditLogs.map(log => log.id)))
    }
  }

  const getChangesCount = (oldValues: Record<string, any>, newValues: Record<string, any>) => {
    const allFields = new Set([...Object.keys(oldValues || {}), ...Object.keys(newValues || {})])
    let changesCount = 0

    allFields.forEach(field => {
      const oldValue = oldValues?.[field]
      const newValue = newValues?.[field]
      if (JSON.stringify(oldValue) !== JSON.stringify(newValue)) {
        changesCount++
      }
    })

    return changesCount
  }

  const toggleExpanded = (auditId: number) => {
    setExpandedItems(prev => {
      const newSet = new Set(prev)
      if (newSet.has(auditId)) {
        newSet.delete(auditId)
      } else {
        newSet.add(auditId)
      }
      return newSet
    })
  }

  const renderChanges = (oldValues: Record<string, any>, newValues: Record<string, any>) => {
    const allFields = new Set([...Object.keys(oldValues || {}), ...Object.keys(newValues || {})])

    return Array.from(allFields).map(field => {
      const oldValue = oldValues?.[field]
      const newValue = newValues?.[field]
      const hasChanged = JSON.stringify(oldValue) !== JSON.stringify(newValue)

      if (!hasChanged) return null

      return (
        <div key={field} className="mb-3 p-3 bg-gray-50 rounded-lg">
          <div className="font-medium text-sm text-gray-700 mb-2">
            {formatFieldName(field)}
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <div className="text-xs text-red-600 font-medium mb-1">Sebelum:</div>
              <div className="text-sm text-gray-600 bg-red-50 p-2 rounded border">
                {formatValue(oldValue)}
              </div>
            </div>
            <div>
              <div className="text-xs text-green-600 font-medium mb-1">Sesudah:</div>
              <div className="text-sm text-gray-600 bg-green-50 p-2 rounded border">
                {formatValue(newValue)}
              </div>
            </div>
          </div>
        </div>
      )
    }).filter(Boolean)
  }

  const defaultTrigger = (
    <Button variant="outline" size="sm">
      <History className="h-4 w-4 mr-2" />
      Riwayat
    </Button>
  )

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        {trigger || defaultTrigger}
      </DialogTrigger>
      <DialogContent className="max-w-4xl max-h-[80vh]">
        <DialogHeader>
          <div className="flex items-center justify-between">
            <div>
              <DialogTitle className="flex items-center gap-2">
                <History className="h-5 w-5" />
                {title}
              </DialogTitle>
              <DialogDescription>
                {description}
              </DialogDescription>
            </div>
            {!loading && !error && auditLogs.length > 0 && (
              <Button
                variant="outline"
                size="sm"
                onClick={toggleAllExpanded}
                className="ml-4"
              >
                {expandedItems.size === auditLogs.length ? (
                  <>
                    <ChevronRight className="h-4 w-4 mr-2" />
                    Collapse All
                  </>
                ) : (
                  <>
                    <ChevronDown className="h-4 w-4 mr-2" />
                    Expand All
                  </>
                )}
              </Button>
            )}
          </div>
        </DialogHeader>

        <div className="flex-1 overflow-hidden">
          {loading && (
            <div className="flex items-center justify-center py-8">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              <span className="ml-2 text-gray-600">Memuat riwayat...</span>
            </div>
          )}

          {error && (
            <div className="flex items-center gap-2 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
              <AlertCircle className="h-5 w-5" />
              {error}
            </div>
          )}

          {!loading && !error && auditLogs.length === 0 && (
            <div className="flex flex-col items-center justify-center py-8 text-gray-500">
              <History className="h-12 w-12 mb-4 text-gray-300" />
              <p>Tidak ada riwayat perubahan</p>
            </div>
          )}

          {!loading && !error && auditLogs.length > 0 && (
            <ScrollArea className="h-[60vh] pr-4">
              <div className="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <div className="flex items-center justify-between text-sm">
                  <span className="text-blue-700 font-medium">
                    📊 Total {auditLogs.length} riwayat perubahan
                  </span>
                  <span className="text-blue-600">
                    {auditLogs.filter(log => log.old_values || log.new_values).length} dengan detail perubahan
                  </span>
                </div>
              </div>

              <div className="space-y-4">
                {auditLogs.map((log, index) => (
                  <div key={log.id} className="border rounded-lg p-4 bg-white">
                    <div className="flex items-start justify-between mb-3">
                      <div className="flex items-center gap-3">
                        {getEventIcon(log.event)}
                        <div>
                          <Badge variant="outline" className={getEventColor(log.event)}>
                            {log.event.toUpperCase()}
                          </Badge>
                          <div className="text-sm text-gray-600 mt-1">
                            {log.auditable_type} #{log.auditable_id}
                          </div>
                        </div>
                      </div>
                      <div className="text-right text-sm text-gray-500">
                        <div className="flex items-center gap-1">
                          <Calendar className="h-4 w-4" />
                          {moment(log.created_at).format('DD/MM/YYYY')}
                        </div>
                        <div className="flex items-center gap-1 mt-1">
                          <Clock className="h-4 w-4" />
                          {moment(log.created_at).format('HH:mm:ss')}
                        </div>
                      </div>
                    </div>

                    {log.user && (
                      <div className="flex items-center gap-2 mb-3 p-2 bg-blue-50 rounded-lg">
                        <User className="h-4 w-4 text-blue-600" />
                        <span className="text-sm font-medium text-blue-700">
                          {log.user.name}
                        </span>
                        <span className="text-sm text-blue-600">
                          ({log.user.email})
                        </span>
                      </div>
                    )}

                    {log.event === 'deleted' && log.delete_reason && (
                      <div className="flex items-start gap-2 mb-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <AlertCircle className="h-4 w-4 text-red-600 mt-0.5" />
                        <div>
                          <div className="text-sm font-medium text-red-700 mb-1">
                            Alasan Penghapusan:
                          </div>
                          <div className="text-sm text-red-600">
                            {log.delete_reason}
                          </div>
                        </div>
                      </div>
                    )}

                    {(log.old_values || log.new_values) && (
                      <div className="mb-3">
                        <button
                          onClick={() => toggleExpanded(log.id)}
                          className="flex items-center gap-2 w-full text-left p-2 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                          {expandedItems.has(log.id) ? (
                            <ChevronDown className="h-4 w-4 text-gray-600" />
                          ) : (
                            <ChevronRight className="h-4 w-4 text-gray-600" />
                          )}
                          <Eye className="h-4 w-4 text-gray-600" />
                          <span className="text-sm font-medium text-gray-700">
                            Perubahan Data
                          </span>
                          <Badge variant="secondary" className="ml-auto text-xs">
                            {getChangesCount(log.old_values || {}, log.new_values || {})} perubahan
                          </Badge>
                        </button>

                        {expandedItems.has(log.id) && (
                          <div className="mt-2 max-h-60 overflow-y-auto transition-all duration-300 ease-in-out">
                            {renderChanges(log.old_values || {}, log.new_values || {})}
                          </div>
                        )}
                      </div>
                    )}

                    <div className="text-xs text-gray-500 space-y-1">
                      <div>IP: {log.ip_address}</div>
                      {log.tags && <div>Tag: {log.tags}</div>}
                    </div>

                    {index < auditLogs.length - 1 && <Separator className="mt-4" />}
                  </div>
                ))}
              </div>
            </ScrollArea>
          )}
        </div>
      </DialogContent>
    </Dialog>
  )
}

export default AuditDialog
