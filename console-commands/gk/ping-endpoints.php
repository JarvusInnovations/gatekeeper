<?php

$logger = $_COMMAND['LOGGER'];

$logger->info('Pinging endpoints...');

$pinged = Gatekeeper\Endpoints\Pinger::pingOverdueEndpoints($logger);

$logger->info("{$pinged} endpoints pinged");
