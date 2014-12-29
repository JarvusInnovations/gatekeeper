<?php

namespace Gatekeeper\Alerts;

class BandwidthLimitApproached extends AbstractAlert
{
    public static $notificationTemplate = 'bandwidthLimitApproached';

    public static $validators = [
        'Endpoint' => 'require-relationship'
    ];
}