<?php
/*
Plugin Name: Zedity
Plugin URI: http://zedity.com/plugin/wp
Description: The Best Editor to create any design you want, very easily and with unprecedented possibilities!
Version: 2.5.4
Author: Zuyoy LLC
Author URI: http://zuyoy.com
License: GPL3
License URI: http://zedity.com/license/freewp
*/

$path = plugin_dir_path(__FILE__);
$filepremium = "$path/premium.php";
load_plugin_textdomain('zedity', false, dirname(plugin_basename(__FILE__)).'/languages/');
  
//check if another Zedity plugin is already enabled
if (class_exists('WP_Zedity_Plugin')) {
	
	if (file_exists($filepremium)) {
		$thisp = 'Premium';
		$otherp = 'free';
	} else {
		$thisp = 'free';
		$otherp = 'Premium';
	}

	$message = sprintf(__('<b>Could not activate</b> %s plugin.','zedity'),"Zedity $thisp") . ' '.
		sprintf(__('You cannot activate both %s and %s plugins at the same time.','zedity'),'Zedity free','Zedity Premium') . '<br/>' .
		sprintf(__('Please <b>first deactivate</b> the %s plugin, <b>then activate</b> the %s plugin.','zedity'),"Zedity $otherp","Zedity $thisp");
	
	if (isset($_GET['action']) && $_GET['action'] == 'error_scrape') {
		echo $message;
		exit;
	} else {
		trigger_error($message, E_USER_ERROR);
	}
	
} else {



	class WP_Zedity_Plugin {
		
		const MIN_WIDTH = 50; // pixels
		const MAX_WIDTH = 2500; // pixels
		const DEFAULT_WIDTH = 600; // a typical width for some themes in wordpress
		
		const MIN_HEIGHT = 20; // pixels
		const MAX_HEIGHT = 6000; // pixels
		const DEFAULT_HEIGHT = 600;
		
		const WARNING_CONTENT_SIZE = 1000000; // 1MB (system dependent, current value based on observed cases)
		const ATTEMPT_MAX_SIZE = 10000000; // 10MB
		
		public $MAX_UPLOAD_SIZE;
		
		public function __construct() {
			register_activation_hook(__FILE__, array(&$this, 'activate'));

			$plugin = plugin_basename(__FILE__);

			//add custom css to reset Zedity content + webfonts
			add_action('wp_head', array(&$this,'add_head_css'));
			//add javascript for responsive content
			add_action('wp_print_footer_scripts', array(&$this, 'add_front_js'));

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
			add_action('admin_print_footer_scripts', array(&$this, 'add_admin_js'));

			//add TinyMCE css
			add_filter('mce_css', array(&$this, 'add_mce_css'));
			//add TinyMCE buttons
			add_filter('mce_buttons', array(&$this,'register_mce_buttons'));
			add_filter('mce_external_plugins', array(&$this,'add_mce_buttons'), 999);
			//TinyMCE configuration
			add_filter('tiny_mce_before_init', array(&$this,'mce_config'));

			//Zedity into ThickBox
			add_action('admin_enqueue_scripts', array(&$this,'admin_enqueue_scripts'));
			
			//use ajax mechanism to load views
			add_action('wp_ajax_zedity_editor', array(&$this,'add_zedity_editor_page'));
			add_action('wp_ajax_zedity_ajax', array(&$this,'add_zedity_ajax_page'));

			//Zedity into Media Library
			add_filter('post_mime_types', array(&$this,'add_zedity_mime_type'));
			
			//add custom css to reset Zedity content + webfonts
			add_action('admin_head', array(&$this,'add_head_css'));
			
			//show messages
			add_action('admin_notices', array(&$this,'admin_notices'));
			
			//max upload size
			//ini_set('post_max_size',self::ATTEMPT_MAX_SIZE);
			//ini_set('upload_max_filesize',self::ATTEMPT_MAX_SIZE);
			if (function_exists('wp_max_upload_size')) {
				$this->MAX_UPLOAD_SIZE = wp_max_upload_size();
			} else {
				$this->MAX_UPLOAD_SIZE = self::WARNING_CONTENT_SIZE;
			}
		}
		
		public function activate($network_wide) {
			$defaults = $this->get_defaults();
			add_option($this->get_options_name(),$defaults);
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
			register_setting('wp_zedity_plugin', $this->get_options_name(), array($this,'zedity_settings_validate'));
			add_settings_section('zedity_main', 'Page', array($this,'section_zedity_page'), 'zedity_settings_section');
		}

		public function add_menu() {
			add_options_page('Zedity Settings', 'Zedity Editor', 'manage_options', 'wp_zedity_plugin', array(&$this, 'add_settings_page'));
		}
		

		function add_zedity_mime_type($post_mime_types) {
			$post_mime_types['application/zedity'] = array('Zedity', sprintf(__('Manage %s contents','zedity'),'Zedity'), _n_noop('Zedity <span class="count">(%s)</span>','Zedity <span class="count">(%s)</span>'));
			return $post_mime_types;
		}


		//----------------------------------------------------------------------------------------------
		//EDITOR


		public function add_zedity_editor_page() {
			if (!empty($_REQUEST['data']) && $_REQUEST['data']=='saved') {
				//get data when ajax request is redirected with 302 Moved Temporarily
				$attachments = get_posts(array(
					'post_type' => 'attachment',
					'posts_per_page' => 1,
					'post_status' => null,
					'post_mime_type' => 'application/zedity',
				));
				if (!empty($attachments[0])) {
					$id = isset($attachments[0]->ID) ? $attachments[0]->ID : NULL;
					$url = isset($attachments[0]->guid) ? $attachments[0]->guid : NULL;
					if (empty($id) || empty($url)) {
					    $response = array('error' => sprintf(__('Save content: empty id (%s) or url (%s).','zedity'),$id,$url));
					} else {
					    $response = array('id' => $id, 'url' => $url);
					}
				} else {
					$response = array('error' => __('Save content: could not find attachment.','zedity'));
				}
				echo json_encode($response);
				exit;
			}

			require(ABSPATH . WPINC . '/version.php');
			$options = $this->get_options();
			include(sprintf("%s/views/editor.php", dirname(__FILE__)));
			exit;
		}

		public function add_zedity_ajax_page() {
			require(ABSPATH . WPINC . '/version.php');
			$options = $this->get_options();
			include(sprintf("%s/views/ajax.php", dirname(__FILE__)));
			exit;
		}


		//----------------------------------------------------------------------------------------------
		//JAVASCRIPT

		public function add_admin_js(){
			?>
			<script type="text/javascript">
			jQuery(document).ready(function(){
				if (!window.tinyMCE) return;
				
				tinyMCE.addI18n({'<?php echo (class_exists('_WP_Editors') ? _WP_Editors::$mce_locale : substr(WPLANG,0,2)) ?>': {
					zedity: {
						edit_content: '<?php echo sprintf(addslashes(__('Edit %s content','zedity')),'Zedity')?>',
						delete_content: '<?php echo sprintf(addslashes(__('Delete %s content','zedity')),'Zedity')?>'
					}
				}});

				//Handle ThickBox window close
				var old_tb_remove = tb_remove;
				tb_remove = function(){
					var $iframe = jQuery('#TB_iframeContent.zedity-editor-iframe');
					if ($iframe.length) {
						if ($iframe[0].contentWindow.zedityEditor.contentChanged) {
							var ret = confirm(
								'<?php echo addslashes(__('You haven\'t saved your modifications!','zedity'))?>'+
								'\n\n'+
								'<?php echo addslashes(__('To save changes, click on Cancel and then select Content->Save from the menu.','zedity'))?>'+
								'\n\n'+
								'<?php echo sprintf(addslashes(__('To discard the modifications and close the %s, click OK.','zedity')),'Zedity editor')?>'
							);
							if (!ret) return;
						}
						//deselect
						/*
						var mceiframe = window.document.getElementById('content_ifr');
						var idoc = mceiframe.contentDocument || mceiframe.contentWindow.document;
						idoc.getSelection().removeAllRanges();
						*/
					}
					$iframe.removeClass('zedity-editor-iframe');
					old_tb_remove.apply(this,arguments);
					var ed = tinyMCE.activeEditor;
					if (ed && ed.plugins.zedity) ed.plugins.zedity._closeZedity();
				};
				
				window.askPublish = function(){
					if (!window.tinyMCE || !tinyMCE.get('content')) return;
					jQuery(
						'<div><p>'+
						'<?php echo sprintf(addslashes(__('You have modified your %s content.','zedity')),'Zedity')?>'+
						'</p><p>'+
						'<?php echo addslashes(__('Please don\'t forget to publish your post for the modifications to appear in your published post as well.','zedity'))?>'+
						'</p></div>'
					).dialog({
						title: 'Zedity',
						dialogClass: 'zedity-dialog',
						autoOpen: true,
						modal: true,
						resizable: false,
						close: function(){
							jQuery(this).dialog('destroy').remove();
						},
						buttons: [{
							text: '<?php echo addslashes(__('Publish now','zedity'))?>',
							click: function(){
								jQuery(this).dialog('close');
								jQuery('#publish').trigger('click');
							}
						},{
							text: '<?php echo addslashes(__('Publish later','zedity'))?>',
							click: function(){
								jQuery(this).dialog('close');
							}
						}]
					});
				};

				//Handle ThickBox window resize
				resizeForZedity = function(){
					var $tb = jQuery('#TB_window');
					if ($tb.find('.zedity-editor-iframe').length==0) return;
					$tb.addClass('zedity-window');
					jQuery('#TB_overlay').addClass('zedity-overlay');
					var $iframe = jQuery('#TB_iframeContent.zedity-editor-iframe');
					if ($iframe.length>0 && $iframe[0].contentWindow.resizeEditor) {
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
						if (ed && ed.plugins.zedity) ed.plugins.zedity._hideOverlay();
						old_go.apply(this,arguments);
					};
				}

				var hideOverlay = function(){
					if (!window.tinyMCE) return;
					var ed = tinyMCE.activeEditor;
					if (ed && ed.plugins.zedity) ed.plugins.zedity._hideOverlay();
				};
				jQuery('body').on('click.zedity',hideOverlay);
				jQuery('#adminmenu a.wp-has-submenu').on('mouseover',hideOverlay);
			});
			</script>
			<?php
		}
		
		public function additional_editor_js($options){						
			?>
			<script type="text/javascript">
			//add Responsive to menu (disabled)
			zedityMenu.find('li.ui-menubar:first-child > ul > li:nth-child(5)').after(
				'<li class="ui-state-disabled ui-menu-item" role="presentation" aria-disabled="true">'+
					'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem">'+
						'<span class="ui-menu-icon ui-icon ui-icon-carat-1-e"></span>'+
						'<span class="zedity-menu-icon zedity-icon-none"></span>'+
						'<?php echo addslashes(__('Responsive Design','zedity'))?> <small style="color:blue">(Premium)</small>'+
					'</a>'+
				'</li>'
			);
			</script>
			<?php			
		}
		
		public function add_front_js(){
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
			$options = $this->get_options();
			if ($options['iframe_preview']) {
				//include custom media plugin for iframe preview
				$plugins['newmedia'] = plugins_url('mce/media/editor_plugin.js', __FILE__);
			}
			//include custom noneditable plugin to disallow Zedity content edit inside TinyMCE
			$plugins['newnoneditable'] = plugins_url('mce/noneditable/editor_plugin.js', __FILE__);
			return $plugins;
		}


		public function add_mce_css($mce_css) {
			if (!empty($mce_css)) $mce_css .= ',';
			$mce_css .= plugins_url('mce/mce-editor-zedity.css', __FILE__) . ',';
			$mce_css .= plugins_url('css/zedity-reset.css', __FILE__) . ',';
			$mce_css .= 'admin-ajax.php?action=zedity_ajax&zaction=webfonts';
			return $mce_css;
		}
		
		public function mce_config($init) {
			if (empty($init)) return;
			
			//ensure that iframes are allowed
			if (!isset($init['extended_valid_elements'])) {
				$init['extended_valid_elements'] = 'iframe[*]';
			} else {
				$init['extended_valid_elements'] .= ',iframe[*]';
			}
			$options = $this->get_options();
			
			if (!empty($init['plugins'])) {
				//disable conflicting tinymce plugins
				$plugins = explode(',',$init['plugins']);
				$plugins = array_diff($plugins, array('noneditable','-noneditable'));
				if ($options['iframe_preview']) {
					$plugins = array_diff($plugins, array('media','-media'));
				}
				$init['plugins'] = implode(',',$plugins);
			}
			return $init;
		}




		//----------------------------------------------------------------------------------------------
		//SETTINGS

		
		public function get_defaults(){
			return array(
				'save_mode' => 1,
				'page_width' => self::DEFAULT_WIDTH,
				'page_height' => self::DEFAULT_HEIGHT,
				'webfonts' => array(),
				'watermark' => 'none',
				'customfontscss' => '',
				'customfonts' => array(),
				'responsive' => 0,
				'responsive_noconflict' => FALSE,
				'iframe_preview' => TRUE,
				'snap_to_page' => FALSE,
				'snap_to_boxes' => FALSE,
			);
		}
		
		public function get_options_name(){
			return 'zedity_settings';
		}
		
		public function get_options(){
			$options = get_option($this->get_options_name(),array());
			//convert from old versions
			if ($options['responsive']===FALSE) $options['responsive']=0;
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
			$settings_link = '<a href="options-general.php?page=wp_zedity_plugin">'.__('Settings').'</a>';
			array_unshift($links, $settings_link);
			return $links;
		}

		public function plugin_row($links,$file) {
			if ($file == plugin_basename(__FILE__)) {
				//Add "Donate" and "Get Premium" links if it is not Premium
				if (!$this->is_premium()) {
					$links[] = '<a class="button" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=WXNQFRAGR5WKQ" target="_blank">'.__('Donate','zedity').'</a>';
					$links[] = '<a class="button" href="'.$this->plugindata['PluginURI'].'" target="_blank">'.sprintf(__('Get %s','zedity'),'Zedity Premium').'</a>';
				}
			}
			return $links;
		}

		//Validation
		public function zedity_settings_validate($input) {
			$options = $this->get_options();
			
			$options['save_mode'] = $input['save_mode'];
			if ($options['save_mode']!=1 and $options['save_mode']!=2) $options['save_mode'] = 1;

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

			$options['iframe_preview'] = ($input['iframe_preview'] == 1);
			
			return $options;
		}
		
		public function additional_settings_page($options){
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
				global $post;
				if (!empty($post)) {
					?><script type="text/javascript">window.post_id = <?php echo $post->ID ?>;</script><?php
				}
				//add thickbox css here because using wp_enqueue_style() or add_thickbox() breaks RTL
				?>
				<link rel="stylesheet" href="<?php echo get_bloginfo('wpurl')?>/wp-includes/js/thickbox/thickbox.css" type="text/css" />
				<link rel="stylesheet" href="<?php echo plugins_url('mce/content-overlay.css', __FILE__); ?>" type="text/css" media="all" />
				<link rel="stylesheet" href="<?php echo plugins_url('css/zedity-admin.css', __FILE__)?>" type="text/css" media="all" />
				<?php
			}
		}
		
		
		
		//--------------------------------------------------------------------------------

		
		public function get_font_sizes(){
			return array('11','12','14','16','19','21','24','27','29','32','37','48','53','64');
		}
		
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
