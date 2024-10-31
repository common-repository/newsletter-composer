<?php
error_reporting(E_ALL ^ E_NOTICE);

$newsletter->set_limits();

if (!isset($newsletter_options_main['no_translation'])) {
	load_plugin_textdomain( 'newsletter-composer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

$action = $_REQUEST['act'];
$step = null;
if (isset($action) && !check_admin_referer()) die('Invalid call');
$errors = null;
$messages = null;

/**
 * Utility class to generate HTML form fields.
 */
class NewsletterControls {

    var $data;
    var $action = false;

    function is_action($action = null) {
        if ($action == null) return !empty($_REQUEST['act']);
        if (empty($_REQUEST['act'])) return false;
        if ($_REQUEST['act'] != $action) return false;
        if (check_admin_referer ()) return true;
        die('Invalid call');
    }

    function errors($errors) {
        if (is_null($errors)) return;
        echo '<script type="text/javascript">';
        echo 'alert("' . addslashes($errors) . '");';
        echo '</script>';
    }

    function messages($messages) {
        if (is_null($messages)) return;
        echo '<script type="text/javascript">';
        echo 'alert("' . addslashes($messages) . '");';
        echo '</script>';
    }

    function NewsletterControls($options=null) {
        if ($options == null) $this->data = stripslashes_deep($_POST['options']);
        else $this->data = $options;
    }

    function select($name, $options, $first = null) {
        $value = $this->data[$name];

        echo '<select id="options-' . $name . '" name="options[' . $name . ']">';
        if (!empty($first)) {
            echo '<option value="">' . htmlspecialchars($first) . '</option>';
        }
        foreach ($options as $key => $label) {
            echo '<option value="' . $key . '"';
            if ($value == $key) echo ' selected';
            echo '>' . htmlspecialchars($label) . '</option>';
        }
        echo '</select>';
    }

    function select_grouped($name, $groups) {
        $value = $this->data[$name];

        echo '<select name="options[' . $name . ']">';

        foreach ($groups as $group) {
            echo '<optgroup label="' . htmlspecialchars($group['']) . '">';
            foreach ($group as $key => $label) {
                if ($key == '') continue;
                echo '<option value="' . $key . '"';
                if ($value == $key) echo ' selected';
                echo '>' . htmlspecialchars($label) . '</option>';
            }
            echo '</optgroup>';
        }
        echo '</select>';
    }
    
    function value($name) {
        echo htmlspecialchars($this->data[$name]);
    }

    function value_date($name) {
        $time = $this->data[$name];
        echo gmdate(get_option('date_format') . ' ' . get_option('time_format'), $time + get_option('gmt_offset') * 3600);
    }

    function text($name, $size=20) {
        echo '<input name="options[' . $name . ']" type="text" size="' . $size . '" value="';
        echo htmlspecialchars($this->data[$name]);
        echo '"/>';
    }

    function hidden($name) {
        echo '<input name="options[' . $name . ']" type="hidden" value="';
        echo htmlspecialchars($this->data[$name]);
        echo '"/>';
    }

    function button($action, $label, $function=null) {
        if (!$this->action) echo '<input name="act" type="hidden" value=""/>';
        $this->action = true;
        if ($function != null) {
            echo '<input class="button-secondary" type="submit" value="' . $label . '" onclick="this.form.act.value=\'' . $action . '\';' . htmlspecialchars($function) . '"/>';
        }
        else {
            echo '<input class="button-secondary" type="submit" value="' . $label . '" onclick="this.form.act.value=\'' . $action . '\';this.form.submit()"/>';
        }
    }

    function button_confirm($action, $label, $message, $data='') {
        if (!$this->action) echo '<input name="act" type="hidden" value=""/>';
        $this->action = true;
        echo '<input class="button-secondary" type="button" value="' . $label . '" onclick="this.form.btn.value=\'' . $data . '\';this.form.act.value=\'' . $action . '\';if (confirm(\'' .
        htmlspecialchars($message) . '\')) this.form.submit()"/>';
    }

    function editor($name, $rows=5, $cols=75) {
        echo '<textarea class="visual" name="options[' . $name . ']" style="width: 100%" wrap="off" rows="' . $rows . '" cols="' . $cols . '">';
        echo htmlspecialchars($this->data[$name]);
        echo '</textarea>';
    }

    function textarea($name, $width='100%', $height='50') {
        echo '<textarea class="dymanic" name="options[' . $name . ']" wrap="off" style="width:' . $width . ';height:' . $height . '">';
        echo htmlspecialchars($this->data[$name]);
        echo '</textarea>';
    }

    function textarea_fixed($name, $width='100%', $height='50') {
        echo '<textarea name="options[' . $name . ']" wrap="off" style="width:' . $width . ';height:' . $height . '">';
        echo htmlspecialchars($this->data[$name]);
        echo '</textarea>';
    }

    function checkbox($name, $label='') {
        echo '<input type="checkbox" id="' . $name . '" name="options[' . $name . ']" value="1"';
        if (!empty($this->data[$name])) echo ' checked="checked"';
        echo '/>';
        if ($label != '') echo ' <label for="' . $name . '">' . $label . '</label>';
    }

    function init() {
        echo '<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery("textarea.dynamic").focus(function() {
            jQuery("textarea.dynamic").css("height", "50px");
            jQuery(this).css("height", "400px");
        });
    });
</script>
';
        echo '<input name="act" type="hidden" value=""/>';
        echo '<input name="btn" type="hidden" value=""/>';
        $this->action = true;
        wp_nonce_field();
    }

    function save($table, $data=null) {
        global $wpdb;
        if ($data == null) $data = $this->data;
        $keys = array_keys($data);
		$i=0;
        foreach ($keys as $key) {
			$i++;
            if ($key[0] == '_') unset($data[$key]);
        }
        $id = $data['id'];
        unset($data['id']);
        if (empty($id)) {
            $wpdb->insert($table, $data);
            $id = $wpdb->insert_id;
        }
        else {
            $wpdb->update($table, $data, array('id' => $id));
        }
        $this->data = $wpdb->get_row("select * from " . $table . " where id=" . $id, ARRAY_A);
    }

    function load($table, $id) {
        global $wpdb;
        if ($id == 0) $this->data = array('id' => 0);
        else $this->data = $wpdb->get_row("select * from " . $table . " where id=" . $id, ARRAY_A);
    }

    function update($table, $field, $value, $id=null) {
        global $wpdb;
        if ($id == null) $id = $this->data['id'];
        $wpdb->query("update " . $table . " set " . $field . "='" . mysql_escape_string($value) . "' where id=" . $id);
        $this->data[$field] = $value;
    }

}

$newsletter_options_main = get_option('newsletter_main', array());


/**
 * Retrieves a list of custom themes located under wp-plugins/newsletter-composer/themes.
 * Return a list of theme names (which are folder names where the theme files are stored.
 */
function newsletter_get_themes() {
    $handle = @opendir(ABSPATH . 'wp-content/plugins/newsletter-composer/themes');
    $list = array();
    if (!$handle) return $list;
    while ($file = readdir($handle)) {
        if ($file == '.' || $file == '..') continue;
        if (!is_dir(ABSPATH . 'wp-content/plugins/newsletter-composer/themes/' . $file)) continue;
        if (!is_file(ABSPATH . 'wp-content/plugins/newsletter-composer/themes/' . $file . '/theme.php')) continue;
        $list['*' . $file] = $file;
    }
    closedir($handle);
    return $list;
}


function output_file($file, $name, $mime_type='')
{
	 /*
	 This function takes a path to a file to output ($file), 
	 the filename that the browser will see ($name) and 
	 the MIME type of the file ($mime_type, optional).
	 
	 If you want to do something on download abort/finish,
	 register_shutdown_function('function_name');
	 */
	 if(!is_readable($file)) die('File not found or inaccessible!');
	 
	 $size = filesize($file);
	 $name = rawurldecode($name);
	 
	 /* Figure out the MIME type (if not specified) */
	 $known_mime_types=array(
		"pdf" => "application/pdf",
		"txt" => "text/plain",
		"html" => "text/html",
		"htm" => "text/html",
		"exe" => "application/octet-stream",
		"zip" => "application/zip",
		"doc" => "application/msword",
		"xls" => "application/vnd.ms-excel",
		"ppt" => "application/vnd.ms-powerpoint",
		"gif" => "image/gif",
		"png" => "image/png",
		"jpeg"=> "image/jpg",
		"jpg" =>  "image/jpg",
		"php" => "text/plain"
	 );
	 
	 if($mime_type==''){
		 $file_extension = strtolower(substr(strrchr($file,"."),1));
		 if(array_key_exists($file_extension, $known_mime_types)){
			$mime_type=$known_mime_types[$file_extension];
		 } else {
			$mime_type="application/force-download";
		 };
	 };
	 
	 @ob_end_clean(); //turn off output buffering to decrease cpu usage
	 
	 // required for IE, otherwise Content-Disposition may be ignored
	 if(ini_get('zlib.output_compression'))
	  ini_set('zlib.output_compression', 'Off');
	 
	 header('Content-Type: ' . $mime_type);
	 header('Content-Disposition: attachment; filename="'.$name.'"');
	 header("Content-Transfer-Encoding: binary");
	 header('Accept-Ranges: bytes');
	 
	 /* The three lines below basically make the 
		download non-cacheable */
	 header("Cache-control: private");
	 header('Pragma: private');
	 header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	 
	 // multipart-download and download resuming support
	 if(isset($_SERVER['HTTP_RANGE']))
	 {
		list($a, $range) = explode("=",$_SERVER['HTTP_RANGE'],2);
		list($range) = explode(",",$range,2);
		list($range, $range_end) = explode("-", $range);
		$range=intval($range);
		if(!$range_end) {
			$range_end=$size-1;
		} else {
			$range_end=intval($range_end);
		}
	 
		$new_length = $range_end-$range+1;
		header("HTTP/1.1 206 Partial Content");
		header("Content-Length: $new_length");
		header("Content-Range: bytes $range-$range_end/$size");
	 } else {
		$new_length=$size;
		header("Content-Length: ".$size);
	 }
	 
	 /* output the file itself */
	 $chunksize = 1*(1024*1024); //you may want to change this
	 $bytes_send = 0;
	 if ($file = fopen($file, 'r'))
	 {
		if(isset($_SERVER['HTTP_RANGE']))
		fseek($file, $range);
	 
		while(!feof($file) && 
			(!connection_aborted()) && 
			($bytes_send<$new_length)
			  )
		{
			$buffer = fread($file, $chunksize);
			print($buffer); //echo($buffer); // is also possible
			flush();
			$bytes_send += strlen($buffer);
		}
	 fclose($file);
	 } else die('Error - can not open file.');
	 
	die();
}
?>
