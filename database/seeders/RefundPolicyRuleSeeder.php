<?php

namespace Database\Seeders;

use App\Models\RefundPolicyRule;
use Illuminate\Database\Seeder;

class RefundPolicyRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            'refund_window_days' => [
                'value' => config('refund_policy.refund_window_days'),
                'description' => 'Orders older than this many days cannot be refunded.',
            ],
            'human_review_threshold_minor' => [
                'value' => config('refund_policy.human_review_threshold_minor'),
                'description' => 'Refund requests above this amount (in minor currency units) require human review.',
            ],
            'final_sale_blocks_refund' => [
                'value' => config('refund_policy.final_sale_blocks_refund'),
                'description' => 'Whether final-sale items are ineligible for refunds.',
            ],
        ];

        foreach ($rules as $key => $rule) {
            RefundPolicyRule::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $rule, 'description' => $rule['description']],
            );
        }
    }
}
