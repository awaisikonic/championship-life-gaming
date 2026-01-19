<?php

if (!defined('ABSPATH')) {
    exit;
}

final class CLG_Admin
{
    private static $instance = null;

    public static function instance(): CLG_Admin
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->hooks();
        }
        return self::$instance;
    }

    private function hooks(): void
    {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_post_clg_import_seed', [$this, 'handle_seed_import']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_post'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function admin_menu(): void
    {
        $cap = 'manage_options';
        $slug = 'clg';

        add_menu_page('Championship Life', 'Championship Life', $cap, $slug, [$this, 'render_calendar_page'], 'dashicons-awards', 26);

        add_submenu_page($slug, 'Calendar & Days', 'Calendar & Days', $cap, 'clg-calendar', [$this, 'render_calendar_page']);
        add_submenu_page($slug, 'Week Packs', 'Week Packs', $cap, 'edit.php?post_type=' . CLG_CPT::CPT_WEEK_PACK);
        add_submenu_page($slug, 'Days', 'Days', $cap, 'edit.php?post_type=' . CLG_CPT::CPT_DAY);
        add_submenu_page($slug, 'Mini-games', 'Mini-games', $cap, 'edit.php?post_type=' . CLG_CPT::CPT_MINIGAME);
        add_submenu_page($slug, 'Questionnaires', 'Questionnaires', $cap, 'edit.php?post_type=' . CLG_CPT::CPT_QUESTIONNAIRE);
        add_submenu_page($slug, 'Questions', 'Questions', $cap, 'edit.php?post_type=' . CLG_CPT::CPT_QUESTION);
        add_submenu_page($slug, 'Themes', 'Themes', $cap, 'edit.php?post_type=' . CLG_CPT::CPT_THEME);
        add_submenu_page($slug, 'Rewards & Rules', 'Rewards & Rules', $cap, 'clg-settings', [$this, 'render_settings_page']);

        add_submenu_page($slug, 'Demo Seed', 'Demo Seed', $cap, 'clg-seed', [$this, 'render_seed_page']);
    }

    public function render_seed_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $notice = '';
        if (isset($_GET['clg_seed']) && $_GET['clg_seed'] === 'ok') {
            $notice = 'Demo seed imported successfully.';
        } elseif (isset($_GET['clg_seed']) && $_GET['clg_seed'] === 'err') {
            $notice = 'Seed import failed. Please check the JSON file and try again.';
        }

        $bundled_seed = CLG_PATH . 'seeds/demo-week1.json';
        $bundled_exists = file_exists($bundled_seed);

        include CLG_PATH . 'admin/views/page-seed.php';
    }

    public function register_settings(): void
    {
        register_setting('clg_calendar', CLG_Options::KEY_BASE_START_DATE, ['sanitize_callback' => [$this, 'sanitize_date']]);
        register_setting('clg_calendar', CLG_Options::KEY_TOTAL_DAYS, ['sanitize_callback' => 'absint']);
        register_setting('clg_calendar', CLG_Options::KEY_TIMEZONE, ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('clg_calendar', CLG_Options::KEY_DAILY_RESET_TIME, ['sanitize_callback' => [$this, 'sanitize_time']]);

        register_setting('clg_settings', CLG_Options::KEY_ALLOW_PLAY_AHEAD, ['sanitize_callback' => 'absint']);
        register_setting('clg_settings', CLG_Options::KEY_DAY_COMPLETE_REQUIRES_ALL, ['sanitize_callback' => 'absint']);
        register_setting('clg_settings', CLG_Options::KEY_ALLOW_REPLAY, ['sanitize_callback' => 'absint']);
        register_setting('clg_settings', CLG_Options::KEY_REPLAY_AWARDS_POINTS, ['sanitize_callback' => 'absint']);
        register_setting('clg_settings', CLG_Options::KEY_STREAK_TRIGGER, ['sanitize_callback' => 'sanitize_text_field']);

        register_setting('clg_settings', CLG_Options::KEY_DEFAULT_MINIGAME_POINTS, ['sanitize_callback' => 'absint']);
        register_setting('clg_settings', CLG_Options::KEY_DAY_BONUS_ENABLED, ['sanitize_callback' => 'absint']);
        register_setting('clg_settings', CLG_Options::KEY_DAY_BONUS_POINTS, ['sanitize_callback' => 'absint']);
        register_setting('clg_settings', CLG_Options::KEY_PACK_BONUS_ENABLED, ['sanitize_callback' => 'absint']);
        register_setting('clg_settings', CLG_Options::KEY_PACK_BONUS_POINTS, ['sanitize_callback' => 'absint']);

        register_setting('clg_settings', CLG_Options::KEY_DEFAULT_THEME_ID, ['sanitize_callback' => 'absint']);
    }

    public function sanitize_date($value)
    {
        $value = sanitize_text_field($value);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return date('Y-m-d');
        }
        return $value;
    }

    public function sanitize_time($value)
    {
        $value = sanitize_text_field($value);
        if (!preg_match('/^\d{2}:\d{2}$/', $value)) {
            return '00:00';
        }
        return $value;
    }

    public function render_calendar_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['clg_generate_days']) && check_admin_referer('clg_generate_days_action', 'clg_generate_days_nonce')) {
            $this->sync_days_from_calendar();
            add_settings_error('clg_calendar', 'clg_days_synced', 'Days synced successfully.', 'updated');
        }

        $base_start_date = CLG_Options::get(CLG_Options::KEY_BASE_START_DATE);
        $total_days = (int) CLG_Options::get(CLG_Options::KEY_TOTAL_DAYS);
        $tz = CLG_Options::get(CLG_Options::KEY_TIMEZONE);
        $reset_time = CLG_Options::get(CLG_Options::KEY_DAILY_RESET_TIME);
        $default_theme_id = (int) CLG_Options::get(CLG_Options::KEY_DEFAULT_THEME_ID);

        include CLG_PATH . 'admin/views/page-calendar.php';
    }

    public function render_settings_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        $opts = [
            'allow_play_ahead' => (int) CLG_Options::get(CLG_Options::KEY_ALLOW_PLAY_AHEAD),
            'day_complete_requires_all' => (int) CLG_Options::get(CLG_Options::KEY_DAY_COMPLETE_REQUIRES_ALL),
            'allow_replay' => (int) CLG_Options::get(CLG_Options::KEY_ALLOW_REPLAY),
            'replay_awards_points' => (int) CLG_Options::get(CLG_Options::KEY_REPLAY_AWARDS_POINTS),
            'streak_trigger' => (string) CLG_Options::get(CLG_Options::KEY_STREAK_TRIGGER),
            'default_minigame_points' => (int) CLG_Options::get(CLG_Options::KEY_DEFAULT_MINIGAME_POINTS),
            'day_bonus_enabled' => (int) CLG_Options::get(CLG_Options::KEY_DAY_BONUS_ENABLED),
            'day_bonus_points' => (int) CLG_Options::get(CLG_Options::KEY_DAY_BONUS_POINTS),
            'pack_bonus_enabled' => (int) CLG_Options::get(CLG_Options::KEY_PACK_BONUS_ENABLED),
            'pack_bonus_points' => (int) CLG_Options::get(CLG_Options::KEY_PACK_BONUS_POINTS),
            'default_theme_id' => (int) CLG_Options::get(CLG_Options::KEY_DEFAULT_THEME_ID),
        ];

        include CLG_PATH . 'admin/views/page-settings.php';
    }

    /**
     * Handle demo seed imports.
     * Supports importing the bundled Week 1 seed or a user-uploaded JSON.
     */
    public function handle_seed_import(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions.');
        }

        check_admin_referer('clg_seed_import_action', 'clg_seed_import_nonce');

        $json = '';
        $mode = sanitize_text_field($_POST['mode'] ?? 'bundled');

        if ($mode === 'upload') {
            if (!isset($_FILES['seed_file']) || empty($_FILES['seed_file']['tmp_name'])) {
                wp_safe_redirect(admin_url('admin.php?page=clg-seed&clg_seed=err'));
                exit;
            }
            $tmp = $_FILES['seed_file']['tmp_name'];
            $json = (string) file_get_contents($tmp);
        } else {
            $bundled = CLG_PATH . 'seeds/demo-week1.json';
            if (!file_exists($bundled)) {
                wp_safe_redirect(admin_url('admin.php?page=clg-seed&clg_seed=err'));
                exit;
            }
            $json = (string) file_get_contents($bundled);
        }

        $ok = $this->import_seed_json($json);
        wp_safe_redirect(admin_url('admin.php?page=clg-seed&clg_seed=' . ($ok ? 'ok' : 'err')));
        exit;
    }

    private function import_seed_json(string $json): bool
    {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return false;
        }

        $version = (int)($data['version'] ?? 0);
        if ($version !== 1) {
            return false;
        }

        $map = [
            'questions' => [],
            'questionnaires' => [],
            'minigames' => [],
            'week_packs' => [],
            'days' => [],
        ];

        $days_needed = [];
        foreach (($data['day_assignments'] ?? []) as $da) {
            $days_needed[] = (int)($da['day_number'] ?? 0);
        }
        foreach (($data['week_packs'] ?? []) as $wp) {
            $start = (int)($wp['start_day'] ?? 0);
            $end = (int)($wp['end_day'] ?? 0);
            if ($start > 0 && $end > 0) {
                if ($end < $start) {
                    list($start, $end) = [$end, $start];
                }

                for ($d = $start; $d <= $end; $d++) {
                    $days_needed[] = $d;
                }
            }
        }
        $days_needed = array_values(array_unique(array_filter($days_needed)));

        foreach ($days_needed as $dn) {
            $day_id = $this->upsert_day_by_number($dn);
            if (!$day_id) return false;
            $map['days'][$dn] = $day_id;
        }

        foreach (($data['questions'] ?? []) as $q) {
            $slug = sanitize_title($q['slug'] ?? '');
            $title = sanitize_text_field($q['title'] ?? '');
            if (!$slug || !$title) return false;

            $qid = $this->upsert_post(CLG_CPT::CPT_QUESTION, $slug, $title, wp_kses_post($q['content'] ?? ''));
            if (!$qid) return false;

            update_post_meta($qid, 'cl_question_type', sanitize_text_field($q['type'] ?? 'single_mcq'));
            update_post_meta($qid, 'cl_required', absint($q['required'] ?? 1));
            update_post_meta($qid, 'cl_sort_order', absint($q['sort_order'] ?? 1));

            $opts = $q['options'] ?? [];
            if (is_array($opts) && !empty($opts)) {
                update_post_meta($qid, 'cl_options_json', wp_json_encode(array_values($opts)));
            }
            if (isset($q['min'])) update_post_meta($qid, 'cl_min', $q['min']);
            if (isset($q['max'])) update_post_meta($qid, 'cl_max', $q['max']);
            if (isset($q['step'])) update_post_meta($qid, 'cl_step', $q['step']);
            if (isset($q['min_len'])) update_post_meta($qid, 'cl_min_len', absint($q['min_len']));
            if (isset($q['max_len'])) update_post_meta($qid, 'cl_max_len', absint($q['max_len']));

            $map['questions'][$slug] = $qid;
        }

        foreach (($data['questionnaires'] ?? []) as $qn) {
            $slug = sanitize_title($qn['slug'] ?? '');
            $title = sanitize_text_field($qn['title'] ?? '');
            if (!$slug || !$title) return false;

            $qid = $this->upsert_post(CLG_CPT::CPT_QUESTIONNAIRE, $slug, $title, '');
            if (!$qid) return false;

            update_post_meta($qid, 'cl_instructions', sanitize_textarea_field($qn['instructions'] ?? ''));
            update_post_meta($qid, 'cl_questionnaire_type', sanitize_text_field($qn['type'] ?? 'single_mcq'));
            update_post_meta($qid, 'cl_completion_mode', sanitize_text_field($qn['completion_mode'] ?? 'all_questions'));
            update_post_meta($qid, 'cl_scoring_mode', sanitize_text_field($qn['scoring_mode'] ?? 'participation'));

            $qslugs = $qn['questions'] ?? [];
            $qids = [];
            if (is_array($qslugs)) {
                foreach ($qslugs as $qs) {
                    $qs = sanitize_title($qs);
                    if (isset($map['questions'][$qs])) {
                        $qids[] = (int)$map['questions'][$qs];
                    }
                }
            }
            update_post_meta($qid, 'cl_question_ids', implode(',', $qids));
            $map['questionnaires'][$slug] = $qid;
        }

        foreach (($data['minigames'] ?? []) as $mg) {
            $slug = sanitize_title($mg['slug'] ?? '');
            $title = sanitize_text_field($mg['title'] ?? '');
            if (!$slug || !$title) return false;

            $mid = $this->upsert_post(CLG_CPT::CPT_MINIGAME, $slug, $title, '');
            if (!$mid) return false;

            update_post_meta($mid, 'cl_minigame_type', sanitize_text_field($mg['type'] ?? 'single_mcq'));
            update_post_meta($mid, 'cl_points', absint($mg['points'] ?? 10));
            update_post_meta($mid, 'cl_icon_key', sanitize_text_field($mg['icon_key'] ?? ''));
            update_post_meta($mid, 'cl_circle_label', sanitize_text_field($mg['circle_label'] ?? ''));

            $qref = sanitize_title($mg['questionnaire'] ?? '');
            $qid = $qref && isset($map['questionnaires'][$qref]) ? (int)$map['questionnaires'][$qref] : 0;
            update_post_meta($mid, 'cl_questionnaire_id', $qid);

            $map['minigames'][$slug] = $mid;
        }

        foreach (($data['week_packs'] ?? []) as $wp) {
            $slug = sanitize_title($wp['slug'] ?? ('week-' . ($wp['pack_number'] ?? 1)));
            $title = sanitize_text_field($wp['title'] ?? 'Week Pack');
            $pid = $this->upsert_post(CLG_CPT::CPT_WEEK_PACK, $slug, $title, '');
            if (!$pid) return false;

            $pack_number = absint($wp['pack_number'] ?? 1);
            $start_day = absint($wp['start_day'] ?? 1);
            $end_day = absint($wp['end_day'] ?? 7);

            update_post_meta($pid, 'cl_pack_number', $pack_number);
            update_post_meta($pid, 'cl_start_day', $start_day);
            update_post_meta($pid, 'cl_end_day', $end_day);

            $q = new WP_Query([
                'post_type' => CLG_CPT::CPT_DAY,
                'posts_per_page' => -1,
                'post_status' => ['publish', 'draft'],
                'meta_query' => [[
                    'key' => 'cl_day_number',
                    'value' => [$start_day, $end_day],
                    'compare' => 'BETWEEN',
                    'type' => 'NUMERIC'
                ]],
                'fields' => 'ids',
            ]);
            foreach ($q->posts as $day_id) {
                update_post_meta((int)$day_id, 'cl_week_pack_id', $pid);
            }

            $map['week_packs'][$slug] = $pid;
        }

        foreach (($data['day_assignments'] ?? []) as $da) {
            $dn = absint($da['day_number'] ?? 0);
            if ($dn <= 0) continue;
            $day_id = $map['days'][$dn] ?? 0;
            if (!$day_id) continue;

            $mg_slugs = $da['minigames'] ?? [];
            if (!is_array($mg_slugs)) continue;

            $mg_ids = [];
            foreach ($mg_slugs as $ms) {
                $ms = sanitize_title($ms);
                if (isset($map['minigames'][$ms])) {
                    $mg_ids[] = (int)$map['minigames'][$ms];
                }
            }

            global $wpdb;
            $table = CLG_DB::table_day_minigames();
            $wpdb->delete($table, ['day_post_id' => $day_id]);
            $order = 1;
            foreach ($mg_ids as $mid) {
                $wpdb->insert($table, [
                    'day_post_id' => $day_id,
                    'minigame_post_id' => $mid,
                    'sort_order' => $order,
                ], ['%d', '%d', '%d']);
                $order++;
            }
        }

        return true;
    }

    private function upsert_post(string $post_type, string $slug, string $title, string $content): int
    {
        $existing = get_page_by_path($slug, OBJECT, $post_type);
        $args = [
            'post_type' => $post_type,
            'post_title' => $title,
            'post_status' => 'publish',
            'post_name' => $slug,
            'post_content' => $content,
        ];
        if ($existing && isset($existing->ID)) {
            $args['ID'] = (int)$existing->ID;
            $id = wp_update_post($args, true);
        } else {
            $id = wp_insert_post($args, true);
        }
        return is_wp_error($id) ? 0 : (int)$id;
    }

    private function upsert_day_by_number(int $day_number): int
    {
        if ($day_number <= 0) return 0;
        $q = new WP_Query([
            'post_type' => CLG_CPT::CPT_DAY,
            'posts_per_page' => 1,
            'post_status' => ['publish', 'draft'],
            'meta_key' => 'cl_day_number',
            'meta_value' => $day_number,
            'fields' => 'ids',
        ]);
        if (!empty($q->posts)) {
            $id = (int)$q->posts[0];
            wp_update_post(['ID' => $id, 'post_status' => 'publish', 'post_title' => 'Day ' . $day_number]);
            return $id;
        }

        $id = wp_insert_post([
            'post_type' => CLG_CPT::CPT_DAY,
            'post_title' => 'Day ' . $day_number,
            'post_status' => 'publish',
        ]);
        if (is_wp_error($id) || !$id) return 0;

        update_post_meta((int)$id, 'cl_day_number', $day_number);
        $base = CLG_Options::get(CLG_Options::KEY_BASE_START_DATE);
        $base_ts = strtotime($base . ' 00:00:00');
        if ($base_ts) {
            $date = date('Y-m-d', strtotime('+' . ($day_number - 1) . ' days', $base_ts));
            update_post_meta((int)$id, 'cl_calendar_date', $date);
        }
        return (int)$id;
    }

    private function sync_days_from_calendar(): void
    {
        $base = CLG_Options::get(CLG_Options::KEY_BASE_START_DATE);
        $total = max(1, (int) CLG_Options::get(CLG_Options::KEY_TOTAL_DAYS));
        $default_theme_id = (int) CLG_Options::get(CLG_Options::KEY_DEFAULT_THEME_ID);

        $existing = [];
        $q = new WP_Query([
            'post_type' => CLG_CPT::CPT_DAY,
            'posts_per_page' => -1,
            'post_status' => ['publish', 'draft'],
            'meta_key' => 'cl_day_number',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'fields' => 'ids',
        ]);
        foreach ($q->posts as $pid) {
            $dn = (int) get_post_meta($pid, 'cl_day_number', true);
            if ($dn > 0) {
                $existing[$dn] = $pid;
            }
        }

        $base_ts = strtotime($base . ' 00:00:00');
        for ($i = 1; $i <= $total; $i++) {
            $date = date('Y-m-d', strtotime('+' . ($i - 1) . ' days', $base_ts));
            $title = 'Day ' . $i;

            if (isset($existing[$i])) {
                $pid = $existing[$i];
                wp_update_post([
                    'ID' => $pid,
                    'post_title' => $title,
                    'post_status' => 'publish',
                ]);
            } else {
                $pid = wp_insert_post([
                    'post_type' => CLG_CPT::CPT_DAY,
                    'post_title' => $title,
                    'post_status' => 'publish',
                ]);
            }

            update_post_meta($pid, 'cl_day_number', $i);
            update_post_meta($pid, 'cl_calendar_date', $date);

            if ($default_theme_id > 0 && (int)get_post_meta($pid, 'cl_theme_id', true) === 0) {
                update_post_meta($pid, 'cl_theme_id', $default_theme_id);
            }
        }

        foreach ($existing as $dn => $pid) {
            if ($dn > $total) {
                wp_update_post(['ID' => $pid, 'post_status' => 'draft']);
            }
        }
    }

    public function add_meta_boxes(): void
    {
        add_meta_box('clg_day_minigames', 'Mini-games (Sequential)', [$this, 'render_day_minigames_metabox'], CLG_CPT::CPT_DAY, 'normal', 'high');
        add_meta_box('clg_day_pack', 'Week Pack', [$this, 'render_day_pack_metabox'], CLG_CPT::CPT_DAY, 'side', 'default');
        add_meta_box('clg_minigame_meta', 'Mini-game Settings', [$this, 'render_minigame_metabox'], CLG_CPT::CPT_MINIGAME, 'normal', 'high');
        add_meta_box('clg_questionnaire_meta', 'Questionnaire Settings', [$this, 'render_questionnaire_metabox'], CLG_CPT::CPT_QUESTIONNAIRE, 'normal', 'high');
        add_meta_box('clg_question_meta', 'Question Settings', [$this, 'render_question_metabox'], CLG_CPT::CPT_QUESTION, 'normal', 'high');
        add_meta_box('clg_theme_meta', 'Theme Colors', [$this, 'render_theme_metabox'], CLG_CPT::CPT_THEME, 'normal', 'high');
        add_meta_box('clg_pack_meta', 'Pack Range', [$this, 'render_pack_metabox'], CLG_CPT::CPT_WEEK_PACK, 'normal', 'high');
    }

    public function render_day_pack_metabox($post): void
    {
        $week_pack_id = (int) (get_post_meta($post->ID, 'cl_week_pack_id', true) ?: 0);
        $week_pack = $week_pack_id ? get_post($week_pack_id) : null;
        $pack_number = $week_pack_id ? (int) (get_post_meta($week_pack_id, 'cl_pack_number', true) ?: 0) : 0;
        $start_day = $week_pack_id ? (int) (get_post_meta($week_pack_id, 'cl_start_day', true) ?: 0) : 0;
        $end_day = $week_pack_id ? (int) (get_post_meta($week_pack_id, 'cl_end_day', true) ?: 0) : 0;

        include CLG_PATH . 'admin/views/metabox-day-pack.php';
    }

    public function enqueue_admin_assets($hook): void
    {
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        $post_types = [CLG_CPT::CPT_DAY, CLG_CPT::CPT_MINIGAME, CLG_CPT::CPT_QUESTIONNAIRE, CLG_CPT::CPT_QUESTION, CLG_CPT::CPT_THEME, CLG_CPT::CPT_WEEK_PACK];
        if (in_array($screen->post_type, $post_types, true)) {
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('clg-admin', CLG_URL . 'assets/js/admin.js', ['jquery', 'jquery-ui-sortable'], CLG_VERSION, true);
            wp_enqueue_style('clg-admin', CLG_URL . 'assets/css/admin.css', [], CLG_VERSION);

            wp_localize_script('clg-admin', 'CLG_ADMIN', [
                'nonce' => wp_create_nonce('clg_admin_nonce'),
            ]);
        }
    }

    public function render_day_minigames_metabox($post): void
    {
        wp_nonce_field('clg_day_minigames_save', 'clg_day_minigames_nonce');

        global $wpdb;
        $table = CLG_DB::table_day_minigames();
        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} WHERE day_post_id = %d ORDER BY sort_order ASC", $post->ID));

        $minigames = get_posts([
            'post_type' => CLG_CPT::CPT_MINIGAME,
            'posts_per_page' => -1,
            'post_status' => ['publish', 'draft'],
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        include CLG_PATH . 'admin/views/metabox-day-minigames.php';
    }

    public function render_minigame_metabox($post): void
    {
        wp_nonce_field('clg_minigame_save', 'clg_minigame_nonce');
        $type = get_post_meta($post->ID, 'cl_minigame_type', true) ?: 'single_mcq';
        $points = (int) (get_post_meta($post->ID, 'cl_points', true) ?: CLG_Options::get(CLG_Options::KEY_DEFAULT_MINIGAME_POINTS));
        $questionnaire_id = (int) (get_post_meta($post->ID, 'cl_questionnaire_id', true) ?: 0);
        $icon_key = (string) (get_post_meta($post->ID, 'cl_icon_key', true) ?: '');
        $circle_label = (string) (get_post_meta($post->ID, 'cl_circle_label', true) ?: '');

        $questionnaires = get_posts([
            'post_type' => CLG_CPT::CPT_QUESTIONNAIRE,
            'posts_per_page' => -1,
            'post_status' => ['publish', 'draft'],
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        include CLG_PATH . 'admin/views/metabox-minigame.php';
    }

    public function render_questionnaire_metabox($post): void
    {
        wp_nonce_field('clg_questionnaire_save', 'clg_questionnaire_nonce');
        $instructions = (string) get_post_meta($post->ID, 'cl_instructions', true);
        $questionnaire_type = (string) (get_post_meta($post->ID, 'cl_questionnaire_type', true) ?: 'single_mcq');
        $completion_mode = (string) (get_post_meta($post->ID, 'cl_completion_mode', true) ?: 'all_questions');
        $scoring_mode = (string) (get_post_meta($post->ID, 'cl_scoring_mode', true) ?: 'participation');
        $question_ids_csv = (string) get_post_meta($post->ID, 'cl_question_ids', true);
        $selected_ids = array_values(array_filter(array_map('absint', explode(',', $question_ids_csv))));

        $questions = get_posts([
            'post_type' => CLG_CPT::CPT_QUESTION,
            'posts_per_page' => -1,
            'post_status' => ['publish', 'draft'],
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        include CLG_PATH . 'admin/views/metabox-questionnaire.php';
    }

    public function render_question_metabox($post): void
    {
        wp_nonce_field('clg_question_save', 'clg_question_nonce');

        $question_text = (string) ($post->post_content ?? '');

        $linked_questionnaires = [];
        $q = new WP_Query([
            'post_type' => CLG_CPT::CPT_QUESTIONNAIRE,
            'posts_per_page' => -1,
            'post_status' => ['publish', 'draft'],
            'meta_query' => [
                [
                    'key' => 'cl_question_ids',
                    'value' => (string) $post->ID,
                    'compare' => 'LIKE',
                ]
            ],
        ]);
        foreach ($q->posts as $qp) {
            $linked_questionnaires[] = $qp;
        }

        $qtype = (string) (get_post_meta($post->ID, 'cl_question_type', true) ?: 'single_mcq');
        $required = (int) (get_post_meta($post->ID, 'cl_required', true) ?: 1);
        $options_json = (string) (get_post_meta($post->ID, 'cl_options_json', true) ?: '');

        $min = get_post_meta($post->ID, 'cl_min', true);
        $max = get_post_meta($post->ID, 'cl_max', true);
        $step = get_post_meta($post->ID, 'cl_step', true);

        $min_len = (int) (get_post_meta($post->ID, 'cl_min_len', true) ?: 1);
        $max_len = (int) (get_post_meta($post->ID, 'cl_max_len', true) ?: 500);
        $sort_order = (int) (get_post_meta($post->ID, 'cl_sort_order', true) ?: 1);

        include CLG_PATH . 'admin/views/metabox-question.php';
    }

    public function render_theme_metabox($post): void
    {
        wp_nonce_field('clg_theme_save', 'clg_theme_nonce');
        $primary = (string) (get_post_meta($post->ID, 'cl_primary', true) ?: '#111827');
        $secondary = (string) (get_post_meta($post->ID, 'cl_secondary', true) ?: '#6b7280');
        $bg_token = (string) (get_post_meta($post->ID, 'cl_bg_token', true) ?: 'default');
        include CLG_PATH . 'admin/views/metabox-theme.php';
    }

    public function render_pack_metabox($post): void
    {
        wp_nonce_field('clg_pack_save', 'clg_pack_nonce');
        $pack_number = (int) (get_post_meta($post->ID, 'cl_pack_number', true) ?: 1);
        $start_day = (int) (get_post_meta($post->ID, 'cl_start_day', true) ?: 1);
        $end_day = (int) (get_post_meta($post->ID, 'cl_end_day', true) ?: 7);
        include CLG_PATH . 'admin/views/metabox-pack.php';
    }

    public function save_post(int $post_id, WP_Post $post): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('manage_options')) {
            return;
        }

        switch ($post->post_type) {
            case CLG_CPT::CPT_DAY:
                $this->save_day_minigames($post_id);
                $this->save_day_meta($post_id);
                break;
            case CLG_CPT::CPT_MINIGAME:
                $this->save_minigame_meta($post_id);
                break;
            case CLG_CPT::CPT_QUESTIONNAIRE:
                $this->save_questionnaire_meta($post_id);
                break;
            case CLG_CPT::CPT_QUESTION:
                $this->save_question_meta($post_id);
                break;
            case CLG_CPT::CPT_THEME:
                $this->save_theme_meta($post_id);
                break;
            case CLG_CPT::CPT_WEEK_PACK:
                $this->save_pack_meta($post_id);
                break;
        }
    }

    private function save_day_meta(int $post_id): void
    {
        if (isset($_POST['clg_day_meta_nonce']) && wp_verify_nonce($_POST['clg_day_meta_nonce'], 'clg_day_meta_save')) {
            if (isset($_POST['cl_day_number'])) update_post_meta($post_id, 'cl_day_number', absint($_POST['cl_day_number']));
            if (isset($_POST['cl_calendar_date'])) update_post_meta($post_id, 'cl_calendar_date', $this->sanitize_date($_POST['cl_calendar_date']));
            if (isset($_POST['cl_theme_id'])) update_post_meta($post_id, 'cl_theme_id', absint($_POST['cl_theme_id']));
        }
    }

    private function save_day_minigames(int $post_id): void
    {
        if (!isset($_POST['clg_day_minigames_nonce']) || !wp_verify_nonce($_POST['clg_day_minigames_nonce'], 'clg_day_minigames_save')) {
            return;
        }

        $ids = isset($_POST['cl_day_minigame_ids']) ? (array) $_POST['cl_day_minigame_ids'] : [];
        $clean = [];
        foreach ($ids as $id) {
            $id = absint($id);
            if ($id > 0) {
                $clean[] = $id;
            }
        }

        $unique = [];
        foreach ($clean as $id) {
            if (!in_array($id, $unique, true)) {
                $unique[] = $id;
            }
        }

        global $wpdb;
        $table = CLG_DB::table_day_minigames();
        $wpdb->delete($table, ['day_post_id' => $post_id]);

        $order = 1;
        foreach ($unique as $mid) {
            $wpdb->insert($table, [
                'day_post_id' => $post_id,
                'minigame_post_id' => $mid,
                'sort_order' => $order,
            ], ['%d', '%d', '%d']);
            $order++;
        }
    }

    private function save_minigame_meta(int $post_id): void
    {
        if (!isset($_POST['clg_minigame_nonce']) || !wp_verify_nonce($_POST['clg_minigame_nonce'], 'clg_minigame_save')) {
            return;
        }
        update_post_meta($post_id, 'cl_minigame_type', sanitize_text_field($_POST['cl_minigame_type'] ?? 'single_mcq'));
        update_post_meta($post_id, 'cl_points', absint($_POST['cl_points'] ?? 0));
        update_post_meta($post_id, 'cl_questionnaire_id', absint($_POST['cl_questionnaire_id'] ?? 0));
        update_post_meta($post_id, 'cl_icon_key', sanitize_text_field($_POST['cl_icon_key'] ?? ''));
        update_post_meta($post_id, 'cl_circle_label', sanitize_text_field($_POST['cl_circle_label'] ?? ''));
    }

    private function save_questionnaire_meta(int $post_id): void
    {
        if (!isset($_POST['clg_questionnaire_nonce']) || !wp_verify_nonce($_POST['clg_questionnaire_nonce'], 'clg_questionnaire_save')) {
            return;
        }
        update_post_meta($post_id, 'cl_instructions', sanitize_textarea_field($_POST['cl_instructions'] ?? ''));
        update_post_meta($post_id, 'cl_questionnaire_type', sanitize_text_field($_POST['cl_questionnaire_type'] ?? 'single_mcq'));
        update_post_meta($post_id, 'cl_completion_mode', sanitize_text_field($_POST['cl_completion_mode'] ?? 'all_questions'));
        update_post_meta($post_id, 'cl_scoring_mode', sanitize_text_field($_POST['cl_scoring_mode'] ?? 'participation'));

        $ids = isset($_POST['cl_question_ids']) ? (array) $_POST['cl_question_ids'] : [];
        $clean = [];
        foreach ($ids as $id) {
            $id = absint($id);
            if ($id > 0) $clean[] = $id;
        }

        $unique = [];
        foreach ($clean as $id) {
            if (!in_array($id, $unique, true)) $unique[] = $id;
        }
        update_post_meta($post_id, 'cl_question_ids', implode(',', $unique));
    }

    private function save_question_meta(int $post_id): void
    {
        if (!isset($_POST['clg_question_nonce']) || !wp_verify_nonce($_POST['clg_question_nonce'], 'clg_question_save')) {
            return;
        }

        if (isset($_POST['cl_question_text'])) {
            $content = wp_kses_post(wp_unslash($_POST['cl_question_text']));
            remove_action('save_post', [$this, 'save_post'], 10);
            wp_update_post(['ID' => $post_id, 'post_content' => $content]);
            add_action('save_post', [$this, 'save_post'], 10, 2);
        }

        $qtype = sanitize_text_field($_POST['cl_question_type'] ?? 'single_mcq');
        update_post_meta($post_id, 'cl_question_type', $qtype);
        update_post_meta($post_id, 'cl_required', absint($_POST['cl_required'] ?? 1));
        update_post_meta($post_id, 'cl_sort_order', absint($_POST['cl_sort_order'] ?? 1));

        $options_lines = sanitize_textarea_field($_POST['cl_options_lines'] ?? '');
        if ($options_lines !== '') {
            $opts = array_values(array_filter(array_map('trim', explode("\n", $options_lines))));
            update_post_meta($post_id, 'cl_options_json', wp_json_encode($opts));
        } else {
            update_post_meta($post_id, 'cl_options_json', '');
        }

        update_post_meta($post_id, 'cl_min', is_numeric($_POST['cl_min'] ?? null) ? (0 + $_POST['cl_min']) : '');
        update_post_meta($post_id, 'cl_max', is_numeric($_POST['cl_max'] ?? null) ? (0 + $_POST['cl_max']) : '');
        update_post_meta($post_id, 'cl_step', is_numeric($_POST['cl_step'] ?? null) ? (0 + $_POST['cl_step']) : '');

        update_post_meta($post_id, 'cl_min_len', absint($_POST['cl_min_len'] ?? 1));
        update_post_meta($post_id, 'cl_max_len', absint($_POST['cl_max_len'] ?? 500));
    }

    private function save_theme_meta(int $post_id): void
    {
        if (!isset($_POST['clg_theme_nonce']) || !wp_verify_nonce($_POST['clg_theme_nonce'], 'clg_theme_save')) {
            return;
        }
        update_post_meta($post_id, 'cl_primary', sanitize_text_field($_POST['cl_primary'] ?? '#111827'));
        update_post_meta($post_id, 'cl_secondary', sanitize_text_field($_POST['cl_secondary'] ?? '#6b7280'));
        update_post_meta($post_id, 'cl_bg_token', sanitize_text_field($_POST['cl_bg_token'] ?? 'default'));
    }

    private function save_pack_meta(int $post_id): void
    {
        if (!isset($_POST['clg_pack_nonce']) || !wp_verify_nonce($_POST['clg_pack_nonce'], 'clg_pack_save')) {
            return;
        }
        $pack_number = absint($_POST['cl_pack_number'] ?? 1);
        $start_day = absint($_POST['cl_start_day'] ?? 1);
        $end_day = absint($_POST['cl_end_day'] ?? 7);
        if ($end_day < $start_day) {
            $tmp = $start_day;
            $start_day = $end_day;
            $end_day = $tmp;
        }

        update_post_meta($post_id, 'cl_pack_number', $pack_number);
        update_post_meta($post_id, 'cl_start_day', $start_day);
        update_post_meta($post_id, 'cl_end_day', $end_day);

        $q = new WP_Query([
            'post_type' => CLG_CPT::CPT_DAY,
            'posts_per_page' => -1,
            'post_status' => ['publish', 'draft'],
            'meta_query' => [
                [
                    'key' => 'cl_day_number',
                    'value' => [$start_day, $end_day],
                    'compare' => 'BETWEEN',
                    'type' => 'NUMERIC'
                ]
            ],
            'fields' => 'ids',
        ]);
        foreach ($q->posts as $day_id) {
            update_post_meta($day_id, 'cl_week_pack_id', $post_id);
        }
    }
}
