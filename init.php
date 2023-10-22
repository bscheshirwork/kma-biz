<?php
sleep(2);

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

const INIT_FLAG = __DIR__ . '/runtime/init.flag';

if (!file_exists(INIT_FLAG)) {
    for ($attempt = 0; $attempt < 20; $attempt++) {
        try {
            $connection = new AMQPStreamConnection('rabbitmq', 5672, $_ENV['RABBITMQ_USER'], $_ENV['RABBITMQ_PASSWORD']);
        } catch (PhpAmqpLib\Exception\AMQPIOException $exception) {
            sleep(5);
        }
    }
    if (!isset($connection)){
        echo " [x] AMQPStreamConnection fail'\n";

        exit(1);
    }
    echo " [x] AMQPStreamConnection success'\n";

    $channel = $connection->channel();

    $channel->queue_declare('hello', false, false, false, false);

    $msg = new AMQPMessage('Hello World!');
    $channel->basic_publish($msg, '', 'hello');

    echo " [x] Sent 'Hello World!'\n";

    $channel->close();
    $connection->close();
    touch(INIT_FLAG);
}
