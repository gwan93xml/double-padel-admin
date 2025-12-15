import { useState } from 'react'
import { Head, Link, router } from '@inertiajs/react'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Badge } from '@/Components/ui/badge'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/Components/ui/dialog'
import { Label } from '@/Components/ui/label'
import { Switch } from '@/Components/ui/switch'
import { Textarea } from '@/Components/ui/textarea'
import { AlertCircle, Plus, Shield, Trash2, Edit, ToggleLeft, ToggleRight, Clock, Users, UserCheck } from 'lucide-react'
import { Alert, AlertDescription } from '@/Components/ui/alert'

export default function Index({ allowedIps, currentIp, stats, auth } : any) {
    const [showCreateDialog, setShowCreateDialog] = useState(false)
    const [formData, setFormData] = useState({
        ip_address: '',
        description: '',
        is_active: true
    })
    const [processing, setProcessing] = useState(false)

    const handleSubmit = (e : any) => {
        e.preventDefault()
        setProcessing(true)

        router.post(route('admin.allowed-ips.store'), formData, {
            onSuccess: () => {
                setShowCreateDialog(false)
                setFormData({ ip_address: '', description: '', is_active: true })
            },
            onFinish: () => setProcessing(false)
        })
    }

    const toggleIp = (id : number) => {
        router.post(route('admin.allowed-ips.toggle', id))
    }

    const deleteIp = (id : number) => {
        if (confirm('Are you sure you want to delete this IP address?')) {
            router.delete(route('admin.allowed-ips.destroy', id))
        }
    }

    const addCurrentIp = () => {
        router.post(route('admin.allowed-ips.add-current'))
    }

    const bulkDeleteInactive = () => {
        if (confirm('Are you sure you want to delete all inactive IP addresses?')) {
            router.delete(route('admin.allowed-ips.bulk-delete-inactive'))
        }
    }

    const formatDate = (dateString : any) => {
        return new Date(dateString).toLocaleString()
    }

    return (

        <div className="p-5">
            <div className="space-y-6">

                {/* Current IP Alert */}
                <Alert>
                    <AlertCircle className="h-4 w-4" />
                    <AlertDescription>
                        Your current IP address is: <strong>{currentIp}</strong>
                        {!allowedIps.data.some((ip : any) => ip.ip_address === currentIp && ip.is_active) && (
                            <span className="ml-2">
                                <Button
                                    size="sm"
                                    onClick={addCurrentIp}
                                    className="ml-2"
                                >
                                    Add to Allowed List
                                </Button>
                            </span>
                        )}
                    </AlertDescription>
                </Alert>

                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total IPs</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Active IPs</CardTitle>
                            <UserCheck className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">{stats.active}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Recently Used (7 days)</CardTitle>
                            <Clock className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600">{stats.recently_used}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Actions */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Shield className="h-5 w-5" />
                            IP Access Control
                        </CardTitle>
                        <CardDescription>
                            Manage IP addresses that are allowed to access the application
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex gap-2 flex-wrap">
                            <Dialog open={showCreateDialog} onOpenChange={setShowCreateDialog}>
                                <DialogTrigger asChild>
                                    <Button>
                                        <Plus className="h-4 w-4 mr-2" />
                                        Add IP Address
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>Add New IP Address</DialogTitle>
                                        <DialogDescription>
                                            Add a new IP address or CIDR range to the allowed list
                                        </DialogDescription>
                                    </DialogHeader>
                                    <form onSubmit={handleSubmit}>
                                        <div className="grid gap-4 py-4">
                                            <div className="grid gap-2">
                                                <Label htmlFor="ip_address">IP Address or CIDR Range</Label>
                                                <Input
                                                    id="ip_address"
                                                    placeholder="192.168.1.100 or 192.168.1.0/24"
                                                    value={formData.ip_address}
                                                    onChange={(e) => setFormData(prev => ({ ...prev, ip_address: e.target.value }))}
                                                    required
                                                />
                                            </div>
                                            <div className="grid gap-2">
                                                <Label htmlFor="description">Description</Label>
                                                <Textarea
                                                    id="description"
                                                    placeholder="Office network, Admin home, etc."
                                                    value={formData.description}
                                                    onChange={(e) => setFormData(prev => ({ ...prev, description: e.target.value }))}
                                                />
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Switch
                                                    id="is_active"
                                                    checked={formData.is_active}
                                                    onCheckedChange={(checked) => setFormData(prev => ({ ...prev, is_active: checked }))}
                                                />
                                                <Label htmlFor="is_active">Active</Label>
                                            </div>
                                        </div>
                                        <DialogFooter>
                                            <Button type="submit" disabled={processing}>
                                                {processing ? 'Adding...' : 'Add IP'}
                                            </Button>
                                        </DialogFooter>
                                    </form>
                                </DialogContent>
                            </Dialog>

                            <Button
                                variant="outline"
                                onClick={addCurrentIp}
                                disabled={allowedIps.data.some((ip : any) => ip.ip_address === currentIp && ip.is_active)}
                            >
                                Add Current IP ({currentIp})
                            </Button>

                            {stats.total > stats.active && (
                                <Button
                                    variant="outline"
                                    onClick={bulkDeleteInactive}
                                    className="text-red-600 hover:text-red-700"
                                >
                                    <Trash2 className="h-4 w-4 mr-2" />
                                    Delete Inactive
                                </Button>
                            )}
                        </div>
                    </CardContent>
                </Card>

                {/* IP List */}
                <Card>
                    <CardHeader>
                        <CardTitle>Allowed IP Addresses</CardTitle>
                        <CardDescription>
                            {stats.total === 0
                                ? 'No IP restrictions configured. All IPs are allowed to access the application.'
                                : `${stats.total} IP address(es) configured. Only these IPs can access the application.`
                            }
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {allowedIps.data.length > 0 ? (
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>IP Address</TableHead>
                                        <TableHead>Description</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Created By</TableHead>
                                        <TableHead>Last Used</TableHead>
                                        <TableHead>Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {allowedIps.data.map((ip : any) => (
                                        <TableRow key={ip.id}>
                                            <TableCell className="font-mono">
                                                {ip.ip_address}
                                                {ip.ip_address === currentIp && (
                                                    <Badge variant="outline" className="ml-2 text-xs">
                                                        Current
                                                    </Badge>
                                                )}
                                            </TableCell>
                                            <TableCell>{ip.description || '-'}</TableCell>
                                            <TableCell>
                                                <Badge variant={ip.is_active ? 'default' : 'secondary'}>
                                                    {ip.is_active ? 'Active' : 'Inactive'}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>{ip.created_by || '-'}</TableCell>
                                            <TableCell>
                                                {ip.last_used_at ? formatDate(ip.last_used_at) : 'Never'}
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex gap-2">
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() => toggleIp(ip.id)}
                                                    >
                                                        {ip.is_active ? (
                                                            <ToggleRight className="h-4 w-4" />
                                                        ) : (
                                                            <ToggleLeft className="h-4 w-4" />
                                                        )}
                                                    </Button>
                                                    <Link href={route('admin.allowed-ips.edit', ip.id)}>
                                                        <Button size="sm" variant="outline">
                                                            <Edit className="h-4 w-4" />
                                                        </Button>
                                                    </Link>
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() => deleteIp(ip.id)}
                                                        className="text-red-600 hover:text-red-700"
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        ) : (
                            <div className="text-center py-8 text-gray-500">
                                <Shield className="h-12 w-12 mx-auto mb-4 text-gray-300" />
                                <p>No IP addresses configured</p>
                                <p className="text-sm">All IP addresses are currently allowed to access the application</p>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Pagination */}
                {allowedIps.last_page > 1 && (
                    <div className="flex justify-center">
                        <div className="flex gap-2">
                            {allowedIps.links.map((link : any, index : any) => (
                                <Link
                                    key={index}
                                    href={link.url}
                                    className={`px-3 py-2 rounded ${link.active
                                            ? 'bg-blue-500 text-white'
                                            : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                        }`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </div>
    )
}


Index.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/admin' },
            { label: 'Pengaturan', href: '#' },
            { label: 'IP Access Control', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
