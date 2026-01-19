<?php
class CLG_Progress
{

    public static function get_user_progress($user_id, $day_id, $minigame_id)
    {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}clg_user_progress WHERE user_id = %d AND day_id = %d AND minigame_id = %d",
                $user_id,
                $day_id,
                $minigame_id
            )
        );
    }

    public static function record_progress($user_id, $day_id, $minigame_id, $status, $score)
    {
        global $wpdb;

        $existing_progress = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}clg_user_progress WHERE user_id = %d AND day_id = %d AND minigame_id = %d",
                $user_id,
                $day_id,
                $minigame_id
            )
        );

        if ($existing_progress) {
            $wpdb->update(
                "{$wpdb->prefix}clg_user_progress",
                array(
                    'status' => $status,
                    'score' => $score,
                    'attempts' => $wpdb->prepare("attempts + 1"),
                    'completed_at' => ($status == 'completed') ? current_time('mysql') : null
                ),
                array('id' => $existing_progress)
            );
        } else {
            $wpdb->insert(
                "{$wpdb->prefix}clg_user_progress",
                array(
                    'user_id' => $user_id,
                    'day_id' => $day_id,
                    'minigame_id' => $minigame_id,
                    'status' => $status,
                    'score' => $score,
                    'attempts' => 1,
                    'completed_at' => ($status == 'completed') ? current_time('mysql') : null
                )
            );
        }

        self::update_user_streak($user_id);
    }

    public static function get_user_streak($user_id)
    {
        global $wpdb;

        $progress = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}clg_user_progress WHERE user_id = %d AND status = 'completed' ORDER BY completed_at DESC",
                $user_id
            )
        );

        $streak = 0;
        $last_completed = null;

        foreach ($progress as $row) {
            if ($last_completed && (strtotime($row->completed_at) - strtotime($last_completed)) <= 86400) {
                $streak++;
            } else {
                $streak = 1;
            }

            $last_completed = $row->completed_at;
        }

        return $streak;
    }

    public static function update_user_streak($user_id)
    {
        $streak = self::get_user_streak($user_id);

        global $wpdb;
        $existing_streak = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}clg_streak WHERE user_id = %d",
                $user_id
            )
        );

        if ($existing_streak) {
            $wpdb->update(
                "{$wpdb->prefix}clg_streak",
                array(
                    'current_streak' => $streak,
                    'longest_streak' => max($streak, $wpdb->get_var("SELECT longest_streak FROM {$wpdb->prefix}clg_streak WHERE user_id = $user_id"))
                ),
                array('id' => $existing_streak)
            );
        } else {
            $wpdb->insert(
                "{$wpdb->prefix}clg_streak",
                array(
                    'user_id' => $user_id,
                    'current_streak' => $streak,
                    'longest_streak' => $streak,
                    'last_active_date' => current_time('Y-m-d')
                )
            );
        }
    }

    public static function apply_rewards($user_id, $score, $status)
    {
        if ($status == 'completed') {
            global $wpdb;
            $wpdb->insert(
                "{$wpdb->prefix}clg_points_ledger",
                array(
                    'user_id' => $user_id,
                    'points' => $score,
                    'reason' => 'Mini-game completion',
                    'created_at' => current_time('mysql')
                )
            );
        }
    }
}
