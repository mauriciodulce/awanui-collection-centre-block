<?php
/**
 * Awanui Collection Centre Block - Render functionality
 *
 * Handles the server-side rendering of the collection centre block
 * on the frontend of the website.
 *
 * @package AwanuiCollectionCentreBlock
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('awanui_render_centre_block')) {
    /**
     * Renders the Awanui Collection Centre block on the frontend.
     *
     * This function takes the block attributes (primarily the centre ID),
     * fetches the centre data from the API, and returns formatted HTML
     * to display the collection centre information including name, address,
     * phone number, opening hours, and a link to Google Maps directions.
     *
     * @since 1.0.0
     *
     * @param array $attributes {
     *     The block attributes containing the selected centre configuration.
     *
     *     @type string $centreId The ID of the selected collection centre.
     * }
     *
     * @return string The rendered HTML for the collection centre block.
     *                Returns appropriate error message HTML if centre not found,
     *                API fails, or no centre is selected.
     */
    function awanui_render_centre_block($attributes)
    {
        // Check if a centre ID has been selected
        if (empty($attributes['centreId'])) {
            return awanui_render_centre_placeholder();
        }

        // Sanitize the centre ID to prevent XSS attacks
        $centre_id = sanitize_text_field($attributes['centreId']);

        // Fetch centre data from the external API
        $centre_data = awanui_fetch_centre_from_api($centre_id);

        // Handle API errors or invalid responses
        if (is_wp_error($centre_data) || !$centre_data) {
            return awanui_render_centre_error('Collection centre not found or API unavailable.');
        }

        // Generate and return the HTML output
        return awanui_generate_centre_html($centre_data);
    }
}

if (!function_exists('awanui_render_centre_placeholder')) {
    /**
     * Renders a placeholder message when no collection centre is selected.
     *
     * @since 1.0.0
     *
     * @return string HTML markup for the placeholder state.
     */
    function awanui_render_centre_placeholder()
    {
        return '<div class="awanui-centre-placeholder"><p>No collection centre selected.</p></div>';
    }
}

if (!function_exists('awanui_render_centre_error')) {
    /**
     * Renders an error message when something goes wrong with the API or data processing.
     *
     * @since 1.0.0
     *
     * @param string $message The error message to display to users.
     *
     * @return string HTML markup for the error state.
     */
    function awanui_render_centre_error($message)
    {
        $safe_message = esc_html($message);
        return "<div class=\"awanui-centre-error\"><p>{$safe_message}</p></div>";
    }
}

if (!function_exists('awanui_fetch_centre_from_api')) {
    /**
     * Fetches collection centre data from the external API.
     *
     * Makes an HTTP request to the labtests API, processes the response,
     * and returns the specific centre data matching the provided ID.
     *
     * @since 1.0.0
     *
     * @param string $centre_id The ID of the centre to fetch.
     *
     * @return array|WP_Error|false The centre data array on success,
     *                              WP_Error on HTTP failure,
     *                              false if centre not found in response.
     */
    function awanui_fetch_centre_from_api($centre_id)
    {
        $api_url = "https://loc.aphg.co.nz/wp-json/labtests/v1/centres/";

        // Make HTTP request with reasonable timeout
        $response = wp_remote_get($api_url, [
            "timeout" => 10,
            "headers" => [
                "User-Agent" => "WordPress/AwanuiCollectionCentreBlock"
            ]
        ]);

        // Check for HTTP errors
        if (is_wp_error($response)) {
            return $response;
        }

        // Get and decode the response body
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Validate JSON decode and response structure
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return new WP_Error('invalid_json', 'Invalid JSON response from API');
        }

        // Find the specific centre in the response
        return awanui_find_centre_by_id($data, $centre_id);
    }
}

if (!function_exists('awanui_find_centre_by_id')) {
    /**
     * Searches through an array of centres to find one matching the given ID.
     *
     * @since 1.0.0
     *
     * @param array  $centres   Array of centre data from the API.
     * @param string $centre_id The ID to search for.
     *
     * @return array|false The matching centre data array, or false if not found.
     */
    function awanui_find_centre_by_id($centres, $centre_id)
    {
        foreach ($centres as $centre) {
            if (isset($centre['id']) && $centre['id'] == $centre_id) {
                return $centre;
            }
        }
        return false;
    }
}

if (!function_exists('awanui_generate_centre_html')) {
    /**
     * Generates the HTML markup for displaying collection centre information.
     *
     * Takes the centre data array and creates a formatted HTML output
     * including all relevant information like name, address, phone, hours, and directions.
     *
     * @since 1.0.0
     *
     * @param array $centre {
     *     The centre data array from the API.
     *
     *     @type string       $name         Centre name.
     *     @type string       $title        Alternative centre name/title.
     *     @type string       $address      Street address.
     *     @type string       $city         City name.
     *     @type string       $region       Region/state.
     *     @type string       $post_code    Postal code.
     *     @type string       $phone_number Phone number.
     *     @type array        $opening_hours Array of opening hours strings.
     *     @type array        $location     Alternative location data structure.
     * }
     *
     * @return string The complete HTML markup for the centre display.
     */
    function awanui_generate_centre_html($centre)
    {
        // Extract and sanitize centre data with fallbacks
        $name = esc_html($centre['name'] ?? $centre['title'] ?? 'Collection Centre');
        $address = esc_html($centre['address'] ?? $centre['location']['address'] ?? 'Address not available');
        $phone = wp_kses_post($centre['phone_number'] ?? 'Phone not available');
        $city = esc_html($centre['city'] ?? '');
        $region = esc_html($centre['region'] ?? '');
        $post_code = esc_html($centre['post_code'] ?? '');

        // Process opening hours
        $opening_hours = $centre['opening_hours'] ?? [];
        $formatted_hours = awanui_format_opening_hours($opening_hours);

        // Generate Google Maps URL
        $maps_url = awanui_generate_maps_url($address);

        // Use output buffering to generate clean HTML
        ob_start();
        ?>
        <div class="awanui-collection-centre-block">
            <div class="awanui-centre-info">
                <h3 class="awanui-centre-name"><?php echo $name; ?></h3>

                <div class="awanui-centre-details">
                    <div class="awanui-centre-address">
                        <strong>Address:</strong>
                        <p>
                            <?php echo $address; ?>
                            <?php if ($city): ?><br><?php echo $city; ?><?php endif; ?>
                            <?php if ($region): ?><br><?php echo $region; ?><?php endif; ?>
                            <?php if ($post_code): ?><br><?php echo $post_code; ?><?php endif; ?>
                        </p>
                    </div>

                    <div class="awanui-centre-phone">
                        <strong>Phone:</strong>
                        <p><?php echo $phone; ?></p>
                    </div>

                    <?php if ($formatted_hours): ?>
                    <div class="awanui-centre-hours">
                        <strong>Opening Hours:</strong>
                        <pre><?php echo $formatted_hours; ?></pre>
                    </div>
                    <?php endif; ?>

                    <div class="awanui-centre-directions">
                        <a class="awanui-directions-link"
                           href="<?php echo esc_url($maps_url); ?>"
                           target="_blank"
                           rel="noopener noreferrer">
                            Get Directions
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

if (!function_exists('awanui_format_opening_hours')) {
    /**
     * Formats opening hours array into a single string for display.
     *
     * @since 1.0.0
     *
     * @param array $opening_hours Array of opening hours strings.
     *
     * @return string Formatted opening hours as a single string with line breaks,
     *                or empty string if no hours provided.
     */
    function awanui_format_opening_hours($opening_hours)
    {
        if (empty($opening_hours) || !is_array($opening_hours)) {
            return '';
        }

        $formatted_lines = array_map('esc_html', $opening_hours);
        return implode("\n", $formatted_lines);
    }
}

if (!function_exists('awanui_generate_maps_url')) {
    /**
     * Generates a Google Maps search URL for the given address.
     *
     * @since 1.0.0
     *
     * @param string $address The address to create a maps URL for.
     *
     * @return string The complete Google Maps search URL.
     */
    function awanui_generate_maps_url($address)
    {
        $base_url = "https://www.google.com/maps/search/?api=1&query=";
        return $base_url . urlencode($address);
    }
}
