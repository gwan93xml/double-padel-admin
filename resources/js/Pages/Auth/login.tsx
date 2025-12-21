
import ProcessingButton from '@/Components/ProcessingButton';
import FormGroup from '@/Components/ui/form-group';
import { Input } from '@/Components/ui/input';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
    group: string;
}
export default function Login({
    status,
}: LoginProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Log in" />
            {status && (
                <div className="mb-4 text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <form onSubmit={submit} >
                <FormGroup label='Email' error={errors.email}>
                    <Input
                        id="email"
                        type="text"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        onChange={(e) => setData('email', e.target.value)}
                    />
                </FormGroup>

                <FormGroup className="mt-4" label='Password' error={errors.password}>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        autoComplete="current-password"
                        onChange={(e) => setData('password', e.target.value)}
                    />
                </FormGroup>

               



                <div className="mt-4 flex items-center justify-end">
                    

                    <ProcessingButton
                        type='submit'
                        label='Login'
                        processingLabel='Logging in...'
                        processing={processing}
                        className="ms-4"
                    />
                </div>
            </form>
        </GuestLayout>
    );
}
