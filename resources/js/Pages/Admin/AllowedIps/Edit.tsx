import { useState } from 'react'
import { Head, router } from '@inertiajs/react'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Label } from '@/Components/ui/label'
import { Switch } from '@/Components/ui/switch'
import { Textarea } from '@/Components/ui/textarea'
import { ArrowLeft, Shield } from 'lucide-react'

export default function Edit({ allowedIp, auth }: any) {
    const [formData, setFormData] = useState({
        ip_address: allowedIp.ip_address || '',
        description: allowedIp.description || '',
        is_active: allowedIp.is_active || false
    })
    const [processing, setProcessing] = useState(false)

    const handleSubmit = (e: any) => {
        e.preventDefault()
        setProcessing(true)
        
        router.put(route('admin.allowed-ips.update', allowedIp.id), formData, {
            onFinish: () => setProcessing(false)
        })
    }

    return (

            <div className="p-5">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Shield className="h-5 w-5" />
                                Edit IP Address
                            </CardTitle>
                            <CardDescription>
                                Modify the IP address settings
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-6">
                                <div className="grid gap-2">
                                    <Label htmlFor="ip_address">IP Address or CIDR Range</Label>
                                    <Input
                                        id="ip_address"
                                        placeholder="192.168.1.100 or 192.168.1.0/24"
                                        value={formData.ip_address}
                                        onChange={(e) => setFormData(prev => ({ ...prev, ip_address: e.target.value }))}
                                        required
                                    />
                                    <p className="text-sm text-gray-500">
                                        Examples: 192.168.1.100, 10.0.0.0/8, 172.16.0.0/12
                                    </p>
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="description">Description</Label>
                                    <Textarea
                                        id="description"
                                        placeholder="Office network, Admin home, etc."
                                        value={formData.description}
                                        onChange={(e) => setFormData(prev => ({ ...prev, description: e.target.value }))}
                                        rows={3}
                                    />
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Switch
                                        id="is_active"
                                        checked={formData.is_active}
                                        onCheckedChange={(checked) => setFormData(prev => ({ ...prev, is_active: checked }))}
                                    />
                                    <Label htmlFor="is_active">Active</Label>
                                    <p className="text-sm text-gray-500 ml-2">
                                        Only active IP addresses can access the application
                                    </p>
                                </div>

                                <div className="flex gap-2 pt-4">
                                    <Button type="submit" disabled={processing}>
                                        {processing ? 'Updating...' : 'Update IP'}
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => router.visit(route('admin.allowed-ips.index'))}
                                    >
                                        <ArrowLeft className="h-4 w-4 mr-2" />
                                        Back to List
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>

                    {/* IP Info */}
                    <Card className="mt-6">
                        <CardHeader>
                            <CardTitle>IP Information</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <strong>Created:</strong> {new Date(allowedIp.created_at).toLocaleString()}
                                </div>
                                <div>
                                    <strong>Updated:</strong> {new Date(allowedIp.updated_at).toLocaleString()}
                                </div>
                                <div>
                                    <strong>Created By:</strong> {allowedIp.created_by || 'System'}
                                </div>
                                <div>
                                    <strong>Last Used:</strong> {allowedIp.last_used_at ? new Date(allowedIp.last_used_at).toLocaleString() : 'Never'}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
            </div>
    )
}

Edit.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/admin' },
            { label: 'Pengaturan', href: '#' },
            { label: 'IP Access Control', href: '/admin/allowed-ips' },
            { label: 'Edit', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
