<?php

/**
 * Plugin name: Data Plugin
 * Description: Plugin for transferring Data
 * Version: 1.0.1
 * Text Domain: data_plugin
 * Author: CC dev
 */

class webService
{
    public function __construct()
    {
        add_shortcode('socket_status', array($this, 'socket_status_shortcode'));
        add_shortcode('connect_button', array($this, 'connect_button_shortcode'));
        add_shortcode('datatable', array($this, 'datatable_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_listener'));
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

    public function socket_status_shortcode()
    {
        return '<div id="socket-status">[Not Connected]</div>';
    }

    public function socket_data_shortcode($atts, $content = null)
    {
        return '<div id="socket-data">' . do_shortcode($content) . '</div>';
    }

    public function connect_button_shortcode()
    {
        return '<button id="connect-button">Connect</button>';
    }


    public function insert_socket_data()
    {

        if (isset($_POST['sysUUID'], $_POST['sid'], $_POST['score'], $_POST['timestamp'], $_POST['duration'], $_POST['ts'])) {
            global $wpdb;

            // Extract values from the form data
            $sysUUID = $_POST['sysUUID'];
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