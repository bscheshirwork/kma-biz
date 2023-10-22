<?php

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


//проверка чтения из очереди
try {
    $connection = new AMQPStreamConnection('rabbitmq', 5672, $_ENV['RABBITMQ_USER'], $_ENV['RABBITMQ_PASSWORD']);
} catch (PhpAmqpLib\Exception\AMQPIOException $exception) {
    exit(1);
}
$channel = $connection->channel();

$channel->queue_declare('hello', false, false, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";


echo " [*] clickhouse here\n";
//проверка записи clickhouse
$config = [
    'host' => 'clickhouse',
    'port' => '8123',
    'username' => $_ENV['CLICKHOUSE_USERNAME'],
    'password' => $_ENV['CLICKHOUSE_PASSWORD'],
];
$db = new ClickHouseDB\Client($config);
$db->database($_ENV['CLICKHOUSE_DATABASE']);
$db->setTimeout(10);       // 10 seconds
$db->setConnectTimeOut(5); // 5 seconds

for ($attempt = 0; $attempt < 20; $attempt++) {
    try {
        $db->ping(true); // if can`t connect throw exception
    } catch (ClickHouseDB\Exception\TransportException $exception) {
        sleep(5);
    }
}
$db->ping(true);
echo " [*] showTables\n";
print_r($db->showTables());

echo " [*] MariaDB\n";

$dsn = "mysql:host=mariadb;dbname=" . $_ENV['MARIADB_DATABASE'] . ";charset=utf8mb4";
$pdoConnection = new PDO($dsn, $_ENV['MARIADB_USER'], $_ENV['MARIADB_PASSWORD']);

try {
    $result = $pdoConnection->query("SHOW TABLES");
    $mass = $result->fetchAll(PDO::FETCH_ASSOC);
    print_r($mass);
} catch (Exception $e) {
    throw new PDOException(var_export($pdoConnection->errorInfo(), true));
}



$callback = function ($msg) use ($db, $pdoConnection) {
    echo ' [x] Received ', $msg->getBody(), "\n";
    //обработка
    $msg->ack();
};

$channel->basic_consume('hello', '', false, true, false, false, $callback);

try {
    $channel->consume();
} catch (\Throwable $exception) {
    echo $exception->getMessage();
}

$channel->close();
$connection->close();


