# JSON-RPC Client (Laravel 5, Lumen 5)
This repository is a hard fork of tochka-developers/jsonrpc-client.
## Описание
JsonRpc клиент - реализация клиента для JsonRpc-сервера.
Работает по спецификации JsonRpc 2.0. Протестирован и работает с оригинальным сервером JsonRpc от Tochka.
## Установка
### Laravel
1. ``composer require tochka-developers/jsonrpc-client``
2. Если планируете использовать автоматическую генерацию прокси-клиента - необходимо подклюдчить сервис-провайдер в
в конфигурации приложения (`config/app.php`):
```php
'providers' => [
    //...
    Nbz4live\JsonRpc\Client\ServiceProvider::class,
],
```
3. Опубликуйте конфигурацию:  
```
php artisan vendor:publish --provider="Nbz4live\JsonRpc\Client\ServiceProvider"
```

### Lumen
1. ``composer require tochka-developers/jsonrpc-client``
2. Скопируйте конфигурацию из пакета (`vendor/tochka-developers/jsonrpc/config/jsonrpc.php`) в проект (`config/jsonrpc.php`)
3. Подключите конфигурацию в `bootstrap/app.php`:
```php
$app->configure('jsonrpc');
```
4. Включите поддержку фасадов в `bootstrap/app.php`:
```php
$app->withFacades();
```
5. Если планируете использовать автоматическую генерацию прокси-клиента - зарегистрируйте сервис-провайдер 
`Nbz4live\JsonRpc\Client\ServiceProvider` в `bootstrap/app.php`:
```php
$app->register(Nbz4live\JsonRpc\Client\ServiceProvider::class);
```
## Использование
### Настройка
Конфигурация находится в файле `app/jsonrpcclient.php`. 
В данном файле прописываются настройки для всех JsonRpc-подключений.
* `clientName` - Имя клиента. Данное имя будет подставляться в ID-всех запросов в виде префикса.
Позволяет идентифицировать сервис.
* `default` - подключение по умолчанию. Должно содержать имя подключения.
* `connections` - массив подключений. Каждое подключение должно иметь уникальный ключ (название подключения).
Настройки подключений:
* `url` - URL-адрес (или IP) для подключения к JsonRpc-серверу. Должен содержать полный путь к точке входа
(например: https://api.jsonrpc.com/v1/jsonrpc).
* `authHeaderName` - имя заголовка для передачи на сервер токена авторизации. Если на сервере не используется 
авторизация по заголовку - можно не указывать.
* `key` - токен авторизации. Если на сервере не используется авторизация по заголовку - можно не указывать.
* `clientClass` - класс, который используется в качестве прокси-класса. Необходимо указывать полное наименование 
(с пространством имен). Используется при автоматической генерации прокси-класса.
### Вызовы без прокси-класса
Вызов метода JsonRpc:
```php
use Nbz4live\JsonRpc\Client\Client;
//....
$result = Client::fooBar('Some text');
```
Если необходимо использовать конкретное подключение, используется метод `get`:
```php
$result = Client::get('api')->fooBar('Some text');
```
Если не указано конкретное подключение - используется подключение по умолчанию.

По умолчанию клиент передает все переданные в метод параметры в виде индексированного массива.
Если JsonRpc-сервер требует передачи именнованных параметров - воспользуйтесь методом `call`:
```php
$result = Client::get('api')->call('fooBar', ['text' => 'Some text']);
```
Клиент поддерживает вызов нескольких удаленных методов через один запрос:
```php
$api = Client::get('api')->batch();
$resultFoo = $api->foo('params');
$resultBar = $api->bar(123);
$resultSome = $api->call('someMethod', ['param1' => 1, 'param2' => true]);
$api->execute();
```
В указанном примере в переменных $resultFoo, $resultBar и $resultSome будет пустой класс `Nbz4live\JsonRpc\Client\Response`, 
пока не будет вызван метод `execute`. После этого будет осуществлен один запрос на JsonRpc-сервер, переменные 
заполнятся вернувшимися результатами с сервера.

Клиент поддерживает кеширование результатов с помощью метода `cache`:
```php
$result = Client::get('api')->cache(10)->fooBar('Some text');
```
При таком вызове результаты будут закешированы на 10 минут, и последующих вызовах этого метода с такими же параметрами - 
запрос на сервер не будет посылаться, результат будет сразу получаться из кеша. Естественно, результаты кешируются 
только для успешных вызовов. 

Также кеширование поддерживается и для нескольких вызовов:
```php
$api = Client::get('api')->batch();
$resultFoo = $api->cache(10)->foo('params');
$resultBar = $api->bar(123);
$resultSome = $api->cache(60)->call('someMethod', ['param1' => 1, 'param2' => true]);
$api->execute();
```
Учтите, что кешироваться будет только тот метод, перед которым был вызван `cache`. 

### Генерация прокси-класса
Прокси-класс - это наследник JsonRpcClient, который содержит информацию обо всех доступных методах
JsonRpc-сервера, а также сам делает маппинг параметров, переданных в метод, в виде ассоциативного массива.
Если сервер умеет возвращать SMD-схему, то такой класс может быть сгенерирован автоматически.

Для генерации класса воспользуйтесь командой:
```
php artisan jsonrpc:generateClient connection
```
Для успешной генерации должно выполняться несколько условий:
1. JsonRpc-сервер должын поддерживать возврат SMD-схемы (при передаче GET-параметра ?smd)
2. Желательно, чтобы в качестве сервера использовался `tochka-developers/jsonrpc`. Данный пакет умеет возвращать
расширенную информацию для более точной генерации прокси-класса
3. Должен быть прописан URL-адрес JsonRpc-сервера
4. Должно быть указано полное имя прокси-класса. Путь к файлу класса будет сгенерирован автоматически исходя из 
пространства имен и настроек `composer`.
5. Папка, в которой будет находиться прокси-класс, должна иметь иметь права на запись.

Если все указанные условия выполняются - то будет создан прокси-класс на указанное соединение.
Для обновления прокси-класса (в случае обновления методов сервера) - повторно вызовите указанную команду.
Если необходимо сгенерировать классы для всех указанных соединений - вызовите указанную команду без указания соединения:
```
php artisan jsonrpc:generateClient
```
### Вызовы через прокси-класс
Прокси-класс уже содержит информацию об используемом соединении, поэтому метод `get` вызывать не нужно.
Кроме того, прокси-класс сам реализует маппинг параметров, передаваемых в метод, в ассоциативный массив для
передачи  на JsonRpc-сервер. Реализация маппинга происходит только если JsonRpc-сервер использует ассоциативные 
параметры.

Примеры вызовов:
```php
// Single call
$result = Api::fooBar('Some text');

// Multiple call
$api = Api::batch();
$resultFoo = $api->cache(10)->foo('params');
$resultBar = $api->bar(123);
$resultSome = $api->cache(60)->someMethod(1, true);
$api->execute();
```