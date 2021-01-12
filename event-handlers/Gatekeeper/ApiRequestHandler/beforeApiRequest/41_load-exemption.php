<?php

namespace Gatekeeper;

use Gatekeeper\Exemptions\Exemption;


$Exemption = Exemption::getForApiRequest($_EVENT['request']);

if ($Exemption) {
    $_EVENT['request']->setExemption($Exemption);
}
