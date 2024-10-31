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

    <table width="720" border="0" cellspacing="0" cellpadding="0" align="center" style="font-family:Arial,Geneva,sans-serif;font-size:12px;background-color:#FFFFFF;" bgcolor="#FFFFFF"> 
        <tr>
            <td width="10">&nbsp;</td>
            <td>
                <table width="100%">
                    <tr>
                        <td align="center" style="font-family:Arial,Geneva,sans-serif;font-size:12px;">Si no ves correctamente este e-mail haz <a href="#NEWSLETTER_URL" style="color:#935D27" target="_blank">click aqu&iacute;</a></td>
                    </tr>
                </table>
            </td>
            <td width="10">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="3" height="10">&nbsp;</td>
        </tr>
        <tr>
            <td width="10">&nbsp;</td>
            <td bgcolor="#6D9322">
                <table width="100%">
                    <tr>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td width="500"><a href="http://wordpress.org/" target="_blank"><img alt="Example" border="0" style="display:block" src="<?php echo $dir_img; ?>/_img/logo_example.png" /></a></td>
                                    <td align="center"></td>
                                    <td width="10">&nbsp;</td>
                                    <td align="center"><a href="http://www.facebook.com/#" target="_blank"><img alt="Hazte fan en Facebook" src="<?php echo $dir_img; ?>/_img/facebook.png" border="0" style="display:block" /></a>
                                        <a href="https://twitter.com/#" target="_blank"><img alt="S&iacute;guenos en Twitter" src="<?php echo $dir_img; ?>/_img/twitter.png" border="0" style="margin-top:5px;display:block" /></a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
            <td width="10">&nbsp;</td>
        </tr>
        <tr>
            <td width="10">&nbsp;</td>
            <td>
                <table width="100%">
                    <tr>
                        <td style="font-family:Arial,Geneva,sans-serif;font-size:20px;"><a href="#" target="_blank" style="text-decoration: none; color: #312f2d; font-weight: bold;"><?php echo $title; ?></a></td>
                    </tr>
                </table>
            </td>
            <td width="10">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="3" height="10">&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>
                <table width="100%">
<?php
	$p = 0;
	$n = 0;
	
	if (strlen($_POST['news_order'])>0) {
		$posts_id = explode(",",$_POST['news_order']);
		$order_nums = count($posts_id); //Count of numbers in hidden "Orden"
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
									$new_image[] = nt_post_image($news_id[$n],'newsletter-leftside');
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
									$new_image[] = nt_post_image(get_the_ID(),'newsletter-leftside');
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
?>
                    <tr>
                        <td>
                            <table width="100%" style="border:1px solid #CCC;" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="230" rowspan="2"><a href="<?php echo $new_link[$n]; ?>" target="_blank"><img alt="<?php echo $new_title[$n]; ?>" src="<?php echo $new_image[$n]; ?>" width="230" border="0" /></a></td>
                                    <td width="6" rowspan="2">&nbsp;</td>
                                    <td valign="top">
                                        <table width="100%">
                                            <tr>
                                                <td><a href="<?php echo $new_link[$n]; ?>" target="_blank" style="font-family:Arial,Geneva,sans-serif;font-size:18px;color:#935D27;text-decoration:none"><strong><?php echo $new_title[$n]; ?></strong></a></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <span style="font-family:Arial,Geneva,sans-serif;font-size:12px;"><?php echo $new_htmlcontent[$n]; ?></span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="bottom">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td align="right">
                                                	<div style="text-align:right;margin-right: 20px; margin-bottom: 10px">
                                                		<a href="<?php echo $new_link[$n]; ?>" target="_blank" style="font-family:Arial,Geneva,sans-serif; font-size:11px; text-align: right;color:#FFF;padding:4px;background-color:#935d27;text-decoration:none"><span style="#FFF">Leer más</span></a>
                                                  	</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td height="30" align="right" style="border-top: 1px solid #CCC; background-color:#D9D9D9;font-family:Arial,Geneva,sans-serif;font-size:12px;">Comp&aacute;rtelo en <a href="https://twitter.com/share?url=<?php echo $new_link[$n]; ?>&amp;lang=es&amp;via=example" target="_blank" style="font-family:'Courier New', Courier, monospace;font-size:12px;padding:1px;background-color:#1daced;color:#FFFFFF;text-decoration:none;">&nbsp;twitter&nbsp;</a> <a href="http://www.facebook.com/share.php?u=<?php echo $new_link[$n]; ?>" target="_blank" style="font-family:Arial,Geneva,sans-serif;font-size:12px;padding:1px;background-color:#3C5A98;color:#FFFFFF;text-decoration:none;">&nbsp;Facebook&nbsp;</a>&nbsp;</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                  </td>
                    </tr>
                    <!-- ESPACIO ENTREOFERTA -->
                    <tr>
                        <td height="10">&nbsp;</td>
                    </tr>
                    <!-- FIN ESPACIO ENTREOFERTA -->
<?php
	
		$n++;
	}
?>
                </table>
            </td>
            <td>&nbsp;</td>
        </tr>
        <!-- AVISO LEGAL -->
        <tr>
            <td width="10">&nbsp;</td>
            <td>
                <table width="100%">
                    <tr>
                      <td style="font-family:Arial,Geneva,sans-serif;font-size:10px;color:#666;text-align:justify;border-top:1px solid #CCC">AVISO LEGAL. Conforme a la Ley 34/2002 de Servicios de la Sociedad de la Informaci&oacute;n y Comercio Electr&oacute;nico, as&iacute; como a la vigente Ley Org&aacute;nica 15/1999 de Protecci&oacute;n de Datos de car&aacute;cter personal, su direcci&oacute;n de correo electr&oacute;nico est&aacute; incluida en nuestra base de datos con el fin de seguir ofreci&eacute;ndole informaci&oacute;n que consideramos de su inter&eacute;s. Puede ejercer sus derechos de acceso, rectificaci&oacute;n, oposici&oacute;n y cancelaci&oacute;n de los mismos, as&iacute; como gestionar las comunicaciones electr&oacute;nicas que le enviamos haciendo <a href="#" target="_blank" style="color:#935D27">clic aqu&iacute;</a>.</td>
                    </tr>
                </table>
            </td>
            <td width="10">&nbsp;</td>
        </tr>
		<!-- FIN AVISO LEGAL -->
    </table>
<!-- NEWS-Fin --> <!-- Don't remove this line -->