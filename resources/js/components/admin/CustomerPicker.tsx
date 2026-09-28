import { useEffect, useState } from 'react';
import { Input } from '@/components/ui/input';
import { CheckIcon, SearchIcon } from '@/components/ui/icons';
import { api } from '@/lib/api';
import type { Customer } from '@/types';

type Props = {
    selected: Customer | null;
    onSelect: (customer: Customer | null) => void;
};

export function CustomerPicker({ selected, onSelect }: Props) {
    const [query, setQuery] = useState('');
    const [results, setResults] = useState<Customer[]>([]);

    useEffect(() => {
        if (query.trim().length < 2) {
            setResults([]);
            return;
        }

        const timeout = setTimeout(async () => {
            try {
                const response = await api.get<{ data: Customer[] }>(`/api/admin/customers/search?q=${encodeURIComponent(query)}`);
                setResults(response.data);
            } catch {
                setResults([]);
            }
        }, 300);

        return () => clearTimeout(timeout);
    }, [query]);

    if (selected) {
        return (
            <div className="flex items-center justify-between rounded-md border p-3 text-sm">
                <div>
                    <div className="font-medium">{selected.name}</div>
                    <div className="text-xs text-muted-foreground">{selected.email}</div>
                </div>
                <button
                    type="button"
                    onClick={() => onSelect(null)}
                    className="text-xs text-muted-foreground underline underline-offset-4"
                >
                    Change
                </button>
            </div>
        );
    }

    return (
        <div className="space-y-2">
            <div className="relative">
                <SearchIcon className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    value={query}
                    onChange={(e) => setQuery(e.target.value)}
                    placeholder="Search customers by name or email..."
                    className="pl-9"
                />
            </div>
            {results.length > 0 && (
                <div className="max-h-48 overflow-y-auto rounded-md border">
                    {results.map((customer) => (
                        <button
                            key={customer.id}
                            type="button"
                            onClick={() => onSelect(customer)}
                            className="flex w-full items-center justify-between border-b p-2.5 text-left text-sm last:border-b-0 hover:bg-muted"
                        >
                            <div>
                                <div className="font-medium">{customer.name}</div>
                                <div className="text-xs text-muted-foreground">{customer.email}</div>
                            </div>
                            <CheckIcon className="size-4 shrink-0 opacity-0" />
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
