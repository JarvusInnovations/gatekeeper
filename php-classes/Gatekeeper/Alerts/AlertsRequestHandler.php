<?php

namespace Gatekeeper\Alerts;

use ActiveRecord;
use Gatekeeper\Endpoint;

class AlertsRequestHandler extends \RecordsRequestHandler
{
    public static $recordClass = AbstractAlert::class;
    public static $browseOrder = ['ID' => 'DESC'];

    public static function handleBrowseRequest($options = [], $conditions = [], $responseID = null, $responseData = [])
    {
        // apply status filter
        if (empty($_GET['status'])) {
            $status = 'open';
        } elseif ($_GET['status'] == 'any') {
            $status = null;
        } elseif (in_array($_GET['status'], AbstractAlert::getFieldOptions('Status', 'values'))) {
            $status = $_GET['status'];
        } else {
            $status = 'open';
        }

        if ($status) {
            $responseData['status'] = $conditions['Status'] = $status;
        }


        // apply endpoint filter
        if (!empty($_GET['endpoint']) && !empty($_GET['endpointVersion'])) {
            if (!$Endpoint = Endpoint::getByHandleAndVersion($_GET['endpoint'], $_GET['endpointVersion'])) {
                return static::throwNotFoundError('Endpoint not found');
            }
        } elseif (!empty($_GET['endpoint']) && ctype_digit($_GET['endpoint'])) {
            if (!$Endpoint = Endpoint::getByID($_GET['endpoint'])) {
                return static::throwNotFoundError('Endpoint not found');
            }
        }

        if (isset($Endpoint)) {
            $conditions['EndpointID'] = $Endpoint->ID;
            $responseData['Endpoint'] = $Endpoint;
        }

        return parent::handleBrowseRequest($options, $conditions, $responseID, $responseData);
    }

    public static function handleRecordRequest(ActiveRecord $Alert, $action = false)
    {
        switch ($action ?: $action = static::shiftPath()) {
            case 'acknowledge':
                return static::handleAcknowledgeRequest($Alert);
            case 'dismiss':
                return static::handleDismissRequest($Alert);
            case 'email-preview':
                return static::handleEmailPreviewRequest($Alert);
            default:
                return parent::handleRecordRequest($Alert, $action);
        }
    }

    public static function handleAcknowledgeRequest(AbstractAlert $Alert)
    {
        $GLOBALS['Session']->requireAccountLevel('Staff');

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return static::respond('confirm', [
                'question' => 'Are you sure you want to acknowledge alert <strong>'.htmlspecialchars($Alert->getTitle()).'</strong>?',
                'data' => $Alert
            ]);
        }

        $Alert->Acknowledger = $GLOBALS['Session']->Person;
        $Alert->save();

        return static::respond('alertAcknowledged', [
            'success' => true,
            'data' => $Alert
        ]);
    }

    public static function handleDismissRequest(AbstractAlert $Alert)
    {
        $GLOBALS['Session']->requireAccountLevel('Staff');

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return static::respond('confirm', [
                'question' => 'Are you sure you want to dismiss alert <strong>'.htmlspecialchars($Alert->getTitle()).'</strong>?',
                'data' => $Alert
            ]);
        }

        $Alert->Acknowledger = $GLOBALS['Session']->Person;
        $Alert->Status = 'dismissed';
        $Alert->save();

        return static::respond('alertDismissed', [
            'success' => true,
            'data' => $Alert
        ]);
    }

    public static function handleEmailPreviewRequest(AbstractAlert $Alert)
    {
        $GLOBALS['Session']->requireAccountLevel('Staff');

        $email = \Emergence\Mailer\AbstractMailer::renderTemplate('alerts/notifications/' . $Alert::$notificationTemplate, ['Alert' => $Alert]);

        \Debug::dumpVar($Alert->getData(), false, 'Alert');

        print '<h2>Email</h2>';
        print '<pre>';
        print 'From: ' . ($email['from'] ?: \Emergence\Mailer\AbstractMailer::getDefaultFrom()) . PHP_EOL;
        print 'Subject: ' . $email['subject'] . PHP_EOL . PHP_EOL;
        print htmlspecialchars($email['body']);
        print '</pre>';

        print '<h2>Preview</h2>';
        print '<div style="border: 1px dashed #ccc; width: 600px; font-family: arial,sans-serif; font-size: 80%; padding: 15px">' . $email['body'] . '</div>';
    }
}