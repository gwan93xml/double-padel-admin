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
import { Input } from "@/Components/ui/input"
import { Label } from "@/Components/ui/label"
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

interface DetailAuditDialogProps {
  auditableType: string
  keyField: string
  valueField: string
  trigger?: React.ReactNode
  title?: string
  description?: string
}

const DetailAuditDialog: React.FC<DetailAuditDialogProps> = ({
  auditableType,
  keyField,
  valueField,
  trigger,
  title = "Riwayat Perubahan Detail",
  description = "Lihat semua perubahan yang terkait dengan data ini"
}) => {
  const [open, setOpen] = useState(false)
  const [auditLogs, setAuditLogs] = useState<AuditLog[]>([])
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const fetchAuditLogs = async () => {
    setLoading(true)
    setError(null)
    try {
      const response = await axios.get('/admin/audits', {
        params: {
          auditable_type: auditableType,
          key: keyField,
          value: valueField,
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
    }
  }, [open, auditableType, keyField, valueField])

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
        return 'bg-gray-50 text-gray-'
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

  const groupAuditsByMinute = (audits: AuditLog[]) => {
    const groups: Record<string, AuditLog[]> = {}

    audits.forEach(audit => {
      const minuteKey = moment(audit.created_at).format('YYYY-MM-DD HH:mm')
      if (!groups[minuteKey]) {
        groups[minuteKey] = []
      }
      groups[minuteKey].push(audit)
    })

    return groups
  }

  const getItemFieldValue = (values: Record<string, any>, field: string) => {
    if (!values) return '-'
    return values[field] !== undefined ? values[field] : '-'
  }

  const renderGroupedAudits = () => {
    const groupedAudits = groupAuditsByMinute(auditLogs)

    return Object.entries(groupedAudits)
      .sort(([a], [b]) => moment(b).diff(moment(a)))
      .map(([minuteKey, audits]) => (
        <div key={minuteKey} className="mb-6">
          <div className="flex items-center gap-2 mb-3 p-3 border rounded-lg">
            <Clock className="h-4 w-4 " />
            <span className="font-medium ">
              {moment(minuteKey).format('DD/MM/YYYY HH:mm')}
            </span>
            <Badge variant="secondary" className="ml-auto">
              {audits.length} perubahan
            </Badge>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full border-collapse  rounded-lg">
              <thead>
                <tr className="">
                  <th className=" px-3 py-2 text-left text-sm font-medium ">
                    Item
                  </th>
                  <th className=" px-3 py-2 text-left text-sm font-medium ">
                    Quantity
                  </th>
                  <th className=" px-3 py-2 text-left text-sm font-medium ">
                    Price
                  </th>
                  <th className=" px-3 py-2 text-left text-sm font-medium ">
                    Subtotal
                  </th>
                  <th className=" px-3 py-2 text-left text-sm font-medium ">
                    Event
                  </th>
                  <th className=" px-3 py-2 text-left text-sm font-medium ">
                    User
                  </th>
                  <th className=" px-3 py-2 text-left text-sm font-medium ">
                    Time
                  </th>
                </tr>
              </thead>
              <tbody>
                {audits.map((audit, index) => (
                  <tr key={audit.id} className={index % 2 === 0 ? '' : ''}>
                    <td className=" px-3 py-2 text-sm">
                      <div className="font-medium">
                        {getItemFieldValue(audit.new_values, 'item_name') ||
                         getItemFieldValue(audit.old_values, 'item_name')}
                      </div>
                      <div className="text-xs text-gray-500">
                        ID: {getItemFieldValue(audit.new_values, 'item_id') ||
                             getItemFieldValue(audit.old_values, 'item_id')}
                      </div>
                    </td>
                    <td className=" px-3 py-2 text-sm">
                      <div className="flex flex-col">
                        {audit.old_values?.quantity !== audit.new_values?.quantity && (
                          <>
                            <span className="text-red-600 line-through">
                              {getItemFieldValue(audit.old_values, 'quantity')}
                            </span>
                            <span className="text-green-600 font-medium">
                              {getItemFieldValue(audit.new_values, 'quantity')}
                            </span>
                          </>
                        )}
                        {audit.old_values?.quantity === audit.new_values?.quantity && (
                          <span>{getItemFieldValue(audit.new_values, 'quantity')}</span>
                        )}
                      </div>
                    </td>
                    <td className=" px-3 py-2 text-sm">
                      <div className="flex flex-col">
                        {audit.old_values?.price !== audit.new_values?.price && (
                          <>
                            <span className="text-red-600 line-through">
                              {formatValue(audit.old_values?.price)}
                            </span>
                            <span className="text-green-600 font-medium">
                              {formatValue(audit.new_values?.price)}
                            </span>
                          </>
                        )}
                        {audit.old_values?.price === audit.new_values?.price && (
                          <span>{formatValue(audit.new_values?.price)}</span>
                        )}
                      </div>
                    </td>
                    <td className=" px-3 py-2 text-sm">
                      <div className="flex flex-col">
                        {audit.old_values?.subtotal !== audit.new_values?.subtotal && (
                          <>
                            <span className="text-red-600 line-through">
                              {formatValue(audit.old_values?.subtotal)}
                            </span>
                            <span className="text-green-600 font-medium">
                              {formatValue(audit.new_values?.subtotal)}
                            </span>
                          </>
                        )}
                        {audit.old_values?.subtotal === audit.new_values?.subtotal && (
                          <span>{formatValue(audit.new_values?.subtotal)}</span>
                        )}
                      </div>
                    </td>
                    <td className=" px-3 py-2 text-sm">
                      <Badge variant="outline" className={getEventColor(audit.event)}>
                        {audit.event.toUpperCase()}
                      </Badge>
                    </td>
                    <td className=" px-3 py-2 text-sm">
                      {audit.user ? (
                        <div>
                          <div className="font-medium">{audit.user.name}</div>
                          <div className="text-xs text-gray-500">{audit.user.email}</div>
                        </div>
                      ) : (
                        <span className="text-gray-500">System</span>
                      )}
                    </td>
                    <td className=" px-3 py-2 text-sm text-gray-500">
                      {moment(audit.created_at).format('HH:mm:ss')}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      ))
  }

  const defaultTrigger = (
    <Button variant="outline" size="sm">
      <History className="h-4 w-4 mr-2" />
      Riwayat Detail
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
              <div className="mt-2 text-sm text-gray-600">
                Mencari audit untuk: <strong>{keyField} = {valueField}</strong>
              </div>
            </div>
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
              <History className="h-12 w-12 mb-4 " />
              <p>Tidak ada riwayat perubahan untuk data ini</p>
              <p className="text-sm mt-2">Key: {keyField}, Value: {valueField}</p>
            </div>
          )}

          {!loading && !error && auditLogs.length > 0 && (
            <ScrollArea className="h-[60vh] pr-4">
              <div className="mb-4 p-3  border rounded-lg">
                <div className="flex items-center justify-between text-sm">
                  <span className=" font-medium">
                    📊 Total {auditLogs.length} perubahan dalam {Object.keys(groupAuditsByMinute(auditLogs)).length} kelompok waktu
                  </span>
                  <span className="">
                    Dikelompokkan per menit untuk memudahkan tracking
                  </span>
                </div>
              </div>

              {renderGroupedAudits()}
            </ScrollArea>
          )}
        </div>
      </DialogContent>
    </Dialog>
  )
}

export default DetailAuditDialog