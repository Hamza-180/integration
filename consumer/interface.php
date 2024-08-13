<?php
/*
Plugin Name: Client Manager
Description: Manage clients and send data to RabbitMQ
Version: 1.0
Author: Amghar Hamza
*/

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Voeg menu en submenu toe
add_action('admin_menu', 'client_manager_menu');

function client_manager_menu() {
    add_menu_page('Client Manager', 'Client Manager', 'manage_options', 'client-manager', 'client_manager_overview_page');
    add_submenu_page('client-manager', 'Add Client', 'Add Client', 'manage_options', 'add-client', 'client_manager_add_page');
    add_submenu_page('client-manager', 'Edit Client', 'Edit Client', 'manage_options', 'edit-client', 'client_manager_edit_page');
}

// Overzichtspagina
function client_manager_overview_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'clients';
    $clients = $wpdb->get_results("SELECT * FROM $table_name");
    ?>
    <div class="wrap">
        <h2>Client Manager</h2>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?php echo esc_html($client->id); ?></td>
                        <td><?php echo esc_html($client->name); ?></td>
                        <td><?php echo esc_html($client->email); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=edit-client&id=' . $client->id); ?>">Edit</a> |
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?action=delete_client&id=' . $client->id), 'delete_client'); ?>" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Pagina voor het toevoegen van klanten
function client_manager_add_page() {
    if (isset($_POST['submit_client_form'])) {
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clients';
        $wpdb->insert($table_name, [
            'name' => $name,
            'email' => $email,
            'created_at' => current_time('mysql')
        ]);
        
        // Verstuur de gegevens naar RabbitMQ
        $sender = new RabbitSender();
        $sender->publish(json_encode(['action' => 'create', 'name' => $name, 'email' => $email]));
        
        echo '<div class="updated"><p>Client added and sent to RabbitMQ!</p></div>';
    }
    ?>
    <div class="wrap">
        <h2>Add Client</h2>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Name</th>
                    <td><input type="text" name="name" required /></td>
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
}

// Pagina voor het bewerken van klanten
function client_manager_edit_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'clients';
    $client_id = intval($_GET['id']);
    $client = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $client_id");
    
    if (isset($_POST['submit_client_form'])) {
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        
        $wpdb->update($table_name, [
            'name' => $name,
            'email' => $email
        ], ['id' => $client_id]);
        
        // Verstuur de bijgewerkte gegevens naar RabbitMQ
        $sender = new RabbitSender();
        $sender->publish(json_encode(['action' => 'update', 'id' => $client_id, 'name' => $name, 'email' => $email]));
        
        echo '<div class="updated"><p>Client updated and sent to RabbitMQ!</p></div>';
    }
    ?>
    <div class="wrap">
        <h2>Edit Client</h2>
        <form method="post" action="">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Name</th>
                    <td><input type="text" name="name" value="<?php echo esc_attr($client->name); ?>" required /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Email</th>
                    <td><input type="email" name="email" value="<?php echo esc_attr($client->email); ?>" required /></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit_client_form" class="button-primary" value="Submit" />
            </p>
        </form>
    </div>
    <?php
}

// Functie voor het versturen van verwijderde klantgegevens naar RabbitMQ
function send_deleted_user_to_rabbitmq($user_id) {
    $user = get_userdata($user_id);
    $user_data = array(
        'action' => 'delete',
        'ID' => $user->ID,
        'user_login' => $user->user_login,
        'user_email' => $user->user_email
    );

    // Verstuur de verwijderde gegevens naar RabbitMQ
    $sender = new RabbitSender();
    $sender->publish(json_encode($user_data));
}
add_action('delete_user', 'send_deleted_user_to_rabbitmq');

// Class voor het versturen van berichten naar RabbitMQ
class RabbitSender {
    private $connection;
    private $channel;

    public function __construct() {
        $this->connection = new AMQPStreamConnection('rabbitmq', 5672, 'user', 'password'); 
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare('wp_client_queue', false, false, false, false);
        $this->channel->queue_declare('wp_client_update_queue', false, false, false, false);
        $this->channel->queue_declare('wp_client_delete_queue', false, false, false, false);
    }

    public function publish($message) {
        $msg = new AMQPMessage($message);
        $action = json_decode($message, true)['action'];
        switch ($action) {
            case 'create':
                $this->channel->basic_publish($msg, '', 'wp_client_queue');
                break;
            case 'update':
                $this->channel->basic_publish($msg, '', 'wp_client_update_queue');
                break;
            case 'delete':
                $this->channel->basic_publish($msg, '', 'wp_client_delete_queue');
                break;
        }
    }

    public function __destruct() {
        $this->channel->close();
        $this->connection->close();
    }
}

// Functie om een aangepaste database tabel aan te maken
register_activation_hook(__FILE__, 'create_custom_db_table');

function create_custom_db_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'clients';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email varchar(100) NOT NULL UNIQUE,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Verwijder client actie
function client_manager_delete_client() {
    if (isset($_GET['action']) && $_GET['action'] == 'delete_client' && isset($_GET['id'])) {
        $client_id = intval($_GET['id']);
        if (wp_verify_nonce($_GET['_wpnonce'], 'delete_client')) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'clients';
            $wpdb->delete($table_name, ['id' => $client_id]);
            
            // Verstuur de verwijderde gegevens naar RabbitMQ
            $sender = new RabbitSender();
            $sender->publish(json_encode(['action' => 'delete', 'id' => $client_id]));
            
            wp_redirect(admin_url('admin.php?page=client-manager'));
            exit;
        }
    }
}
add_action('admin_init', 'client_manager_delete_client');

// REST API endpoints
add_action('rest_api_init', function () {
    register_rest_route('wp/v2', '/clients', [
        'methods' => 'POST',
        'callback' => 'create_client',
        
    ]);

    register_rest_route('wp/v2', '/clients/(?P<id>\d+)', [
        'methods' => 'POST',
        'callback' => 'update_client',
        
    ]);

    register_rest_route('wp/v2', '/clients/(?P<id>\d+)', [
        'methods' => 'DELETE',
        'callback' => 'delete_client',
        
    ]);
});

function create_client(WP_REST_Request $request) {
    $name = sanitize_text_field($request['name']);
    $email = sanitize_email($request['email']);

    if (empty($name) || empty($email)) {
        return new WP_Error('invalid_data', 'Name and email are required', array('status' => 422));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'clients';
    $wpdb->insert($table_name, [
        'name' => $name,
        'email' => $email,
        'created_at' => current_time('mysql')
    ]);

    return new WP_REST_Response(['status' => 'success'], 201);
}

function update_client(WP_REST_Request $request) {
    $id = (int) $request['id'];
    $name = sanitize_text_field($request['name']);
    $email = sanitize_email($request['email']);

    if (empty($name) || empty($email)) {
        return new WP_Error('invalid_data', 'Name and email are required', array('status' => 422));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'clients';
    $wpdb->update($table_name, [
        'name' => $name,
        'email' => $email
    ], ['id' => $id]);

    return new WP_REST_Response(['status' => 'success'], 200);
}

function delete_client(WP_REST_Request $request) {
    $id = (int) $request['id'];

    global $wpdb;
    $table_name = $wpdb->prefix . 'clients';
    $wpdb->delete($table_name, ['id' => $id]);

    return new WP_REST_Response(['status' => 'success'], 200);
}
?>
