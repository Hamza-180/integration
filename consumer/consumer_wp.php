<?php

/*


require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

$maxRetries = 5;
$retryDelay = 10; // secondes
$attempts = 0;

while ($attempts < $maxRetries) {
    try {
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'user', 'password');
        $channel = $connection->channel();

        $channel->queue_declare('foss_client_queue', false, false, false, false);
        $channel->queue_declare('foss_client_update_queue', false, false, false, false);
        $channel->queue_declare('foss_client_delete_queue', false, false, false, false);

        echo " [*] Waiting for messages. To exit press CTRL+C\n";

        $client = new Client();

        $callback = function ($msg) use ($client) {
            $data = json_decode($msg->body, true);
            $action = $data['action'];

            // Log des données reçues de RabbitMQ
            echo ' [x] Received data: ' . print_r($data, true) . "\n";

            try {
                $response = null;
                switch ($action) {
                    case 'create':
                        $response = $client->post('http://192.168.129.69:8081/wp-json/wp/v2/clients', [
                            'json' => [
                                'name' => $data['name'],  // Utilisez les champs exacts reçus
                                'email' => $data['email'],
                            ]
                        ]);
                        break;

                    case 'update':
                        $response = $client->post('http://192.168.129.69:8081/wp-json/wp/v2/clients/' . $data['id'], [
                            'json' => [
                                'name' => $data['name'],  // Utilisez les champs exacts reçus
                                'email' => $data['email'],
                            ]
                        ]);
                        break;

                    case 'delete':
                        $response = $client->delete('http://192.168.129.69:8081/wp-json/wp/v2/clients/' . $data['id']);
                        break;
                }

                if ($response) {
                    echo ' [x] Request Body: ' . json_encode($data) . "\n";
                    echo ' [x] Response: ' . $response->getBody() . "\n";
                }
            } catch (RequestException $e) {
                echo ' [!] Request Error: ' . $e->getMessage() . "\n";
                if ($e->hasResponse()) {
                    echo ' [!] Response Body: ' . $e->getResponse()->getBody() . "\n";
                }
            }

            echo ' [x] Processed message: ', $msg->body, "\n";
        };

        $channel->basic_consume('foss_client_queue', '', false, true, false, false, $callback);
        $channel->basic_consume('foss_client_update_queue', '', false, true, false, false, $callback);
        $channel->basic_consume('foss_client_delete_queue', '', false, true, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        break; // Si la connexion réussit, sortir de la boucle

    } catch (Exception $e) {
        $attempts++;
        if ($attempts >= $maxRetries) {
            echo "Error: ", $e->getMessage(), "\n";
        } else {
            echo "Attempt $attempts failed: ", $e->getMessage(), "\n";
            sleep($retryDelay); // Attendre avant de réessayer
        }
    }
} */