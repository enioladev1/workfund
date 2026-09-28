<?php

/**
 * Default policy values. These seed the refund_policy_rules table, which is the
 * actual source of truth read by RefundPolicyService (cached in Redis). Nothing
 * in the policy engine reads these config values directly at runtime.
 */
return [
    'refund_window_days' => (int) env('REFUND_WINDOW_DAYS', 30),

    'human_review_threshold_minor' => (int) env('REFUND_HUMAN_REVIEW_THRESHOLD_MINOR', 50000),

    'final_sale_blocks_refund' => true,

    'currency' => env('REFUND_CURRENCY', 'USD'),

    'cache_ttl_seconds' => 3600,
];
