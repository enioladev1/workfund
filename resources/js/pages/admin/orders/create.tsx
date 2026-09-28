import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { CustomerPicker } from '@/components/admin/CustomerPicker';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { AlertCircleIcon, PlusIcon, SpinnerIcon, XIcon } from '@/components/ui/icons';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import type { Customer } from '@/types';

type ItemForm = {
    name: string;
    sku: string;
    quantity: string;
    unit_price: string;
    is_final_sale: boolean;
};

const EMPTY_ITEM: ItemForm = { name: '', sku: '', quantity: '1', unit_price: '', is_final_sale: false };

const FIELD_LABELS: Record<string, string> = {
    customer_id: 'Customer',
    new_customer_name: 'New customer name',
    new_customer_email: 'New customer email',
    order_number: 'Order number',
    ordered_at: 'Ordered on',
    delivered_at: 'Delivered on',
    items: 'Items',
};

export default function AdminOrderCreate() {
    const [customerMode, setCustomerMode] = useState<'existing' | 'new'>('existing');
    const [customer, setCustomer] = useState<Customer | null>(null);
    const [newCustomer, setNewCustomer] = useState({ name: '', email: '', phone: '' });

    const [orderNumber, setOrderNumber] = useState('');
    const [currency, setCurrency] = useState('USD');
    const [isFinalSale, setIsFinalSale] = useState(false);
    const [orderedAt, setOrderedAt] = useState(() => new Date().toISOString().slice(0, 10));
    const [deliveredAt, setDeliveredAt] = useState('');

    const [items, setItems] = useState<ItemForm[]>([{ ...EMPTY_ITEM }]);
    const [processing, setProcessing] = useState(false);
    // Inertia gives one string message per field, not an array.
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [clientError, setClientError] = useState<string | null>(null);

    function updateItem(index: number, patch: Partial<ItemForm>) {
        setItems((current) => current.map((item, i) => (i === index ? { ...item, ...patch } : item)));
    }

    function addItem() {
        setItems((current) => [...current, { ...EMPTY_ITEM }]);
    }

    function removeItem(index: number) {
        setItems((current) => current.filter((_, i) => i !== index));
    }

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        setErrors({});
        setClientError(null);

        if (customerMode === 'existing' && !customer) {
            setClientError('Please search for a customer above and click their name to select them.');
            return;
        }

        if (customerMode === 'new' && (!newCustomer.name.trim() || !newCustomer.email.trim())) {
            setClientError('Please fill in the new customer’s name and email.');
            return;
        }

        setProcessing(true);

        router.post(
            '/admin/orders',
            {
                customer_id: customerMode === 'existing' ? customer?.id : undefined,
                new_customer_name: customerMode === 'new' ? newCustomer.name : undefined,
                new_customer_email: customerMode === 'new' ? newCustomer.email : undefined,
                new_customer_phone: customerMode === 'new' ? newCustomer.phone : undefined,
                order_number: orderNumber || undefined,
                currency,
                is_final_sale: isFinalSale,
                ordered_at: orderedAt,
                delivered_at: deliveredAt || undefined,
                items,
            },
            {
                onError: (validationErrors) => {
                    setErrors(validationErrors);

                    // If the server flagged the "new customer" fields, make sure that tab is
                    // visible instead of leaving the admin looking at an empty "existing" tab.
                    if (validationErrors.new_customer_name || validationErrors.new_customer_email) {
                        setCustomerMode('new');
                    }
                },
                onFinish: () => setProcessing(false),
            },
        );
    }

    const errorEntries = Object.entries(errors);

    return (
        <>
            <Head title="New order" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div>
                    <h1 className="text-lg font-semibold">New order</h1>
                    <p className="text-sm text-muted-foreground">
                        Create a synthetic order for testing the refund flow.
                    </p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {(clientError || errorEntries.length > 0) && (
                        <Alert variant="destructive">
                            <AlertCircleIcon />
                            <AlertTitle>This order could not be created</AlertTitle>
                            <AlertDescription>
                                {clientError ? (
                                    <p>{clientError}</p>
                                ) : (
                                    <ul className="list-inside list-disc">
                                        {errorEntries.map(([field, message]) => (
                                            <li key={field}>{FIELD_LABELS[field] ?? field}: {message}</li>
                                        ))}
                                    </ul>
                                )}
                            </AlertDescription>
                        </Alert>
                    )}

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Customer</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Tabs value={customerMode} onValueChange={(v) => setCustomerMode(v as 'existing' | 'new')}>
                                <TabsList>
                                    <TabsTrigger value="existing">Existing customer</TabsTrigger>
                                    <TabsTrigger value="new">New customer</TabsTrigger>
                                </TabsList>
                                <TabsContent value="existing" className="pt-4">
                                    <CustomerPicker selected={customer} onSelect={setCustomer} />
                                    {errors.customer_id && <p className="mt-1 text-sm text-destructive">{errors.customer_id}</p>}
                                </TabsContent>
                                <TabsContent value="new" className="grid gap-4 pt-4 sm:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label>Name</Label>
                                        <Input
                                            value={newCustomer.name}
                                            onChange={(e) => setNewCustomer((c) => ({ ...c, name: e.target.value }))}
                                        />
                                        {errors.new_customer_name && (
                                            <p className="text-sm text-destructive">{errors.new_customer_name}</p>
                                        )}
                                    </div>
                                    <div className="grid gap-2">
                                        <Label>Email</Label>
                                        <Input
                                            type="email"
                                            value={newCustomer.email}
                                            onChange={(e) => setNewCustomer((c) => ({ ...c, email: e.target.value }))}
                                        />
                                        {errors.new_customer_email && (
                                            <p className="text-sm text-destructive">{errors.new_customer_email}</p>
                                        )}
                                    </div>
                                    <div className="grid gap-2">
                                        <Label>Phone (optional)</Label>
                                        <Input
                                            value={newCustomer.phone}
                                            onChange={(e) => setNewCustomer((c) => ({ ...c, phone: e.target.value }))}
                                        />
                                    </div>
                                </TabsContent>
                            </Tabs>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Order details</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4 sm:grid-cols-2">
                            <div className="grid gap-2">
                                <Label>Order number (optional)</Label>
                                <Input
                                    value={orderNumber}
                                    onChange={(e) => setOrderNumber(e.target.value)}
                                    placeholder="Auto-generated if left blank"
                                />
                                {errors.order_number && <p className="text-sm text-destructive">{errors.order_number}</p>}
                            </div>
                            <div className="grid gap-2">
                                <Label>Currency</Label>
                                <Input value={currency} onChange={(e) => setCurrency(e.target.value.toUpperCase())} maxLength={3} />
                            </div>
                            <div className="grid gap-2">
                                <Label>Ordered on</Label>
                                <Input type="date" value={orderedAt} onChange={(e) => setOrderedAt(e.target.value)} />
                                {errors.ordered_at && <p className="text-sm text-destructive">{errors.ordered_at}</p>}
                            </div>
                            <div className="grid gap-2">
                                <Label>Delivered on (optional)</Label>
                                <Input type="date" value={deliveredAt} onChange={(e) => setDeliveredAt(e.target.value)} />
                                {errors.delivered_at && <p className="text-sm text-destructive">{errors.delivered_at}</p>}
                            </div>
                            <div className="flex items-center gap-2 sm:col-span-2">
                                <Checkbox checked={isFinalSale} onCheckedChange={(v) => setIsFinalSale(v === true)} id="order_final_sale" />
                                <Label htmlFor="order_final_sale" className="font-normal">
                                    Entire order is final sale
                                </Label>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Items</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {items.map((item, index) => (
                                <div key={index} className="grid gap-3 rounded-md border p-3 sm:grid-cols-5">
                                    <div className="grid gap-1.5 sm:col-span-2">
                                        <Label className="text-xs">Name</Label>
                                        <Input value={item.name} onChange={(e) => updateItem(index, { name: e.target.value })} />
                                    </div>
                                    <div className="grid gap-1.5">
                                        <Label className="text-xs">SKU</Label>
                                        <Input value={item.sku} onChange={(e) => updateItem(index, { sku: e.target.value })} />
                                    </div>
                                    <div className="grid gap-1.5">
                                        <Label className="text-xs">Qty</Label>
                                        <Input
                                            type="number"
                                            min={1}
                                            value={item.quantity}
                                            onChange={(e) => updateItem(index, { quantity: e.target.value })}
                                        />
                                    </div>
                                    <div className="grid gap-1.5">
                                        <Label className="text-xs">Unit price</Label>
                                        <Input
                                            value={item.unit_price}
                                            onChange={(e) => updateItem(index, { unit_price: e.target.value })}
                                            placeholder="49.99"
                                        />
                                    </div>
                                    <div className="flex items-center gap-2 sm:col-span-4">
                                        <Checkbox
                                            checked={item.is_final_sale}
                                            onCheckedChange={(v) => updateItem(index, { is_final_sale: v === true })}
                                            id={`item_final_sale_${index}`}
                                        />
                                        <Label htmlFor={`item_final_sale_${index}`} className="font-normal">
                                            Final sale
                                        </Label>
                                    </div>
                                    {items.length > 1 && (
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            className="justify-self-end sm:col-span-1"
                                            onClick={() => removeItem(index)}
                                        >
                                            <XIcon className="size-4" />
                                            Remove
                                        </Button>
                                    )}
                                </div>
                            ))}

                            <Button type="button" variant="outline" onClick={addItem}>
                                <PlusIcon className="size-4" />
                                Add item
                            </Button>
                        </CardContent>
                    </Card>

                    <Button type="submit" disabled={processing}>
                        {processing && <SpinnerIcon className="size-4 animate-spin" />}
                        Create order
                    </Button>
                </form>
            </div>
        </>
    );
}
