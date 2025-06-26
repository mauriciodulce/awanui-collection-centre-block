<?php
/**
 * Plugin Name:       Awanui Collection Centre Block
 * Description:       A custom WordPress block to display Awanui Labs collection centre information.
 * Version:           1.1.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Mauricio Dulce
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       awanui-collection-centre-block
 */

if (!defined("ABSPATH")) {
    exit();
}

/**
 * Register the block using metadata from block.json
 */
function awanui_collection_centre_block_init() {
    require_once __DIR__ . '/build/awanui-collection-centre-block/render.php';

    register_block_type(__DIR__ . '/build/awanui-collection-centre-block', [
        'render_callback' => 'awanui_render_centre_block',
    ]);
}
add_action('init', 'awanui_collection_centre_block_init');
add_action("init", "awanui_collection_centre_block_init");

/**
 * Register REST API proxy endpoint
 */
function awanui_register_api_proxy()
{
    register_rest_route("awanui/v1", "/centres", [
        "methods" => "GET",
        "callback" => "awanui_get_centres",
        "permission_callback" => "__return_true",
    ]);
}
add_action("rest_api_init", "awanui_register_api_proxy");

/**
 * Callback to proxy the external API
 */
function awanui_get_centres($request)
{
    $api_url = "https://loc.aphg.co.nz/wp-json/labtests/v1/centres/";
    $response = wp_remote_get($api_url, [
        "timeout" => 15,
        "headers" => [
            "User-Agent" =>
                "WordPress/" . get_bloginfo("version") . "; " . home_url(),
        ],
    ]);

    if (is_wp_error($response)) {
        return new WP_Error("api_error", "Failed to fetch collection centres", [
            "status" => 500,
        ]);
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error("json_error", "Invalid JSON response", [
            "status" => 500,
        ]);
    }

    return rest_ensure_response($data);
}
