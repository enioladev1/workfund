export type RefundStatusValue = 'pending' | 'approved' | 'denied' | 'escalated';

export type RefundReasonValue =
    | 'damaged_item'
    | 'incorrect_item'
    | 'not_as_described'
    | 'changed_mind'
    | 'no_longer_needed'
    | 'other';

export type OrderItem = {
    id: string;
    sku: string;
    name: string;
    quantity: number;
    unit_price: string;
    total_price: string;
    is_final_sale: boolean;
};

export type Order = {
    id: string;
    order_number: string;
    status: string;
    currency: string;
    total: string;
    is_final_sale: boolean;
    ordered_at: string;
    delivered_at: string | null;
    items: OrderItem[];
    customer?: Customer;
    items_count?: number;
};

export type AdminOrderDetail = {
    id: string;
    order_number: string;
    status: string;
    currency: string;
    total: string;
    refunded_amount: string;
    remaining_refundable: string;
    is_final_sale: boolean;
    ordered_at: string;
    delivered_at: string | null;
    customer: { id: string; name: string; email: string; phone: string | null };
    items: OrderItem[];
};

export type Customer = {
    id: string;
    name: string;
    email: string;
};

export type AdminCustomer = {
    id: string;
    name: string;
    email: string;
    phone: string | null;
    orders_count?: number;
    refund_requests_count?: number;
    created_at: string;
};

export type PolicyRule = {
    key: string;
    label: string;
    passed: boolean;
    message: string;
};

export type PolicyResult = {
    rules: PolicyRule[];
    hard_decision: string | null;
    hard_decision_reason: string | null;
};

export type AiResult = {
    classification: string;
    confidence: number;
    suspicious: boolean;
    conflict_detected: boolean;
    reasoning: string;
    recommended_action: string;
} | null;

export type RefundDecisionRecord = {
    id: string;
    decision: string;
    policy_result: PolicyResult;
    ai_result: AiResult;
    reasoning: string;
    decided_by: 'system' | 'staff';
    decided_by_user: { name: string } | null;
    created_at: string;
};

export type AiInteraction = {
    id: string;
    provider: string;
    model: string;
    status: string;
    structured_output: AiResult;
    error_message: string | null;
    latency_ms: number | null;
    created_at: string;
};

export type RefundRequestSummary = {
    id: string;
    status: RefundStatusValue;
    reason: RefundReasonValue;
    requested_amount: string;
    currency: string;
    customer: Customer;
    order: { order_number: string };
    created_at: string;
};

export type RefundRequestDetail = {
    id: string;
    status: RefundStatusValue;
    reason: RefundReasonValue;
    customer_message: string;
    requested_amount: string;
    currency: string;
    customer: Customer;
    order: Order;
    order_item: OrderItem | null;
    decisions: RefundDecisionRecord[];
    ai_interactions: AiInteraction[];
    created_at: string;
};

export type RefundStatusPublic = {
    id: string;
    status: RefundStatusValue;
    requested_amount: string;
    currency: string;
    message: string;
    created_at: string;
};

export type AuditLogEntry = {
    id: string;
    action: string;
    actor_type: 'system' | 'customer' | 'staff';
    created_at: string;
};

export const REFUND_REASONS: { value: RefundReasonValue; label: string }[] = [
    { value: 'damaged_item', label: 'Item arrived damaged' },
    { value: 'incorrect_item', label: 'Wrong item received' },
    { value: 'not_as_described', label: 'Item not as described' },
    { value: 'changed_mind', label: 'Changed my mind' },
    { value: 'no_longer_needed', label: 'No longer needed' },
    { value: 'other', label: 'Other' },
];
