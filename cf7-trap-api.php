<?php
/**
 * Plugin Name: CF7 Form Trap - Rekry Group
 * Plugin URI: https://github.com/tyoharjoittelija/rg-likeit-link
 * Author: Harjoittelija, Rekry Group Oy
 * Description: A custom plugin for handling job application form data from Wordpress and forwards it to Likeit API.
 * Version: 1.1.0
 */

defined('ABSPATH') or die('Unauthorized access!');

require __DIR__ . '/vendor/autoload.php';   // Include the Composer autoload file
use Ramsey\Uuid\Uuid;                       // Import the UUID library
use Firebase\JWT\JWT;                       // Import JWT library

// check likeit_key
if (!defined('LIKEIT_KEY')) {
    error_log('API key not defined in configuration');
    return;
}
// check likeit_url
if (!defined('LIKEIT_URL')) {
    error_log('API url not defined in configuration');
    return;
}
// check the first path of the url
if (!defined('PATH_START')) {
    error_log('API path start not defined in configuration');
    return;
}

$api_base_url = LIKEIT_URL;   
$api_secret = LIKEIT_KEY;
$path_start = PATH_START;
$api_user = explode('-', $api_secret)[0];   // First part of Api Key

/** This function generates encoded jwt tokens for request headers */
function generate_jwt_token($path, $method, $queryStr, $uri) {  // y is the $uri there tho?
    global $api_secret, $api_user;
    
    $payload = array(
        "htm" => $method,
        "sub" => $api_user,
        "htu" => $path,                     // /rekrygrouptest/api/.../...
        "exp" => time() + 300,              // 300 = 5 minutes
        "iat" => time(),                    // time now
        "jti" => Uuid::uuid1()->toString(), // Using Ramsey UUID
        "querystr" => $queryStr                    
    );

    $jwt = JWT::encode($payload, $api_secret, 'HS256');

    return $jwt;
}

/* Sets headers for HTTP requests. Adds content-type if needed */
function set_header($jwt, $content_type = null) {
    $headers = [ 'Authorization: Bearer ' . $jwt ];

    if ($content_type) {
        $headers[] = 'Content-Type: ' . $content_type; // add content type if given (in PUT requests. GET requests don't need one)
    }

    return $headers;
}

/* This function forwards the form data to the Like it API. */
function likeit_api_put($path, $data) {
    global $api_base_url, $api_secret, $api_user, $path_start;
    $method = 'put';
    $queryStr = '';
    $uri = $api_base_url . $path;

    // get jwt token
    $jwt = generate_jwt_token($path, $method, $queryStr, $uri);

    // Set headers
    $headers = set_header($jwt);

    // JSON encode the data
    $json_data = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    // Initialize cURL
    $ch = curl_init($api_base_url . $path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

    // Execute cURL request
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log('cURL error: ' . curl_error($ch));
    }
    curl_close($ch);

    return $response;
}

/* This function checks if the email received from the form already exists
   in the system. If a match is found the found applicant's id and Created date is returned. */
function check_applicant_exists($email) {
    global $api_base_url, $api_secret, $path_start;
    $method = 'get';
    $path = $path_start . '/resources';
    $queryStr = 'Email=' . urlencode($email);
    $uri = $api_base_url . $path . $queryStr;

    // get jwt token
    $jwt = generate_jwt_token($path, $method, $queryStr, $uri);

    // Set headers
    $headers = set_header($jwt, 'Content-Type: application/json');

    // Initialize cURL
    $ch = curl_init($api_base_url . $path . '?' . $queryStr);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Execute cURL request
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log('cURL error: ' . curl_error($ch));
        return null;
    }
    curl_close($ch);
    
    // Decode the JSON response
    $responseData = json_decode($response, true);

    if (isset($responseData['Data'][0]['Id'])) { // $responseData['Data'][0] accesses the first element of the Data field of the response data.
        $id = $responseData['Data'][0]['Id'];
        $created = $responseData['Data'][0]['Created'];
        return [$id, $created];
    } else {
        return null;
    }
}

// Encodes the submitted files into base64
function encode_file_to_base64($file_path) {
    if (file_exists($file_path)) {
        $file_contents = file_get_contents($file_path);
        return base64_encode($file_contents);
    } else {
        error_log("File not found: $file_path");
        return '';
    }
}

// Helper function to get the MIME type of a file
function get_mime_type($file_path) {
    if (file_exists($file_path)) {
        $filetype = wp_check_filetype($file_path);
        return $filetype['type'];
    } else {
        error_log("File not found: $file_path");
        return '';
    }
}

// Helper function to get the current time in the correct format
function get_current_time_iso8601() {
    $tz = new DateTimeZone('Europe/Helsinki');
    $now = new DateTime('now', $tz);
    $formatted_time = $now->format('Y-m-d\TH:i:s.u'); // Include microseconds
    $offset = $now->format('P'); // Get timezone offset
    return $formatted_time . substr_replace($offset, ':', -2, 0); // Insert colon in timezone offset
}

function get_cfdb7_uploads_path() {
    // Get the uploads directory
    $upload_dir = wp_upload_dir();

    // Construct the path to the cfdb7_uploads directory
    $cfdb7_uploads_path = $upload_dir['basedir'] . '/cfdb7_uploads';

    return $cfdb7_uploads_path;
}

add_action( 'cfdb7_before_save', 'cf7_data' ); // run cf7_data() -method when "before_save" action happens
add_action('cfdb7_after_save_data', 'handle_after_save', 10, 1); // run handle_after_save() after data is saved

/* This is the function that receives the submitted forms and then 
   prepares the data received from them to be sent to the Likeit API. 
   This function does NOT make any API calls.
   It only catches the data from form before submission and
   then calls another function (likeit_api_put) with the received data as params */
function cf7_data( $form_data ) {
    global $api_base_url, $api_secret, $api_user, $path_start, $wpdb, $likeit_api_response_status, $cfdb7_form_data;
    $photo_filepath = null;
    $application_filepath = null;
    $cv_filepath = null;

    // Sanitize form data
    $form_name = sanitize_text_field($form_data['form-name'] ?? '');
    $email = sanitize_email($form_data['your-email'] ?? '');
    $advert_id = sanitize_text_field($form_data['advert-id'] ?? '');

    //error_log("form_name: " . $form_name . " email: " . $email . " advert_id: " . $advert_id);

    $existingApplicant = check_applicant_exists($email); // check if the applicant already exists in the system

    // if an applicant is found, we use the existing applicant's id. Otherwise id = 0
    // we also reuse the 'Created' date value of the found applicant (if exists)
    if ($existingApplicant) {
        $applicantId = $existingApplicant[0];
        $applicantCreated = $existingApplicant[1];
    } else {
        $applicantId = 0;
        $applicantCreated = get_current_time_iso8601();
    }

    $path = $path_start . '/adverts' . '/' . $advert_id . '/applicants';

    // finds location for log file and names the log file
    $cfdb7_dirname = get_cfdb7_uploads_path();
    $log_file = $cfdb7_dirname . '/cf7_data_log.txt';

    // Construct file paths and sanitize filenames
    if (!empty($form_data['your-photocfdb7_file'])) {
        $photo_filepath = $cfdb7_dirname . '/' . basename(sanitize_file_name($form_data['your-photocfdb7_file']));
    }
    if (!empty($form_data['your-applicationcfdb7_file'])) {
        $application_filepath = $cfdb7_dirname . '/' . basename(sanitize_file_name($form_data['your-applicationcfdb7_file']));
    }
    if (!empty($form_data['your-cvcfdb7_file'])) {
        $cv_filepath = $cfdb7_dirname . '/' . basename(sanitize_file_name($form_data['your-cvcfdb7_file']));
    }

    // extract data from form submission and prepare & sanitize it for API
    $data = [
        'Id'=> $applicantId,
        'FirstName' => sanitize_text_field($form_data['your-firstname'] ?? ''),
        'LastName' => sanitize_text_field($form_data['your-lastname'] ?? ''),
        'Email' => $email,
        'MobileNumber' => sanitize_text_field($form_data['your-tel'] ?? ''),
        'Address' => sanitize_text_field($form_data['your-address'] ?? ''),
        'City' => sanitize_text_field($form_data['your-city'] ?? ''),
        'Application' => sanitize_textarea_field($form_data['your-message'] ?? ''),
        'IsActive' => true,
        'Name' => sanitize_text_field($form_data['your-lastname'] ?? '') . ', ' . sanitize_text_field($form_data['your-firstname'] ?? ''),
        'Created' => $applicantCreated,
        'Modified' => get_current_time_iso8601(),
    ];

    // Conditionally add the Photo field
    if (isset($form_data['your-photocfdb7_file']) && !empty($form_data['your-photocfdb7_file'])) {
        $photo_filepath = $cfdb7_dirname . '/' . basename($form_data['your-photocfdb7_file']);
        if (file_exists($photo_filepath)) {
            $data['Photo'] = [
                'Name' => basename($form_data['your-photocfdb7_file']),
                'DataType' => get_mime_type($photo_filepath),
                'DataSize' => filesize($photo_filepath),
                'Base64Data' => encode_file_to_base64($photo_filepath)
            ];
        }
    }

    // Conditionally add the ApplicationDocument field
    if (isset($form_data['your-applicationcfdb7_file']) && !empty($form_data['your-applicationcfdb7_file'])) {
        $application_filepath = $cfdb7_dirname . '/' . basename($form_data['your-applicationcfdb7_file']);
        if (file_exists($application_filepath)) {
            $data['ApplicationDocument'] = [
                'Name' => basename($form_data['your-applicationcfdb7_file']),
                'DataType' => get_mime_type($application_filepath),
                'Base64Data' => encode_file_to_base64($application_filepath)
            ];
        }
    }

    // Conditionally add the CVDocument1 field
    if (isset($form_data['your-cvcfdb7_file']) && !empty($form_data['your-cvcfdb7_file'])) {
        $cv_filepath = $cfdb7_dirname . '/' . basename($form_data['your-cvcfdb7_file']);
        if (file_exists($cv_filepath)) {
            $data['CVDocument1'] = [
                'Name' => basename($form_data['your-cvcfdb7_file']),
                'DataType' => get_mime_type($cv_filepath),
                'Base64Data' => encode_file_to_base64($cv_filepath)
            ];
        }
    }

    // Wrap in array
    $data = [$data];

    // Call the API with the path and data
    $response = likeit_api_put($path, $data);

    // Decoding json resp
    $responseData = json_decode($response, true);

    // Store the resp status in a global variable
    $likeit_api_response_status = isset($responseData['Status']) ? $responseData['Status'] : 'Failed';

    // Check for a successful response before deleting files
    if (isset($responseData['Status']) && $responseData['Status'] === 'Success') {
        unset($data); // remove data after forwarding to API
        // Delete the uploaded files if they exist
        if ($photo_filepath && file_exists($photo_filepath)) {
            unlink($photo_filepath);
        }
        if ($application_filepath && file_exists($application_filepath)) {
            unlink($application_filepath);
        }
        if ($cv_filepath && file_exists($cv_filepath)) {
            unlink($cv_filepath);
        }
    } else {
        error_log('API request failed, not deleting uploaded files.');
    }
}

/* This function handles the logic after form data has been saved. 
   It deletes data from the database after it has been sent to the API as that data is
   no longer needed by the WordPress website. Data is also deleted for security reasons
   it is not recommended to store sensitive data when it's no longer needed. */
function handle_after_save($entry_id) {
    global $wpdb, $likeit_api_response_status;

    // check if the resp status is success before deleting the created entry
    if ($likeit_api_response_status === 'Success') {
        // delete the form entry from the database
        $table_name = $wpdb->prefix . 'db7_forms';
        $wpdb->delete($table_name, array('form_id' => $entry_id));
    } else {
        error_log('API request failed or response status is not success, not deleting form entry.');
    }
}
?>

