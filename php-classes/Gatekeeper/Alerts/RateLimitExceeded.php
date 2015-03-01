<?php

namespace Gatekeeper\Alerts;

class RateLimitExceeded extends AbstractAlert
{
    public static $notificationTemplate = 'rateLimitExceeded';
    public static $isFatal = true;

    public static $validators = [
        'Endpoint' => 'require-relationship'
    ];

    public function save($deep = true)
    {
        parent::save($deep);

        // check if there is an open RateLimitApproached alert for the same endpoint and close it
        if ($this->isNew && $this->EndpointID && $this->Status == 'open') {
            $ApproachedAlert = RateLimitApproached::getByWhere([
                'Class' => RateLimitApproached::class,
                'EndpointID' => $this->EndpointID,
                'Status' => 'open'
            ]);

            if ($ApproachedAlert) {
                $ApproachedAlert->Status = 'closed';
                $ApproachedAlert->save();
            }
        }
    }
}