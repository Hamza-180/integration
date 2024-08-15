<?php
require_once './vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Define the interval in seconds
$interval = 10;

while (true) {
    echo "Running task at " . date('Y-m-d H:i:s') . "\n";
    sleep($interval);

    $host = 'rabbitmq'; // Change this to your RabbitMQ host
    $port = 5672;
    $user = 'user';
    $password = 'password';

    // Create a new connection
    try {
        $connection = new AMQPStreamConnection($host, $port, $user, $password);
        echo "Connected to RabbitMQ\n";
    } catch (Exception $e) {
        echo "Failed to connect to RabbitMQ: " . $e->getMessage() . "\n";
        continue;
    }

    $channel = $connection->channel();

    // Declare a queue
    $channel->queue_declare('wp_client_queue', false, true, false, false);

    // Function to handle the user creation
    function create_user_foss($data) {
        $url = "http://192.168.129.69:8090/api/admin/client/create";
        $data = array(
            "email" => $data['email'],
            "first_name" => $data['name'],
            "password" => "Student1"
        );
        $jsonData = json_encode($data);
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        $username = "admin";
        $password = "ceTQeItxoQNeJEKu3Yyd7hsHTf3lzDhl";
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

        // Set options for debugging
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch) . "\n";
        } else {
            echo "HTTP Code: " . $http_code . "\n";
            echo 'Response: ' . $response . "\n";
        }

        curl_close($ch);
    }

    // Function to handle the user deletion
    function delete_user_foss($data) {
        $url = "http://192.168.129.69:8090/api/admin/client/delete";
        $data = array(
            "id" => $data['id']
        );
        $jsonData = json_encode($data);
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        $username = "admin";
        $password = "ceTQeItxoQNeJEKu3Yyd7hsHTf3lzDhl";
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

        // Set options for debugging
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch) . "\n";
        } else {
            echo "HTTP Code: " . $http_code . "\n";
            echo 'Response: ' . $response . "\n";
        }

        curl_close($ch);
    }

    // Function to handle the user update
    function update_user_foss($data) {
        $url = "http://192.168.129.69:8090/api/admin/client/update";
        $data = array(
            "id" => $data['id'],
            "email" => $data['email'],
            "first_name" => $data['name']
        );
        $jsonData = json_encode($data);
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        $username = "admin";
        $password = "ceTQeItxoQNeJEKu3Yyd7hsHTf3lzDhl";
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

        // Set options for debugging
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch) . "\n";
        } else {
            echo "HTTP Code: " . $http_code . "\n";
            echo 'Response: ' . $response . "\n";
        }

        curl_close($ch);
    }

    // Callback function to handle messages
    $callback = function($msg) {
        echo 'Received ', $msg->body, "\n";
        $data = json_decode($msg->body, true);
        $action = $data['action'];
        switch ($action) {
            case 'create':
                create_user_foss($data);
                break;
            case 'delete':
                delete_user_foss($data);
                break;
            case 'update':
                update_user_foss($data);
                break;
            default:
                echo "Unknown action: $action\n";
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
