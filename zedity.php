<?php
/*
Plugin Name: Zedity
Plugin URI: http://zedity.com
Description: Take your site to the next level by adding multimedia content with unprecedented possibilities and flexibility.
Version: 1.3.1
Author: Zuyoy LLC
Author URI: http://zuyoy.com
License: GPL3
*/

$path = plugin_dir_path(__FILE__);
require_once("$path../../../wp-includes/pluggable.php");


$file = "$path/premium.php";
if (file_exists($file)) include($file);




class WP_Zedity_Plugin {
	
	const MIN_WIDTH = 50;
	const MAX_WIDTH = 1920;
	const DEFAULT_WIDTH = 600; // looks like the max we can have in wordpress editor...

	const MIN_HEIGHT = 20;
	const MAX_HEIGHT = 6000;
	const DEFAULT_HEIGHT = 600;


	public function __construct() {
		$plugin = plugin_basename(__FILE__);

		if ((current_user_can('edit_posts') || current_user_can('edit_pages')) && get_user_option('rich_editing')) {

			// register actions
			add_action('admin_init', array(&$this, 'admin_init'));
			add_action('admin_menu', array(&$this, 'add_menu'));
			
			//add javascript
			add_action('admin_print_footer_scripts', array(&$this, 'add_js'));

			//add TinyMCE css
			add_filter('mce_css', array(&$this, 'add_mce_css'));
			//add TinyMCE buttons
			add_filter('mce_buttons', array(&$this,'register_mce_buttons'));
			add_filter('mce_external_plugins', array(&$this,'add_mce_buttons'));

			//link to the settings page
			add_filter("plugin_action_links_$plugin", array(&$this,'settings_link'));

			//Zedity into ThickBox
			add_thickbox();
			add_action('load-dashboard_page_zedity_editor', array(&$this,'add_zedity_editor_page'));

			//add custom css to reset Zedity content + webfonts
			add_action('wp_head', array(&$this,'add_head_css'));
			add_action('admin_head', array(&$this,'add_head_css'));

		}
	}
	
	public static function activate() {
		add_option('zedity_settings',array(
			'page_width' => self::DEFAULT_WIDTH,
			'page_height' => self::DEFAULT_HEIGHT,
			'webfonts' => array(),
			'watermark' => 'none',
		));
	}
	
	public static function deactivate() {
		delete_option('zedity_settings');
	}




	//----------------------------------------------------------------------------------------------
	//VIEWS


	public function admin_init() {
		register_setting('wp_zedity_plugin', 'zedity_settings', array($this,'zedity_settings_validate'));
		add_settings_section('zedity_main', 'Page', array($this,'section_zedity_page'), 'zedity_settings_section');
	}

	public function add_menu() {
		add_options_page('Zedity Settings', 'Zedity Editor', 'manage_options', 'wp_zedity_plugin', array(&$this, 'add_settings_page'));
		add_submenu_page(null, 'Zedity Editor', 'Zedity Editor', 'edit_pages', 'zedity_editor', array(&$this, 'add_zedity_editor_page'));
	}



	//----------------------------------------------------------------------------------------------
	//EDITOR


	public function add_zedity_editor_page() {
		require(ABSPATH . WPINC . '/version.php');
		$options = get_option('zedity_settings');
		include(sprintf("%s/views/editor.php", dirname(__FILE__)));
		exit;
	}



	//----------------------------------------------------------------------------------------------
	//JAVASCRIPT

	public function add_js(){
		?>
		<script type="text/javascript">
		jQuery(document).ready(function(){

			//Handle ThickBox window close
			var old_tb_remove = tb_remove;
			tb_remove = function(){
				var $iframe = jQuery('#TB_iframeContent');
				if ($iframe.hasClass('zedity-iframe')) {
					if ($iframe[0].contentWindow.zedityEditor.contentChanged) {
						var ret = confirm('Are you sure you want to close the Zedity Editor?\nIf you close you will lose any unsaved changes.\n\nTo save changes, select from the menu Page->Save.');
						if (!ret) return;
					}
					//deselect
					/*
					var mceiframe = window.document.getElementById('content_ifr');
					var idoc = mceiframe.contentDocument || mceiframe.contentWindow.document;
					idoc.getSelection().removeAllRanges();
					*/
				}
				old_tb_remove.apply(this,arguments);
				tinyMCE.get('content').plugins.zedity._closeZedity();
			};

			//Handle ThickBox window resize
			resizeForZedity = function(){
				jQuery('#TB_window').css({
					width: '90%',
					left: '5%',
					'margin-left': ''
				});
				var $iframe = jQuery('#TB_iframeContent');
				if ($iframe.length>0) {
					$iframe.css('width','100%');
					$iframe[0].contentWindow.resizeEditor($iframe[0].contentWindow.zedityEditor);
				}
			};
			jQuery(window).on('resize',resizeForZedity);

			//Handle WP editor switch
			if (window.switchEditors) {
				var old_go = switchEditors.go;
				switchEditors.go = function(){
					var ed = tinyMCE.get('content');
					if (ed) ed.plugins.zedity._hideOverlay();
					old_go.apply(this,arguments);
				};
			}

		});
		</script>
		<?php
	}
	

	//----------------------------------------------------------------------------------------------
	//TINYMCE

	public function register_mce_buttons($buttons) {
		$buttons[] = 'zedity';
	    return $buttons;
	}


	public function add_mce_buttons($plugins) {
		$plugins['zedity'] = plugins_url('mce/zedity-mce-button.js', __FILE__);
		return $plugins;
	}


	public function add_mce_css($mce_css) {
		@session_start();
		$options = get_option('zedity_settings');
		$_SESSION['zedity_webfonts'] = $options['webfonts'];

		if (!empty($mce_css)) $mce_css .= ',';
		$mce_css .= plugins_url('mce/mce-editor-zedity.css', __FILE__) . ',';
		$mce_css .= plugins_url('mce/webfonts.php', __FILE__);
		return $mce_css;
	}






	//----------------------------------------------------------------------------------------------
	//SETTINGS



	public function add_settings_page() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access	this page.'));
		}
		$options = get_option('zedity_settings');
		//Render the settings template
		include(sprintf("%s/views/settings.php", dirname(__FILE__)));
	}


	// Add a link to the settings page onto the plugin page
	public function settings_link($links) {
		$settings_link = '<a href="options-general.php?page=wp_zedity_plugin">Settings</a>';
		array_unshift($links, $settings_link);
		return $links;
	}


	//Validation
	public function zedity_settings_validate($input) {
		$options = get_option('zedity_settings');

		$options['page_width'] = trim($input['page_width']);
		if (!preg_match('/^[0-9]{3,5}$/i', $options['page_width'])) {
			$options['page_width'] = self::DEFAULT_WIDTH;
		}
		if ($options['page_width']<self::MIN_WIDTH) {
			$options['page_width'] = self::MIN_WIDTH;
		}
		if ($options['page_width']>self::MAX_WIDTH) {
			$options['page_width'] = self::MAX_WIDTH;
		}

		$options['page_height'] = trim($input['page_height']);
		if (!preg_match('/^[0-9]{3,5}$/i', $options['page_height'])) {
			$options['page_height'] = self::DEFAULT_HEIGHT;
		}
		if ($options['page_height']<self::MIN_WIDTH) {
			$options['page_height'] = self::MIN_HEIGHT;
		}
		if ($options['page_height']>self::MAX_WIDTH) {
			$options['page_height'] = self::MAX_HEIGHT;
		}

		$options['webfonts'] = array();
		if (isset($input['webfonts'])) {
			foreach ($input['webfonts'] as $font){
				$options['webfonts'][] = $font;
			}
		}

		$allowed = array('none','topleft','topright','bottomleft','bottomright');
		$options['watermark'] = 'none';
		if (in_array($input['watermark'],$allowed)) {
			$options['watermark'] = $input['watermark'];
		}

		return $options;
	}




	//----------------------------------------------------------------------------------------------
	//HEAD CSS


	public static function add_head_css() {
		$options = get_option('zedity_settings');
		?>
		<link rel="stylesheet" href="<?php echo plugins_url('css/zedity-reset.css', __FILE__); ?>" type="text/css" media="all" />
		<?php
		if (isset($options['webfonts'])) {
			foreach ($options['webfonts'] as $font) {
				$fontname = explode(',',$font);
				$fontname = urlencode($fontname[0]);
				?>
				<link href='//fonts.googleapis.com/css?family=<?php echo $fontname?>' rel='stylesheet' type='text/css'>
				<?php
			}
		}
	}


}



register_activation_hook(__FILE__, array('WP_Zedity_Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('WP_Zedity_Plugin', 'deactivate'));

$wp_zedity_plugin = new WP_Zedity_Plugin();






function zedity_get_all_webfonts() {
	$webfonts = array(
		'Caesar Dressing,cursive',
		'Crafty Girls,cursive',
		'Jacques Francois,serif',
		'Quintessential,cursive',
	);
	if (function_exists('zedity_get_premium_webfonts')) {
		$webfonts = array_unique(array_merge($webfonts, zedity_get_premium_webfonts()));
	}
	asort($webfonts);
	return $webfonts;
}



function zedity_get_all_videoembeds() {
	$vembeds = array(
		'youtube' => 'http://www.youtube.com',
		'vimeo' => 'http://vimeo.com',
	);
	if (function_exists('zedity_get_premium_videoembeds')) {
		$vembeds = array_unique(array_merge($vembeds, zedity_get_premium_videoembeds()));
	}
	return $vembeds;
}



function zedity_get_all_audioembeds() {
	$aembeds = array(
		'soundcloud' => 'http://soundcloud.com',
		'reverbnation' => 'http://www.reverbnation.com',
	);
	if (function_exists('zedity_get_premium_audioembeds')) {
		$aembeds = array_unique(array_merge($aembeds, zedity_get_premium_audioembeds()));
	}
	return $aembeds;
}


