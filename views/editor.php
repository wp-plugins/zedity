<html>
	<head>
		<title>Zedity - Create Content Easily</title>

		<link rel="stylesheet" href="<?php echo plugins_url('jquery/jquery-ui.min.css',dirname(__FILE__))?>" type="text/css" media="all" />
		<?php
		//remove all external scripts/styles
		global $wp_scripts, $wp_styles;
		if (!empty($wp_scripts) && !empty($wp_scripts->queue)) {
			foreach($wp_scripts->queue as $handle) {
				wp_dequeue_script($handle);
			}
		}
		if (!empty($wp_styles) && !empty($wp_styles->queue)) {
			foreach($wp_styles->queue as $handle) {
				wp_dequeue_style($handle);
			}
		}
		//add jQuery and jQueryUI bundled with WordPress
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_script('jquery-ui-draggable');
		wp_enqueue_script('jquery-ui-resizable');
		wp_enqueue_script('jquery-ui-droppable');
		wp_enqueue_script('jquery-ui-menu');
		wp_enqueue_script('jquery-ui-slider');
		wp_enqueue_script('jquery-ui-tooltip');
		wp_enqueue_script('jquery-ui-accordion');
		//print scripts
		wp_print_head_scripts();
		wp_print_footer_scripts();
		?>
		<script type="text/javascript">
		$ = jQuery;
		var linkMsg = '<?php echo sprintf(addslashes(__('For information or to upgrade to %s, please visit %s.','zedity')),'Zedity Premium','<a href="http://zedity.com/plugin/wp" target="_blank">zedity.com</a>');?>';
		ZedityPromo = {
			product: 'Zedity Premium',
			productShort: 'Premium',
			message: '<?php echo sprintf(addslashes(__('This is a %s feature.','zedity')),'Zedity Premium')?><br/>'+linkMsg,
			feature: {
				linkOnBox: '<?php echo sprintf(addslashes(__('%s feature: associate a link to the box.','zedity')),'Premium')?><br/>'+linkMsg,
				boxSize: '<?php echo sprintf(addslashes(__('%s feature: view and set exact box size.','zedity')),'Premium')?><br/>'+linkMsg,
				textParagraph: '<?php echo sprintf(addslashes(__('%s feature: SEO friendly tags, e.g. title, paragraph, etc.','zedity')),'Premium')?><br/>'+linkMsg,
				textLink: '<?php echo sprintf(addslashes(__('%s feature: open link in a new tab.','zedity')),'Premium')?><br/>'+linkMsg,
				imageFilters: '<?php echo sprintf(addslashes(__('%s feature: enhance images with special effects.','zedity')),'Premium')?><br/>'+linkMsg,
				colorButtons: '<?php echo sprintf(addslashes(__('%s feature: set custom RGB or Hex colors.','zedity')),'Premium')?>',
				additionalMedia: linkMsg,
				additionalBoxes: true // this message is not shown anyway (disabled items in menu)
			}
		};
		ZedityLang = '<?php echo substr(WPLANG,0,2)?>';
		</script>
		
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
				background: white;
			}
			.zedity-mainmenu {
				position: fixed !important;
				top: 32px;
				width: 99%;
			}
			.zedity-editor {
				margin-top: 65px;
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
			/*Hide disabled features*/
			.zedity-bar span[data-panel=zedity-imagefilters],
			.zedity-menu-imgqual-menu,
			.zedity-dialog-image .tabs li[aria-controls=tab-image-disk] {
				display: none !important;
			}
			/*status bar*/
			#statusbar {
				position: fixed;
				margin: 0 auto;
				min-width: 630px;
				height: 30px;
				font-family: Tahoma, Arial, Verdana, sans-serif;
				font-size: 12px;
				border: 1px solid lightgray;
				border-radius: 2px;
				z-index: 1999;
				color: #444;
				background: #e0f3fa;
				background: -moz-linear-gradient(top,  #e0f3fa 0%, #d8f0fc 22%, #b8e2f6 96%, #b6dffd 100%);
				background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#e0f3fa), color-stop(22%,#d8f0fc), color-stop(96%,#b8e2f6), color-stop(100%,#b6dffd));
				background: -webkit-linear-gradient(top,  #e0f3fa 0%,#d8f0fc 22%,#b8e2f6 96%,#b6dffd 100%);
				background: -o-linear-gradient(top,  #e0f3fa 0%,#d8f0fc 22%,#b8e2f6 96%,#b6dffd 100%);
				background: -ms-linear-gradient(top,  #e0f3fa 0%,#d8f0fc 22%,#b8e2f6 96%,#b6dffd 100%);
				background: linear-gradient(to bottom,  #e0f3fa 0%,#d8f0fc 22%,#b8e2f6 96%,#b6dffd 100%);
			}
			#statusbar select {
				max-width: 100px;
			}
			#statusbar .info {
				line-height: 30px;
				margin-left: 15px;
			}
			#statusbar .info .yes {
				color: darkgreen;
				font-weight: bold;
			}
			#statusbar .info .no {
				color: #40737a;
				font-weight: bold;
			}
			#goPremiumLink {
				font-family: tahoma, Helvetica, tahoma;
				color: #999;
				background: none;
				border-radius: 5px;
				border: 1px inset transparent;
				padding: 0 2px;
				outline: none;
				text-decoration: none;
				line-height: 30px;
				-webkit-transition: 0.8s;
				transition: 0.8s;
			}
			#goPremiumLink:hover {
				color: #444;
				background: white;
				border: 1px inset #999;
			}
			#goPremiumLink.premiumfeat {
				color: #444;
				background: #ffAA66;
			}
			#saveBtn {
				color: white;
				float: right;
				display: inline-block;
				padding: 2px 15px;
				margin-bottom: 0;
				margin-right: 20px;
				font-size: 14px;
				font-weight: normal;
				line-height: 1.428571429;
				text-align: center;
				white-space: nowrap;
				vertical-align: middle;
				cursor: pointer;
				background-image: none;
				border: 1px solid transparent;
				border-radius: 4px;
				-webkit-user-select: none;
				-moz-user-select: none;
				-ms-user-select: none;
				-o-user-select: none;
				user-select: none;

				background: #9dd53a;
				background: -moz-linear-gradient(top, #9dd53a 0%, #a1d54f 9%, #80c217 54%, #7cbc0a 100%);
				background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#9dd53a), color-stop(9%,#a1d54f), color-stop(54%,#80c217), color-stop(100%,#7cbc0a));
				background: -webkit-linear-gradient(top, #9dd53a 0%,#a1d54f 9%,#80c217 54%,#7cbc0a 100%);
				background: -o-linear-gradient(top, #9dd53a 0%,#a1d54f 9%,#80c217 54%,#7cbc0a 100%);
				background: -ms-linear-gradient(top, #9dd53a 0%,#a1d54f 9%,#80c217 54%,#7cbc0a 100%);
				background: linear-gradient(to bottom, #9dd53a 0%,#a1d54f 9%,#80c217 54%,#7cbc0a 100%);
			}

			#saveBtn:focus {
				outline: thin dotted;
				outline: 5px auto -webkit-focus-ring-color;
				outline-offset: -2px;
			}

			#saveBtn:hover,
			#saveBtn:focus {
				color: #fcefa1;
				text-decoration: none;
			}

			#saveBtn:active,
			#saveBtn.active {
				/*background-image: none;*/
				outline: 0;
				-webkit-box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
				box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
			}
			#currentLayout {
				position: relative;
				display: inline-block;
				padding: 1px 5px;
				line-height: 16px;
				font-weight: bold;
				background: whitesmoke;
				cursor: pointer;
				border-radius: 10px;
				border: none;
				outline: none;
				box-shadow: 0px 0px 4px 1px black;
			}
			#currentLayout:hover {
				background: lightgray;
			}
			#currentLayout:active {
				background: lightgray;
				top: -1px;
				box-shadow: 0px 0px 1px 1px black;
			}
			/*
			#statusbar .led {
				display: inline-block;
				width: 10px;
				height: 10px;
				margin: 4px 5px 0 10px;
				border: 1px solid black;
				border-radius: 10px;
			}
			#statusbar .led.on {
				background: #0C0;
			}
			#statusbar .led.off {
				background: #E00;
			}
			*/
		</style>
	</head>

	<body>
		<div id="statusbar">
			<span class="info"><?php _e('Content mode:','zedity')?>
				<select id="ddSaveMode">
					<option value="1"><?php _e('Isolated','zedity')?></option>
					<option value="2"><?php _e('Standard','zedity')?></option>
				</select>
				<span id="statusBarContentModeTT" class="zedity-tooltip" title="">?</span>
			</span>

			<?php if ($this->is_premium()) { ?>
				<span class="info"><?php _e('Responsive:','zedity')?>
					<select id="ddResponsive">
						<option value="0"><?php _e('No','zedity')?></option>
						<option value="1"><?php _e('Scaling','zedity')?></option>
						<option value="2"><?php _e('Multiple layout','zedity')?></option>
					</select>
				</span>
				<span class="info"><button id="currentLayout" style="display:none" title="<?php _e('Current layout','zedity')?>"></button></span>
			<?php } else { ?>
				<span class="info"><?php _e('Responsive:','zedity')?>
					<select>
						<option><?php _e('No','zedity')?></option>
						<option disabled="disabled"><?php _e('Scaling','zedity')?> (Premium)</option>
						<option disabled="disabled"><?php _e('Multiple layout','zedity')?> (Premium)</option>
					</select>
				</span>
				<!-- show disabled premium features in free version -->
				<a target="_blank" id="goPremiumLink" href="http://zedity.com/plugin/wpfeatures" class="info"><?php echo sprintf(__('Go %s','zedity'),'Premium')?></a>
			<?php } ?>

			<button id="saveBtn"><?php _e('Save','zedity')?></button>

		</div>
		<div id="zedityEditorW"></div>
		<div id="filler"></div>

		<script type="text/javascript">
		//-----------------------------------------------------------------------------------------
		//helper functions
		var content = {
			mce: null,
			element: null,
			id: null,
			title: null,
			needBrBefore: true,
			needBrAfter: true,
			watermarkposition: '<?php echo $options['watermark']?>',
			responsive: <?php echo $options['responsive']?>,
			savemode: '<?php echo $options['save_mode']?>', // 1: isolated (iframe); 2: standard (inline)
			alignment: '',
			needsPublish: false,
			//get content from tinymce editor
			getFromTinyMCE: function(){
				var content = '';
				//get TinyMCE reference
				this.mce = parent.tinyMCE.activeEditor;
				//get the Zedity content element
				this.element = this.mce.selection.getNode();
				this.element = this.mce.dom.getParent(this.element,function(elem){
					var $elem = $(elem);
					if ($elem.hasClass('zedity-editor') && $elem.parent().hasClass('zedity-wrapper')) return false;
					if ($elem.hasClass('zedity-iframe-wrapper') && $elem.parent().hasClass('zedity-wrapper')) return false;
					return $elem.hasClass('zedity-editor') || $elem.hasClass('zedity-wrapper') || $elem.hasClass('zedity-iframe-wrapper');
				});
				if (this.element) {
					//select content
					this.mce.selection.select(this.element);
					//get content
					content = this.mce.selection.getContent({format:'html'});
					//check if <br> is needed before/after the content
					this.needBrBefore = $(this.element).prev(':not(.zedity-editor):not(.zedity-wrapper)').length==0;
					this.needBrAfter = $(this.element).next(':not(.zedity-editor):not(.zedity-wrapper)').length==0;
					//check when changes something that needs publishing
					zedityMenu.find('.zedity-menu-PageSize,.zedity-menu-PageAlign').on('click',$.proxy(function(){
						this.needsPublish = true;
					},this));
				}
				//check if existing content
				var $content = $('<div/>').html(content);
				var $iframe = $content.find('.zedity-iframe-wrapper > iframe');
				if ($iframe.length) {
					//iframe
					this.savemode = 1;
					this.title = $iframe.attr('title');
					this.id = $iframe.attr('data-id');
					this.loadFromFile($iframe.attr('src'));
				} else if ($content.find('.zedity-editor').length) {
					//inline content
					this.savemode = 2;
					this.setContentInEditor($content.find('.zedity-editor').get(0).outerHTML);
				} else if ($content.children().length) {
					alert('<?php echo addslashes(__('The content may have been manually modified and got corrupted.','zedity'))?>');
				}
				//new content otherwise
				zedityEditor.contentChanged = false;
			},
			//send content to tinymce editor
			sendToTinyMCE: function(content){
				//re-select content
				this.mce.selection.select(this.element);
				//add a paragraph before and/or after the content (if needed) to permit adding text from WP editor if no other content is present
				//insert raw HTML (mceInsertContent or send_to_editor() have problems #614)
				this.mce.execCommand('mceInsertRawHTML',false,
					(this.needBrBefore ? '<p>&nbsp;</p>' : '') +
					content +
					(this.needBrAfter ? '<p>&nbsp;</p>' : '')
				);
				zedityEditor.contentChanged = false;
				//close editor window
				parent.tb_remove();
				//cleanup TinyMCE leftovers
				$(this.mce.getDoc()).find('style').each(function(idx,elem){
					var $elem = $(elem);
					if ($elem.hasClass('imgData') && $elem.parents('.zedity-editor').length==0) {
						$elem.remove();
					}
				});
				//show overlay on new content
				this.mce.plugins.zedity._zedityContent = $(this.mce.getDoc()).find('#'+zedityEditor.id)[0];
				if (this.needsPublish) parent.askPublish();
			},
			//convert content
			convert: function(content){
				var $div = $('<div/>').html(content);
				//get watermark
				this.watermarkposition = $div.find('.zedity-watermark').attr('data-pos') || 'none';
				//get alignment
				var $el = $div.find('.zedity-editor') || $div.find('.zedity-iframe-container');
				this.alignment = '';
				if ($el.hasClass('alignleft')) this.alignment='left';
				if ($el.hasClass('alignright')) this.alignment='right';
				if ($el.hasClass('aligncenter')) this.alignment='center';
				//remove <p> around images (inserted automatically by WP)
				$div.find('.zedity-box-Image').each(function(){
					$(this).find('p img').unwrap();
				});
				//unwrap images from anchors and set link
				$div.find('.zedity-box-Image a img').each(function(idx,elem){
					var $elem = $(elem);
					var href = $elem.parent().attr('href');
					var target = $elem.parent().attr('target');
					$elem.unwrap();
					$elem.parent().attr('data-href',href).attr('data-target',target||'_self');
				});
				//convert target attributes
				$div.find('[target=_top],a:not([target])').each(function(){
					$(this).attr('target','_self').attr('data-target','_self');
				});
				$div.find('[data-target=_top]').each(function(){
					$(this).attr('data-target','_self');
				});
				//convert spacers
				$div.find('.zedity-spacer').replaceWith('<br/>');
				return $div.html();
			},
			//set content into Zedity editor
			setContentInEditor: function(content){
				this.responsive = $(content).hasClass('zedity-responsive') ? 1 : $(content).hasClass('zedity-responsive-layout') ? 2 : 0;
				content = this.convert(content);
				zedityEditor.page.content(content);
				//reset undo data
				Zedity.core.store.delprefix('zedUn');
				Zedity.core.gc.flushData();
				zedityEditor.page._saveUndo();
				setTimeout(function(){
					zedityEditor.contentChanged = false;
				},0);
			},
			//load content from file (via ajax direct url)
			loadFromFile: function(url){
				zedityEditor.lock('<p><?php echo addslashes(__('Loading content.','zedity'))?><br/><?php echo addslashes(__('Please wait...','zedity'))?></p>');
				console.log('Loading content from file via cached direct url='+url);
				$.ajax({
					type: 'GET',
					url: url, //use direct url because is already cached
					dataType: 'html',
					success: $.proxy(function(data){
						//let's not use jQuery and avoid reg exp
						var docfrag = document.createDocumentFragment();
						var d = document.createElement("div");
						d.innerHTML = data;
						docfrag.appendChild(d);
						data = docfrag.querySelector('.zedity-editor');

						this.setContentInEditor(data);
					},this),
					error: $.proxy(function(xhr,status,error){
						console.log('Failed. Status=',status,'\n error=',error);
						this.loadFromFile2(url);
					},this),
					complete: function(){
						zedityEditor.unlock();
					}
				});
			},
			//load content from file (via ajax helper)
			//(used if the cached url changed, causing an apparent cross domain)
			loadFromFile2: function(){
				var url = 'admin-ajax.php?action=zedity_ajax';
				console.log('Loading content from file (now via ajx helper), url='+url);
				zedityEditor.lock('<p><?php echo addslashes(__('Loading content.','zedity'))?><br/><?php echo addslashes(__('Please wait...','zedity'))?></p>');
				$.ajax({
					type: 'GET',
					url: url,
					data: {
						zaction: 'load',
						tk: '<?php echo wp_create_nonce('zedity') ?>',
						id: this.id
					},
					dataType: 'json',
					success: $.proxy(function(data){
						if (data.error) {
							alert('<?php echo addslashes(__('Error during content load:','zedity'))?>\n'+data.error);
							return;
						}
						//let's not use jQuery and avoid reg exp
						var docfrag = document.createDocumentFragment();
						var d = document.createElement('div');
						d.innerHTML = data.content; //here data.content
						docfrag.appendChild(d);
						data = docfrag.querySelector('.zedity-editor');

						this.setContentInEditor(data);
					},this),
					error: function(xhr,status,error){
						alert('<?php echo addslashes(__('Unexpected error during content load:','zedity'))?>\n'+error.toString());
					},
					complete: function(){
						zedityEditor.unlock();
					}
				});
			},
			//save content to file
			saveToFile: function(content){
				zedityEditor.lock('<p><?php echo addslashes(__('Uploading content.','zedity'))?><br/><?php echo addslashes(__('Please wait...','zedity'))?></p>');
				$.ajax({
					type: 'POST',
					url: 'admin-ajax.php?action=zedity_ajax',
					data: {
						zaction: 'save',
						tk: '<?php echo wp_create_nonce('zedity') ?>',
						id: this.id,
						post_id: parent.post_id,
						title: this.title,
						content: content
					},
					dataType: 'json',
					success: $.proxy(function(data){
						if (!data) {
							alert('<?php echo addslashes(__('Unexpected error during content save:','zedity'))?>\n<?php echo addslashes(__('No data received from the server.','zedity'))?>');
							return;
						}
						if (data.error) {
							alert('<?php echo addslashes(__('Error during content save:','zedity'))?>\n'+data.error);
							if (data.reload) location.reload();
							return;
						}
						var size = zedityEditor.page.size();
						var align = this.alignment==='' ? '' : ' align'+this.alignment;
						var $div = $(content);
						//construct <iframe> and wrappers
						if ($div.hasClass('zedity-responsive-layout')) {
							//new responsive layout
							var $wrapper = $(
								'<div id="'+zedityEditor.id+'" class="zedity-wrapper'+align+'">'+
								'<div class="zedity-iframe-wrapper zedity-responsive-layout'+align+'">'+
								'<iframe class="zedity-iframe" src="'+data.url+'?'+Zedity.core.genId('')+'" style="width:100%;height:100%" scrolling="no" data-id="'+data.id+'"></iframe>'+
								'</div></div>'
							);
							//transfer data attributes
							$.each($div.get(0).attributes, function(idx,attr){
								if (/^data-layout/.test(attr.nodeName))
									$wrapper.find('.zedity-iframe-wrapper').attr(attr.nodeName,attr.nodeValue);
							});
						} else {
							var responsive = this.responsive==1 ? ' zedity-responsive' : '';
							var $wrapper = $(
								'<div id="'+zedityEditor.id+'" class="zedity-wrapper'+align+'">'+
								'<div class="zedity-iframe-wrapper'+responsive+align+'" style="max-width:'+size.width+'px;max-height:'+size.height+'px" data-origw="'+size.width+'" data-origh="'+size.height+'">'+
								'<iframe class="zedity-iframe" src="'+data.url+'?'+Zedity.core.genId('')+'" width="'+size.width+'" height="'+size.height+'" scrolling="no" data-id="'+data.id+'"></iframe>'+
								'</div></div>'
							);
						}
						$wrapper.find('.zedity-iframe').attr('title',this.title);
						this.sendToTinyMCE($wrapper.get(0).outerHTML);
					},this),
					error: function(xhr,status,error){
						if (error.name=='SyntaxError') {
							alert('<?php echo addslashes(__('Unexpected error during content save.','zedity'))?>');
						} else {
							alert('<?php echo addslashes(__('Unexpected error during content save:','zedity'))?>\n'+error.toString());
						}
					},
					complete: function(){
						zedityEditor.unlock();
					}
				});
			},
			//add watermark to content
			setWatermark: function(content){
				var $html = $('<div/>').append(content);
				//if (this.watermarkposition=='none') {
				//	$html.find('.zedity-editor').append('<div class="zedity-watermark" style="display:none" data-pos="none"/>');
				//	return $html.html();
				//}
				var datapos;
				var css = "position:absolute;background:rgba(70,70,70,0.75);z-index:99999;padding:0 6px 1px;border-radius:6px;height:20px;line-height:20px;";
				switch (this.watermarkposition) {
					case 'topleft':
						datapos = 'topleft';
						css += "top:0;left:0;";
					break;
					case 'topright':
						datapos = 'topright';
						css += "top:0;right:0;";
					break;
					case 'bottomleft':
						datapos = 'bottomleft';
						css += "bottom:0;left:0;";
					break;
					case 'bottomright':
						datapos = 'bottomright';
						css += "bottom:0;right:0;";
					break;

					default:
						datapos = 'none';
						//css = "display:none;top:0;left:0;";
					break;
				}

				//construct watermark
				if (datapos!='none') {
					$html.find('.zedity-editor').append(
						'<div class="zedity-watermark" style="'+css+'" data-pos="'+datapos+'">'+
						'<span style="color:#ffd6ba;font-size:11px;font-family:Tahoma,Arial,sans-serif">Powered by <a href="http://zedity.com" target="_blank" style="font-size:11px;font-weight:bold;color:white;font-family:Verdana,Tahoma;text-decoration:none;">Zedity</a></span>'+'</div>'
					);
				}
				return $html.html();
			},
			//save content from editor
			save: function(){
				//scroll up
				$('html,body').scrollTop(0);
				var maxSize = <?php echo $this->MAX_UPLOAD_SIZE ?>;
				this.size = zedityEditor.page.size();
				//convert target attributes (avoid opening links inside the iframe)
				zedityEditor.$container.find('[target=_self],a:not([target])').each(function(){
					$(this).attr('target','_top').attr('data-target','_top');
				});
				zedityEditor.$container.find('[data-target=_self]').each(function(){
					$(this).attr('data-target','_top');
				});
				//add spacers
				zedityEditor.$container.find('.zedity-box-Text').find('p,h1,h2,h3,h4,h5,h6').filter(':empty').append('<br/>');
				zedityEditor.$container.find('.zedity-box-Text p br').each(function(){
					var $this = $(this);
					if ($.trim($this.parent().text())=='') {
						$this.replaceWith('<span class="zedity-spacer">&nbsp;</span>');
					}
				});
				
				//alignment
				if (content.alignment) zedityEditor.$this.addClass('align'+content.alignment);
				<?php if ($this->is_premium()) { ?>
					//responsive
					zedityEditor.$this.toggleClass('zedity-responsive',this.responsive==1);
				<?php } ?>
				//size
				zedityEditor.$this.attr('data-origw',this.size.width).attr('data-origh',this.size.height);
				zedityEditor.save($.proxy(function(html){
					if (html.length > maxSize) {
						alert(
							'<?php echo addslashes(__('The content you have created exceeds the maximum upload size for this site','zedity'))?> ('+Math.round(maxSize/1000000)+'MB).'+
							'\n\n'+
							'<?php echo addslashes(__('Please review your content and try again.','zedity'))?>'
						);
						return;
					}
					html = this.setWatermark(html);
					setTimeout($.proxy(function(){
						if (this.savemode==2) {
							//save inline
							this.needsPublish = false;
							var size = zedityEditor.page.size();
							var align = this.alignment==='' ? '' : ' align'+this.alignment;
							var style = this.responsive==1 ? ' style="max-width:'+size.width+'px;max-height:'+size.height+'px"' : '';
							this.sendToTinyMCE(
								'<div id="'+zedityEditor.id+'" class="zedity-wrapper'+align+'"'+style+'>'+
								html+
								'</div>'
							);
						} else {
							//save in iframe
							this.saveToFile(html);
						}
					},this),10);
				},this));
			}
		};


		tickMenu = function($item){
			$item.find('.zedity-menu-icon').removeClass('zedity-icon-none').addClass('zedity-icon-yes');
			$item.siblings().each(function(idx,elem){
				$(elem).find('.zedity-menu-icon').removeClass('zedity-icon-yes').addClass('zedity-icon-none');
			});
		};
		//TODO: rename it to refreshEditor
		resizeEditor = function(editor){
			//reposition to center the editor
			var ew = Math.max(editor.page.size().width,630);
			var bw = $('body').width();
			$('.zedity-mainmenu').css('width', Math.min(ew,bw)-4);
			editor.$container.css({
				width: Math.min(ew,bw)+4,
				'margin-left': (ew<bw) ? (bw-ew)/2 : ''
			});
			$('#statusbar').css({
				width: Math.min(ew,bw),
				'margin-left': (ew<bw) ? (bw-ew)/2+1 : ''
			});
			//refresh alignment
			tickMenu(editor.$container.find('.zedity-mainmenu .zedity-menu-PageAlign[data-type='+content.alignment+']'));
			//refresh watermark
			tickMenu(editor.$container.find('.zedity-mainmenu .zedity-menu-Watermark[data-type='+content.watermarkposition+']'));
			//refresh theme style
			var type = editor.$this.hasClass('zedity-notheme') ? 'no' : 'yes';
			tickMenu(editor.$container.find('.zedity-mainmenu .zedity-menu-ThemeStyle[data-type='+type+']'));
			editor.$container.find('.zedity-mainmenu .zedity-menu-ThemeStyleMain').toggleClass('ui-state-disabled',content.savemode==1);
			<?php if ($this->is_premium()){ ?>
				//set title for disk save icon (and save menu item) to reflect the actual the responsive setting
				editor.$container.find('.zedity-mainmenu .zedity-menu-SavePage a').attr(
					'title',
					'<?php echo addslashes(__('Save','zedity'))?> ('+(content.responsive ? '<?php echo addslashes(__('Responsive','zedity'))?>' : '<?php echo addslashes(__('Not responsive','zedity'))?>')+')'
				);
				//force content alignment to center if content is responsive (#623)
				var pamenu = editor.$container.find('.zedity-mainmenu .zedity-menu-PageAlignMain');
				if (content.responsive && !pamenu.hasClass('ui-state-disabled')) {
					pamenu.addClass('ui-state-disabled');
					editor.$container.find('.zedity-mainmenu .zedity-menu-PageAlign[data-type=center]').trigger('click');
				} else {
					pamenu.toggleClass('ui-state-disabled',!!content.responsive);
				}
				if (editor.responsive && content.responsive==2) {
					var short = {1:'XS', 6:'S', 11:'L', 16:'XL'};
					$('#currentLayout').show().text(short[editor.responsive.current]);
				} else {
					$('#currentLayout').hide();
				}
			<?php } ?>
			//refresh status bar
			$('#ddSaveMode').val(content.savemode);
			//content.responsive = editor.$this.hasClass('zedity-responsive-layout') ? 1 : editor.$this.hasClass('zedity-responsive-layout') ? 2 : 0;
			$('#ddResponsive').val(content.responsive);
			//$('#ddResponsive').val(content.responsive ? 1 : (editor.$this.hasClass('zedity-responsive-layout')) ? 2 : 0);

			$('#statusBarContentModeTT').prop(
				'title',
				content.savemode == 1 ?
					'<?php echo addslashes(__('<b>Isolated mode</b>: the HTML content is saved into a file in your Media Library and loaded inside an iframe into your page. This is useful to prevent the theme or other plugins from causing undesired modifications to your designs.','zedity'))?>' :
					'<?php echo addslashes(__('<b>Standard mode</b>: the HTML content is saved inline, just like if it was created with the WordPress editor (and, as such, other plugins or the theme may modify it). This is the preferred mode for SEO and social sharing.','zedity'))?>'
			).tooltip();
		};

		$('#statusbar').on('change','#ddSaveMode',function(){
			content.savemode = parseInt($(this).val());
			resizeEditor(zedityEditor);
		});
		$('#statusbar').on('change','#ddResponsive',function(){
			var old = content.responsive;
			var val = parseInt($(this).val());
			content.responsive = val;
			if (val==2 && zedityEditor.responsive) {
				zedityEditor.responsive.start();
			} else if (zedityEditor.$this.hasClass('zedity-responsive-layout') && zedityEditor.responsive) {
				zedityEditor.responsive.revert();
				if (zedityEditor.$this.hasClass('zedity-responsive-layout')) {
					content.responsive = old;
				}
			}
			content.needsPublish = true;
			resizeEditor(zedityEditor);
		});

		$(document).on('dialogopen','.zedity-dialog-responsive',function(event,ui){
			//on dialog open, add link to tutorial
			if ($(this).find('.zedity-tutorial').length) return;
			$(this).append(
				'<p class="zedity-tutorial" style="text-align:center"><a href="http://zedity.com/blog/multiple-layout-resposive-design/" target="_blank">'+
					'<?php echo addslashes(__('Learn more about Multiple Layout Responsive Design','zedity'))?> (MLRD).'+
				'</a></p>'
			);
		});
		//-----------------------------------------------------------------------------------------
		//Media Library
		
		var zedity_ML_frame_image = null;
		$(document).on('dialogcreate','.zedity-dialog-image',function(event,ui){
			if (!zedity_ML_frame_image) {
				//add new tab for media library
				var $tabs = $('.zedity-dialog-image .tabs');
				$tabs.find('ul').prepend('<li><a href="#tab-image-ML"><?php echo addslashes(__('Media Library','zedity'))?></a></li>');
				$tabs.append(
					'<div id="tab-image-ML">'+
					'<p><?php echo addslashes(__('Insert an image from the WordPress Media Library.','zedity'))?></p>'+
					'<p><?php echo addslashes(__('You can choose among the images you already have in your library, or upload a new one.','zedity'))?></p>'+
					'<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only zedity-open-ML"><span class="ui-button-text"><?php echo addslashes(__('Open Media Library...','zedity'))?></span></button>'+
					'</div>'
				);
				$tabs.tabs('refresh');
				$tabs.tabs('selected','tab-image-ML');
				$tabs.find('.zedity-open-ML').on('click.zedity',function(){
					zedity_ML_frame_image.open();
				});
				
				//create media library frame
				zedity_ML_frame_image = parent.wp.media.frames.zedity_media_frame = parent.wp.media({
					className: 'media-frame zedity-media-frame',
					title: jQuery(this).data('uploader_title'),
					button: {
						text: jQuery(this).data('uploader_button_text'),
					},
					library: {
						type: 'image'
					},
					multiple: false
				});
				zedity_ML_frame_image.on('select', function(){
					var $dialog = $('.zedity-dialog-image');
					var file = zedity_ML_frame_image.state().get('selection').first().toJSON();
					//console.log('file',file);
					$dialog.find('.tabs').tabs('selected','tab-image-link');
					$dialog.find('#zedity-txtImageLink').val(file.url);
					$dialog.find('#zedity-txtImageDescription').val(file.alt||file.description||file.title||file.caption);
				});
				
				<?php if (!$this->is_premium()) { ?>
					$('#tab-image-link').prepend(
						'<div id="zedity-pnlThumbML">'+
						'<?php echo addslashes(__('Select size format from Media Library:','zedity'))?><br/>'+
						'<select id="zedity-ddThumbML" style="width:170px" disabled="disabled">'+
							'<option value="full"><?php echo addslashes(__('Full size','zedity'))?></option>'+
						'</select>&nbsp;<span class="zedity-tooltip">?</span>'+
						'</div><br/>'
					);
					$('#tab-image-link .zedity-tooltip').attr('title',ZedityPromo.message).tooltip();
				<?php } ?>
			}
		});

		//-----------------------------------------------------------------------------------------
		//Zedity editor


		var fontSizes = <?php echo json_encode($this->get_font_sizes())?>;
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
			snapPage: <?php echo $options['snap_to_page']?'true':'false'?>,
			snapBoxes: <?php echo $options['snap_to_boxes']?'true':'false'?>,
			onchange: function(){
				this.contentChanged = true;
				window.resizeEditor(this);
				<?php if (!$this->is_premium()) { ?>
					if ($('.zedity-fss-menu').length && !$('.zedity-fss-menu .ui-menu-item-promo').length) {
						$('.zedity-fss-menu').append(
							'<li data-value="0" class="ui-menu-item ui-menu-item-promo ui-state-disabled" role="presentation">'+
							'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem">'+
							'<small><?php echo addslashes(sprintf(__('More sizes in %s','zedity'),'Zedity Premium'))?></small>'+
							'</a></li>'
						);
					}
				<?php } ?>
			},
			onsave: function(){
				setTimeout(function(){
					saveContent();
				},100);
			},
			Text: {
				fontSizes: fontSizes,
				defaultFontSize: fontSizes.indexOf('14'),
				fonts: fonts
			},
			Image: {
				layout: 'fit',
				maxSize: 10485760, // 10MB (keep it in sync with utils/img2base64.php MAX_FILESIZE
				action: '<?php echo plugins_url('views/img2base64.php',dirname(__FILE__))?>'
			}
		});
		zedityEditor.page._sizeConstraints.minWidth = <?php echo WP_Zedity_Plugin::MIN_WIDTH?>;
		zedityEditor.page._sizeConstraints.minHeight = <?php echo WP_Zedity_Plugin::MIN_HEIGHT?>;
		zedityEditor.$this.addClass('zedity-notheme');

		var zedityMenu = zedityEditor.$container.find('.zedity-mainmenu');
		
		//move 'Clear all' to 'Edit' menu
		zedityMenu.find('li.zedity-menu-ClearAll')
			.add(zedityMenu.find('li.zedity-menu-ClearAll').prev())
			.appendTo('.zedity-mainmenu > li:nth-child(2) > ul');
		
		//add Alignment to menu
		zedityMenu.find('li.ui-menubar:first-child > ul').append(
			'<li class="ui-state-disabled zedity-separator ui-menu-item" role="presentation" aria-disabled="true"><a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"></a></li>'+
			'<li class="ui-menu-item zedity-menu-PageAlignMain" role="presentation">'+
				'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="ui-menu-icon ui-icon ui-icon-carat-1-e"></span><span class="zedity-menu-icon zedity-icon-none"></span><?php echo addslashes(__('Alignment','zedity'))?></a>'+
				'<ul class="ui-menu ui-widget ui-widget-content ui-corner-all" role="menu" aria-expanded="false" style="display:none" aria-hidden="true">'+
					'<li class="zedity-menu-PageAlign ui-menu-item" role="presentation" data-type="">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-yes"></span><?php echo addslashes(__('None','zedity'))?></a>'+
					'</li>'+
					'<li class="zedity-menu-PageAlign ui-menu-item" role="presentation" data-type="left">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-none"></span><?php echo addslashes(__('Left','zedity'))?></a>'+
					'</li>'+
					'<li class="zedity-menu-PageAlign ui-menu-item" role="presentation" data-type="center">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-none"></span><?php echo addslashes(__('Center','zedit'))?></a>'+
					'</li>'+
					'<li class="zedity-menu-PageAlign ui-menu-item" role="presentation" data-type="right">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-none"></span><?php echo addslashes(__('Right','zedity'))?></a>'+
					'</li>'+
				'</ul>'+
			'</li>'
		);
		//add Theme style to menu
		zedityMenu.find('li.ui-menubar:first-child > ul').append(
			'<li class="ui-menu-item zedity-menu-ThemeStyleMain" role="presentation">'+
				'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="ui-menu-icon ui-icon ui-icon-carat-1-e"></span><span class="zedity-menu-icon zedity-icon-none"></span><?php echo addslashes(__('Theme style','zedity'))?></a>'+
				'<ul class="ui-menu ui-widget ui-widget-content ui-corner-all" role="menu" aria-expanded="false" style="display:none" aria-hidden="true">'+
					'<li class="zedity-menu-ThemeStyle ui-menu-item" role="presentation" data-type="yes">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-none"></span><?php echo addslashes(__('Enabled','zedity'))?></a>'+
					'</li>'+
					'<li class="zedity-menu-ThemeStyle ui-menu-item" role="presentation" data-type="no">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-yes"></span><?php echo addslashes(__('Disabled','zedity'))?></a>'+
					'</li>'+
				'</ul>'+
			'</li>'
		);
		//add Watermark to menu
		zedityMenu.find('li.ui-menubar:first-child > ul').append(
			'<li class="ui-menu-item" role="presentation">'+
				'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="ui-menu-icon ui-icon ui-icon-carat-1-e"></span><span class="zedity-menu-icon zedity-icon-none"></span><?php echo addslashes(__('Watermark','zedity'))?></a>'+
				'<ul class="ui-menu ui-widget ui-widget-content ui-corner-all" role="menu" aria-expanded="false" style="display:none" aria-hidden="true">'+
					'<li class="zedity-menu-Watermark ui-menu-item" role="presentation" data-type="none">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-yes"></span><?php echo addslashes(__('None','zedity'))?></a>'+
					'</li>'+
					'<li class="zedity-menu-Watermark ui-menu-item" role="presentation" data-type="topleft">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-none"></span><?php echo addslashes(__('Top left','zedity'))?></a>'+
					'</li>'+
					'<li class="zedity-menu-Watermark ui-menu-item" role="presentation" data-type="topright">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-none"></span><?php echo addslashes(__('Top right','zedity'))?></a>'+
					'</li>'+
					'<li class="zedity-menu-Watermark ui-menu-item" role="presentation" data-type="bottomleft">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-none"></span><?php echo addslashes(__('Bottom left','zedity'))?></a>'+
					'</li>'+
					'<li class="zedity-menu-Watermark ui-menu-item" role="presentation" data-type="bottomright">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-none"></span><?php echo addslashes(__('Bottom right','zedity'))?></a>'+
					'</li>'+
				'</ul>'+
			'</li>'
		);
		//add Save to menu
		zedityMenu.find('li.ui-menubar:first-child > ul').append(
			'<li class="ui-state-disabled zedity-separator ui-menu-item" role="presentation" aria-disabled="true"><a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"></a></li>'+
			'<li class="zedity-menu-SavePage ui-menu-item" role="presentation">'+
				'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-disk"></span><?php echo addslashes(__('Save','zedity'))?></a>'+
			'</li>'
		);
		//add shortcut buttons
		zedityMenu.append(
			'<li class="ui-menu-item" role="presentation">'+
				'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><?php echo addslashes(__('Help','zedity'))?></a>'+
				'<ul class="ui-menu ui-widget ui-widget-content ui-corner-all" role="menu" aria-expanded="true" style="display:none">'+
					'<li class="ui-menu-item" role="presentation">'+
						'<a href="http://zedity.com/blog/tutorials" class="ui-corner-all" tabindex="-1" role="menuitem" target="_blank"><?php echo addslashes(__('Tutorials','zedity'))?></a>'+
					'</li>'+
				'</ul>'+
			'</li>'+
			//'<li class="zedity-menu-SavePage ui-menu-item zedity-menu-quick" role="presentation" title="Save">'+
			//	'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-disk"></span></a>'+
			//'</li>'+
			'<li class="zedity-menu-EditUndoRedo ui-menu-item zedity-menu-quick" data-type="redo" role="presentation" title="<?php echo addslashes(__('Redo','zedity'))?> (ctrl+y)">'+
				'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-redo" style="background-size:85%"></span></a>'+
			'</li>'+
			'<li class="zedity-menu-EditUndoRedo ui-menu-item zedity-menu-quick" data-type="undo" role="presentation" title="<?php echo addslashes(__('Undo','zedity'))?> (ctrl+z)">'+
				'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-undo" style="background-size:85%"></span></a>'+
			'</li>'
			//+'<li class="zedity-menu-separator ui-menu-item ui-state-disabled zedity-menu-quick"></li>'
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
			content.alignment = $(this).attr('data-type');
			zedityEditor.$this.removeClass('alignleft alignright aligncenter');
			if (content.alignment) zedityEditor.$this.addClass('align'+content.alignment);
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
			content.watermarkposition = $(this).attr('data-type');
			resizeEditor(zedityEditor);
		});
		//add boxes
		zedityMenu.find('.zedity-menu-AddBox').off('click.zedity').on('click.zedity',function(event){
			zedityEditor.menu.close();
			zedityEditor.boxes.add($(this).attr('data-boxtype'),{
				x: Math.max($(window).scrollLeft()-zedityEditor.$this.offset().left,0)+20,
				y: Math.max($(window).scrollTop()-zedityEditor.$this.offset().top+65,0)+20
			});
			return false;
		});
		//save
		var saveContent = function(){
			if (zedityEditor.responsive && zedityEditor.responsive.current) {
				zedityEditor.responsive.saveLayout();
				return;
			}
			if (content.title || content.savemode==2) {
				content.save();
			} else {
				Zedity.core.dialog({
					question: '<?php echo addslashes(sprintf(__('Please provide a title for this %s content:','zedity'),'Zedity'))?> '+
						'<span class="zedity-tooltip" title="<?php echo addslashes(__('A short description used as the name of the file into which the content is saved in your Media Library.','zedity'))?>">?</span>',
					mandatory: '<?php echo addslashes(__('Please insert a title.','zedity'))?>',
					ok: function(answer){
						content.title = answer;
						content.save();
					}
				});
			}
		};
		$('#saveBtn').on('click', function(){
			saveContent();
			return false;
		});
		zedityMenu.find('.zedity-menu-SavePage').on('click',function(){
			saveContent();
			return false;
		});
		<?php if (!$this->is_premium()) { ?>
			//show "premium feature" notice in status bar
			Zedity.core.shortcuts.add('up down left right',zedityEditor,function(event){
				if (this.$this.children('.zedity-box.zedity-selected').length>0) {
					$('#goPremiumLink').addClass('premiumfeat');
					setTimeout(function(){
						$('#goPremiumLink').removeClass('premiumfeat');
					}, 1000);
					return true;
				}
			},true);
		<?php } ?>


		//-----------------------------------------------------------------------------------------
		//resizing
		$(parent.document).find('#TB_iframeContent').addClass('zedity-editor-iframe').css('width','100%');
		parent.resizeForZedity();
		
		</script>
		
		<?php $this->additional_editor_js($options); ?>
		
		<script type="text/javascript">
		//set content
		try {
			content.getFromTinyMCE();
		} catch(e) {}
		resizeEditor(zedityEditor);
		$('#ddResponsive').trigger('change');
		</script>
	</body>
	
</html>
