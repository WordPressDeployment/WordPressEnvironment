<?php

/**
 * Plugin name: data plugin with display shortcode
 * Description: Plugin for transferring Data
 * Version: 1.0.26
 * Text Domain: data_plugin
 * Author: CC dev
 */

class webService
{
    public function __construct()
    {
        add_shortcode('datatable', array($this, 'datatable_shortcode'));
        //experimental area
        add_shortcode('db_display', 'display_database_table');
        add_action('wp_enqueue_scripts', 'enqueue_ajax_refresh_scripts');
        add_action('wp_ajax_refresh_table_ajax', 'refresh_table_ajax_callback');
        add_action('wp_ajax_nopriv_refresh_table_ajax', 'refresh_table_ajax_callback'); // For non-logged in users
        add_action('wp_head', 'output_custom_css');
        add_action('plugins_loaded', 'create_custom_table');
        add_shortcode('custom_login_form', 'custom_login_form_shortcode');
        //experimental area
        add_shortcode('wp_enqueue_scripts', array($this, 'enqueue_listener'));
        // add_action('wp_enqueue_scripts', array($this, 'enqueue_listener'));
        add_action('wp_ajax_insert_socket_data', array($this, 'insert_socket_data'));
        add_action('wp_ajax_nopriv_insert_socket_data', array($this, 'insert_socket_data'));
    }



    public function enqueue_listener()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('socket-listener', plugins_url('includes/listener.js', __FILE__), array('jquery'), '1.0.0', true);
        wp_enqueue_script('socket-io', 'https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.4.1/socket.io.js', array(), null, true);
        wp_localize_script('socket-listener', 'socket_listener_ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
    }


    public function insert_socket_data()
    {

        if (isset($_POST['sysUUID'], $_POST['lastrowid'], $_POST['sid'], $_POST['score'], $_POST['timestamp'], $_POST['duration'], $_POST['ts'])) {
            global $wpdb;

            // Extract values from the form data
            $sysUUID = $_POST['sysUUID'];
            $lastrowid = $_POST['lastrowid'];
            $sid = $_POST['sid'];
            $score = $_POST['score'];
            $timestamp = $_POST['timestamp'];
            $duration = $_POST['duration'];
            $ts = $_POST['ts'];

            // Insert data into the "ms_cleandata" table
            $wpdb->insert(
                $wpdb->prefix . 'ms_cleandata',
                array(
                    'sysUUID' => $sysUUID,
                    'lastrowid' => $lastrowid,
                    'sid' => $sid,
                    'score' => $score,
                    'timestamp' => $timestamp,
                    'duration' => $duration,
                    'ts' => $ts,
                )
            );

            // Prepare data to return to the client
            $response_data = array(
                'success' => true,
                'message' => 'Data inserted successfully',
            );

            // Return the JSON response
            wp_send_json_success($response_data);
        } else {
            // Prepare error response for missing data
            $response_data = array(
                'success' => false,
                'message' => 'Invalid or missing data',
            );

            // Return the JSON error response
            wp_send_json_error($response_data);
        }
    }
}



class DataPlugin //class for the plugin to API
{
    private $api_endpoint;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'linkOption'));

        $this->api_endpoint = 'https://infoapi-buox.onrender.com';
    }

    public function linkOption()
    {
        add_options_page('Plugin to API', 'Plugin to API Options', 'manage_options', 'data_plugin_options', array($this, 'wporg_options_page'));
    }

    public function wporg_options_page()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->process_form_data();
        }

        // Fetch data when the settings page is loaded
        $results = $this->get_data_from_api();

?>
        <div class="wrap">
            <h1><?php esc_html_e('Plugin to API', 'data_plugin'); ?></h1>

            <form method="post" action="">
                <label for="name"><?php esc_html_e('API name:', 'data_plugin'); ?></label>
                <input type="text" name="name" required>
                <br>

                <label for="ID"><?php esc_html_e('Unique ID:', 'data_plugin'); ?></label>
                <input type="text" name="ID" required>
                <br>

                <label for="url"><?php esc_html_e('Base URL:', 'data_plugin'); ?></label>
                <input type="url" name="url" required>
                <br>

                <?php submit_button(__('Submit', 'data_plugin')); ?>
            </form>

            <h2><?php esc_html_e('Stored Data', 'data_plugin'); ?></h2>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('API name', 'data_plugin'); ?></th>
                        <th><?php esc_html_e('Unique ID', 'data_plugin'); ?></th>
                        <th><?php esc_html_e('Base URL', 'data_plugin'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($results as $result) {
                        echo '<tr>';
                        echo '<td>' . esc_html($result['name']) . '</td>';
                        echo '<td>' . esc_html($result['id']) . '</td>';
                        echo '<td>' . esc_url($result['url']) . '</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>

        </div>
    <?php
    }

    private function process_form_data()
    {
        $name = sanitize_text_field($_POST['name']);
        $ID = sanitize_text_field($_POST['ID']);
        $url = esc_url($_POST['url']);

        $api_data = array(
            'name' => $name,
            'id' => $ID,
            'url' => $url,
        );

        $response = wp_remote_post($this->api_endpoint, array(
            'body' => json_encode($api_data),
            'headers' => array('Content-Type' => 'application/json'),
        ));

        if (is_wp_error($response)) {
            echo 'Error: ' . $response->get_error_message();
        } else {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            // Check if the API response indicates success
            if (isset($data['success']) && $data['success'] === true) {
                echo 'Data submitted successfully!';
            } else {
                echo 'Error: ' . $data['message']; // Display the error message from the API
            }
        }
    }


    private function get_data_from_api()
    {
        $response = wp_remote_get($this->api_endpoint);

        // Check if the request was unsuccessful
        if (is_wp_error($response)) {
            echo 'Error: ' . $response->get_error_message();
            return array(); // Return an empty array in case of an error
        }

        // Check if the response code is not successful (2xx)
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code < 200 || $response_code >= 300) {
            echo 'Error: Unexpected response code ' . $response_code;
            return array(); // Return an empty array in case of an error
        }

        // Check if the body is a non-empty string
        $body = wp_remote_retrieve_body($response);
        if (!is_string($body) || empty($body)) {
            echo 'Error: Empty or invalid response body';
            return array(); // Return an empty array in case of an error
        }

        // Decode the JSON response
        $json_data = json_decode($body, true);

        // Check if decoding was successful
        if ($json_data === null) {
            echo 'Error: Unable to decode JSON response';
            return array(); // Return an empty array in case of an error
        }

        // Check if the API response indicates success
        if (isset($json_data['success']) && $json_data['success'] === true && isset($json_data['data'])) {
            return $json_data['data'];
        } else {
            echo 'Error: Invalid API response format';
            return array(); // Return an empty array in case of an error
        }
    }
}


class DataTableShortcode //pokemon class
{
    private $api_endpoint;

    public function __construct()
    {
        $this->api_endpoint = 'https://pokeapi.co/api/v2/pokemon?limit=100000&offset=0';
        add_shortcode('datatable', array($this, 'datatable_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_datatable_scripts'));
        add_action('wp_ajax_get_datatable_data', array($this, 'get_datatable_data'));
        add_action('wp_ajax_nopriv_get_datatable_data', array($this, 'get_datatable_data'));
        add_action('wp_ajax_get_pokemon_details', array($this, 'get_pokemon_details'));
        add_action('wp_ajax_nopriv_get_pokemon_details', array($this, 'get_pokemon_details'));
    }

    public function datatable_shortcode($atts)
    {
        ob_start();
    ?>
        <table id="datatable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th><?php esc_html_e('Pokemon Name', 'data_plugin'); ?></th>
                    <th><?php esc_html_e('Pokemon Details', 'data_plugin'); ?></th>
                </tr>
            </thead>
        </table>
        <!-- Bootstrap Modal for Pokemon Details -->
        <div class="modal fade" id="pokemonModal" tabindex="-1" role="dialog" aria-labelledby="pokemonModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pokemonModalLabel"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p id="abilitiesContent"></p>
                        <p id="statsContent"></p>
                        <p id="typesContent"></p>
                        <p id="weightContent"></p>
                        <img id="pokemonImage" src="" alt="Pokemon Image" style="max-width: 100%; display: none;">
                    </div>
                </div>
            </div>
        </div>


<?php
        return ob_get_clean(); //
    }

    public function enqueue_datatable_scripts()
    {
        wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
        wp_enqueue_style('datatable-css', 'https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css');
        wp_enqueue_style('pokemon-styles', plugin_dir_url(__FILE__) . 'includes/templates/style.css'); // Add your custom style here
        wp_enqueue_script('jquery');
        wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery'), '4.5.2', true);
        wp_enqueue_script('datatable-js', 'https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js', array('jquery'), '1.10.25', true);
        wp_enqueue_script('datatable-script', plugin_dir_url(__FILE__) . 'includes/datatable-script.js', array('jquery', 'datatable-js', 'bootstrap-js'), '1.0.0', true);
        wp_localize_script('datatable-script', 'datatable_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    }

    public function get_datatable_data()
    {
        $response = wp_remote_get($this->api_endpoint);

        if (is_wp_error($response)) {
            wp_send_json_error('Error: ' . $response->get_error_message());
        } else {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            wp_send_json_success($data['results']);
        }
    }

    public function get_pokemon_details()
    {
        $pokemon_name = sanitize_text_field($_POST['name']);

        if (empty($pokemon_name)) {
            wp_send_json_error('Error: Pokemon name is empty.');
        }

        $api_url = 'https://pokeapi.co/api/v2/pokemon/' . strtolower($pokemon_name);

        $response = wp_safe_remote_get($api_url);

        if (is_wp_error($response)) {
            wp_send_json_error('Error: ' . $response->get_error_message());
        }

        $http_status = wp_remote_retrieve_response_code($response);

        if ($http_status !== 200) {
            wp_send_json_error('Error: Unable to fetch Pokemon details. HTTP Status Code: ' . $http_status);
        }

        $pokemon_data = json_decode(wp_remote_retrieve_body($response), true);

        if ($pokemon_data) {
            $abilities = array_map(
                function ($ability) {
                    return $ability['ability']['name'];
                },
                $pokemon_data['abilities']
            );

            $stats = array_map(
                function ($stat) {
                    return array(
                        'name' => $stat['stat']['name'],
                        'base_stat' => $stat['base_stat'],
                        'effort' => $stat['effort'],
                    );
                },
                $pokemon_data['stats']
            );

            $types = array_map(
                function ($type) {
                    return $type['type']['name'];
                },
                $pokemon_data['types']
            );

            $pokemon_details = array(
                'name' => $pokemon_data['name'],
                'abilities' => $abilities,
                'stats' => $stats,
                'types' => $types,
                'weight' => $pokemon_data['weight'],
                'image_url' => $pokemon_data['sprites']['other']['official-artwork']['front_default'] ?? null,
            );

            error_log(print_r($pokemon_details, true));

            wp_send_json_success($pokemon_details);
        } else {
            wp_send_json_error('Error: Unable to fetch Pokemon details');
        }
    }
}


$web_service = new webService();
$data_plugin = new DataPlugin();
$dataTableShortcode = new DataTableShortcode();



// Function to display the database table
function display_database_table($atts) {
    // Shortcode attributes (if any)
    $atts = shortcode_atts(
        array(
            'table_name' => 'ms_cleandata', // Default table name
            'columns' => '*', // Default: all columns
        ),
        $atts
    );

    global $wpdb;

    // Query to fetch data from the specified table
    $query = "SELECT {$atts['columns']} FROM {$wpdb->prefix}{$atts['table_name']} ORDER BY timestamp DESC";
    $results = $wpdb->get_results($query);

    // If there are no results, return a message
    if (empty($results)) {
        return "No data found.";
    }

    // Start building the table HTML
    $html = '<div class="db-table-container" style="max-height: 400px; overflow-y: auto;">'; // Set max-height and overflow-y for scrollable content
    $html .= '<table class="sticky-header">';
    $html .= '<thead><tr>';
    
    // Fetch column names and create table header
    $columns = array_keys(get_object_vars($results[0]));
    foreach ($columns as $column) {
        $html .= '<th>' . $column . '</th>';
    }
    $html .= '</tr></thead>';
    $html .= '<tbody>';

    // Fetch and display the data
    foreach ($results as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $html .= '<td>' . $cell . '</td>';
        }
        $html .= '</tr>';
    }

    $html .= '</tbody>';
    $html .= '</table>';
    $html .= '</div>';

    // Return the HTML table
    return $html;
}

// Output CSS styles directly
function output_custom_css() {
    ?>
    <style>
        /* CSS for sticky headers */
        .sticky-header thead {
            position: sticky;
            top: 0;
            background-color: #fff; /* Adjust as needed */
            z-index: 1000; /* Ensure it's above other content */
        }
    </style>
    <?php
}



// Enqueue scripts for AJAX refresh
function enqueue_ajax_refresh_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('ajax-refresh', plugins_url('includes/ajax-refresh.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('ajax-refresh', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}




// AJAX handler to refresh the table
function refresh_table_ajax_callback() {
    // Your code to fetch updated data from the database
    $html = display_database_table(array());

    echo $html; // Output the updated table HTML
    wp_die();
}




// Function to create the custom table
function create_custom_table() {
    global $wpdb;

    // Define table name
    $table_name = $wpdb->prefix . 'ms_cleandata';

     // SQL to create the table
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        sysUUID varchar(255) NOT NULL,
        lastrowid int(11) NOT NULL,
        sid int(11) NOT NULL,
        score float NOT NULL,
        timestamp int(11) NOT NULL,
        duration int(11) NOT NULL,
        ts int(11) NOT NULL,
        PRIMARY KEY (sysUUID)
    ) ENGINE=InnoDB;";

    // Include upgrade.php for dbDelta() function
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Create the table
    dbDelta($sql);
}


//================ endpoint registration for GET requests for db information ========================================

// Register custom REST API endpoint
function register_custom_rest_endpoint() {
    register_rest_route( 'myplugin/v1', '/get-db-data', array(
        'methods'             => 'GET',
        'callback'            => 'get_db_data_callback',
        'permission_callback' => 'custom_rest_permission_callback', // Add permission callback
    ));
}
add_action( 'rest_api_init', 'register_custom_rest_endpoint' );

// Permission callback function for the custom REST endpoint
function custom_rest_permission_callback( $request ) {
    // Check if the current user is authenticated and has the administrator role
    if ( ! current_user_can( 'administrator' ) ) {
        return new WP_Error( 'rest_forbidden', esc_html__( 'You do not have permission to access this endpoint.', 'myplugin' ), array( 'status' => 403 ) );
    }

    return true; // Access granted
}

// Callback function for the custom REST endpoint
function get_db_data_callback( $request ) {
    global $wpdb;

    // Step 1: Connect to the WordPress database
    $wpdb->ms_cleandata = $wpdb->prefix . 'ms_cleandata';

    // Step 2: Query the database
    $data = $wpdb->get_results("SELECT * FROM $wpdb->ms_cleandata", ARRAY_A);

    // Return the data as the response
    return rest_ensure_response( $data );
}

// want to request db data? use this (http://cyliaaudioidrevamp.local/wp-json/myplugin/v1/get-db-data) replace base url


//================ endpoint registration for GET requests for db information ========================================


//================= login page shortcode with signout =======================================================
// Register the shortcode
function custom_login_form_shortcode() {
    // Check if the user is logged in
    if (is_user_logged_in()) {
        // If logged in, display a message and a logout link
        $current_user = wp_get_current_user();
        $logout_url = wp_logout_url(home_url());
        $message = 'You are already signed in as ' . esc_html($current_user->user_login) . '. <a href="' . esc_url($logout_url) . '">Logout</a>';
        return $message;
    } else {
        // If not logged in, display the WordPress login form
        ob_start();
        wp_login_form();
        return ob_get_clean();
    }
}

//================= login page shortcode with signout =======================================================