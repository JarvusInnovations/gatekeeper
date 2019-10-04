<?php

namespace Gatekeeper\Reports;

class AbstractReportRequestHandler extends \RequestHandler
{
    public static $userResponseModes = [
        'application/json' => 'json',
        'text/csv' => 'csv'
    ];
}