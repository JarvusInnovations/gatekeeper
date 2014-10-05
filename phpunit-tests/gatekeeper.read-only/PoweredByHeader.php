<?php

class PoweredByHeader extends PHPUnit_Framework_TestCase
{
    public function testPoweredByHeader()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('http://%s/api/test-api-status/v1', Site::getConfig('primary_hostname')));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        
        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);

        $this->assertRegExp('/^X-Powered-By:\s*'.preg_quote(ApiRequestHandler::$poweredByHeader).'\s*$/m', $header);
    }
}