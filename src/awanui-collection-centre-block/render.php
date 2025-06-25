<?php

if (!function_exists('awanui_render_centre_block')) {
    function awanui_render_centre_block($attributes)
    {
        if (empty($attributes['centreId'])) {
            return '<div class="awanui-centre-placeholder"><p>No collection centre selected.</p></div>';
        }

        $centre_id = sanitize_text_field($attributes['centreId']);
        $api_url = "https://loc.aphg.co.nz/wp-json/labtests/v1/centres/";

        $response = wp_remote_get($api_url, ["timeout" => 10]);

        if (is_wp_error($response)) {
            return '<div class="awanui-centre-error"><p>Unable to load collection centre information.</p></div>';
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return '<div class="awanui-centre-error"><p>Invalid response from API.</p></div>';
        }

        $centre = null;

        foreach ($data as $item) {
            if (isset($item['id']) && $item['id'] == $centre_id) {
                $centre = $item;
                break;
            }
        }

        if (!$centre) {
            return '<div class="awanui-centre-error"><p>Collection centre not found.</p></div>';
        }

        $name = esc_html($centre['name'] ?? $centre['title'] ?? 'Collection Centre');
		$address = esc_html($centre['address'] ?? $centre['location']['address'] ?? 'Address not available');
		$phone = wp_kses_post($centre['phone_number'] ?? 'Phone not available');
		$city = esc_html($centre['city'] ?? '');
		$region = esc_html($centre['region'] ?? '');
		$post_code = esc_html($centre['post_code'] ?? '');

        $opening_hours = $centre['opening_hours'] ?? [];
        $formatted_hours = '';

        $opening_hours = $centre['opening_hours'] ?? [];
		$formatted_hours = '';

		foreach ($opening_hours as $line) {
			$formatted_hours .= '<div>' . esc_html($line) . '</div>';
		}

        $maps_url = "https://www.google.com/maps/search/?api=1&query=" . urlencode($address);

        ob_start();
        ?>
        <div class="awanui-collection-centre-block">
            <div class="awanui-centre-info">
                <h3 class="awanui-centre-name"><?php echo $name; ?></h3>

                <div class="awanui-centre-details">
                    <div class="awanui-centre-address">
                        <strong>Address:</strong>
                        <p><?php echo $address; ?><br>
							<?php echo $city; ?><br>
						<?php echo $region; ?><br>
					<?php echo $post_code; ?><br></p>
                    </div>

                    <div class="awanui-centre-phone">
                        <strong>Phone:</strong>
                        <p><?php echo $phone; ?></p>
                    </div>

                    <div class="awanui-centre-hours">
                        <strong>Opening Hours:</strong>
                        <?php echo $formatted_hours; ?>
                    </div>

                    <div class="awanui-centre-directions">
                        <a href="<?php echo esc_url($maps_url); ?>" target="_blank" rel="noopener noreferrer">
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
