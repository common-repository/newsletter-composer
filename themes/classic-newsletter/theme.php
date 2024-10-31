<?php
// PHP HEADER is REQUIRED
// PHP HEADER
$posts = new WP_Query();

$cat_selected = $_POST['category_id'];
$posts->query(array('cat' => $cat_selected)); //Classified as selected category array
$num_found_posts= $posts->post_count; //number of found posts
$max_news = $_POST['max_news'];
$max_main_news = $_POST['max_main_news'];


if($max_news > $num_found_posts){
	$max_news = $num_found_posts;
	$max_news = $num_found_posts;
}

if($max_main_news > $max_news){
	$max_main_news = $max_news;
}

$dir_img = plugins_url().'/'.plugin_basename(__FILE__);
$dir_array = explode('/',$dir_img);
$last= count($dir_array) - 1;
unset($dir_array[$last]);
$dir_img = implode('/',$dir_array);

$title =  $_POST['options']['subject'];

// END PHP HEADER

// SHORTCODES
/*
To call the title text you can use this: <?php echo $title; ?>
To call an image or some file from this folder you can use this: <?php echo $dir_img; ?>
To get URL of the HTML file will saved you can use #NEWSLETTER_URL
*/
// END SHORT CODES
?>
<!-- NEWS-Inicio --><!-- CSS --><!-- Don't remove this line -->
<!-- HEADER -->
<p align="center"><a href="#NEWSLETTER_URL" target="_blank" style="color:#000;font-size:10px;font-family:Arial,Verdana">If you cannot view this email correctly please click here</a></p>
<table class="head-wrap" bgcolor="#999999">
	<tr>
		<td></td>
		<td class="header container" align="">
			
			<!-- /content -->
			<div class="content">
				<table bgcolor="#999999" >
					<tr>
						<td><img src="<?php echo $dir_img; ?>/_img/logo_example.png" /></td>
						<td align="right"><h6 class="collapse"><?php echo $title; ?></h6></td>
					</tr>
				</table>
			</div><!-- /content -->
			
		</td>
		<td></td>
	</tr>
</table><!-- /HEADER -->

<!-- BODY -->
<table class="body-wrap" bgcolor="">
	<tr>
		<td></td>
		<td class="container" align="" bgcolor="#FFFFFF">

<?php
	$p = 0;
	$n = 0;
	
	if (strlen($_POST['news_order'])>0) {
		$posts_id = explode(",",$_POST['news_order']);
		$order_nums = count($posts_id); //Count of numbers in hidden "Order"
		if (($order_nums==$max_news) || (($order_nums<=$max_news) && ($order_nums == $num_found_posts))){
			$news_id = $posts_id;
		}
	}
	
	
	 while (($posts->have_posts()) && ($n< $max_news)) {
		$posts->the_post();
		
		if ((strlen($_POST['news_order'])>0) && 
		   (($order_nums==$max_news) || (($order_nums<=$max_news) && ($order_nums == $num_found_posts)))) {
			$post_id = get_post($posts_id[$n]); 
			$new_title[] = $post_id->post_title;
			$category = get_the_category($post_id->ID); 
			$new_category_id[] = $category[0]->term_id;
			$new_category[] = $category[0]->cat_name;
			$new_image[] = nt_post_image($news_id[$n],'st_medium_thumb');
			$new_link[] = get_permalink($news_id[$n]);
			$new_content = apply_filters('the_content', $post_id->post_content);
			$new_content = preg_replace('/<img[^>]+./','', $new_content);
			$new_content = preg_replace('/<p class="wp-caption-text">.+?<\/p>/','',$new_content);
			$new_content = preg_replace('/<p>&nbsp;<\/p>/','',$new_content);
			$new_content = preg_replace('/width: 610px/','display:none',$new_content);
			$new_content = preg_replace('/width: 310px/','display:none',$new_content);									
			$new_content = preg_replace('/<p>/i','<p style="color: rgb(85, 85, 85); font-family: Calibri, Arial, Helvetica, sans-serif; font-size: 14px; line-height: 18px; text-align:left;">',$new_content);
			$new_content = preg_replace('/<h2>/i','<h2 style="font-size: 18px; line-height: normal; color: rgb(51, 51, 51); margin-top: 5px; margin-bottom: 10px; font-weight: normal; font-family: Calibri, Arial, Helvetica, sans-serif; text-align:left;">',$new_content);
			$new_content = preg_replace('/<h3>/i','<h3 style="font-size: 16px; line-height: 18px; color: rgb(85, 85, 85); margin-top: 4px; margin-bottom: 4px; font-weight: normal; font-family: Calibri, Arial, Helvetica, sans-serif; text-align:left;">',$new_content);
			$new_content = preg_replace('/<h6>/i','<h6 style="font-size: 12px; line-height:18px; color: rgb(85, 85, 85); margin-top: 4px; margin-bottom: 4px; font-weight:normal; font-family: Calibri, Arial, Helvetica, sans-serif; text-align:left;">',$new_content);
			$new_content = preg_replace('/<h4>/i','<h4 style="font-size: 12px; line-height: 18px; color: rgb(85, 85, 85); margin-top: 4px; margin-bottom: 4px; font-weight: normal; font-family: Calibri, Arial, Helvetica, sans-serif; text-align:left;">',$new_content);
			$new_content = preg_replace('/<p dir="ltr">/i','<p style="color: rgb(85, 85, 85); font-family: Calibri, Arial, Helvetica, sans-serif; font-size: 14px; line-height: 18px; text-align:left;">',$new_content);
			$new_content = preg_replace('/<div id="attach.+?<\/div>/','',$new_content);
			$new_content = preg_replace('/<form.+?<\/form>/','',$new_content);
			$new_htmlcontent[] = $new_content;
		} else {
			$news_id[] = get_the_ID();
			$new_title[] = get_the_title($news_id[$n]);
			$category = get_the_category($news_id[$n]);
			$new_category[] = $category[0]->cat_name;
			$new_category_id[] = $category[0]->term_id;
			$new_image[] = nt_post_image(get_the_ID(),'st_medium_thumb');
			$new_link[] = get_permalink($news_id[$n]);
			ob_start();
			the_content();
			$new_content = preg_replace('/<img[^>]+./','', ob_get_contents());
			$new_content = preg_replace('/<p class="wp-caption-text">.+?<\/p>/','',$new_content);
			$new_content = preg_replace('/<p>&nbsp;<\/p>/','',$new_content);
			$new_content = preg_replace('/width: 610px/','display:none',$new_content);
			$new_content = preg_replace('/width: 310px/','display:none',$new_content);									
			$new_content = preg_replace('/<p>/i','<p style="color: rgb(85, 85, 85); font-family: Calibri, Arial, Helvetica, sans-serif; font-size: 14px; line-height: 18px; text-align:left;">',$new_content);
			$new_content = preg_replace('/<h2>/i','<h2 style="font-size: 18px; line-height: normal; color: rgb(51, 51, 51); margin-top: 5px; margin-bottom: 10px; font-weight: normal; font-family: Calibri, Arial, Helvetica, sans-serif; text-align:left;">',$new_content);
			$new_content = preg_replace('/<h3>/i','<h3 style="font-size: 16px; line-height: 18px; color: rgb(85, 85, 85); margin-top: 4px; margin-bottom: 4px; font-weight: normal; font-family: Calibri, Arial, Helvetica, sans-serif; text-align:left;">',$new_content);
			$new_content = preg_replace('/<h6>/i','<h6 style="font-size: 12px; line-height:18px; color: rgb(85, 85, 85); margin-top: 4px; margin-bottom: 4px; font-weight:normal; font-family: Calibri, Arial, Helvetica, sans-serif; text-align:left;">',$new_content);
			$new_content = preg_replace('/<h4>/i','<h4 style="font-size: 12px; line-height: 18px; color: rgb(85, 85, 85); margin-top: 4px; margin-bottom: 4px; font-weight: normal; font-family: Calibri, Arial, Helvetica, sans-serif; text-align:left;">',$new_content);
			$new_content = preg_replace('/<p dir="ltr">/i','<p style="color: rgb(85, 85, 85); font-family: Calibri, Arial, Helvetica, sans-serif; font-size: 14px; line-height: 18px; text-align:left;">',$new_content);
			$new_content = preg_replace('/<div id="attach.+?<\/div>/','',$new_content);
			$new_content = preg_replace('/<form.+?<\/form>/','',$new_content);
			$new_htmlcontent[] = $new_content;
			ob_end_clean();
		}

		if ($p<$max_main_news) {
			$p++;
?>

			
			<!-- content -->
			<div class="content">
				<table>
					<tr>
						<td>
							
							<h1><?php echo $new_title[$n]; ?></h1>
							<!--p class="lead">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et.</p-->
							
							<!-- A Real Hero (and a real human being) -->
							<p><a href="<?php echo $new_link[$n]; ?>"><img src="<?php echo $new_image[$n]; ?>" /></a></p><!-- /hero -->
							<p><?php echo $new_htmlcontent[$n]; ?></p>
						</td>
					</tr>
				</table>
			</div><!-- /content -->

<?php
		} else {

?>

			
			<!-- content -->
			<div class="content">
				
				<table bgcolor="">
					<tr>
						<td class="small" width="20%" style="vertical-align: top; padding-right:10px;"><img src="<?php echo $new_image[$n]; ?>" /></td>
						<td>
							<h4><?php echo $new_title[$n]; ?></h4>
							<p class=""><?php echo $new_htmlcontent[$n]; ?></p>
							<a class="btn">Read more &raquo;</a>
						</td>
					</tr>
				</table>
			
			</div><!-- /content -->


<?php
		}
	
		$n++;
	}
?>
			
			
			<!-- content -->
			<div class="content">
				<table bgcolor="">
					<tr>
						<td>
							
							<!-- social & contact -->
							<table bgcolor="" class="social" width="100%">
								<tr>
									<td>
										
										<!--- column 1 -->
										<div class="column">
											<table bgcolor="" cellpadding="" align="left">
										<tr>
											<td>				
												
												<h5 class="">Connect with Us:</h5>
												<p class=""><a href="#" class="soc-btn fb">Facebook</a> <a href="#" class="soc-btn tw">Twitter</a> <a href="#" class="soc-btn gp">Google+</a></p>
						
												
											</td>
										</tr>
									</table><!-- /column 1 -->
										</div>
										
										<!--- column 2 -->
										<div class="column">
											<table bgcolor="" cellpadding="" align="left">
										<tr>
											<td>				
																			
												<h5 class="">Contact Info:</h5>												
												<p>Phone: <strong>408.341.0600</strong><br/>
                Email: <strong><a href="emailto:hseldon@trantor.com">hseldon@trantor.com</a></strong></p>
                
											</td>
										</tr>
									</table><!-- /column 2 -->	
										</div>
										
										<div class="clear"></div>
	
									</td>
								</tr>
							</table><!-- /social & contact -->
							
						</td>
					</tr>
				</table>
			</div><!-- /content -->
			

		</td>
		<td></td>
	</tr>
</table><!-- /BODY -->

<!-- FOOTER -->
<table class="footer-wrap">
	<tr>
		<td></td>
		<td class="container">
			
				<!-- content -->
				<div class="content">
					<table>
						<tr>
							<td align="center">
								<p>
									<a href="#">Terms</a> |
									<a href="#">Privacy</a> |
									<a href="#"><unsubscribe>Unsubscribe</unsubscribe></a>
								</p>
							</td>
						</tr>
					</table>
				</div><!-- /content -->
				
		</td>
		<td></td>
	</tr>
</table><!-- /FOOTER -->

<!-- NEWS-Fin --> <!-- Don't remove this line -->