<?php
/*
Plugin Name: Client Form to RabbitMQ
Description: Form to collect client data and send to RabbitMQ
Version: 1.0
Author: Amghar Hamza
*/

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Functie om het admin menu item toe te voegen
 */
add_action('admin_menu', 'client_form_to_rabbitmq_menu');

function client_form_to_rabbitmq_menu() {
    add_menu_page('Client Form', 'Client Form', 'manage_options', 'client-form', 'client_form_page');
}

/**
 * Functie om de admin pagina weer te geven
 */
function client_form_page() {
    ?>
    <div class="wrap">
        <h2>Client Form</h2>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">First Name</th>
                    <td><input type="text" name="first_name" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Last Name</th>
                    <td><input type="text" name="last_name" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Email</th>
                    <td><input type="email" name="email" required /></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit_client_form" class="button-primary" value="Submit" />
            </p>
        </form>
    </div>
    <?php

    if (isset($_POST['submit_client_form'])) {
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);

        // Verstuur de gegevens naar RabbitMQ
        $sender = new RabbitSender();
        $sender->publish(json_encode(['first_name' => $first_name, 'last_name' => $last_name, 'email' => $email]));

        echo '<div class="updated"><p>Client data sent to RabbitMQ!</p></div>';
    }
}

/**
 * Class voor het versturen van berichten naar RabbitMQ
 */
class RabbitSender {
    private $connection;
    private $channel;

    public function __construct() {
        $this->connection = new AMQPStreamConnection('rabbitmq', 5672, 'user', 'password'); 
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare('wordpress_to_fossbilling_queue', false, false, false, false);
    }

    public function publish($message) {
        $msg = new AMQPMessage($message);
        $this->channel->basic_publish($msg, '', 'wordpress_to_fossbilling_queue');
    }

    public function __destruct() {
        $this->channel->close();
        $this->connection->close();
    }
}
?>
