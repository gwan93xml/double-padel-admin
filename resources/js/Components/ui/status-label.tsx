import { Badge } from "@/Components/ui/badge"

type StatusLabelProps = {
  status: 'pending' | 'completed' | 'out' | 'in'
}

export default function StatusLabel({ status }: StatusLabelProps) {
  if (status === 'pending' || status === 'out') {
    return (
      <Badge variant="secondary" className="bg-yellow-100 text-yellow-800 hover:bg-yellow-200 hover:text-yellow-900">
        {status.toUpperCase()}
      </Badge>
    )
  }

  if (status === 'completed' || status === 'in') {
    return (
      <Badge variant="secondary" className="bg-green-100 text-green-800 hover:bg-green-200 hover:text-green-900">
        {status.toUpperCase()}
      </Badge>
    )
  }

  return null
}

