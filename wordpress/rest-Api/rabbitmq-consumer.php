<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Configuration de RabbitMQ
$connection = new AMQPStreamConnection('rabbitmq', 5672, 'user', 'password');
$channel = $connection->channel();
$channel->queue_declare('foss_client_queue', false, false, false, false);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

$callback = function ($msg) {
    global $wpdb;
    $data = json_decode($msg->body, true);

    // Vérifiez l'action et insérez ou mettez à jour l'utilisateur dans WordPress
    if (isset($data['action']) && $data['action'] == 'create') {
        $table_name = $wpdb->prefix . 'clients';
        $wpdb->insert($table_name, [
            'name' => $data['first_name'],
            'email' => $data['email'],
            'created_at' => current_time('mysql')
        ]);
        echo " [x] User created: ", $data['first_name'], "\n";
    } elseif (isset($data['action']) && $data['action'] == 'update') {
        $table_name = $wpdb->prefix . 'clients';
        $wpdb->update($table_name, [
            'name' => $data['clientData']['first_name'],
            'email' => $data['clientData']['email'],
        ], ['id' => $data['clientId']]);
        echo " [x] User updated: ", $data['clientData']['first_name'], "\n";
    }
};

$channel->basic_consume('foss_client_queue', '', false, true, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
