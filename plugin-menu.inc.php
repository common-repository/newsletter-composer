<?php
//Select language
$blog_language = get_bloginfo('language'); 
if(!strcmp($blog_language,'es-ES')){
	@include_once 'languages/es-ES.php';
}else{
	@include_once 'languages/en-EN.php';
}

add_menu_page('Newsletter', 'Newsletter', 'edit_published_posts', 'newsletter-composer/emails.php', '', '');
add_submenu_page('newsletter-composer/emails.php', $submenu_title, $submenu_title, 'edit_published_posts', 'newsletter-composer/emails-edit.php');
?>