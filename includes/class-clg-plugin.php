<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once CLG_PATH . 'includes/class-clg-cpt.php';
require_once CLG_PATH . 'includes/class-clg-db.php';
require_once CLG_PATH . 'includes/class-clg-options.php';
require_once CLG_PATH . 'admin/class-clg-admin.php';
require_once CLG_PATH . 'includes/class-clg-progress.php';
require_once CLG_PATH . 'includes/class-clg-api.php';

final class CLG_Plugin
{
    private static $instance = null;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    private function init()
    {
        add_action('init', ['CLG_CPT', 'register']);

        if (is_admin()) {
            CLG_Admin::instance();
        }

        CLG_API::init();
    }

    public static function activate()
    {
        CLG_DB::create_tables();
        CLG_Options::ensure_defaults();
        CLG_CPT::register();
        flush_rewrite_rules();
    }

    public static function deactivate()
    {
        flush_rewrite_rules();
    }
}
