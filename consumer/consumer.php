<?php
require_once './vendor/autoload.php';
 
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

file_put_contents('php://stdout', "consumer.php started\n");

$interval = 10;
 
while (true) {
    echo "Running task at " . date('Y-m-d H:i:s') . "\n";
 
    sleep($interval);
 
    $host = 'rabbitmq'; 
    $port = 5672;
    $user = 'user';
    $password = 'password';
 
    try {
        $connection = new AMQPStreamConnection($host, $port, $user, $password);
        file_put_contents('php://stdout', "Connected to RabbitMQ\n");
    } catch (Exception $e) {
        file_put_contents('php://stdout', "Failed to connect to RabbitMQ: " . $e->getMessage() . "\n");
        continue;
    }

    $channel = $connection->channel();
 
    $channel->queue_declare('wp_client_queue', false, true, false, false);
 
    function create_user_foss($data){
        $url = "http://192.168.129.69:8090/api/admin/client/create";
 
        $data = array(
            "email" => $data['email'],
            "first_name" => $data['name'],
            "password" => "User12345"
        );
 
        $jsonData = json_encode($data);
 
        $ch = curl_init($url);
 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
 
        curl_setopt($ch, CURLOPT_USERPWD, "admin:5bQCJkzKkFeS39drmUc1mE2cfCIGcFWz");
 
        $response = curl_exec($ch);
 
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            echo 'Response:' . $response;
        }
 
        curl_close($ch);
    }
 
    $callback = function($msg) {
        echo 'Received ', $msg->body, "\n";
        $data = json_decode($msg->body, true);
        $action = $data['action'];
        switch ($action) {
            case 'create':
                create_user_foss($data);
                break;
            default:
                break;
        }
        echo "Done\n";
    };
 
    $channel->basic_consume('wp_client_queue', '', false, true, false, false, $callback);
 
    while ($channel->is_consuming()) {
        $channel->wait();
    }
 
    $channel->close();
    $connection->close();
}
