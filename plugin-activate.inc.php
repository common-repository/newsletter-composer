<?php
global $charset_collate;

require_once(get_home_path().'wp-admin/includes/upgrade.php');

$version = get_option('newsletter_version', 0);


// newsletter_composer
$sql = "CREATE TABLE " . $wpdb->prefix . "newsletter_composer (
        `id` int auto_increment,
        `subject` varchar(255) NOT NULL DEFAULT '',
        `message` longtext,
		`url` varchar(255) DEFAULT '',
		`category` int DEFAULT 0,
		`max_news` int DEFAULT 0,
		`max_main_news` int DEFAULT 0,
		`order` varchar(255) DEFAULT '',
        `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `theme` varchar(50) NOT NULL DEFAULT '',
        PRIMARY KEY (id)
        ) $charset_collate;";

@$wpdb->query($sql);

$sql = "ALTER TABLE " . $wpdb->prefix . "newsletter_composer CONVERT TO CHARACTER SET utf8";
@$wpdb->query($sql);

// Load DEFAULT options (language specific)
include dirname(__FILE__) . '/languages/es_ES.php';
@include dirname(__FILE__) . '/languages/' . WPLANG . '.php';

// MAIN OPTIONS
$options = get_option('newsletter_main', array());

if ($version < 250) {
    // Migration of "protect" configuration
    if (!isset($options['lock_url'])) {
        $protect = get_option('newsletter_protect', array());
        $options['lock_message'] = $protect['message'];
        $options['lock_url'] = $protect['url'];
        delete_option('newsletter_protect');
    }
}

if (empty($options['theme'])) $options['theme'] = $defaults_main['theme'];

update_option('newsletter_main', array_merge($defaults_main, $options));