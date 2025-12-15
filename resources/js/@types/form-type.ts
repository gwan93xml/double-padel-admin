type FormType = {
    isDialogOpen: boolean,
    setIsDialogOpen: (value: boolean) => void
    onSubmitSuccess: (closeDialog?: boolean) => void
    initialData: any
}