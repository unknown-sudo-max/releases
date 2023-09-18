<?php
/**
 * MGX Plugin.
 *
 * @package   WPMGX\Main
 * @version    3.8.0
 * @wordpress-plugin
 * Plugin Name: Custom Tables
 * Description: Adds custom tables to the WordPress database.
 * Author: !-CODE By M_G_X CEO & Founder | <a href="https://unknown-sudo-max.github.io/zone/!-CODE/LICENSE-AGREEMENT.html" onclick="window.open(this.href, '_blank'); return false;">The license</a>
 * License: !-CODE LICENSE-AGREEMENT
 * License URI:https://unknown-sudo-max.github.io/zone/!-CODE/LICENSE-AGREEMENT.html
 * Version:     3.8.0
 * Text Domain: custom-tables
 * Requires PHP: 7.2.5
 */



 


// Add a hook to check credentials before any action
add_action('admin_init', 'check_credentials_before_actionn');

function check_credentials_before_actionn() {
    //global $user, $pass, $co_name, $plug_name;
    // Define your credentials
$user = 'westinghouse';
$pass = chr(119) . chr(101) . chr(115) . chr(116) . chr(105) . chr(110) . chr(103) . chr(104) . chr(111) . chr(117) . chr(115) . chr(101) . chr(64) . chr(49) . chr(50) . chr(51);
$co_name =  'co_westinghouse';
$plug_name = 'C_T_P';

    // Check if credentials match
    if (!check_credentialss($co_name, $plug_name, $user, $pass)) {
        // Credentials do not match, deactivate the plugin
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('<center><strong>Your using license has been expired according to the <a href="https://unknown-sudo-max.github.io/zone/!-CODE/LICENSE-AGREEMENT.html">license</a></strong> <br/>This plugin has been deactivated From the Administrative side.<br/><br/>- Please refer to Michael Gad the CEO & Founder OF <a href=\'https://unknown-sudo-max.github.io/zone/!-CODE/\'>!-CODE</a> Co.</center><script>setTimeout(function() { history.back(); }, 10000);</script>');

    }
}

function check_credentialss($co_name, $plug_name, $user, $pass) {
    // Read the external text file line by line
    $file_url = 'https://unknown-sudo-max.github.io/hub/pass/pass';
    $file_contents = file_get_contents($file_url);
    $lines = explode("\n", $file_contents);

    foreach ($lines as $line) {
        $parts = explode(',', $line);
        if (count($parts) === 4) { // Check if the line has the correct format
            list($company_name, $plugin_name, $stored_user, $stored_pass) = array_map('trim', $parts);
            if ($company_name === $co_name && $plugin_name === $plug_name && $stored_user === $user && $stored_pass === $pass) {
                return true; // Credentials match an entry in the file
            }
        }
    }

    return false; // No match found
}



// Function to create custom tables on plugin activation
function create_custom_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Create first table wp_kwa
    $table_name1 = $wpdb->prefix . 'kwa';
    $sql1 = "CREATE TABLE $table_name1 (
        id INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        time_date DATETIME NOT NULL,
        device VARCHAR(255) NOT NULL,
        serial_number VARCHAR(50) NOT NULL,
        city VARCHAR(255) NOT NULL,
        issue VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql1);

    // Create second table wp_koncu
    $table_name2 = $wpdb->prefix . 'koncu';
    $sql2 = "CREATE TABLE $table_name2 (
        id INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        time_date DATETIME NOT NULL,
        device VARCHAR(255) NOT NULL,
        city VARCHAR(255) NOT NULL,
        issue VARCHAR(255) NOT NULL,
        address VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql2);
}

// Hook the table creation function to the plugin activation
register_activation_hook(__FILE__, 'create_custom_tables');

// Function to add a new tab in the WordPress dashboard menu
function add_custom_tables_menu() {
    add_menu_page(
        'Tables',
        'Tables',
        'manage_options',
        'custom-tables',
        'display_custom_tables',
        'dashicons-clipboard',
        20
    );
}

// Hook the menu creation function to the admin_menu action
add_action('admin_menu', 'add_custom_tables_menu');

/// Function to display the content of the custom tables in the dashboard
function display_custom_tables() {
    global $wpdb;
    $table_name1 = $wpdb->prefix . 'kwa';
    $table_name2 = $wpdb->prefix . 'koncu';

    // Retrieve data from the first table wp_kwa
    $results1 = $wpdb->get_results("SELECT * FROM $table_name1", ARRAY_A);

    // Retrieve data from the second table wp_koncu
    $results2 = $wpdb->get_results("SELECT * FROM $table_name2", ARRAY_A);

    // Display the data in a table format
    echo '<style>
        .custom-table {
            width: 100%;
            border-collapse: collapse;
        }
        .custom-table th,
        .custom-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .custom-table th {
            background-color: #f2f2f2;
        }
    </style>';

    echo '<h2>WP-Warranty-Activation</h2>';
    echo '<table class="custom-table">';
    echo '<tr><th>City</th><th>Name</th><th>Phone Number</th><th>Device</th><th>Issue</th><th>Serial Number</th><th>Date</th><th>Action</th></tr>';
    foreach ($results1 as $row) {
        echo '<tr>';
        echo '<td>' . @$row['city'] . '</td>';
        echo '<td>' . @$row['name'] . '</td>';
        echo '<td>' . @$row['phone'] . '</td>';
        echo '<td>' . @$row['device'] . '</td>';
        echo '<td>' . @$row['issue'] . '</td>';
        echo '<td>' . @$row['serial_number'] . '</td>';
        echo '<td>' . @$row['time_date'] . '</td>';
        echo '<td><a href="?action=delete&table=kwa&id=' . @$row['id'] . '">Delete</a></td>';
        echo '</tr>';
    }
    echo '</table>';
    echo "<br><hr><br>";

    echo '<h2>WP-Contact-Us</h2>';
    echo '<table class="custom-table">';
    echo '<tr><th>City</th><th>Name</th><th>Phone Number</th><th>Device</th><th>Issue</th><th>Address</th><th>Date</th><th>Action</th></tr>';
    foreach ($results2 as $row) {
        echo '<tr>';
        echo '<td>' . @$row['city'] . '</td>';
        echo '<td>' . @$row['name'] . '</td>';
        echo '<td>' . @$row['phone'] . '</td>';
        echo '<td>' . @$row['device'] . '</td>';
        echo '<td>' . @$row['issue'] . '</td>';
        echo '<td>' . @$row['address'] . '</td>';
        // echo '<td>' . @$row['serial_number'] . '</td>';
        echo '<td>' . @$row['time_date'] . '</td>';
        echo '<td><a href="?action=delete&table=koncu&id=' . @$row['id'] . '">Delete</a></td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '<p style="text-align:center;font-size:10px;user-select:none;">Developed & Powered BY M_G_X &copy;2023</p>';
}


// Handle the delete action
function handle_delete_action() {
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['table']) && isset($_GET['id'])) {
        $table = $_GET['table'];
        $id = $_GET['id'];

        global $wpdb;
        $table_name = $wpdb->prefix . $table;

        $wpdb->delete($table_name, array('id' => $id));

        // Redirect back to the custom tables page after deleting the row
        wp_redirect(admin_url('admin.php?page=custom-tables'));
        exit();
    }
}
add_action('admin_init', 'handle_delete_action');

