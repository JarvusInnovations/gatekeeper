<?php

printf('Flushing metrics...') && flush();

$flushed = Gatekeeper\Metrics\Metrics::flushMetricSamples();

printf('%u metrics flushed' . PHP_EOL, $flushed);