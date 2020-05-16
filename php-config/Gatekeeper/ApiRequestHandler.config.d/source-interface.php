<?php

/**
 * Change the network interface that Gatekeeper makes requests from
 *
 * - `false`: no override (let cURL choose)
 * - `null`: override automatically (use HTTP_HOST)
 * - String: any IP or hostname
 */
Gatekeeper\ApiRequestHandler::$sourceInterface = false;
