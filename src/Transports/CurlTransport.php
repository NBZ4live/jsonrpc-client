<?php

namespace Nbz4live\JsonRpc\Client\Transports;

use Nbz4live\JsonRpc\Client\Exceptions\JsonRpcException;
use stdClass;

class CurlTransport implements JsonRpcTransport
{
    public function execute(string $serviceName, array $settings, array $requests, array $headers): ?stdClass
    {
        $request = \json_encode($requests);

        $curl = \curl_init($settings['host']);
        \curl_setopt($curl, CURLOPT_HEADER, false);
        \curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        \curl_setopt($curl, CURLOPT_POST, true);
        \curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        \curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        \curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

        $response = \curl_exec($curl);

        if ($response === false) {
            $message = \curl_error($curl);
            $code = \curl_errno($curl);
            \curl_close($curl);

            throw new JsonRpcException($message, $code);
        }

        \curl_close($curl);

        return \json_decode($response);
    }
}
