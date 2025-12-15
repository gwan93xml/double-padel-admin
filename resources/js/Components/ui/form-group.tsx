import { Label } from "./label";

type FormGroupType = {
    children: React.ReactNode;
    label?: string;
    name?: string;
    error?: string;
    required?: boolean;
    horizontal?: boolean;
    className?: string;
}
export default function FormGroup({ children, label, name, error, required, horizontal = false, className }: FormGroupType) {
    if(horizontal) {
        return (
            <div className="mb-1 flex flex-row items-start">
                {label && (
                    <Label htmlFor={name} className="w-1/3 text-right mr-3 mb-2">{label} {required ? (<span className="text-red-500">*</span>) : ''} :</Label>
                )}
                <div className="w-2/3">
                    {children}
                    {error && <div className="text-sm text-red-500">{error}</div>}
                </div>
            </div>
        )
    }
    return (
        <div className={`space-y-2 ${className || ''}`}>
            {label && (
                <Label htmlFor={name} className="mb-2">{label} {required ? (<span className="text-red-500">*</span>) : ''}</Label>
            )}
            {children}
            {error && <div className="text-sm text-red-500">{error}</div>}
        </div>
    );
}