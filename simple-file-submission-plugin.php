<?php
/**
 * Plugin Name:       Simple file submission
 * Description:       Simple file submission plugin
 * Requires at least: 6.6
 * Requires PHP:      7.0
 * Version:           1.0.0
 * Author:            permafrost06
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       permafrost-file-submission-plugin
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

register_activation_hook(__FILE__, 'add_db_table');

add_action('admin_menu', function () {
    add_menu_page(
        'File Submissions', // Page title
        'File Submissions', // Menu title
        'manage_options', // Capability
        'file-submissions', // Menu slug
        'render_file_submissions_page', // Callback function
        'dashicons-media-text', // Icon
        20 // Position
    );
});

function render_file_submissions_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'file_submissions';
    $entries = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
    
    echo '<div class="wrap">';
    echo '<h1>File Submissions</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Files</th>
            </tr>
          </thead>';
    echo '<tbody>';
    
    foreach ($entries as $entry) {
        $files = [];
        if ($entry->file1) $files[] = '<a href="' . esc_url($entry->file1) . '" target="_blank">File 1</a>';
        if ($entry->file2) $files[] = '<a href="' . esc_url($entry->file2) . '" target="_blank">File 2</a>';
        if ($entry->file3) $files[] = '<a href="' . esc_url($entry->file3) . '" target="_blank">File 3</a>';
        $files_output = !empty($files) ? implode(', ', $files) : 'N/A';
        
        echo '<tr>
                <td>' . esc_html($entry->id) . '</td>
                <td>' . esc_html($entry->name) . '</td>
                <td>' . esc_html($entry->email) . '</td>
                <td>' . $files_output . '</td>
              </tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}

$lines = file(
    path_join(plugin_dir_path(__FILE__), '.env'),
    FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
);

foreach ($lines as $line_num => $line) {
    [$key, $value] = explode('=', $line, 2);
    $_ENV[$key] = $value;
}

add_shortcode('file_upload_form', function () {
    wp_enqueue_script(
        'file_upload_handler',
        plugins_url('index.js', __FILE__),
        false,
        '0.0.1',
        true
    );

    wp_enqueue_script(
        'cf-turnstile',
        'https://challenges.cloudflare.com/turnstile/v0/api.js'
    );

    wp_enqueue_script(
        'font-awesome',
        "https://kit.fontawesome.com/16d5298cef.js"
    );

    ob_start();
    include 'form.php';
    return ob_get_clean();
});

function verify_turnstile_token() {
    $token = $_POST['cf-turnstile-response'] ?? null;
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? null;

    $secretKey = $_ENV['cf_secretkey'];

    $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    $postData = [
        'secret' => $secretKey,
        'response' => $token,
        'remoteip' => $ip,
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    $response = curl_exec($ch);
    curl_close($ch);

    $outcome = json_decode($response, true);

    if (!$outcome['success']) {
        return false;
    }

    return true;
}

function checkName($name) {
    if (empty($name)) {
        return "Name cannot be empty";
    }

    $nameWords = explode(" ", $name);

    if (count($nameWords) < 2) {
        return "Full name must be provided (both firstname and lastname)";
    }

    if (count($nameWords) === 2 && trim($nameWords[1]) === "") {
        return "Full name must be provided (both firstname and lastname)";
    }

    return "valid";
}

function checkEmail($email) {
    if (empty($email)) {
        return "Email cannot be empty";
    }

    $emailParts = explode("@", $email);
    if (count($emailParts) < 2) {
        return "Invalid email";
    }

    $domainParts = explode(".", $emailParts[1]);
    if (count($domainParts) < 2 || end($domainParts) === "") {
        return "Invalid email";
    }

    return "valid";
}

function checkFiles($files) {
    $validExtensions = ["md", "docx"];
    $fileKeys = ['file1', 'file2', 'file3'];
    $providedFiles = [];

    foreach ($fileKeys as $key) {
        if (!empty($files[$key]['name'])) {
            $providedFiles[] = $files[$key];
        }
    }

    if (count($providedFiles) === 0) {
        return "No files provided";
    }

    foreach ($providedFiles as $file) {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (!in_array($extension, $validExtensions)) {
            return "One or more files provided have invalid file extensions. Allowed extensions are: .md and .docx";
        }
    }

    return "valid";
}

function handle_file_submission() {
    if (!verify_turnstile_token()) {
        return ['captchaError' => 'CAPTCHA failed. Please refresh the page and try again.'];
    };

    $errors = [];

    if (!isset($_FILES)){
        $errors['fileError'] = ['error' => 'File(s) not provided'];
    }

    if (!isset($_POST['name']) || $_POST['name'] === '') {
        $errors['nameError'] = ['error' => 'Name not provided'];
    }

    if (!isset($_POST['email']) || $_POST['email'] === '') {
        $errors['emailError'] = ['error' => 'Email not provided'];
    }

    if (count($errors) > 0) {
        return ['errors' => $errors];
    }

    $name = $_POST['name'];
    $email = $_POST['email'];
    $files = $_FILES;

    $nameValidation = checkName($name);
    $emailValidation = checkEmail($email);
    $fileValidation = checkFiles($files);

    if ($nameValidation !== "valid") {
        $errors['nameError'] = $nameValidation;
    }
    if ($emailValidation !== "valid") {
        $errors['emailError'] = $emailValidation;
    }
    if ($fileValidation !== "valid") {
        $errors['fileError'] = $fileValidation;
    }

    if (count($errors) > 0) {
        return ['errors' => $errors];
    }

    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    add_filter('sanitize_file_name',  function ($filename) {
        return $_POST['name'] . "-" . $_POST['email'] .
            "." . pathinfo($filename)['extension'];
    }, 10);

    $overrides = [
        'test_form' => false, 
        'test_type' => false, 
    ];

    $uploaded_files = [null, null, null];

    $file = $_FILES['file1'];
    $move = wp_handle_upload($file, $overrides);
    if (!$move || isset($move['error'])) {
        $errors['fileError'] = $move['error'];
    } else {
        $uploaded_files[0] = $move['url'];
    }

    if (isset($_FILES['file2'])) {
        $file = $_FILES['file2'];
        $move = wp_handle_upload($file, $overrides);
        if (!$move || isset($move['error'])) {
            $errors['fileError'] = $move['error'];
        } else {
            $uploaded_files[1] = $move['url'];
        }
    }

    if (isset($_FILES['file3'])) {
        $file = $_FILES['file3'];
        $move = wp_handle_upload($file, $overrides);
        if (!$move || isset($move['error'])) {
            $errors['fileError'] = $move['error'];
        } else {
            $uploaded_files[2] = $move['url'];
        }
    }

    if (count($errors) > 0) {
        return ['errors' => $errors];
    }

    global $wpdb;
    $wpdb->insert(
        "{$wpdb->prefix}file_submissions",
        [
            'name' => $name,
            'email' => $email,
            'file1' => $uploaded_files[0],
            'file2' => $uploaded_files[1],
            'file3' => $uploaded_files[2],
        ]
    );

    return ['success' => true];
}

function add_db_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $create_table_query = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}file_submissions` (
              `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `name` varchar(32) NOT NULL,
              `email` varchar(255) NOT NULL,
              `file1` varchar(255) NOT NULL,
              `file2` varchar(255),
              `file3` varchar(255)
            ) {$charset_collate};
    ";

    if (!function_exists('dbDelta')) {
      require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    }

    dbDelta($create_table_query);
}

add_action('rest_api_init', function () {
    register_rest_route( 'file-upload/v1', 'file-submission', array(
        'methods' => 'POST',
        'callback' => 'handle_file_submission',
        'permission_callback' => '__return_true',
    ));
});
