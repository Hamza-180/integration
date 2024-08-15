<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use GuzzleHttp\Client;

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
    $channel->queue_declare('foss_client_queue', false, true, false, false);

    function create_user_wordpress($data) {
        $client = new Client();
        $url = "http://192.168.129.69:8081/wp-json/wp/v2/clients";

        $jsonData = [
            "name" => $data['name'],
            "email" => $data['email'],
        ];

        try {
            $response = $client->post($url, [
                'json' => $jsonData
            ]);

            echo "Response status: " . $response->getStatusCode() . "\n";
            echo "Response body: " . $response->getBody() . "\n";

            if ($response->getStatusCode() == 201) {
                echo "Action create completed for user: " . $data['email'] . "\n";
            } else {
                echo "Action create failed for user: " . $data['email'] . "\n";
                echo "Response: " . $response->getBody() . "\n";
            }
        } catch (Exception $e) {
            echo "Error processing create action: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
        }
    }

    function delete_user_wordpress($data) {
        if (!isset($data['client_id'])) {
            echo "Missing client_id in delete message\n";
            return;
        }

        $client = new Client();
        $url = "http://192.168.129.69:8081/wp-json/wp/v2/clients/" . $data['client_id'];

        try {
            $response = $client->delete($url);

            echo "Response status: " . $response->getStatusCode() . "\n";
            echo "Response body: " . $response->getBody() . "\n";

            if ($response->getStatusCode() == 200) {
                echo "Action delete completed for user: " . $data['client_id'] . "\n";
            } else {
                echo "Action delete failed for user: " . $data['client_id'] . "\n";
                echo "Response: " . $response->getBody() . "\n";
            }
        } catch (Exception $e) {
            echo "Error processing delete action: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
        }
    }

    function update_user_wordpress($data) {
        if (!isset($data['client_id'])) {
            echo "Missing client_id in update message\n";
            return;
        }

        $client = new Client();
        $url = "http://192.168.129.69:8081/wp-json/wp/v2/clients/" . $data['client_id'];

        $jsonData = [
            "name" => $data['name'],
            "email" => $data['email'],
        ];

        try {
            echo "Sending request to $url with data: " . json_encode($jsonData) . "\n";
            $response = $client->post($url, [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => $jsonData
            ]);

            echo "Response status: " . $response->getStatusCode() . "\n";
            echo "Response body: " . $response->getBody() . "\n";

            if ($response->getStatusCode() == 200) {
                echo "Action update completed for user: " . $data['client_id'] . "\n";
            } else {
                echo "Action update failed for user: " . $data['client_id'] . "\n";
                echo "Response: " . $response->getBody() . "\n";
            }
        } catch (Exception $e) {
            echo "Error processing update action: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
        }
    }

    $callback = function($msg) {
        echo 'Received ', $msg->body, "\n";
        $data = json_decode($msg->body, true);

        if (!isset($data['action'])) {
            echo "Missing action in message\n";
            return;
        }

        $action = $data['action'];
        echo "Processing action: $action\n";
        switch ($action) {
            case 'create':
                create_user_wordpress($data);
                break;
            case 'delete':
                delete_user_wordpress($data);
                break;
            case 'update':
                update_user_wordpress($data);
                break;
            default:
                echo "Unknown action: $action\n";
                break;
        }
        echo "Done\n";
    };

    $channel->basic_consume('foss_client_queue', '', false, true, false, false, $callback);

    while ($channel->is_consuming()) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();
}
