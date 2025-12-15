export interface COA {
  id: string
  code: string
  name: string
  type: "ASSET" | "LIABILITY" | "EQUITY" | "REVENUE" | "EXPENSE"
  parentId?: string
  description?: string
  isActive: boolean
  createdAt: Date
  updatedAt: Date
}

export interface COAFormData {
  code: string
  name: string
  type: "ASSET" | "LIABILITY" | "EQUITY" | "REVENUE" | "EXPENSE"
  parentId?: string
  description?: string
  isActive: boolean
}
