<?php

namespace App\Services\AI;

/**
 * Defense in depth against prompt injection. This heuristic scan runs server-side,
 * independently of whatever the AI itself reports, so a compromised or fooled model
 * cannot hide an injection attempt: if this detects one, the request is flagged
 * suspicious regardless of the AI's own suspicious/conflict_detected fields.
 */
class PromptInjectionGuard
{
    /**
     * @var list<string>
     */
    private const PATTERNS = [
        '/ignore\s+(all\s+)?(previous|prior|above|the)\s+instructions?/i',
        '/disregard\s+(the\s+)?(policy|instructions?|rules?)/i',
        '/you\s+are\s+now\s+(an?\s+)?(admin|administrator|system|developer)/i',
        '/reveal\s+(the\s+)?(system\s+prompt|api\s+key|secret|instructions?)/i',
        '/act\s+as\s+(an?\s+)?(admin|administrator|different|unfiltered)/i',
        '/override\s+(the\s+)?(policy|rules?|decision)/i',
        '/approve\s+(this|my)\s+refund\s+(regardless|no\s+matter|automatically)/i',
        '/\bsystem\s*:\s*/i',
        '/\[\s*system\s*\]/i',
        '/forget\s+(everything|all)\s+(you|above)/i',
        '/new\s+instructions?\s*:/i',
    ];

    public function detect(string $message): bool
    {
        foreach (self::PATTERNS as $pattern) {
            if (preg_match($pattern, $message) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Wraps untrusted content in a delimiter the model is told to treat as inert data.
     * The delimiter itself is not a security boundary, only a labeling convention
     * reinforced by the system prompt's explicit instructions.
     */
    public function wrapUntrusted(string $message): string
    {
        $sanitized = str_replace(['<<<CUSTOMER_MESSAGE_START>>>', '<<<CUSTOMER_MESSAGE_END>>>'], '', $message);

        return "<<<CUSTOMER_MESSAGE_START>>>\n{$sanitized}\n<<<CUSTOMER_MESSAGE_END>>>";
    }
}
