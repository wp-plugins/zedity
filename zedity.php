<?php
/*
Plugin Name: Zedity
Plugin URI: http://zedity.com/plugin/wp
Description: Finally you can create any design you want, the way you have been wishing for!
Version: 1.4.4
Author: Zuyoy LLC
Author URI: http://zuyoy.com
License: GPL3
License URI: http://zedity.com/license/freewp
*/

$path = plugin_dir_path(__FILE__);
$filepremium = "$path/premium.php";

//check if another Zedity plugin is already enabled
if (class_exists('WP_Zedity_Plugin')) {
	
	if (file_exists($filepremium)) {
		$thisp .= 'Premium';
		$otherp.= 'free';
	} else {
		$thisp .= 'free';
		$otherp.= 'Premium';
	}

	$message = "<b>Could not activate</b> Zedity $thisp plugin. You cannot enable both Zedity free and Zedity Premium plugins at the same time.<br/>Please <b>first deactivate</b> the Zedity $otherp plugin, <b>then activate</b> the Zedity $thisp plugin.";
	
	if (isset($_GET['action']) && $_GET['action'] == 'error_scrape') {
		echo $message;
		exit;
	} else {
		trigger_error($message, E_USER_ERROR);
	}
	
} else {



	class WP_Zedity_Plugin {
		
		const MIN_WIDTH = 50; // pixels
		const MAX_WIDTH = 1920; // pixels
		const DEFAULT_WIDTH = 600; // looks like the max we can have in wordpress editor...
		
		const MIN_HEIGHT = 20; // pixels
		const MAX_HEIGHT = 6000; // pixels
		const DEFAULT_HEIGHT = 600;
		
		const WARNING_CONTENT_SIZE = 1000000; // 1MB (system dependent, current value based on observed cases)

		public function __construct() {
			register_activation_hook(__FILE__, array(&$this, 'activate'));

			$plugin = plugin_basename(__FILE__);

			//add custom css to reset Zedity content + webfonts
			add_action('wp_head', array(&$this,'add_head_css'));

			//stop here if we are not in admin area
			if (!is_admin()) return;

			// register actions
			add_action('admin_init', array(&$this, 'admin_init'));
			add_action('admin_menu', array(&$this, 'add_menu'));
			
			$this->plugindata = $this->get_plugin_data();

			//additional links
			add_filter("plugin_action_links_$plugin", array(&$this,'settings_link'));
			add_filter('plugin_row_meta', array(&$this,'plugin_row'), 10, 2);
			
			//add javascript
			add_action('admin_print_footer_scripts', array(&$this, 'add_js'));

			//add TinyMCE css
			add_filter('mce_css', array(&$this, 'add_mce_css'));
			//add TinyMCE buttons
			add_filter('mce_buttons', array(&$this,'register_mce_buttons'));
			add_filter('mce_external_plugins', array(&$this,'add_mce_buttons'));

			//Zedity into ThickBox
			add_action('admin_enqueue_scripts', array(&$this,'admin_enqueue_scripts'));
			add_action('load-dashboard_page_zedity_editor', array(&$this,'add_zedity_editor_page'));
			
			//add custom css to reset Zedity content + webfonts
			add_action('admin_head', array(&$this,'add_head_css'));
			
			//show messages
			add_action('admin_notices', array(&$this,'admin_notices'));
		}
		
		public function activate($network_wide) {
			$defaults = $this->get_defaults();
			add_option('zedity_settings',$defaults);
		}

		
		public function is_premium() {
			return FALSE;
		}

		public function get_plugin_data() {
			return get_file_data(__FILE__,array(
				'Name' => 'Plugin Name',
				'Description' => 'Description',
				'Version' => 'Version',
				'PluginURI' => 'Plugin URI',
				'License' => 'License',
				'LicenseURI' => 'License URI',
			));
		}

		
		public function show_message($message, $pages=TRUE) {
			$notices = get_option('zedity_admin_notices', array());
			$newmsg = array($message,$pages);
			if (!in_array($newmsg,$notices)) $notices[] = $newmsg;
			update_option('zedity_admin_notices', $notices);
		}

		public function admin_notices() {
			global $pagenow;
			$notices = get_option('zedity_admin_notices', array());
			foreach ($notices as $notice) {
				if (is_string($notice)) $notice = array($notice,TRUE);
				if (!is_array($notice)) continue;
				if ($notice[1]===TRUE || in_array($pagenow,$notice[1])) {
					echo "<div class='updated'>{$notice[0]}</div>";
				}
			}
			delete_option('zedity_admin_notices');
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
			$options = $this->get_options();
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
							var ret = confirm('Are you sure you want to close the Zedity Editor?\nIf you close you will lose any unsaved changes.\n\nTo save changes, select from the menu Content->Save.');
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
						old_go.apply(this,arguments); // *** bug
					};
				}

			});
			</script>
			<?php
		}
		
		public function admin_enqueue_scripts() {
			//ThickBox
			wp_enqueue_script('thickbox');
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
			$options = $this->get_options();
			$_SESSION['zedity_webfonts'] = $options['webfonts'];
			$_SESSION['zedity_customfontscss'] = $options['customfontscss'];

			if (!empty($mce_css)) $mce_css .= ',';
			$mce_css .= plugins_url('mce/mce-editor-zedity.css', __FILE__) . ',';
			$mce_css .= plugins_url('mce/webfonts.php', __FILE__);
			return $mce_css;
		}






		//----------------------------------------------------------------------------------------------
		//SETTINGS

		
		public function get_defaults(){
			return array(
				'page_width' => self::DEFAULT_WIDTH,
				'page_height' => self::DEFAULT_HEIGHT,
				'webfonts' => array(),
				'watermark' => 'none',
				'customfontscss' => '',
				'customfonts' => array(),
			);
		}
		
		public function get_options(){
			$options = get_option('zedity_settings',array());
			$defaults = $this->get_defaults();
			return array_merge($defaults,$options);
		}

		public function add_settings_page() {
			if (!current_user_can('manage_options')) {
				wp_die(__('You do not have sufficient permissions to access this page.'));
			}
			$options = $this->get_options();
			//Render the settings template
			include(sprintf("%s/views/settings.php", dirname(__FILE__)));
		}


		// Add a link to the settings page onto the plugin page
		public function settings_link($links) {
			$settings_link = '<a href="options-general.php?page=wp_zedity_plugin">Settings</a>';
			array_unshift($links, $settings_link);
			return $links;
		}

		public function plugin_row($links,$file) {
			if ($file == plugin_basename(__FILE__)) {
				//Add "Donate" and "Get Premium" links if it is not Premium
				if (!$this->is_premium()) {
					$links[] = '<a class="button" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=WXNQFRAGR5WKQ" target="_blank">Donate</a>';
					$links[] = '<a class="button" href="'.$this->plugindata['PluginURI'].'" target="_blank">Get Zedity Premium</a>';
				}
			}
			return $links;
		}

		//Validation
		public function zedity_settings_validate($input) {
			$options = $this->get_options();

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


		public function add_head_css() {
			$options = $this->get_options();
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
			if (is_admin()) {
				//add thickbox css here because using wp_enqueue_style() or add_thickbox() breaks RTL
				?>
				<link rel="stylesheet" href="<?php echo get_bloginfo('wpurl')?>/wp-includes/js/thickbox/thickbox.css" type="text/css" />
				<link rel="stylesheet" href="<?php echo plugins_url('mce/content-overlay.css', __FILE__); ?>" type="text/css" media="all" />
				<style type="text/css">
				#zedity .button,
				#zedity-premium .button {
					padding: 0px 4px;
					line-height: 16px;
					height: auto;
					border: 1px solid #E6DB55;
					background: #FFFFE0;
					background: -webkit-linear-gradient(top, #FFFFE0 0%,#E6DB55 100%);
					background: -moz-linear-gradient(top, #FFFFE0 0%, #E6DB55 100%);
					background: -ms-linear-gradient(top, #FFFFE0 0%,#E6DB55 100%);
					background: -o-linear-gradient(top, #FFFFE0 0%,#E6DB55 100%);
					background: linear-gradient(top, #FFFFE0 0%,#E6DB55 100%);
				}
				#zedity .button:hover,
				#zedity-premium .button:hover {
					background: #FFFFE0;
				}                                                                
				</style>
				<?php
			}
		}
		
		
		
		//--------------------------------------------------------------------------------

		
		public function get_webfonts(){
			return array(
				'Caesar Dressing,cursive',
				'Crafty Girls,cursive',
				'Jacques Francois,serif',
				'Quintessential,cursive',
			);
		}
		
		public function get_videoembeds(){
			return array(
				'youtube' => 'http://www.youtube.com',
				'vimeo' => 'http://vimeo.com',
			);
		}

		public function get_audioembeds(){
			return array(
				'soundcloud' => 'http://soundcloud.com',
				'reverbnation' => 'http://www.reverbnation.com',
			);
		}
		
	}

	
	if (file_exists($filepremium)) {
		require_once($filepremium);
		$wp_zedity_plugin = new WP_Zedity_Plugin_Premium();
	} else {
		$wp_zedity_plugin = new WP_Zedity_Plugin();
	}
	
}

//Fix for Chrome not showing embeds in preview
header('X-XSS-Protection: 0');
