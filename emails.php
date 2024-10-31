<?php
//Select language
$blog_language = get_bloginfo('language'); 
if(!strcmp($blog_language,'es-ES')){
	@include 'languages/es-ES.php';
}else{
	@include 'languages/en-EN.php';
}

@include_once 'commons.php';
$nc = new NewsletterControls();



if (isset($_GET['del_id'])) {
	$wpdb->query("delete from " . $wpdb->prefix . "newsletter_composer where id=" . $_GET['del_id']);
}	

$emails = $wpdb->get_results("select * from " . $wpdb->prefix . "newsletter_composer order by id desc");

?>

<div class="wrap">

<h2>Newsletters</h2>


<form method="post" action="admin.php?page=newsletter-composer/emails.php">
    <?php $nc->init(); ?>

<p><a href="admin.php?page=newsletter-composer/emails-edit.php" class="button"><?php echo $submenu_title; ?></a></p>

    <table class="widefat">
        <thead>
            <tr>
                <th>Id</th>
                <th><?php echo $emails_text['title']; ?></th>
				<th width="20%">HTML</th>
                <th><?php echo $emails_text['date_creation']; ?></th>
                <th width="10%">&nbsp;</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($emails as &$email) { ?>
            <tr>
                <td><a href="admin.php?page=newsletter-composer/emails-edit.php&amp;id=<?php echo $email->id; ?>"><?php echo $email->id; ?></a></td>
                <td><a href="admin.php?page=newsletter-composer/emails-edit.php&amp;id=<?php echo $email->id; ?>"><?php echo htmlspecialchars($email->subject); ?></a></td>
				<td width="20%"><a href="<?php echo $email->url; ?>"><?php echo $email->url; ?></a></td>
                <td><?php echo $email->created; ?></td>
                <td width="10%"><a title="<?php echo $edit_text; ?>" href="admin.php?page=newsletter-composer/emails-edit.php&amp;id=<?php echo $email->id; ?>">
						<img src="<?php echo get_site_url();?>/wp-content/plugins/newsletter-composer/img/edit.ico" border="0" />
					</a>
					<a title="<?php echo $delete_text; ?>" href="admin.php?page=newsletter-composer/emails.php&amp;del_id=<?php echo $email->id; ?>">
						<img src="<?php echo get_site_url();?>/wp-content/plugins/newsletter-composer/img/delete.ico" border="0" />
					</a>
				</td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</form>
</div>
