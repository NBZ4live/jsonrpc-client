<?php

namespace Nbz4live\JsonRpc\Client\Transports;

use Nbz4live\JsonRpc\Client\Exceptions\JsonRpcException;
use stdClass;

interface JsonRpcTransport
{
    /**
     * Calls the Rpc method
     *
     * @param string $serviceName The remote service name
     * @param array $settings Client settings
     * @param array $requests Array of requests
     * @param array $headers Array of request headers
     *
     * @return stdClass|null Rpc response
     *
     * @throws JsonRpcException
     */
    public function execute(string $serviceName, array $settings, array $requests, array $headers): ?stdClass;
}
