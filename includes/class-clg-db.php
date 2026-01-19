<?php

if (!defined('ABSPATH')) {
    exit;
}

final class CLG_DB
{
    public static function table_day_minigames(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'cl_day_minigames';
    }

    public static function table_user_progress(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'clg_user_progress';
    }

    public static function table_user_day_progress(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'clg_user_day_progress';
    }

    public static function table_user_week_pack_progress(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'clg_user_week_pack_progress';
    }

    public static function table_points_ledger(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'clg_points_ledger';
    }

    public static function table_streak(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'clg_streak';
    }

    public static function create_tables(): void
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $t = self::table_day_minigames();

        $sql = "CREATE TABLE {$t} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            day_post_id BIGINT(20) UNSIGNED NOT NULL,
            minigame_post_id BIGINT(20) UNSIGNED NOT NULL,
            sort_order INT(11) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY day_sort_unique (day_post_id, sort_order),
            UNIQUE KEY day_minigame_unique (day_post_id, minigame_post_id),
            KEY day_idx (day_post_id),
            KEY minigame_idx (minigame_post_id)
        ) {$charset_collate};";

        dbDelta($sql);

        $t = self::table_user_progress();
        $sql = "CREATE TABLE {$t} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            day_id INT(11) NOT NULL,
            minigame_id INT(11) NOT NULL,
            status ENUM('not_started', 'in_progress', 'completed') NOT NULL DEFAULT 'not_started',
            attempts INT(11) NOT NULL DEFAULT 0,
            score INT(11) NOT NULL DEFAULT 0,
            completed_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY day_id (day_id),
            KEY minigame_id (minigame_id)
        ) $charset_collate;";

        dbDelta($sql);

        $t = self::table_user_day_progress();
        $sql = "CREATE TABLE {$t} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            day_id INT(11) NOT NULL,
            status ENUM('not_started', 'in_progress', 'completed') NOT NULL DEFAULT 'not_started',
            completed_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY day_id (day_id)
        ) {$wpdb->get_charset_collate()};";
        dbDelta($sql);

        $t = self::table_user_week_pack_progress();
        $sql = "CREATE TABLE {$t} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            week_pack_id INT(11) NOT NULL,
            status ENUM('not_started', 'in_progress', 'completed') NOT NULL DEFAULT 'not_started',
            completed_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY week_pack_id (week_pack_id)
        ) {$wpdb->get_charset_collate()};";
        dbDelta($sql);

        $t = self::table_points_ledger();
        $sql = "CREATE TABLE {$t} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            points INT(11) NOT NULL DEFAULT 0,
            reason VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) {$wpdb->get_charset_collate()};";
        dbDelta($sql);

        $t = self::table_streak();
        $sql = "CREATE TABLE {$t} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            current_streak INT(11) NOT NULL DEFAULT 0,
            longest_streak INT(11) NOT NULL DEFAULT 0,
            last_active_date DATE NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) {$wpdb->get_charset_collate()};";
        dbDelta($sql);
    }
}
