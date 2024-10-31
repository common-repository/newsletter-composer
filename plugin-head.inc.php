<?php $options = get_option('newsletter_profile'); 
//Select language
$blog_language = get_bloginfo('language'); 
if(!strcmp($blog_language,'es-ES')){
	@include_once 'languages/es-ES.php';
}else{
	@include_once 'languages/en-EN.php';
}

?>
<script type="text/javascript">
//<![CDATA[
function newsletter_check(f) {
    var re = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-]{2,})+\.)+([a-zA-Z0-9]{2,})+$/;
    if (!re.test(f.elements["suscriber_email"].value)) {
        alert("<?php echo $email_error; ?>");
        return false;
    }
	
    return true;
}
//]]></script>