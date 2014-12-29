<?php

namespace Gatekeeper\Alerts;

class BandwidthLimitExceeded extends AbstractAlert
{
    public static $notificationTemplate = 'bandwidthLimitExceeded';

    public static $validators = [
        'Endpoint' => 'require-relationship'
    ];

    public function save($deep = true)
    {
        parent::save($deep);

        // check if there is an open BandwidthLimitApproached alert for the same endpoint and close it
        if ($this->isNew && $this->EndpointID && $this->Status == 'open') {
            $ApproachedAlert = BandwidthLimitApproached::getByWhere([
                'Class' => BandwidthLimitApproached::class,
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