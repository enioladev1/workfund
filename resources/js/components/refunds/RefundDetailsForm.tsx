import { useState } from 'react';
import { RefundReasonSelector } from '@/components/refunds/RefundReasonSelector';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { AlertCircleIcon, PackageIcon, SpinnerIcon } from '@/components/ui/icons';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Textarea } from '@/components/ui/textarea';
import { api, ApiError } from '@/lib/api';
import type { Order, RefundReasonValue, RefundStatusPublic } from '@/types';

type Props = {
    order: Order;
    email: string;
    onSubmitted: (result: RefundStatusPublic) => void;
    onBack: () => void;
};

export function RefundDetailsForm({ order, email, onSubmitted, onBack }: Props) {
    const [orderItemId, setOrderItemId] = useState<string>(order.items.length === 1 ? order.items[0].id : 'whole-order');
    const [reason, setReason] = useState<RefundReasonValue | ''>('');
    const [message, setMessage] = useState('');
    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState<string | null>(null);

    async function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        setProcessing(true);
        setError(null);

        try {
            const response = await api.post<{ data: RefundStatusPublic }>('/api/refunds', {
                email,
                order_number: order.order_number,
                order_item_id: orderItemId === 'whole-order' ? null : orderItemId,
                reason,
                customer_message: message,
            });
            onSubmitted(response.data);
        } catch (err) {
            setError(err instanceof ApiError ? err.message : 'Something went wrong. Please try again.');
        } finally {
            setProcessing(false);
        }
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <PackageIcon className="size-4" />
                        Order {order.order_number}
                    </CardTitle>
                    <CardDescription>
                        {order.currency} {order.total} &middot; ordered {new Date(order.ordered_at).toLocaleDateString()}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Label className="mb-2 block">What would you like refunded?</Label>
                    <RadioGroup value={orderItemId} onValueChange={setOrderItemId}>
                        {order.items.length > 1 && (
                            <div className="flex items-center gap-2">
                                <RadioGroupItem value="whole-order" id="whole-order" />
                                <Label htmlFor="whole-order" className="font-normal">
                                    Entire order ({order.currency} {order.total})
                                </Label>
                            </div>
                        )}
                        {order.items.map((item) => (
                            <div key={item.id} className="flex items-center gap-2">
                                <RadioGroupItem value={item.id} id={item.id} />
                                <Label htmlFor={item.id} className="font-normal">
                                    {item.name} ({order.currency} {item.total_price})
                                    {item.is_final_sale && ' (final sale)'}
                                </Label>
                            </div>
                        ))}
                    </RadioGroup>
                </CardContent>
            </Card>

            <RefundReasonSelector value={reason} onChange={setReason} />

            <div className="grid gap-2">
                <Label htmlFor="customer_message">Tell us what happened</Label>
                <Textarea
                    id="customer_message"
                    required
                    minLength={5}
                    maxLength={2000}
                    rows={5}
                    value={message}
                    onChange={(e) => setMessage(e.target.value)}
                    placeholder="Describe what happened with your order..."
                />
            </div>

            {error && (
                <Alert variant="destructive">
                    <AlertCircleIcon />
                    <AlertDescription>{error}</AlertDescription>
                </Alert>
            )}

            <div className="flex gap-3">
                <Button type="button" variant="outline" onClick={onBack} disabled={processing}>
                    Back
                </Button>
                <Button type="submit" disabled={processing || !reason} className="flex-1">
                    {processing && <SpinnerIcon className="size-4 animate-spin" />}
                    Submit refund request
                </Button>
            </div>
        </form>
    );
}
