import { CKEditor } from "@ckeditor/ckeditor5-react";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import { cn } from "@/lib/utils";

interface CKEditorComponentProps {
    value: string;
    onChange: (data: string) => void;
    error?: string;
    disabled?: boolean;
    placeholder?: string;
    className?: string;
}

export default function CKEditorComponent({
    value,
    onChange,
    error,
    disabled = false,
    placeholder = "Isikan konten di sini...",
    className = ""
}: CKEditorComponentProps) {
    return (
        <div className={cn("w-full", className)}>
            <div className={cn(
                "border rounded-md overflow-hidden",
                error ? "border-red-500" : "border-input"
            )}>
                <CKEditor
                
                    editor={ClassicEditor}
                    data={value || ""}
                    disabled={disabled}
                    config={{
                        licenseKey: 'GPL',
                        placeholder: placeholder,
                        toolbar: {
                            items: [
                                'heading',
                                '|',
                                'bold',
                                'italic',
                                'link',
                                'bulletedList',
                                'numberedList',
                                '|',
                                'blockQuote',
                                'codeBlock',
                                '|',
                                'insertTable',
                                '|',
                                'undo',
                                'redo'
                            ]
                        }
                    }}
                    onChange={(event, editor) => {
                        const editorData = editor.getData();
                        onChange(editorData);
                    }}
                />
            </div>
            {error && (
                <p className="text-sm text-red-500 mt-1">{error}</p>
            )}
        </div>
    );
}
