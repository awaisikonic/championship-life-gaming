<?php

if (!defined('ABSPATH')) {
    exit;
}

final class CLG_CPT
{
    public const CPT_DAY = 'clg_day';
    public const CPT_WEEK_PACK = 'clg_week_pack';
    public const CPT_MINIGAME = 'clg_minigame';
    public const CPT_QUESTIONNAIRE = 'clg_questionnaire';
    public const CPT_QUESTION = 'clg_question';
    public const CPT_THEME = 'clg_theme';

    public static function register(): void
    {
        self::register_cpts();
        self::register_meta();
    }

    private static function register_cpts(): void
    {
        $common = [
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_admin_bar' => false,
            'show_in_nav_menus' => false,
            'exclude_from_search' => true,
            'capability_type' => 'post',
            'supports' => ['title'],
            'has_archive' => false,
            'rewrite' => false,
        ];

        register_post_type(self::CPT_DAY, array_merge($common, [
            'labels' => [
                'name' => 'Days',
                'singular_name' => 'Day',
                'add_new_item' => 'Add New Day',
                'edit_item' => 'Edit Day',
            ],
            'menu_icon' => 'dashicons-calendar-alt',
        ]));

        register_post_type(self::CPT_WEEK_PACK, array_merge($common, [
            'labels' => [
                'name' => 'Week Packs',
                'singular_name' => 'Week Pack',
                'add_new_item' => 'Add New Week Pack',
                'edit_item' => 'Edit Week Pack',
            ],
            'menu_icon' => 'dashicons-screenoptions',
        ]));

        register_post_type(self::CPT_MINIGAME, array_merge($common, [
            'labels' => [
                'name' => 'Mini-games',
                'singular_name' => 'Mini-game',
                'add_new_item' => 'Add New Mini-game',
                'edit_item' => 'Edit Mini-game',
            ],
            'menu_icon' => 'dashicons-games',
        ]));

        register_post_type(self::CPT_QUESTIONNAIRE, array_merge($common, [
            'labels' => [
                'name' => 'Questionnaires',
                'singular_name' => 'Questionnaire',
                'add_new_item' => 'Add New Questionnaire',
                'edit_item' => 'Edit Questionnaire',
            ],
            'menu_icon' => 'dashicons-clipboard',
        ]));

        register_post_type(self::CPT_QUESTION, array_merge($common, [
            'labels' => [
                'name' => 'Questions',
                'singular_name' => 'Question',
                'add_new_item' => 'Add New Question',
                'edit_item' => 'Edit Question',
            ],
            'supports' => ['title', 'editor'],
            'menu_icon' => 'dashicons-editor-help',
        ]));

        register_post_type(self::CPT_THEME, array_merge($common, [
            'labels' => [
                'name' => 'Themes',
                'singular_name' => 'Theme',
                'add_new_item' => 'Add New Theme',
                'edit_item' => 'Edit Theme',
            ],
            'menu_icon' => 'dashicons-admin-appearance',
        ]));
    }

    private static function register_meta(): void
    {
        register_post_meta(self::CPT_DAY, 'cl_day_number', ['type' => 'integer', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_DAY, 'cl_calendar_date', ['type' => 'string', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_DAY, 'cl_week_pack_id', ['type' => 'integer', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_DAY, 'cl_theme_id', ['type' => 'integer', 'single' => true, 'show_in_rest' => false]);

        register_post_meta(self::CPT_WEEK_PACK, 'cl_pack_number', ['type' => 'integer', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_WEEK_PACK, 'cl_start_day', ['type' => 'integer', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_WEEK_PACK, 'cl_end_day', ['type' => 'integer', 'single' => true, 'show_in_rest' => false]);

        register_post_meta(self::CPT_MINIGAME, 'cl_minigame_type', ['type' => 'string', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_MINIGAME, 'cl_points', ['type' => 'integer', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_MINIGAME, 'cl_questionnaire_id', ['type' => 'integer', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_MINIGAME, 'cl_icon_key', ['type' => 'string', 'single' => true, 'show_in_rest' => false]);

        register_post_meta(self::CPT_QUESTIONNAIRE, 'cl_instructions', ['type' => 'string', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_QUESTIONNAIRE, 'cl_completion_mode', ['type' => 'string', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_QUESTIONNAIRE, 'cl_scoring_mode', ['type' => 'string', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_QUESTIONNAIRE, 'cl_question_ids', ['type' => 'string', 'single' => true, 'show_in_rest' => false]);

        register_post_meta(self::CPT_QUESTION, 'cl_question_type', ['type' => 'string', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_QUESTION, 'cl_required', ['type' => 'boolean', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_QUESTION, 'cl_options_json', ['type' => 'string', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_QUESTION, 'cl_min', ['type' => 'number', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_QUESTION, 'cl_max', ['type' => 'number', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_QUESTION, 'cl_step', ['type' => 'number', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_QUESTION, 'cl_min_len', ['type' => 'integer', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_QUESTION, 'cl_max_len', ['type' => 'integer', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_QUESTION, 'cl_sort_order', ['type' => 'integer', 'single' => true, 'show_in_rest' => false]);

        register_post_meta(self::CPT_THEME, 'cl_primary', ['type' => 'string', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_THEME, 'cl_secondary', ['type' => 'string', 'single' => true, 'show_in_rest' => false]);
        register_post_meta(self::CPT_THEME, 'cl_bg_token', ['type' => 'string', 'single' => true, 'show_in_rest' => false]);
    }
}
