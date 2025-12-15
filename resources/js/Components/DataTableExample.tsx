// Example usage of Enhanced DataTable with Additional Filters

import EnhancedDataTable from "@/Components/DataTable"

const ExamplePage = () => {
    const columns = [
        {
            accessorKey: "name",
            header: "Name",
            cell: (info: any) => info.getValue()
        },
        {
            accessorKey: "status",
            header: "Status",
            cell: (info: any) => info.getValue()
        }
    ]

    // Example additional filters configuration
    const additionalFilters = [
        {
            key: "status",
            label: "Status",
            type: "select" as const,
            options: [
                { value: "active", label: "Active" },
                { value: "inactive", label: "Inactive" },
                { value: "pending", label: "Pending" }
            ],
            placeholder: "Select Status"
        },
        {
            key: "category_id",
            label: "Category",
            type: "select" as const,
            apiUrl: "/api/categories", // Dynamic options from API
            placeholder: "Select Category"
        },
        {
            key: "amount_min",
            label: "Minimum Amount",
            type: "number" as const,
            placeholder: "Enter minimum amount"
        },
        {
            key: "amount_max",
            label: "Maximum Amount", 
            type: "number" as const,
            placeholder: "Enter maximum amount"
        },
        {
            key: "created_after",
            label: "Created After",
            type: "date" as const
        },
        {
            key: "reference_code",
            label: "Reference Code",
            type: "input" as const,
            placeholder: "Enter reference code"
        }
    ]

    return (
        <EnhancedDataTable
            columns={columns}
            apiUrl="/api/data"
            title="Data Management"
            description="Manage your data with advanced filtering"
            isFilterDate={true}
            isFilterDivision={true}
            additionalFilters={additionalFilters}
            onCreate={() => console.log("Create new")}
            onEdit={(id) => console.log("Edit", id)}
            onDelete={(id) => console.log("Delete", id)}
            onExport={() => console.log("Export data")}
        />
    )
}

export default ExamplePage
