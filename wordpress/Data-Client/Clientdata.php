<?php
function create_custom_db_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'clients';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email varchar(100) NOT NULL UNIQUE,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        custom_1 VARCHAR(255) UNIQUE,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}