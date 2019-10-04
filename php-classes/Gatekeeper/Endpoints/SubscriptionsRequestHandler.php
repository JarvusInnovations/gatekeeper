<?php

namespace Gatekeeper\Endpoints;

class SubscriptionsRequestHandler extends \RequestHandler
{
    public static $userResponseModes = [
        'application/json' => 'json',
        'text/csv' => 'csv'
    ];

    public static function handleRequest()
    {
        $GLOBALS['Session']->requireAuthentication();

        if ($endpointHandle = static::shiftPath()) {
            if (!$Endpoint = Endpoint::getByHandle($endpointHandle)) {
                return static::throwNotFoundError('Endpoint not found');
            }

            return static::handleEndpointRequest($Endpoint);
        }

        return static::respond('subscriptions', [
            'data' => Subscription::getAllByField('PersonID', $GLOBALS['Session']->PersonID)
        ]);
    }

    public static function handleEndpointRequest(Endpoint $Endpoint)
    {
        $Subscription = $Endpoint->getSubscription($GLOBALS['Session']->Person);

        if (!$Subscription && ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'PUT')) {
            $Subscription = Subscription::create([
                'EndpointID' => $Endpoint->ID,
                'PersonID' => $GLOBALS['Session']->PersonID
            ], true);

            return static::respond('subscriptionCreated', [
                'data' => $Subscription,
                'success' => true
            ]);
        } elseif ($Subscription && $_SERVER['REQUEST_METHOD'] == 'DELETE') {
            $Subscription->destroy();
            $Subscription = null;

            return static::respond('subscriptionDeleted', [
                'data' => $Subscription,
                'success' => true
            ]);
        }

        return static::respond('subscription', [
            'data' => $Subscription
        ]);
    }
}