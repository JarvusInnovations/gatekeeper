<?php

$memInfo = apcu_sma_info(true);

$totalBytes = $memInfo['seg_size'] * $memInfo['num_seg'];
$freeBytes = $memInfo['avail_mem'];
$responsesBytes = Cache::getIterator('/^response:/')->getTotalSize();
$applicationBytes = $totalBytes - $freeBytes - $responseBytes;

printf("total\t%u\n", $totalBytes);
printf("free\t%u\n", $freeBytes);
printf("responses\t%u\n", $responsesBytes);
printf("application\t%u\n", $applicationBytes);
