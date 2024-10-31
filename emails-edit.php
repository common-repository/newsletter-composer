<?php
//Configuration
$default_max_news = 20;
$default_max_main_news = 10;
$charset = "iso-8859-15"; //utf8 ?
//Configuration

@include_once 'commons.php';

//Select language
$blog_language = get_bloginfo('language'); 
if(!strcmp($blog_language,'es-ES')){
	@include 'languages/es-ES.php';
	$tinymce_language = "language : 'es',";
}else{
	@include 'languages/en-EN.php';
}

$nc = new NewsletterControls();


if (isset($_GET['id'])) {
    $nc->load($wpdb->prefix . 'newsletter_composer', $_GET['id']);
}else {
	//New, inicialize values
	if ((!$nc->is_action('compose')) && (!$nc->is_action('html')) && (!$nc->is_action('save'))){
		$nc->data['subject'] = $subject;
		$nc->data['message'] = '';
		$nc->data['theme'] = 'blank';
		$nc->data['url'] = '';
		$nc->data['category'] = 0;
		$nc->data['max_news'] = 0;
		$nc->data['max_main_news']= 0;
		$nc->data['order'] = '';
	} 

	//Save in database
    if ($nc->is_action('save')) {
		if(strlen($nc->data['message'])<=4294967295){//If it's bigger, it won´t be able to save all message in DB
			$nc->save($wpdb->prefix . 'newsletter_composer');
		}else{
			echo '<p>'.$emails_edit_text['too_large_save'].'</p>';
		}
    }

	//Delete from database
    if ($nc->is_action('delete')) {
        $wpdb->query("delete from " . $wpdb->prefix . "newsletter_composer where id=" . $nc->data['id']);

		echo'<script>location.href="admin.php?page=newsletter-composer/emails.php";</script>';

        return;
    }

	//Create content from parameters (not saving yet)
    if ($nc->is_action('compose')) {
        if ($nc->data['theme'][0] == '*') 
				$file = plugin_dir_path( __FILE__ ).'themes/'.substr($nc->data['theme'], 1) .'/theme.php';
        else $file = dirname(__FILE__) . '/themes/' . $nc->data['theme'] . '/theme.php';
		
        ob_start();
        @include($file);
        $nc->data['message'] = ob_get_contents();
        ob_end_clean();		
    }
	
	//Save html in UPLOADS folder
    if ($nc->is_action('html')) {
		if(strlen($nc->data['message'])<=4294967295){//If it's bigger, it won´t be able to save all message in DB
		
			//Get UPLOADS adress
			$upload_dir = wp_upload_dir();
			
			//Save in database
			$nc->save($wpdb->prefix . 'newsletter_composer');	
			
			$main_name_file = $nc->data['id'];
			$newsletter_path = $upload_dir['basedir']."/newsletter/";
			$newsletter_filename = "newsletter_".$main_name_file.".html";
			$newsletter_fullpath = $newsletter_path.$newsletter_filename;
			$web_address = $upload_dir['baseurl']."/newsletter/".$newsletter_filename;
			
			$nc->data['url'] = $web_address;
			//Save in database
			$nc->save($wpdb->prefix . 'newsletter_composer',$nc->data);
			
			$newsletter_header = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
	<html xmlns=\"http://www.w3.org/1999/xhtml\">
	<head>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".$charset."\" />
	<title>Newsletter - ".utf8_decode($nc->data['subject'])."</title>
	</head>
	<body style=\"margin:0px;padding:0px;background-color:#EAEAEA\">";

			$newsletter_footer = '</body></html>';

			$newsletter_css_link = '<link rel="stylesheet" type="text/css" href="'.newsletter_get_theme_css_url($nc->data['theme']).'" >';
			
			$fp=fopen($newsletter_fullpath,'w+');

			if($fp==false)
				die($emails_edit_text['error_file']);
		
			fwrite($fp, str_replace("<!-- CSS -->",$newsletter_css_link,str_replace("<!-- NEWS-Fin -->",$newsletter_footer,str_replace("<!-- NEWS-Inicio -->",$newsletter_header,str_replace("#NEWSLETTER_URL",$web_address,$nc->data['message'])))));
			
			fclose($fp);
			
			echo '<p>'.$emails_edit_text['dir_web'].'</p>';

			echo '<p><a href="'.$web_address.'" target="_blank">'.$web_address.'</a>';
		}else{
			echo '<p>'.$emails_edit_text['too_large_html'].'</p>';
		}
    }
}


$options_main = get_option('newsletter_main', array());


// Themes

$nc->errors($errors);
$nc->messages($messages);

function newsletter_get_theme_file($theme) {
    if ($theme[0] == '*')
		$file = plugin_dir_path( __FILE__ ).'themes/'.substr($theme, 1) .'/theme.php';
    else $file = dirname(__FILE__) . '/themes/' . $theme . '/theme.php';
}

function newsletter_get_theme_css_url($theme) {
    if ($theme[0] == '*'){
			$url = plugins_url( 'themes/'.substr($theme, 1) .'/style.css' , __FILE__ ); 
			$file = plugin_dir_path( __FILE__ ).'themes/'.substr($theme, 1) .'/style.css';
    }else{ 
			$url = plugins_url( 'themes/'. $theme .'/style.css' , __FILE__ ); 
			$file = plugin_dir_path( __FILE__ ).'themes/'.$theme.'/style.css';
	}
	if (!file_exists($file)) return 'empty';
	return $url;
}


?>

<?php wp_enqueue_script('jquery-ui-core'); ?>
<?php wp_enqueue_script('jquery-ui-widget'); ?>
<?php wp_enqueue_script('jquery-ui-mouse'); ?>
<?php wp_enqueue_script('jquery-ui-sortable'); ?>

<script type="text/javascript" src="<?php echo plugins_url( 'tinymce/tinymce.min.js', __FILE__ ); ?>"></script>
<script type="text/javascript">
tinymce.init({
		<?php echo $tinymce_language; ?>
	    selector: "textarea",
		mode: "textareas",
        relative_urls : false,
        remove_script_host : false,
		<?php 
			if (newsletter_get_theme_css_url($nc->data['theme']) != "empty"){
				echo 'content_css: "'.newsletter_get_theme_css_url($nc->data['theme']).'",';
			}
		?>
		plugins: [
			 "table,fullscreen,paste,link,importcss,preview,hr,lists,contextmenu,image,code"
	    ],
	    toolbar: "bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | preview fullpage | pastetext image | link unlink | code", 
 });
</script>


<div class="wrap">

    <h2><?php echo $emails_edit_text['title_composer']; ?></h2>

    <form method="post" action="admin.php?page=newsletter-composer/emails-edit.php">
        <?php $nc->init(); ?>
        <?php $nc->hidden('id'); ?>


        <table class="form-table">
            <tr valign="top">
                <th><?php echo $emails_edit_text['Theme']; ?></th>
                <td>
                    <?php $nc->select_grouped('theme', array(
                            array_merge(array(''=>$emails_edit_text['Themes']), newsletter_get_themes()),
                            $themes,
                            $themes_panel
                            ));
                    ?>
                    <?php 
						$nc->button('compose', $emails_edit_text['Compose']); 
						echo $emails_edit_text['care'];
					?> 

                    <div class="hints">
                        <?php echo $emails_edit_text['alert_change'];?>
					</div>
				</td>
            </tr>

			<!-- Categorías de Wordpress  // Wordrepss categories -->
			<tr valign="top">
				<th><?php echo $emails_edit_text['Categories']; ?></th>
				<td>
					<select name="category_id"> 
					<?php 
						global $cat_selected;
						if($nc->data['category']!=0 && !isset($cat_selected)){ //category is saved in DB or changed
							$cat_selected = $nc->data['category'];
							$cat_selected_name = get_the_category_by_ID($cat_selected);
						}else{
							if(!isset($cat_selected)){
								$cat_selected_name = "Newsletter";
							}else{
								$cat_selected_name = get_the_category_by_ID($cat_selected);
								if($nc->data['category'] != $cat_selected){
									$nc->data['category'] = $cat_selected;
									$cat_changed = 1; //True, for not using the hidden order anymore
								}
							}
						}	
						$args = array(
						  'orderby' => 'name',
						  'order' => 'ASC'
						  );
						$categories = get_categories($args);
						foreach($categories as $category) { 
							echo '<option value="';
							echo $category->cat_ID;
							echo '"'; 
							if (!strcasecmp($category->cat_name, $cat_selected_name) ){
								echo 'selected="selected"';
							}
							echo'>';
							echo $category->cat_name;
							echo '</option>';
						}
						
					?>
				    </select>
				</td>
			</tr>
			
			<!-- Número de entradas // Number of entries -->
			<tr valign="top">
				<th><?php echo $emails_edit_text['total_entries']; ?></th>
				<td>
					<select name="max_news"> 
					<?php 
					    global $max_news;
						if($nc->data['max_news']!=0 && !isset($max_news)){ //max_news is saved in DB or has changed
							$max_news = $nc->data['max_news'];
						}else{
							if(isset($max_news)){ 
								$nc->data['max_news']= $max_news;
							}	
						}
						$enc = 0;
						for($i=1;$i<=$default_max_news;$i++){
							echo '<option value="';
							echo $i;
							echo '"'; 
							if (($i == $max_news) || (($i==$default_max_news) && !$enc) ){
								echo 'selected="selected"';
								$enc = 1;
							}
							echo'>';
							echo $i;
							echo '</option>';
						}

					?>
				    </select>
				</td>
			</tr>
			<!--Número de noticias en columna principal // Entries in main column -->
			<tr valign="top">
				<th><?php echo $emails_edit_text['main_entries']; ?></th>
				<td>
					<select name="max_main_news"> 
					<?php 
					    global $max_main_news;
						if($nc->data['max_main_news']!=0 && !isset($max_main_news)){ //max_main_news is saved in DB or has changed
							$max_main_news = $nc->data['max_main_news'];
						}else{
							if(isset($max_main_news)){
								$nc->data['max_main_news']= $max_main_news;
							}	
						}
						$enc = 0;
						for($i=1;$i<=$default_max_main_news;$i++){
							echo '<option value="';
							echo $i;
							echo '"'; 
							if (($i == $max_main_news) || (($i==$default_max_main_news) && !$enc) ){
								echo 'selected="selected"';
								$enc = 1;
							}
							echo'>';
							echo $i;
							echo '</option>';
						}

					?>
				    </select>
				</td>
			</tr>

<?php
		global $news_id;
		$id_order = 0;
		$num_posts = count($news_id);
		
		if (($num_posts >0) || (strlen($nc->data['order'])>0)) {
			if($num_posts >0){
				$string_order = implode(",",$news_id);
				$nc->data['order']= $string_order;
			}else{ //(strlen($nc->data['order'])>0)
				$string_order = $nc->data['order'];
				$news_id= explode(',',$string_order);
				$num_posts = count($news_id);
			}
			
			if (!$cat_changed){//If category has changed, news_order is no longer useful
				echo '<input type="hidden" id="news_order" name="news_order" size="80" value="';
					echo $string_order;
				echo '" />';
			} else {
				echo '<input type="hidden" id="news_order" name="news_order" size="80" value="" />';
			}
?>

			<tr valign="top">
              <th><?php echo $emails_edit_text['order'];?></th>
                <td>

<script>
	function implode (glue, pieces) {
	  // http://kevin.vanzonneveld.net
	  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	  // +   improved by: Waldo Malqui Silva
	  // +   improved by: Itsacon (http://www.itsacon.net/)
	  // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
	  // *     example 1: implode(' ', ['Kevin', 'van', 'Zonneveld']);
	  // *     returns 1: 'Kevin van Zonneveld'
	  // *     example 2: implode(' ', {first:'Kevin', last: 'van Zonneveld'});
	  // *     returns 2: 'Kevin van Zonneveld'
	  var i = '',
		retVal = '',
		tGlue = '';
	  if (arguments.length === 1) {
		pieces = glue;
		glue = '';
	  }
	  if (typeof pieces === 'object') {
		if (Object.prototype.toString.call(pieces) === '[object Array]') {
		  return pieces.join(glue);
		}
		for (i in pieces) {
		  retVal += tGlue + pieces[i];
		  tGlue = glue;
		}
		return retVal;
	  }
	  return pieces;
	}


	
	jQuery(document).ready(function($){
		var arrayElems = new Array();
		$( "#sortable" ).sortable({
			cursor: 'pointer',
			start: function (event, ui) {
					ui.item.toggleClass("highlight");
			},
			stop: function (event, ui) {
					ui.item.toggleClass("highlight");
			},
			update: function( event, ui ) { 
			  $('ul#sortable li').each(function(indice, elemento) {
				  arrayElems[indice] = $(elemento).attr('post_id');
				});
				document.getElementById('news_order').value = implode(',',arrayElems);
			}
		});
		$( "#sortable" ).disableSelection();
	});
</script>

					<?php echo $emails_edit_text['order_desc']; ?>
					<div class="hints" style="cursor: default">
						<ul id="sortable">
						  <?php while ($id_order < $num_posts) {?>
						    <li class="ui-state-default" post_id="<?php echo $news_id[$id_order];?>" ><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><?php echo $news_id[$id_order];?> - <?php echo  get_the_title($news_id[$id_order]);?></li>
						  <?php 
									$id_order++;
								 }//end while($id_order < $num_posts)
						   ?>
						</ul>

					</div>
<?php
		}//end if($num_posts >0)
?>
                </td>
            </tr>

            <tr valign="top">
                <th><?php echo $emails_edit_text['Title'];?></th>
                <td>

                    <?php $nc->text('subject', 70); ?>
                    
                   <div class="hints">
                        <?php echo $emails_edit_text['Example']; ?>: <strong><?php echo $emails_edit_text['example_subject']; ?></strong>
                   </div> 
                </td>
            </tr>

            <tr valign="top">
                <th><?php echo $emails_edit_text['Newsletter']; ?></th>
                <td>
					<?php
						$message_len = strlen($nc->data['message']);
						if($message_len>4294967295){
						$diff = $message_len - 4294967295;
						echo '<div class="hints">';
						echo $emails_edit_text['reduce1'];
						echo $diff;
						echo $emails_edit_text['reduce2'];
						echo '</div>';
						}
					?>	
				
                    <?php $nc->data['editor'] == 0?$nc->editor('message', 20):$nc->textarea_fixed('message', '100%', 400); ?>
                </td>
            </tr>


        </table>

        <p class="submit">
            <?php $nc->button('save', $save_text); ?>
            <?php $nc->button('html', 'HTML'); ?>
            <?php if ($nc->data['id'] != 0) $nc->button_confirm('delete', $delete_text, $delete_text.'?'); ?>
        </p>
		<?php $nc->hidden('category'); ?>
		<?php $nc->hidden('max_news'); ?>
		<?php $nc->hidden('max_main_news'); ?>
		<?php $nc->hidden('order'); ?>
    </form>
</div>

