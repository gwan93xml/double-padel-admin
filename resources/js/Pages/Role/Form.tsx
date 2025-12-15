"use client"

import { Button } from "@/Components/ui/button"
import { Checkbox } from "@/Components/ui/checkbox"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card"
import FormGroup from "@/Components/ui/form-group"
import { Input } from "@/Components/ui/input"
import { Label } from "@/Components/ui/label"
import { Badge } from "@/Components/ui/badge"
import { Separator } from "@/Components/ui/separator"
import { toast } from "@/hooks/use-toast"
import { router, useForm } from "@inertiajs/react"
import { useState } from "react"
import { Settings, Save, CheckCircle, Circle, Lock, Search, X, Filter, Zap, RotateCcw, Shield, Plus } from "lucide-react"

interface RoleFormProps {
  initialData?: {
    id?: string
    name: string
    permissions: any[]
  }
  modules: ModuleType[]
  onSubmit: (data: any) => Promise<void>
  submitButtonText: string
  successMessage: string
  redirectTo: string
  headerTitle: string
  headerDescription: string
  headerIcon: React.ReactNode
  headerColor: string
  showQuickActions?: boolean
  showBackButton?: boolean
}

interface RoleType {
  id?: string
  name: string
  permissions: string[]
}

interface ModuleType {
  id: string
  name: string
  slug: string
  actions: Array<{
    name: string
  }>
}

export default function RoleForm({
  initialData = { name: "", permissions: [] },
  modules,
  onSubmit,
  submitButtonText,
  successMessage,
  redirectTo,
  headerTitle,
  headerDescription,
  headerIcon,
  headerColor,
  showQuickActions = false,
  showBackButton = true,
}: RoleFormProps) {
  const { data, setData, clearErrors, reset, errors, setError } = useForm({
    ...initialData,
    permissions: initialData.permissions?.map((permission: any) =>
      typeof permission === "object" ? permission.name : permission
    ) || [],
  } as RoleType)

  const [processing, setProcessing] = useState(false)
  const [searchQuery, setSearchQuery] = useState("")

  async function handleSubmit() {
    clearErrors()
    setProcessing(true)
    try {
      await onSubmit(data)
      toast({
        title: "Sukses",
        description: successMessage,
      })
      router.visit(redirectTo)
    } catch (error: any) {
      if (error.response?.status === 422) {
        const errors = error.response.data.errors
        setError(errors)
      } else {
        toast({
          variant: "destructive",
          title: "Gagal",
          description: error.response?.data?.message || "Terjadi kesalahan",
        })
      }
    } finally {
      setProcessing(false)
    }
  }

  function handleModuleChecked(checked: any, module: ModuleType) {
    const permissions = module.actions.map((action: any) => `${action.name}-${module.slug}`)
    if (checked) {
      setData("permissions", [...data.permissions!, ...permissions])
    } else {
      const newPermissions = data.permissions!.filter((permission: any) => {
        return !permissions.includes(permission)
      })
      setData("permissions", newPermissions)
    }
  }

  function handleChecked(checked: any, action: any, module: ModuleType) {
    const permission = `${action.name}-${module.slug}`
    if (checked) {
      setData("permissions", [...data.permissions!, permission])
    } else {
      const newPermissions = data.permissions!.filter((permission: any) => {
        return permission !== `${action.name}-${module.slug}`
      })
      setData("permissions", newPermissions)
    }
  }

  const isModuleFullySelected = (module: ModuleType) => {
    const modulePermissions = module.actions.map((action: any) => `${action.name}-${module.slug}`)
    return modulePermissions.every((permission) => data.permissions?.includes(permission))
  }

  const isModulePartiallySelected = (module: ModuleType) => {
    const modulePermissions = module.actions.map((action: any) => `${action.name}-${module.slug}`)
    return (
      modulePermissions.some((permission) => data.permissions?.includes(permission)) && !isModuleFullySelected(module)
    )
  }

  const getSelectedPermissionsCount = () => {
    return data.permissions?.length || 0
  }

  const getTotalPermissionsCount = () => {
    return modules.reduce((total, module) => total + module.actions.length, 0)
  }

  const filteredModules = modules.filter((module) => {
    const moduleNameMatch = module.name.toLowerCase().includes(searchQuery.toLowerCase())
    const actionMatch = module.actions.some((action) => action.name.toLowerCase().includes(searchQuery.toLowerCase()))
    return moduleNameMatch || actionMatch
  })

  const clearSearch = () => {
    setSearchQuery("")
  }

  const selectAllPermissions = () => {
    const allPermissions = modules.flatMap((module) => module.actions.map((action) => `${action.name}-${module.slug}`))
    setData("permissions", allPermissions)
  }

  const clearAllPermissions = () => {
    setData("permissions", [])
  }

  const resetForm = () => {
    reset()
    setSearchQuery("")
  }

  return (
    <div className="p-4 md:p-6 bg-gradient-to-br from-blue-50 to-indigo-50 min-h-screen dark:from-gray-900 dark:to-gray-800">
      {/* Header Section */}
      <div className="mb-8">
        <div className="flex items-center gap-3 mb-2">
          <div className={`p-2 ${headerColor} rounded-lg`}>{headerIcon}</div>
          <div>
            <h1 className="text-3xl font-bold text-gray-900 dark:text-white">{headerTitle}</h1>
            <p className="text-gray-600 dark:text-gray-200">{headerDescription}</p>
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {/* Role Information */}
        <div className="xl:col-span-1">
          <Card className="shadow-lg border-0 sticky top-6">
            <CardHeader className={`${headerColor} text-white rounded-t-lg`}>
              <CardTitle className="flex items-center gap-2">
                <Shield className="h-5 w-5" />
                Informasi Role
              </CardTitle>
              <CardDescription className="text-blue-100">Detail dan statistik role</CardDescription>
            </CardHeader>
            <CardContent className="p-6 space-y-6">
              <FormGroup label="Nama Role" error={errors.name} required>
                <Input
                  type="text"
                  value={data.name}
                  onChange={(e) => setData("name", e.target.value)}
                  placeholder="Masukkan nama role"
                  className="mt-1"
                />
              </FormGroup>

              <Separator />

              {/* Permission Statistics */}
              <div className="space-y-4">
                <h3 className="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                  <Settings className="h-4 w-4" />
                  Statistik Permission
                </h3>

                <div className="grid grid-cols-2 gap-4">
                  <div className="text-center p-3 bg-blue-50 dark:bg-blue-950 rounded-lg">
                    <div className="text-2xl font-bold text-blue-600 dark:text-blue-400">
                      {getSelectedPermissionsCount()}
                    </div>
                    <div className="text-sm text-blue-700 dark:text-blue-300">Dipilih</div>
                  </div>
                  <div className="text-center p-3 bg-gray-50 rounded-lg">
                    <div className="text-2xl font-bold text-gray-600">{getTotalPermissionsCount()}</div>
                    <div className="text-sm text-gray-700">Total</div>
                  </div>
                </div>

                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div
                    className="bg-blue-600 h-2 rounded-full transition-all duration-300"
                    style={{
                      width: `${(getSelectedPermissionsCount() / getTotalPermissionsCount()) * 100}%`,
                    }}
                  ></div>
                </div>
                <p className="text-sm text-gray-600 text-center">
                  {Math.round((getSelectedPermissionsCount() / getTotalPermissionsCount()) * 100)}% permission dipilih
                </p>
              </div>

              <Separator />

              {/* Quick Actions */}
              {showQuickActions && (
                <>
                  <div className="space-y-3">
                    <h4 className="font-medium text-gray-900 flex items-center gap-2">
                      <Zap className="h-4 w-4" />
                      Aksi Cepat
                    </h4>
                    <div className="grid grid-cols-2 gap-2">
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={selectAllPermissions}
                        className="text-xs bg-transparent"
                      >
                        <CheckCircle className="h-3 w-3 mr-1" />
                        Pilih Semua
                      </Button>
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={clearAllPermissions}
                        className="text-xs bg-transparent"
                      >
                        <Circle className="h-3 w-3 mr-1" />
                        Hapus Semua
                      </Button>
                    </div>
                    <Button variant="outline" size="sm" onClick={resetForm} className="w-full text-xs bg-transparent">
                      <RotateCcw className="h-3 w-3 mr-1" />
                      Reset Form
                    </Button>
                  </div>

                  <Separator />
                </>
              )}

              {/* Search Statistics */}
              {searchQuery && (
                <>
                  <div className="space-y-2">
                    <h4 className="font-medium text-gray-900 flex items-center gap-2">
                      <Filter className="h-4 w-4" />
                      Hasil Pencarian
                    </h4>
                    <div className="text-center p-3 bg-blue-50 rounded-lg">
                      <div className="text-xl font-bold text-blue-600">{filteredModules.length}</div>
                      <div className="text-sm text-blue-700">Module ditemukan</div>
                    </div>
                  </div>

                  <Separator />
                </>
              )}

              {/* Save Button */}
              <div className="space-y-3">
                <Button
                  onClick={handleSubmit}
                  disabled={processing}
                  className={`w-full ${headerColor} h-12`}
                  size="lg"
                >
                  {processing ? (
                    <>
                      <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                      Menyimpan...
                    </>
                  ) : (
                    <>
                      <Save className="h-4 w-4 mr-2" />
                      {submitButtonText}
                    </>
                  )}
                </Button>

                {showBackButton && (
                  <Button
                    variant="outline"
                    onClick={() => router.visit(redirectTo)}
                    className="w-full"
                    disabled={processing}
                  >
                    Kembali ke Daftar Role
                  </Button>
                )}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Permissions Section */}
        <div className="xl:col-span-2">
          <Card className="shadow-lg border-0">
            <CardHeader className="bg-gradient-to-r from-green-600 to-green-700 text-white rounded-t-lg">
              <CardTitle className="flex items-center gap-2">
                <Lock className="h-5 w-5" />
                Kelola Permission
              </CardTitle>
              <CardDescription className="text-green-100">
                Pilih permission yang diizinkan untuk role ini
              </CardDescription>
            </CardHeader>
            <CardContent className="p-6">
              {/* Search Section */}
              <div className="mb-6">
                <Label htmlFor="search" className="text-sm font-medium text-gray-700 mb-2 block">
                  Cari Module atau Permission
                </Label>
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                  <Input
                    id="search"
                    type="text"
                    placeholder="Ketik nama module atau permission..."
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    className="pl-10 pr-10"
                  />
                  {searchQuery && (
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={clearSearch}
                      className="absolute right-1 top-1/2 transform -translate-y-1/2 h-8 w-8 p-0 hover:bg-gray-100"
                    >
                      <X className="h-4 w-4" />
                    </Button>
                  )}
                </div>

                {/* Search Results Info */}
                {searchQuery && (
                  <div className="mt-2 flex items-center justify-between text-sm">
                    <span className="text-gray-600">
                      Menampilkan {filteredModules.length} dari {modules.length} module
                    </span>
                    {searchQuery && (
                      <Badge variant="outline" className="bg-blue-50 text-blue-700 border-blue-200">
                        Pencarian: "{searchQuery}"
                      </Badge>
                    )}
                  </div>
                )}
              </div>

              <FormGroup error={errors.permissions} required>
                {/* No Results */}
                {filteredModules.length === 0 && searchQuery && (
                  <Card className="border-2 border-dashed border-gray-300">
                    <CardContent className="flex flex-col items-center justify-center py-12">
                      <Search className="h-12 w-12 text-gray-400 mb-4" />
                      <h3 className="text-lg font-medium text-gray-900 mb-2">Tidak ada hasil</h3>
                      <p className="text-gray-500 text-center mb-4">
                        Tidak ditemukan module atau permission yang cocok dengan "{searchQuery}"
                      </p>
                      <Button variant="outline" onClick={clearSearch}>
                        <X className="h-4 w-4 mr-2" />
                        Hapus Pencarian
                      </Button>
                    </CardContent>
                  </Card>
                )}

                {/* Modules Grid */}
                {filteredModules.length > 0 && (
                  <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {filteredModules.map((module: ModuleType) => (
                      <Card key={module.id} className="border-2 hover:shadow-md transition-shadow">
                        {/* Module Header */}
                        <CardHeader className="pb-3">
                          <div className="flex items-center justify-between">
                            <div className="flex items-center space-x-3">
                              <Checkbox
                                id={`module-${module.id}`}
                                checked={isModuleFullySelected(module)}
                                onCheckedChange={(checked) => handleModuleChecked(checked, module)}
                                className="data-[state=checked]:bg-green-600 data-[state=checked]:border-green-600"
                              />
                              <div>
                                <Label
                                  htmlFor={`module-${module.id}`}
                                  className="text-base font-semibold text-gray-900 dark:text-gray-100 cursor-pointer"
                                >
                                  {/* Highlight search term in module name */}
                                  {searchQuery ? (
                                    <span
                                      dangerouslySetInnerHTML={{
                                        __html: module.name.replace(
                                          new RegExp(`(${searchQuery})`, "gi"),
                                          '<mark class="bg-yellow-200 px-1 rounded">$1</mark>',
                                        ),
                                      }}
                                    />
                                  ) : (
                                    module.name
                                  )}
                                </Label>
                                <div className="flex items-center gap-2 mt-1">
                                  <Badge
                                    variant="outline"
                                    className={`text-xs ${
                                      isModuleFullySelected(module)
                                        ? "bg-green-100 text-green-800 border-green-200"
                                        : isModulePartiallySelected(module)
                                          ? "bg-yellow-100 text-yellow-800 border-yellow-200"
                                          : "bg-gray-100 text-gray-600 border-gray-200"
                                    }`}
                                  >
                                    {isModuleFullySelected(module)
                                      ? "Semua Dipilih"
                                      : isModulePartiallySelected(module)
                                        ? "Sebagian Dipilih"
                                        : "Tidak Dipilih"}
                                  </Badge>
                                  <Badge
                                    variant="outline"
                                    className="text-xs bg-blue-50 text-blue-700 border-blue-200"
                                  >
                                    {module.actions.length} permission
                                  </Badge>
                                </div>
                              </div>
                            </div>
                            {isModuleFullySelected(module) ? (
                              <CheckCircle className="h-5 w-5 text-green-600" />
                            ) : isModulePartiallySelected(module) ? (
                              <Circle className="h-5 w-5 text-yellow-600 fill-current" />
                            ) : (
                              <Circle className="h-5 w-5 text-gray-400" />
                            )}
                          </div>
                        </CardHeader>

                        {/* Module Actions */}
                        <CardContent className="pt-0">
                          <div className="space-y-3">
                            {module.actions?.map((action: any) => (
                              <div
                                key={`${action.name}-${module.slug}`}
                                className="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 transition-colors"
                              >
                                <Checkbox
                                  id={`${action.name}-${module.slug}`}
                                  checked={data.permissions?.includes(`${action.name}-${module.slug}`)}
                                  onCheckedChange={(checked) => handleChecked(checked, action, module)}
                                  className="data-[state=checked]:bg-blue-600 data-[state=checked]:border-blue-600"
                                />
                                <Label
                                  htmlFor={`${action.name}-${module.slug}`}
                                  className="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer flex-1"
                                >
                                  {/* Highlight search term in action name */}
                                  {searchQuery ? (
                                    <span
                                      dangerouslySetInnerHTML={{
                                        __html: action.name.replace(
                                          new RegExp(`(${searchQuery})`, "gi"),
                                          '<mark class="bg-yellow-200 px-1 rounded">$1</mark>',
                                        ),
                                      }}
                                    />
                                  ) : (
                                    action.name
                                  )}
                                </Label>
                                {data.permissions?.includes(`${action.name}-${module.slug}`) && (
                                  <CheckCircle className="h-4 w-4 text-blue-600" />
                                )}
                              </div>
                            ))}
                          </div>
                        </CardContent>
                      </Card>
                    ))}
                  </div>
                )}
              </FormGroup>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  )
}
