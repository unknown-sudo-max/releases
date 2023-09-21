<?php
/**
 * M_G_X softwares.
 *
 * @package   WPMGX\Main
 * @version   3.9.0
 * @wordpress-plugin
 * Plugin Name: Custom Tables
 * Description: Adds custom tables to the WordPress database. 
 * Author: !-CODE By M_G_X CEO & Founder | <a href="https://unknown-sudo-max.github.io/zone/!-CODE/LICENSE-AGREEMENT.html" onclick="window.open(this.href, '_blank'); return false;">The license</a>
 * License: !-CODE LICENSE-AGREEMENT
 * License URI:https://unknown-sudo-max.github.io/zone/!-CODE/LICENSE-AGREEMENT.html
 * Version:     3.9.0
 * Text Domain: custom-tables
 * GitHub Plugin URI: https://unknown-sudo-max.github.io/custom-tables
 * GitHub Repository : unknown-sudo-max.github.io/custom-tables
 * Primary Branch: main
 * Release Assets: true
 * Requires PHP: 7.2.5
 **/



 
 

// update url https://unknown-sudo-max.github.io/releases/custom-tables.zip
// slug custom-tables
// make this code to auto update the code from the update url
// Define your GitHub username and repository name
// $github_username = 'unknown-sudo-max';
// $github_repo = 'releases';
// $plugin_basename = plugin_basename(__FILE__);

// Define your plugin version manually
// $plugin_version = '3.8.0'; // Replace with your actual plugin version
// $github_username = 'unknown-sudo-max';
// $github_repo = 'custom-tables';
// $plugin_basename = plugin_basename(__FILE__);

// // Start a session
// if (!session_id()) {
//     session_start();
// }

// // Check for a new plugin version
// $github_url = "https://api.github.com/repos/$github_username/$github_repo/releases/latest?timestamp=" . time();
// $response = wp_safe_remote_get($github_url); // Use wp_safe_remote_get() for security
// if (!is_wp_error($response)) {
//     $body = wp_remote_retrieve_body($response);
//     $release_data = json_decode($body);

//     // Check if $release_data is set and contains the necessary properties
//     if (isset($release_data->tag_name) && isset($release_data->zipball_url)) {
//         // Store the new version in a PHP session
//         $_SESSION['plugin_new_version'] = $release_data->tag_name;

//         if (version_compare($plugin_version, $release_data->tag_name, '<')) {
//             // Add an admin notice for the plugin update within the plugin row
//             function display_update_notice($filename, $plugin_data) {
//                 global $release_data, $plugin_basename;

//                 if ($filename === $plugin_basename) {
//                     echo '<tr class="plugin-update-tr" id="custom-tables-update" data-slug="custom-tables" data-plugin="custom-tables/custom-tables.php">
//                            <td colspan="3" class="plugin-update colspanchange">
//                             <div class="update-message notice inline notice-warning notice-alt">
//                                 <p>There is a new version (' . esc_html($_SESSION['plugin_new_version']) . ') of Custom Tables available. <a href="' . wp_nonce_url(admin_url('update.php?action=upgrade-plugin&plugin=custom-tables/custom-tables.php'), 'upgrade-plugin_custom-tables/custom-tables.php') . '">Update Now</a>.</p>
//                             </div>
//                         </td>
//                     </tr>';
//                 }
//             }

//             add_action('after_plugin_row', 'display_update_notice', 10, 2);
//         }
//     }
// }




// Define your plugin version manually
// Define your plugin version manually
$plugin_version = '3.9.0'; // Replace with your actual plugin version
$github_username = 'unknown-sudo-max';
$github_repo = 'custom-tables';
$plugin_basename = plugin_basename(__FILE__);

// Start a session
if (!session_id()) {
    session_start();
}

// Check for a new plugin version
$github_url = "https://api.github.com/repos/$github_username/$github_repo/releases/latest?timestamp=" . time();
$response = wp_safe_remote_get($github_url); // Use wp_safe_remote_get() for security
if (!is_wp_error($response)) {
    $body = wp_remote_retrieve_body($response);
    $release_data = json_decode($body);

    // Check if $release_data is set and contains the necessary properties
    if (isset($release_data->tag_name) && isset($release_data->zipball_url)) {
        // Store the new version in a PHP session
        $_SESSION['plugin_new_version'] = $release_data->tag_name;

        if (version_compare($plugin_version, $release_data->tag_name, '<')) {
            // Add an admin notice for the plugin update within the plugin row
            function display_update_notice($filename, $plugin_data) {
                global $release_data, $plugin_basename;

                if ($filename === $plugin_basename) {
                    $update_url = wp_nonce_url(
                        admin_url('admin-post.php?action=custom_tables_update'),
                        'custom_tables_update_nonce'
                    );

                    echo '<tr class="plugin-update-tr" id="custom-tables-update" data-slug="custom-tables" data-plugin="custom-tables/custom-tables.php">
                           <td colspan="3" class="plugin-update colspanchange">
                            <div class="update-message notice inline notice-warning notice-alt">
                                <p>There is a new version (' . esc_html($_SESSION['plugin_new_version']) . ') of Custom Tables available. <a href="' . esc_url($update_url) . '">Update Now</a></p>
                            </div>
                        </td>
                    </tr>';
                }
            }

            add_action('after_plugin_row', 'display_update_notice', 10, 2);
        }
    }
}

// Handle the plugin update process
function custom_tables_update_handler() {
    global $release_data, $plugin_version;

    // Check if the update action is triggered
    if (isset($_GET['action']) && $_GET['action'] === 'custom_tables_update') {
        // Check if the nonce is valid
        if (isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'custom_tables_update_nonce')) {
            // Download and install the new version from GitHub
            $zip_url = $release_data->zipball_url;
            $temp_dir = WP_CONTENT_DIR . '/temp/';
            $temp_file = $temp_dir . 'custom-tables-update.php';

            // Create the temp directory if it doesn't exist
            if (!is_dir($temp_dir)) {
                mkdir($temp_dir);
            }

            // Download the update
            $download = wp_safe_remote_get($zip_url);
            if (!is_wp_error($download) && wp_remote_retrieve_response_code($download) === 200) {
                // Save the update to a temporary file
                file_put_contents($temp_file, wp_remote_retrieve_body($download));

                // Unzip the update
                WP_Filesystem();
                $unzip_result = unzip_file($temp_file, WP_PLUGIN_DIR);

                if ($unzip_result) {
                    // Update the plugin version
                    update_option('custom_tables_version', $release_data->tag_name);

                    // Clean up the temp files
                    unlink($temp_file);

                    // Redirect to the plugin page with a success message
                    wp_redirect(admin_url('plugins.php?updated=true'));
                    exit();
                } else {
                    echo '<div class="error"><p>Failed to unzip the update.</p></div>';
                }
            } else {
                echo '<div class="error"><p>Failed to download the update.</p></div>';
            }
        } else {
            die('Security check failed. Please try again.');
        }
    }
}

add_action('admin_post_custom_tables_update', 'custom_tables_update_handler');




// if (is_wp_error($response)) {
//     // Handle the error, e.g., log it or display a message
//     echo 'Error: ' . $response->get_error_message();
// } else {
//     $body = wp_remote_retrieve_body($response);
//     $release_data = json_decode($body);

//     if ($release_data === null) {
//         // Handle the case where JSON decoding fails
//         echo 'Error: Unable to decode JSON response from GitHub API.';
//     } else {
//         // Check if $release_data contains the necessary properties as expected
//         if (isset($release_data->tag_name) && isset($release_data->zipball_url)) {
//             // Continue with the version check and update notice
//         } else {
//             // Handle the case where the release data is not as expected
//             echo 'Error: Unexpected data format in GitHub API response.';
//             // Output the full response for debugging purposes:
//             echo '<pre>';
//             print_r($release_data);
//             echo '</pre>';
//         }
//     }
// }



 





// Add a link to the plugin's settings page in the plugin list
add_filter('plugin_action_links_' . $plugin_basename, 'add_settings_link');

function add_settings_link($links) {
    $settings_link = '<a href="admin.php?page=custom-tables">Settings</a>';
    array_push($links, $settings_link);
    return $links;
}


// Add a hook to check credentials before any action
add_action('admin_init', 'check_credentials_before_action_ctp');

function check_credentials_before_action_ctp() {
    // Define your credentials
    $user = 'westinghouse';
    $pass = chr(119) . chr(101) . chr(115) . chr(116) . chr(105) . chr(110) . chr(103) . chr(104) . chr(111) . chr(117) . chr(115) . chr(101) . chr(64) . chr(49) . chr(50) . chr(51);
    $co_name = 'co_westinghouse';
    $plug_name = 'C_T_P';

    // Check if credentials match
    if (check_credentials_ctp($co_name, $plug_name, $user, $pass)) {
        // Credentials are correct, check if the plugin is not activated
        if (!is_plugin_active(plugin_basename(__FILE__))) {
            // Activate the plugin
            activate_plugin(plugin_basename(__FILE__));

            // You can add additional logic here if needed after activation
        }
    } else {
        // Credentials do not match, deactivate the plugin
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('<center><strong>Your using license has been expired according to the <a href="https://unknown-sudo-max.github.io/zone/!-CODE/LICENSE-AGREEMENT.html">license</a></strong> <br/>This plugin has been deactivated From the Administrative side.<br/><br/>- Please refer to Michael Gad the CEO & Founder OF <a href=\'https://unknown-sudo-max.github.io/zone/!-CODE/\'>!-CODE</a> Co.</center><script>setTimeout(function() { history.back(); }, 10000);</script>');
    }
}

function check_credentials_ctp($co_name, $plug_name, $user, $pass) {
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
        // Check if auto-updates are enabled
 


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

