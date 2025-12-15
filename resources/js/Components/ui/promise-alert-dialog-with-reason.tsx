"use client"

import { useState } from "react"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/Components/ui/dialog"
import { Button } from "./button"
import { Textarea } from "./textarea"

interface PromiseDialogProps {
  title: string
  description: string
  cancelText?: string
  confirmText?: string
}

interface DialogResult {
  confirmed: boolean
  reason?: string
}

let showAlertDialogWithReason: (props: PromiseDialogProps) => Promise<DialogResult>

export function PromiseAlertDialogWithReason() {
  const [open, setOpen] = useState(false)
  const [resolvePromise, setResolvePromise] = useState<(value: DialogResult) => void>()
  const [dialogProps, setDialogProps] = useState<PromiseDialogProps>({
    title: "",
    description: "",
    cancelText: "Cancel",
    confirmText: "Confirm",
  })
  const [reason, setReason] = useState("")

  showAlertDialogWithReason = (props: PromiseDialogProps) => {
    setDialogProps(props)
    setReason("")
    setOpen(true)
    return new Promise<DialogResult>((resolve) => {
      setResolvePromise(() => resolve)
    })
  }

  const handleClose = (confirmed: boolean) => {
    setOpen(false)
    resolvePromise?.({ confirmed, reason: confirmed ? reason : undefined })
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{dialogProps.title}</DialogTitle>
          <DialogDescription>{dialogProps.description}</DialogDescription>
        </DialogHeader>

        {/* Input alasan */}
        <div className="py-4">
          <label className="block text-sm font-medium mb-1">Alasan</label>
          <Textarea
            className="w-full rounded-md border p-2 text-sm"
            value={reason}
            onChange={(e) => setReason(e.target.value)}
            placeholder="Tulis alasan di sini..."
          />
        </div>

        <DialogFooter>
          <Button
            type="button"
            variant="ghost"
            onClick={() => handleClose(false)}
          >
            {dialogProps.cancelText}
          </Button>
          <Button
            type="button"
            variant="destructive"
            onClick={() => handleClose(true)}
            disabled={!reason.trim()} // kalau wajib isi alasan
          >
            {dialogProps.confirmText}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}

export { showAlertDialogWithReason }
