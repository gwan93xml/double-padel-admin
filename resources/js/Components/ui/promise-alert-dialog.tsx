"use client"

import { useState } from "react"
import {
  Dialog,
  //   DialogAction,
  //   DialogCancel,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/Components/ui/dialog"
import { Button } from "./button"

interface PromiseDialogProps {
  title: string
  description: string
  cancelText?: string
  confirmText?: string
}

let showAlertDialog: (props: PromiseDialogProps) => Promise<boolean>

export function PromiseAlertDialog() {
  const [open, setOpen] = useState(false)
  const [resolvePromise, setResolvePromise] = useState<(value: boolean) => void>()
  const [dialogProps, setDialogProps] = useState<PromiseDialogProps>({
    title: "",
    description: "",
    cancelText: "Cancel",
    confirmText: "Confirm",
  })

  showAlertDialog = (props: PromiseDialogProps) => {
    setDialogProps(props)
    setOpen(true)
    return new Promise<boolean>((resolve) => {
      setResolvePromise(() => resolve)
    })
  }

  const handleClose = (value: boolean) => {
    setOpen(false)
    resolvePromise?.(value)
  }

  return (
    <Dialog open={open} onOpenChange={setOpen} >
      <DialogContent >
        <DialogHeader>
          <DialogTitle>{dialogProps.title}</DialogTitle>
          <DialogDescription>{dialogProps.description}</DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button type="button" variant='ghost' onClick={() => handleClose(false)}>{dialogProps.cancelText}</Button>
          <Button type="button" variant='destructive' onClick={() => handleClose(true)}>{dialogProps.confirmText}</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}

export { showAlertDialog }

