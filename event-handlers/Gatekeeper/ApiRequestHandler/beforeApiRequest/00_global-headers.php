<?php

if (ApiRequestHandler::$poweredByHeader) {
    header('X-Powered-By: '.ApiRequestHandler::$poweredByHeader);
}