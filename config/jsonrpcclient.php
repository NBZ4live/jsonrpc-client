<?php

return [
    // Имя клиента. Используется в качестве префикса к ID запросов
    'clientName' => 'prodam',

    // Соединение по умолчанию
    'default' => 'api',

    /*
     * Add additional headers to all requests.
     * Key: header name
     * Value: header value or callable
     */
    'additionalHeaders' => [],

    'transport_class' => env('JSONRPC_TRANSPORT_CLASS', Nbz4live\JsonRpc\Client\Transports\CurlTransport::class),

    // Список соединений
    'connections' => [
        // Наименование соединения
        'api' => [
            // URL-адрес JsonRpc-сервера
            'url' => 'https://api.jsonrpc.com/v1/jsonrpc',
            // Токен авторизации. Если не используется - можно не указывать
            'key' => 'ToKeN12345',
            // Заголовок авторизации. Если не используется - можно не указывать
            'authHeaderName' => 'X-Access-Key',
            /*
             * Add additional headers to requests of this connection.
             */
            'additionalHeaders' => [],
            // Имя прокси-класса для данного соединения
            'clientClass' => '\\App\\Api\\Client'
        ]
    ]
];
