import { useState } from 'react';
import { AlertCircleIcon, SpinnerIcon } from '@/components/ui/icons';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { api, ApiError } from '@/lib/api';
import type { Order } from '@/types';

type Props = {
    onFound: (order: Order, email: string) => void;
};

export function OrderLookupForm({ onFound }: Props) {
    const [email, setEmail] = useState('');
    const [orderNumber, setOrderNumber] = useState('');
    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState<string | null>(null);

    async function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        setProcessing(true);
        setError(null);

        try {
            const response = await api.post<{ data: Order }>('/api/orders/lookup', {
                email,
                order_number: orderNumber,
            });
            onFound(response.data, email);
        } catch (err) {
            setError(err instanceof ApiError ? err.message : 'Something went wrong. Please try again.');
        } finally {
            setProcessing(false);
        }
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <div className="grid gap-2">
                <Label htmlFor="email">Email address</Label>
                <Input
                    id="email"
                    type="email"
                    required
                    autoFocus
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="you@example.com"
                />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="order_number">Order number</Label>
                <Input
                    id="order_number"
                    required
                    value={orderNumber}
                    onChange={(e) => setOrderNumber(e.target.value)}
                    placeholder="WF-100001"
                />
            </div>

            {error && (
                <Alert variant="destructive">
                    <AlertCircleIcon />
                    <AlertDescription>{error}</AlertDescription>
                </Alert>
            )}

            <Button type="submit" disabled={processing} className="w-full">
                {processing && <SpinnerIcon className="size-4 animate-spin" />}
                Find my order
            </Button>
        </form>
    );
}
