
import { Button } from "@/Components/ui/button"
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/Components/ui/dialog"

export default function CustomIframeModal({ url, open, onClose }: { url: string, open: boolean, onClose: () => void }) {
    return (
        <Dialog open={open} onOpenChange={onClose}>
            <DialogContent className="max-w-[1000px]">
                <DialogHeader>
                    <DialogTitle></DialogTitle>
                </DialogHeader>
                <div className="grid gap-4 py-4">
                    <div className="h-[80vh] w-full border border-gray-200 rounded-md overflow-hidden">
                        <iframe
                            src={url}
                            title="Custom Iframe"
                            width="100%"
                            height="100%"
                            style={{ border: "none" }}
                        />
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    )
}

