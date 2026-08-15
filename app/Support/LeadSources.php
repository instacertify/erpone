<?php

namespace App\Support;

final class LeadSources
{
    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            'google' => 'Google',
            'direct_call' => 'Direct Call',
            'lead_generated' => 'Lead Generated',
            'referral_existing_customer' => 'Referral (Existing Customer)',
            'indiamart' => 'IndiaMART',
            'consultant' => 'Consultant',
        ];
    }
}
