<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo " [*] clickhouse here\n";
//проверка чтения clickhouse
$config = [
    'host' => 'clickhouse',
    'port' => '8123',
    'username' => $_ENV['CLICKHOUSE_USERNAME'],
    'password' => $_ENV['CLICKHOUSE_PASSWORD'],
];
$db = new ClickHouseDB\Client($config);
$db->database('userdb');
$db->setTimeout(10);
$db->setConnectTimeOut(5);
$db->ping(true); // if can`t connect throw exception

echo " [*] showTables\n";
print_r($db->showTables());


//проверка чтения MariaDB
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
