<?php

$Alert = $_EVENT['Record'];

if (
    $Alert->isFieldDirty('Status') &&
    $Alert->Endpoint &&
    ($emailTo = $Alert->Endpoint->getNotificationEmailRecipient())
) {
    \Emergence\Mailer\Mailer::sendFromTemplate($emailTo, 'alerts/notifications/' . $Alert::$notificationTemplate, ['Alert' => $Alert]);
}