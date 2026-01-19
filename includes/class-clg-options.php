<?php

if (!defined('ABSPATH')) {
    exit;
}

final class CLG_Options
{

    public const KEY_BASE_START_DATE = 'cl_base_start_date';
    public const KEY_TOTAL_DAYS = 'cl_total_days';
    public const KEY_TIMEZONE = 'cl_timezone';
    public const KEY_DAILY_RESET_TIME = 'cl_daily_reset_time';

    public const KEY_ALLOW_PLAY_AHEAD = 'cl_allow_play_ahead';
    public const KEY_DAY_COMPLETE_REQUIRES_ALL = 'cl_day_complete_requires_all';
    public const KEY_ALLOW_REPLAY = 'cl_allow_replay';
    public const KEY_REPLAY_AWARDS_POINTS = 'cl_replay_awards_points';
    public const KEY_STREAK_TRIGGER = 'cl_streak_trigger';

    public const KEY_DEFAULT_MINIGAME_POINTS = 'cl_default_minigame_points';
    public const KEY_DAY_BONUS_ENABLED = 'cl_day_bonus_enabled';
    public const KEY_DAY_BONUS_POINTS = 'cl_day_bonus_points';
    public const KEY_PACK_BONUS_ENABLED = 'cl_pack_bonus_enabled';
    public const KEY_PACK_BONUS_POINTS = 'cl_pack_bonus_points';

    public const KEY_DEFAULT_THEME_ID = 'cl_default_theme_id';

    public static function ensure_defaults(): void
    {
        $defaults = self::defaults();
        foreach ($defaults as $k => $v) {
            if (get_option($k, null) === null) {
                add_option($k, $v);
            }
        }
    }

    public static function defaults(): array
    {
        $tz = function_exists('wp_timezone_string') ? wp_timezone_string() : 'UTC';
        return [
            self::KEY_BASE_START_DATE => date('Y-m-d'),
            self::KEY_TOTAL_DAYS => 30,
            self::KEY_TIMEZONE => $tz ?: 'UTC',
            self::KEY_DAILY_RESET_TIME => '00:00',

            self::KEY_ALLOW_PLAY_AHEAD => 1,
            self::KEY_DAY_COMPLETE_REQUIRES_ALL => 1,
            self::KEY_ALLOW_REPLAY => 1,
            self::KEY_REPLAY_AWARDS_POINTS => 0,
            self::KEY_STREAK_TRIGGER => 'any_minigame_completion',

            self::KEY_DEFAULT_MINIGAME_POINTS => 10,
            self::KEY_DAY_BONUS_ENABLED => 0,
            self::KEY_DAY_BONUS_POINTS => 20,
            self::KEY_PACK_BONUS_ENABLED => 0,
            self::KEY_PACK_BONUS_POINTS => 50,

            self::KEY_DEFAULT_THEME_ID => 0,
        ];
    }

    public static function get(string $key, $default = null)
    {
        $v = get_option($key, $default);
        return $v;
    }

    public static function set(string $key, $value): bool
    {
        return update_option($key, $value);
    }
}
