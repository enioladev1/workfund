<?php

namespace App\Mail;

use App\Models\RefundRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Queued so a slow or failing mail transport never blocks the refund
 * request/response cycle. Retries automatically; a permanent failure lands in
 * failed_jobs without affecting the (already-completed) refund decision.
 */
class RefundDecisionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function __construct(
        public readonly RefundRequest $refundRequest,
        public readonly string $customerMessage,
    ) {}

    public function build(): self
    {
        return $this
            ->subject($this->subjectForStatus())
            ->view('emails.refund-decision', [
                'refundRequest' => $this->refundRequest,
                // Never name this "message": Laravel's Mailer always injects its own
                // $message (the Symfony message wrapper) into every mail view, which
                // would silently shadow this and crash the render.
                'explanation' => $this->customerMessage,
            ]);
    }

    private function subjectForStatus(): string
    {
        return match ($this->refundRequest->status->value) {
            'approved' => 'Your refund request was approved',
            'denied' => 'An update on your refund request',
            'escalated' => 'Your refund request is being reviewed',
            default => 'An update on your refund request',
        };
    }
}
