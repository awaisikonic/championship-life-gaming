<?php
class CLG_API
{

    public static function init()
    {
        add_action('rest_api_init', array(__CLASS__, 'register_routes'));
    }

    public static function register_routes()
    {
        register_rest_route('clg/v1', '/me', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_user_profile'),
            'permission_callback' => function () {
                return is_user_logged_in();
            }
        ));

        register_rest_route('clg/v1', '/profile', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'update_user_profile'),
            'permission_callback' => function () {
                return is_user_logged_in();
            }
        ));

        register_rest_route('clg/v1', '/purposes', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_purposes'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('clg/v1', '/profile/purpose', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'update_user_purpose'),
            'permission_callback' => function () {
                return is_user_logged_in();
            }
        ));

        register_rest_route('clg/v1', '/levels', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_levels'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('clg/v1', '/days/(?P<day_id>\d+)/minigames', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_day_minigames'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('clg/v1', '/questionnaires/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_questionnaire'),
            'args' => array(
                'day_id' => array('required' => true),
                'minigame_id' => array('required' => true)
            ),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('clg/v1', '/answers/submit', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'submit_answers'),
            'permission_callback' => function () {
                return is_user_logged_in();
            }
        ));
    }

    public static function get_user_profile()
    {
        $user_id = get_current_user_id();

        if ($user_id) {
            $profile = array(
                'display_name' => get_user_meta($user_id, 'nickname', true),
                'dob' => get_user_meta($user_id, 'dob', true),
                'purpose_key' => get_user_meta($user_id, 'purpose_key', true),
                'streak' => CLG_Progress::get_user_streak($user_id)
            );
            return rest_ensure_response($profile);
        }

        return new WP_Error('no_user', 'No user found', array('status' => 404));
    }

    public static function update_user_profile(WP_REST_Request $request)
    {
        $user_id = get_current_user_id();
        $display_name = sanitize_text_field($request->get_param('display_name'));
        $dob = sanitize_text_field($request->get_param('dob'));

        if ($user_id) {
            update_user_meta($user_id, 'nickname', $display_name);
            update_user_meta($user_id, 'dob', $dob);
            return rest_ensure_response('Profile updated');
        }

        return new WP_Error('no_user', 'No user found', array('status' => 404));
    }

    public static function get_purposes()
    {
        return rest_ensure_response(array(
            array('key' => 'discipline', 'label' => 'Discipline'),
            array('key' => 'focus', 'label' => 'Focus'),
            array('key' => 'confidence', 'label' => 'Confidence')
        ));
    }

    public static function update_user_purpose(WP_REST_Request $request)
    {
        $user_id = get_current_user_id();
        $purpose_key = sanitize_text_field($request->get_param('purpose_key'));

        if ($user_id && in_array($purpose_key, ['discipline', 'focus', 'confidence'])) {
            update_user_meta($user_id, 'purpose_key', $purpose_key);
            return rest_ensure_response('Purpose updated');
        }

        return new WP_Error('invalid_purpose', 'Invalid purpose key', array('status' => 400));
    }

    public static function get_levels()
    {
        global $wpdb;
        $levels = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}clg_day");
        return rest_ensure_response($levels);
    }

    public static function get_day_minigames(WP_REST_Request $request)
    {
        $day_id = $request->get_param('day_id');
        global $wpdb;

        $minigames = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}clg_day_minigames WHERE day_id = %d", $day_id)
        );
        return rest_ensure_response($minigames);
    }

    public static function get_questionnaire(WP_REST_Request $request)
    {
        $questionnaire_id = $request->get_param('id');
        $day_id = $request->get_param('day_id');
        $minigame_id = $request->get_param('minigame_id');

        $questionnaire = array(
            'id' => $questionnaire_id,
            'questions' => array(
                array(
                    'question_id' => 1,
                    'text' => 'How do you feel today?',
                    'options' => ['Great', 'Okay', 'Low']
                ),
                array(
                    'question_id' => 2,
                    'text' => 'What’s your goal?',
                    'options' => ['Focus', 'Discipline', 'Confidence']
                )
            )
        );
        return rest_ensure_response($questionnaire);
    }

    public static function submit_answers(WP_REST_Request $request)
    {
        $user_id = get_current_user_id();
        $answers = $request->get_param('answers');

        foreach ($answers as $answer) {
            CLG_Progress::record_progress(
                $user_id,
                $request->get_param('day_id'),
                $request->get_param('minigame_id'),
                'completed',
                10
            );
        }

        return rest_ensure_response('Answers submitted');
    }
}

CLG_API::init();
