<?php

namespace Gatekeeper\Alerts;

class RateLimitApproached extends AbstractAlert
{
    public static $notificationTemplate = 'rateLimitApproached';

    public static $relationships = [
        'Alert' => 'require-relationship'
    ];
}