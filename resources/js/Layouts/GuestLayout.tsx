import { Card, CardContent, CardHeader } from '@/Components/ui/card';
import { Link, usePage } from '@inertiajs/react';
import { ActivitySquareIcon } from 'lucide-react';
import { PropsWithChildren } from 'react';

export default function Guest({ children }: PropsWithChildren) {
    const { setting } = usePage<any>().props;
    return (
        <div className="flex min-h-screen flex-col items-center  sm:justify-center sm:pt-0">
            <div>
                <Link href="/">
                    <img
                        src={`/assets/images/logo.png`}
                        className="flex h-32 items-center justify-center rounded-lg  text-sidebar-primary-foreground mb-3 bg-black p-4" />
                </Link>
            </div>
            <Card style={{ width : '350px'}}>
                <CardHeader></CardHeader>
                <CardContent>
                    {children}
                </CardContent>
            </Card>
        </div>
    );
}
