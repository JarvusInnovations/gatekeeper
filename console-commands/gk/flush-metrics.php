<?php

$logger = $_COMMAND['LOGGER'];

$logger->info('Flushing metrics');

$flushed = Gatekeeper\Metrics\Metrics::flushMetricSamples();

$logger->info("{$flushed} metrics flushed");
