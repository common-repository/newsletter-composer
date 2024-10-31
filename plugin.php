<?php
/*
  Plugin Name: Newsletter Composer
  Description: This plugins makes an HTML newsletter file with any category you have selected.
  Version: 1.01
  Author: Raul Alonso, Alicia Daza
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided. 
 */

define('NEWSLETTER_LIST_MAX', 9);
define('NEWSLETTER_PROFILE_MAX', 19);

global $newsletter;
$newsletter = new Newsletter();

class Newsletter {
    const VERSION = 101;

    var $time_limit;
    var $email_limit = 10; // Per run
    var $relink_email_id;
    var $relink_user_id;
    var $mailer;
    var $options_main;
    var $message;
    var $user;
    var $error;

    function Newsletter() {
        global $wpdb;

        register_activation_hook(__FILE__, array(&$this, 'hook_activate'));
        register_deactivation_hook(__FILE__, array(&$this, 'hook_deactivate'));

        add_action('init', array(&$this, 'hook_init'));
        add_action('admin_init', array(&$this, 'hook_admin_init'));
        add_action('mailer_bounce_email', array(&$this, 'mailer_bounce_email'));

        add_filter('cron_schedules', array(&$this, 'hook_cron_schedules'), 1000);
        add_action('newsletter', array(&$this, 'hook_newsletter'), 1);

        // This specific event is created by "Feed by mail" panel on configuration
        add_action('template_redirect', array(&$this, 'hook_template_redirect'));
        add_action('wp_head', array(&$this, 'hook_wp_head'));
        add_shortcode('newsletter', array(&$this, 'shortcode_newsletter'));
        add_shortcode('newsletter_lock', array(&$this, 'shortcode_newsletter_lock'));
        add_shortcode('newsletter_form', array(&$this, 'shortcode_newsletter_form'));
        add_shortcode('newsletter_embed', array(&$this, 'shortcode_newsletter_form'));
        add_action('shutdown', array(&$this, 'hook_shutdown'));
        if (is_admin ()) {
            add_action('admin_menu', array(&$this, 'hook_admin_menu'));
            add_action('admin_head', array(&$this, 'hook_admin_head'));
        }

        $this->options_main = get_option('newsletter_main', array());
    }

    function hook_admin_head() {
        if (strpos($_GET['page'], 'newsletter-composer/') === 0) {
            echo '<link type="text/css" rel="stylesheet" href="' .
		   plugins_url(). '/newsletter-composer/style.css"/>';
        }
    }

    function hook_admin_menu() {
        include 'plugin-menu.inc.php';
    }

    function hook_wp_head() {
        include 'plugin-head.inc.php';
    }

    function hook_newsletter_feed() {
    }

    function check_transient($name, $time) {
        if (get_transient($name) !== false) {
            $this->log('Called too quickly');
            return false;
        }
        set_transient($name, 1, $time);
        return true;
    }

    function hook_newsletter() {
        global $wpdb;

        //$this->log();
        if (!$this->check_transient('newsletter', 60)) return;

        $max = $this->options_main['scheduler_max'];
        if (!is_numeric($max)) $max = 100;
        $this->email_limit = max(floor($max / 12), 1);

        $this->set_limits();
    }

	
    function execute($text, $user=null) {
        global $wpdb;
        ob_start();
        $r = eval('?' . '>' . $text);
        if ($r === false) {
            $this->error = 'Error while executing a PHP expression in a message body. See log file.';
            $this->log('Error on execution of ' . $text, 1);
            ob_end_clean();
            return false;
        }

        return ob_get_clean();
    }



    /**
     * Levels are: 1 for errors, 2 for normal activity, 3 for debug.
     */
    function log($text='', $level=2) {
        if ((int) $this->options_main['logs'] < $level) return;

        $db = debug_backtrace(false);
        $time = date('d-m-Y H:i:s ');
        switch ($level) {
            case 1: $time .= '- ERROR';
                break;
            case 2: $time .= '- INFO ';
                break;
            case 3: $time .= '- DEBUG';
                break;
        }
        if (is_array($text) || is_object($text)) $text = print_r($text, true);
        file_put_contents(dirname(__FILE__) . '/log.txt', $time . ' - ' . $db[1]['function'] . ' - ' . $text . "\n", FILE_APPEND | FILE_TEXT);
    }

    function hook_activate() {
        global $wpdb;
        include 'plugin-activate.inc.php';
    }

    function hook_deactivate() {
        wp_clear_scheduled_hook('newsletter');
    }

    function hook_cron_schedules($schedules) {
        $schedules['newsletter'] = array(
            'interval' => 300, // seconds
            'display' => 'Newsletter'
        );
        return $schedules;
    }


    function hook_template_redirect() {
        if (!empty($this->message) && empty($this->options_main['url'])) {
            if ($this->options_main['theme'][0] == '*') //$file = ABSPATH . 'wp-content/plugins/newsletter-composer/themes/' . substr($this->options_main['theme'], 1) . '/theme.php';
					$file = plugins_url( '/themes/'. substr($this->options_main['theme'], 1) .'/theme.php' , __FILE__ ); 
            else $file = dirname(__FILE__) . '/themes/' . $this->options_main['theme'] . '/theme.php';

            // Include the labels, language dependend
            @include(dirname($file) . '/es_ES.php');
            if (defined('WPLANG') && WPLANG != '') @include(dirname($file) . '/' . WPLANG . '.php');

            ob_start();
            @include($file);
            $m = ob_get_contents();
            ob_end_clean();

            echo $this->execute(str_replace('{message}', $this->message, $m));
            die();
        }
    }


    function hook_init() {
        global $cache_stop, $hyper_cache_stop, $wpdb;

        $action = $_REQUEST['na'];
        if (empty($action) || is_admin()) return;

        $hyper_cache_stop = true;
        $cache_stop = true;

        $this->log($action);

        $options = get_option('newsletter', array()); // Subscription options, emails and texts
    }

    function set_limits() {
        global $wpdb;

        $wpdb->query("set session wait_timeout=300");
        // From default-constants.php
        if (function_exists('memory_get_usage') && ( (int) @ini_get('memory_limit') < 128 )) @ini_set('memory_limit', '128M');
    }

    function hook_admin_init() {
        global $wpdb;
        if ($_REQUEST['act'] == 'export' && check_admin_referer()) {
            include 'plugin-export.inc.php';
        }
    }


    function replace_url($text, $tag, $url) {
        $home = get_option('home') . '/';
        $tag_lower = strtolower($tag);
        $text = str_replace($home . '{' . $tag_lower . '}', $url, $text);
        $text = str_replace($home . '%7B' . $tag_lower . '%7D', $url, $text);
        $text = str_replace('{' . $tag_lower . '}', $url, $text);

        // for compatibility
        $text = str_replace($home . $tag, $url, $text);

        return $text;
    }

    function add_qs($url, $qs, $amp=true) {
        if (strpos($url, '?') !== false) {
            if ($amp) return $url . '&amp;' . $qs;
            else return $url . '&' . $qs;
        }
        else return $url . '?' . $qs;
    }

    function post_is_old() {
    }

    function hook_shutdown() {
        if ($this->mailer != null) $this->mailer->SmtpClose();
    }


    function normalize_email($email) {
        $email = strtolower(trim($email));
        if (!is_email($email)) return null;
        return $email;
    }

  /*  function normalize_name($name) {
        $name = str_replace(';', ' ', $name);
        $name = strip_tags($name);
        return $name;
    }
*/
    function is_email($email, $empty_ok=false) {
        $email = strtolower(trim($email));
        if ($empty_ok && $email == '') return true;

        if (!is_email($email)) return false;
        if (strpos($email, 'mailinator.com') !== false) return false;
        if (strpos($email, 'guerrillamailblock.com') !== false) return false;
        if (strpos($email, 'emailtemporanea.net') !== false) return false;
        return true;
    }

    function m2t($s) {
        $s = explode(' ', $s);
        $d = explode('-', $s[0]);
        $t = explode(':', $s[1]);
        return gmmktime((int) $t[0], (int) $t[1], (int) $t[2], (int) $d[1], (int) $d[2], (int) $d[0]);
    }

    function query($query) {
        global $wpdb;

        $this->log($query, 3);
        return $wpdb->query($query);
    }
}


/**
 * Find an image for a post checking the media uploaded for the post and
 * choosing the first image found.
 */
function nt_post_image($post_id, $size='thumbnail', $alternative=null) {

	if (has_post_thumbnail( $post_id ) ):
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $size );
	endif; 

    if (empty($image)) {
        return $alternative;
    }

    return $image[0];
}

function nt_option($name, $def = null) {
    $options = get_option('newsletter_email');
    $option = $options['theme_' . $name];
    if (!isset($option)) return $def;
    else return $option;
}

// For compatibility
function newsletter_form($number=null) {
    global $newsletter;
    echo $newsletter->form($number);
}

// For compatibility
function newsletter_embed_form($number=null) {
    global $newsletter;
    echo $newsletter->form($number);
}
