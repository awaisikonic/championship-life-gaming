<?php

/**
 * Plugin Name: Championship Life – Gaming Module
 * Description: Standalone interactive gaming module (admin foundation only). Creates Days, Week Packs, Mini-games, Questionnaires, Themes, and configuration screens.
 * Version: 0.1.0
 * Author: Awais Ahmed Khan (Ikonic Solution)
 * License: GPLv2 or later
 * Text Domain: clg
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CLG_VERSION', '0.1.0');
define('CLG_PATH', plugin_dir_path(__FILE__));
define('CLG_URL', plugin_dir_url(__FILE__));

require_once CLG_PATH . 'includes/class-clg-plugin.php';

register_activation_hook(__FILE__, ['CLG_Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['CLG_Plugin', 'deactivate']);

add_action('plugins_loaded', function () {
    CLG_Plugin::instance();
});
