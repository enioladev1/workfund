import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { REFUND_REASONS } from '@/types';
import type { RefundReasonValue } from '@/types';

export function RefundReasonSelector({
    value,
    onChange,
}: {
    value: RefundReasonValue | '';
    onChange: (value: RefundReasonValue) => void;
}) {
    return (
        <div className="grid gap-2">
            <Label htmlFor="reason">Reason for refund</Label>
            <Select value={value} onValueChange={(v) => onChange(v as RefundReasonValue)}>
                <SelectTrigger id="reason" className="w-full">
                    <SelectValue placeholder="Select a reason" />
                </SelectTrigger>
                <SelectContent>
                    {REFUND_REASONS.map((reason) => (
                        <SelectItem key={reason.value} value={reason.value}>
                            {reason.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );
}
