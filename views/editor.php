<html>
	<head>
		<title>Zedity - Editing Reinvented</title>

		<link rel="stylesheet" href="<?php echo plugins_url('jquery/jquery-ui.min.css',dirname(__FILE__))?>" type="text/css" media="all" />
		<?php
		//remove unnecessary scripts
		wp_dequeue_script('utils');
		wp_dequeue_script('admin-bar');
		wp_dequeue_script('thickbox');
		wp_dequeue_script('common');
		wp_dequeue_style('admin-bar');
		wp_dequeue_style('thickbox');
		//add jQuery and jQueryUI bundled with WordPress
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_script('jquery-ui-draggable');
		wp_enqueue_script('jquery-ui-resizable');
		wp_enqueue_script('jquery-ui-droppable');
		wp_enqueue_script('jquery-ui-menu');
		wp_enqueue_script('jquery-ui-slider');
		//print scripts
		wp_print_head_scripts();
		wp_print_footer_scripts();
		?>
		<script type="text/javascript">$ = jQuery;</script>
		
		<link rel="stylesheet" href="<?php echo plugins_url('zedity/zedity.min.css',dirname(__FILE__))?>" type="text/css" media="screen" />
		<script src="<?php echo plugins_url('zedity/zedity.min.js',dirname(__FILE__))?>" type="text/javascript"></script>

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
		if (isset($options['customfontscss'])) {
			echo "<style type=\"text/css\">{$options['customfontscss']}</style>";
		} else {
			$options['customfonts'] = array();
		}
		?>

		<style>
			html,body {
				padding: 0;
				margin: 1px 0;
			}
			.zedity-mainmenu {
				position: fixed !important;
				width: 99%;
			}
			.zedity-editor {
				margin-top: 35px;
			}
			#filler {
				width: 100%;
				height: 250px;
			}
			.zedity-menu-quick {
				float: right;
			}
			.zedity-menu-quick a {
				height: 21px;
				padding: 2px 5px !important;
			}
			.zedity-menu-quick.zedity-menu-separator {
				height: 27px;
				width: 5px !important;
				border-left: 1px solid gray;
			}
			.zedity-menu-quick .zedity-icon-disk {
				background-size: 100%;
			}
		</style>
	</head>
	
	<body>
		<div id="zedityEditorW"></div>
		<div id="filler"></div>


		<?php $this->get_embedcodes() ?>


		<script type="text/javascript">
		//-----------------------------------------------------------------------------------------
		//helper functions

		function getZedityContent(){
			mce = parent.tinyMCE.get('content');
			//get the Zedity content element
			element = mce.selection.getNode();
			element = mce.dom.getParent(element,function(elem){
				return $(elem).hasClass('zedity-editor');
			});

			//check if <br> is needed before/after the content
			needBrBefore = $(element).prev(':not(.zedity-editor)').length==0;
			needBrAfter = $(element).next(':not(.zedity-editor)').length==0;

			if (!element) return '';

			//select content
			mce.selection.select(element);

			//get content
			return mce.selection.getContent({format:'html'});
		};

		function convert(content){
			var $div = $('<div/>');
			$div.html(content);
			watermarkposition = $div.find('.zedity-watermark').attr('data-pos') || watermarkposition;
			$div.find('.zedity-box-Image').each(function(){
				$(this).find('p img').unwrap();
			});
			return $div.html();
		};

		tickMenu = function($item){
			$item.find('.zedity-menu-icon').removeClass('zedity-icon-none').addClass('zedity-icon-yes');
			$item.siblings().each(function(idx,elem){
				$(elem).find('.zedity-menu-icon').removeClass('zedity-icon-yes').addClass('zedity-icon-none');
			});
		};

		resizeEditor = function(editor){
			//reposition to center the editor
			var ew = Math.max(editor.page.size().width,480);
			var bw = $('body').width();
			$('.zedity-mainmenu').css('width', Math.min(ew,bw)-4);
			editor.$container.css('margin-left', (ew<bw) ? (bw-ew)/2 : '');
			//refresh alignment
			var type = '';
			if (editor.$this.hasClass('alignleft')) type='left';
			if (editor.$this.hasClass('alignright')) type='right';
			tickMenu(editor.$container.find('.zedity-mainmenu .zedity-menu-PageAlign[data-type='+type+']'));
			//refresh watermark
			tickMenu(editor.$container.find('.zedity-mainmenu .zedity-menu-Watermark[data-type='+watermarkposition+']'));
			//refresh theme style
			type = editor.$this.hasClass('zedity-notheme') ? 'no' : 'yes';
			tickMenu(editor.$container.find('.zedity-mainmenu .zedity-menu-ThemeStyle[data-type='+type+']'));
		};


		//-----------------------------------------------------------------------------------------
		//Zedity editor


		var fonts = [
			'Arial,Helvetica,sans-serif',
			'Arial Black,Gadget,sans-serif',
			'Arial Narrow,sans-serif',
			'Century Gothic,sans-serif',
			'Comic Sans MS,cursive',
			'Copperplate Gothic Light,sans-serif',
			'Courier New,Courier,monospace',
			'Georgia,serif',
			'Gill Sans,sans-serif',
			'Impact,Charcoal,sans-serif',
			'Lucida Console,Monaco,monospace',
			'Lucida Sans Unicode,Lucida Grande,sans-serif',
			'Palatino Linotype,Book Antiqua,Palatino,serif',
			'Tahoma,Geneva,sans-serif',
			'Times New Roman,Times,serif',
			'Trebuchet MS,Helvetica,sans-serif',
			'Verdana,Geneva,sans-serif'
		];

		var webfonts = <?php echo json_encode($options['webfonts'])?>;
		var customfonts = <?php echo json_encode($options['customfonts'])?>;
		var watermarkposition = '<?php echo $options["watermark"]?>';

		fonts = fonts.concat(webfonts);
		fonts = fonts.concat(customfonts);
		fonts = fonts.filter(function(a){if(!this[a]){this[a]=1;return a;}},{});
		fonts.sort(function(a,b){
			a = a.split(',')[0];
			b = b.split(',')[0];
			if (a > b) return 1;
			if (a < b) return -1;
			return 0;
		});

		zedityEditor = new Zedity({
			container: '#zedityEditorW',
			width: <?php echo $options['page_width']?>,
			height: <?php echo $options['page_height']?>,
			minWidth: <?php echo WP_Zedity_Plugin::MIN_WIDTH?>,
			maxWidth: <?php echo WP_Zedity_Plugin::MAX_WIDTH?>,
			minHeight: <?php echo WP_Zedity_Plugin::MIN_HEIGHT?>,
			maxHeight: <?php echo WP_Zedity_Plugin::MAX_HEIGHT?>,
			onchange: function(){
				this.contentChanged = true;
				window.resizeEditor(this);
			},
			Text: {
				fonts: fonts
			},
			Image: {
				maxSize: 10485760, // 10MB (keep it in sync with utils/img2base64.php MAX_FILESIZE
				action: '<?php echo plugins_url("utils/img2base64.php",dirname(__FILE__))?>'
			}
		});
		zedityEditor.page._sizeConstraints.minWidth = <?php echo WP_Zedity_Plugin::MIN_WIDTH?>;
		zedityEditor.page._sizeConstraints.minHeight = <?php echo WP_Zedity_Plugin::MIN_HEIGHT?>;
		zedityEditor.$this.addClass('zedity-notheme');

		var zedityMenu = zedityEditor.$container.find('.zedity-mainmenu');
		
		//move 'Clear all' to 'Edit' menu
		zedityMenu.find('li.zedity-menu-ClearAll')
			.add('.zedity-mainmenu li:nth-child(1) .zedity-separator:eq(0)')
			.appendTo('.zedity-mainmenu li:nth-child(2) ul');
		
		//add Alignment to menu
		zedityMenu.find('li.ui-menubar:first-child > ul').append(
			'<li class="ui-state-disabled zedity-separator ui-menu-item" role="presentation" aria-disabled="true"><a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"></a></li>'+
			'<li class="ui-menu-item" role="presentation">'+
				'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="ui-menu-icon ui-icon ui-icon-carat-1-e"></span><span class="zedity-menu-icon zedity-icon-none"></span>Alignment</a>'+
				'<ul class="ui-menu ui-widget ui-widget-content ui-corner-all" role="menu" aria-expanded="false" style="display:none" aria-hidden="true">'+
					'<li class="zedity-menu-PageAlign ui-menu-item" role="presentation" data-type="">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-yes"></span>None</a>'+
					'</li>'+
					'<li class="zedity-menu-PageAlign ui-menu-item" role="presentation" data-type="left">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-none"></span>Left</a>'+
					'</li>'+
					'<li class="zedity-menu-PageAlign ui-menu-item" role="presentation" data-type="right">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-none"></span>Right</a>'+
					'</li>'+
				'</ul>'+
			'</li>'
		);
		//add Theme style to menu
		zedityMenu.find('li.ui-menubar:first-child > ul').append(
			'<li class="ui-menu-item" role="presentation">'+
				'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="ui-menu-icon ui-icon ui-icon-carat-1-e"></span><span class="zedity-menu-icon zedity-icon-none"></span>Theme style</a>'+
				'<ul class="ui-menu ui-widget ui-widget-content ui-corner-all" role="menu" aria-expanded="false" style="display:none" aria-hidden="true">'+
					'<li class="zedity-menu-ThemeStyle ui-menu-item" role="presentation" data-type="yes">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-none"></span>Enabled</a>'+
					'</li>'+
					'<li class="zedity-menu-ThemeStyle ui-menu-item" role="presentation" data-type="no">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-yes"></span>Disabled</a>'+
					'</li>'+
				'</ul>'+
			'</li>'
		);
		//add Watermark to menu
		zedityMenu.find('li.ui-menubar:first-child > ul').append(
			'<li class="ui-menu-item" role="presentation">'+
				'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="ui-menu-icon ui-icon ui-icon-carat-1-e"></span><span class="zedity-menu-icon zedity-icon-none"></span>Watermark</a>'+
				'<ul class="ui-menu ui-widget ui-widget-content ui-corner-all" role="menu" aria-expanded="false" style="display:none" aria-hidden="true">'+
					'<li class="zedity-menu-Watermark ui-menu-item" role="presentation" data-type="none">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-yes"></span>None</a>'+
					'</li>'+
					'<li class="zedity-menu-Watermark ui-menu-item" role="presentation" data-type="topleft">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-none"></span>Top left</a>'+
					'</li>'+
					'<li class="zedity-menu-Watermark ui-menu-item" role="presentation" data-type="topright">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-none"></span>Top right</a>'+
					'</li>'+
					'<li class="zedity-menu-Watermark ui-menu-item" role="presentation" data-type="bottomleft">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-none"></span>Bottom left</a>'+
					'</li>'+
					'<li class="zedity-menu-Watermark ui-menu-item" role="presentation" data-type="bottomright">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-none"></span>Bottom right</a>'+
					'</li>'+
				'</ul>'+
			'</li>'
		);
		//add Save to menu
		zedityMenu.find('li.ui-menubar:first-child > ul').append(
			'<li class="ui-state-disabled zedity-separator ui-menu-item" role="presentation" aria-disabled="true"><a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"></a></li>'+
			'<li class="zedity-menu-SavePage ui-menu-item" role="presentation">'+
				'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-disk"></span>Save</a>'+
			'</li>'
		);
		
		//add shortcut buttons
		zedityMenu.append(
			'<li class="zedity-menu-SavePage ui-menu-item zedity-menu-quick" role="presentation" title="Save">'+
				'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-disk"></span></a>'+
			'</li>'+
			'<li class="zedity-menu-EditUndoRedo ui-menu-item zedity-menu-quick" data-type="redo" role="presentation" title="Redo (ctrl+y)">'+
				'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-redo" style="background-size:85%"></span></a>'+
			'</li>'+
			'<li class="zedity-menu-EditUndoRedo ui-menu-item zedity-menu-quick" data-type="undo" role="presentation" title="Undo (ctrl+z)">'+
				'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-undo" style="background-size:85%"></span></a>'+
			'</li>'+
			'<li class="zedity-menu-separator ui-menu-item ui-state-disabled zedity-menu-quick">'+
			'</li>'
		);

		//undo/redo
		zedityMenu.find('.zedity-menu-quick.zedity-menu-EditUndoRedo').on('click.zedity',function(event){
			var editor = $(this).editor();
			editor.menu.close();
			editor[$(this).attr('data-type')]();
			return false;
		});
		//page align
		zedityMenu.find('.zedity-menu-PageAlign').on('click',function(){
			var type = $(this).attr('data-type');
			zedityEditor.$this.removeClass('alignleft alignright');
			if (type) zedityEditor.$this.addClass('align'+type);
			resizeEditor(zedityEditor);
		});
		//theme style
		zedityMenu.find('.zedity-menu-ThemeStyle').on('click',function(){
			var type = $(this).attr('data-type');
			zedityEditor.$this.toggleClass('zedity-notheme', type=='no');
			resizeEditor(zedityEditor);
		});
		//watermark
		zedityMenu.find('.zedity-menu-Watermark').on('click',function(){
			watermarkposition = $(this).attr('data-type');
			resizeEditor(zedityEditor);
		});
		//save
		zedityMenu.find('.zedity-menu-SavePage').on('click',function(){
			zedityEditor.save(function(html){
				$(parent.document).find('#TB_iframeContent').removeClass('zedity-iframe');
				var $html = $('<div/>');
				$html.append(html);
				
				if (watermarkposition != 'none') {
					//add watermark
					$html.find('.zedity-editor').append(
						'<div class="zedity-watermark" style="position:absolute;background:rgba(60,60,60,0.6);z-index:99999;padding:0 6px">'+
						'<span style="color:white;font-size:12px;font-family:Arial,Tahoma,Verdana,sans-serif">Powered by '+
						'<a href="http://zedity.com" target="_blank" style="color:yellow;font-size:12px">Zedity</a>'+
						'</span>'+
						'</div>'
					);
					var $wm = $html.find('.zedity-watermark');
					switch (watermarkposition) {
						case 'topleft':
							$wm.css({
								top: '0px',
								left: '0px',
								'border-bottom-right-radius': '6px',
							}).attr('data-pos','topleft');
						break;
						case 'topright':
							$wm.css({
								top: '0px',
								right: '0px',
								'border-bottom-left-radius': '6px',
							}).attr('data-pos','topright');
						break;
						case 'bottomleft':
							$wm.css({
								bottom: '0px',
								left: '0px',
								'border-top-right-radius': '6px',
							}).attr('data-pos','bottomleft');
						break;
						case 'bottomright':
							$wm.css({
								bottom: '0px',
								right: '0px',
								'border-top-left-radius': '6px',
							}).attr('data-pos','bottomright');
						break;
					}
				}

				//re-select content
				mce.selection.select(element);
				// Add a paragraph before and after the content to avoid problems adding content from WP editor if no other content is present
				parent.send_to_editor(
					(needBrBefore ? '<p>&nbsp;</p>' : '') +
					$html.html() +
					(needBrAfter ? '<p>&nbsp;</p>' : '')
				);

				//cleanup TinyMCE leftovers
				$(mce.getDoc()).find('style').each(function(idx,elem){
					$elem = $(elem);
					if ($elem.hasClass('imgData') && $elem.parents('.zedity-editor').length==0) {
						$elem.remove();
					}
				});
				
				//show overlay on new content
				mce.plugins.zedity._zedityContent = $(mce.getDoc()).find('#'+this.id)[0];
			});
		});



		//-----------------------------------------------------------------------------------------
		//resizing

		parent.resizeForZedity();

		$(parent.document).find('#TB_iframeContent').addClass('zedity-iframe').css('width','100%');



		//-----------------------------------------------------------------------------------------
		//set content

		(function(){
			try {
				var content = getZedityContent();
				content = convert(content);
				zedityEditor.page.content(content);
				//reset undo data
				Zedity.core.store.delprefix('zedUn');
				Zedity.core.gc.flushData();
				zedityEditor.page._saveUndo();
			} catch(e) {}
		})();

		zedityEditor.contentChanged = false;
		
		</script>

	</body>
	
</html>
