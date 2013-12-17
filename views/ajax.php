<?php
if ($_SERVER['REQUEST_METHOD']=='POST' && empty($_POST) && $_SERVER['CONTENT_LENGTH']>0) {
	echo '{ "error": "Post size is too big." }';
	exit;
}

if (empty($_REQUEST['action'])) {
	echo '{ "error": "Malformed request." }';
	exit;
}

switch ($_REQUEST['action']) {
	//------------------------------------------------------------------------------------
	case 'save':

		if (empty($_POST['content'])) {
			echo '{ "error": "Ooops... It looks like the content is empty." }';
			exit;
		}
		if (empty($_POST['title'])) {
			echo '{ "error": "Ooops... The title is missing." }';
			exit;
		}
		$content = stripslashes($_POST['content']);
		$title = stripslashes($_POST['title']);
		
		//get upload dir
		$dir = wp_upload_dir();
		
		if (!empty($_POST['id']) && $_POST['id']>0) {
			$attach_id = $_POST['id'];
			//get file name
			$oldfile = get_attached_file($attach_id);
			$filename = basename($oldfile);
			$dirname = dirname($oldfile);
			$dirname = trim($dirname,$dir['basedir']);
			$dirname = trim($dirname,$filename);
			//get dir info (may be in older subdirectory)
			$dir = wp_upload_dir($dirname);
			$edit = TRUE;
		} else {
			//get unique file name from content title
			$filename = sanitize_file_name("$title.html");
			$filename = wp_unique_filename($dir['path'], $filename);
			$edit = FALSE;
		}

		if (!is_writable($dir['path'])) {
			echo '{ "error": "Upload directory is not writable." }';
			exit;
		}
		
		$css = '<style>html,body{padding:0;margin:0}</style>';
		$js = '';
		if ($this->is_premium() && strpos($content,'zedity-responsive')!==FALSE) {
			$css .= '<style>.zedity-responsive{-webkit-transform-origin:0 0;-moz-transform-origin:0 0;-ms-transform-origin:0 0;-o-transform-origin:0 0;transform-origin:0 0}</style>';
			$js = '<script type="text/javascript">(function(){var e=document.querySelector(\'.zedity-responsive\');if(!e)return;var ow=e.offsetWidth;var oh=e.offsetHeight;var ar=false;window.onresize=(function resize(){var w=window.innerWidth/ow;var h=window.innerHeight/oh;if (ar)w=h=Math.min(w,h);var y=e.style;y.webkitTransform=y.MozTransform=y.msTransform=y.OTransform=y.transform=\'scale(\'+w+\',\'+h+\')\';return resize;})();})();</script>';
		}
		//webfonts in iframe
		if (isset($options['webfonts'])) {
			foreach ($options['webfonts'] as $font) {
				$fontname = explode(',',$font);
				$fontname = urlencode($fontname[0]);
				$css .= "<link href=\"//fonts.googleapis.com/css?family=$fontname\" rel=\"stylesheet\" type=\"text/css\">";
			}
		}
		
		//construct html
		$content = "<html><head><title>$title</title>$css</head><body>$content $js</body></html>";

		$ret = @file_put_contents("{$dir['path']}/$filename", $content);
		if ($ret===FALSE) {
			echo '{ "error": "Error writing to file." }';
			exit;
		}
		
		$file['type'] = 'application/zedity';
		$file['path'] = $dir['path'];
		$file['url'] = $dir['url'];
		$file['filename'] = $filename;
		$file['full_path'] = "{$dir['path']}/$filename";
		$file['full_url'] = "{$dir['url']}/$filename";

		$attachment = array(
			'guid' => $file['full_url'],
			'post_type' => 'attachment',
			'post_title' => $title,
			'post_content' => '',
			'post_parent' => empty($_POST['post_id']) ? 0 : $_POST['post_id'],
			'post_mime_type' => $file['type'],
		);
		
		if ($edit) {
			$attachment['ID'] = $attach_id;
		}

		//attach
		$attach_id = wp_insert_attachment($attachment,$file['full_path']);
		//update metadata
		if (!is_wp_error($attach_id)) {
			require_once(ABSPATH . '/wp-admin/includes/image.php');
			$attachment_metadata = wp_generate_attachment_metadata($attach_id,$file['full_path']);
			$a = wp_update_attachment_metadata($attach_id,$attachment_metadata);
		}
		
		$response = array(
			'id' => $attach_id,
			'url' => $file['full_url'],
		);
		
	break;
	
	
	//------------------------------------------------------------------------------------
	case 'load':
		
		if (empty($_REQUEST['id'])) {
			echo '{ "error": "Malformed request." }';
			exit;
		}
		
		$filename = get_attached_file($_REQUEST['id']);
		
		$content = @file_get_contents($filename);
		if ($content===FALSE) {
			echo '{ "error": "Error reading file." }';
			exit;
		}
		
		$response = array(
			'content' => $content,
		);
		
	break;

	//------------------------------------------------------------------------------------
	default:
		echo '{ "error": "Malformed request." }';
		exit;
}



//return response
echo json_encode($response);






/*
if (!function_exists('wp_handle_upload')) {
	require_once(ABSPATH.'wp-admin/includes/file.php');
}

$uploadedfile = $_FILES['file'];
$movefile = wp_handle_upload($uploadedfile, array('test_form' => false));
if ($movefile) {
	echo 'OK!.\n';
	var_dump($movefile);
} else {
	echo 'Error!\n';
}
*/
