import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import { OrderLookupForm } from '@/components/refunds/OrderLookupForm';
import { RefundDetailsForm } from '@/components/refunds/RefundDetailsForm';
import { RefundResult } from '@/components/refunds/RefundResult';
import { login } from '@/routes';
import type { Order, RefundStatusPublic } from '@/types';

type Step = { name: 'lookup' } | { name: 'details'; order: Order; email: string } | { name: 'result'; result: RefundStatusPublic };

export default function RefundRequestPage() {
    const [step, setStep] = useState<Step>({ name: 'lookup' });

    return (
        <>
            <Head title="Request a refund" />
            <div className="flex min-h-screen flex-col items-center bg-background px-4 py-12">
                <div className="w-full max-w-lg space-y-6">
                    <div className="space-y-1 text-center">
                        <h1 className="text-2xl font-semibold">Request a refund</h1>
                        <p className="text-sm text-muted-foreground">
                            Enter your order details below and we&apos;ll review your request right away.
                        </p>
                    </div>

                    {step.name === 'lookup' && (
                        <OrderLookupForm onFound={(order, email) => setStep({ name: 'details', order, email })} />
                    )}

                    {step.name === 'details' && (
                        <RefundDetailsForm
                            order={step.order}
                            email={step.email}
                            onSubmitted={(result) => setStep({ name: 'result', result })}
                            onBack={() => setStep({ name: 'lookup' })}
                        />
                    )}

                    {step.name === 'result' && (
                        <RefundResult result={step.result} onStartOver={() => setStep({ name: 'lookup' })} />
                    )}

                    <p className="text-center text-xs text-muted-foreground">
                        Support staff?{' '}
                        <Link href={login()} className="underline underline-offset-4">
                            Sign in
                        </Link>
                    </p>
                </div>
            </div>
        </>
    );
}
