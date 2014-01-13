<html>
	<head>
		<title>Zedity - Editing Reinvented</title>

		<link rel="stylesheet" href="<?php echo plugins_url('jquery/jquery-ui.min.css',dirname(__FILE__))?>" type="text/css" media="all" />
		<?php
		//remove all external scripts/styles
		global $wp_scripts, $wp_styles;
		foreach($wp_scripts->queue as $handle) {
			wp_dequeue_script($handle);
		}
		foreach($wp_styles->queue as $handle) {
			wp_dequeue_style($handle);
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
		//print scripts
		wp_print_head_scripts();
		wp_print_footer_scripts();
		?>
		<script type="text/javascript">
		$ = jQuery;
		var linkMsg = 'For information or to upgrade to Zedity Premium, please visit <a href="http://zedity.com/plugin/wp" target="_blank">zedity.com</a>.';
		ZedityPromo = {
			product: 'Zedity Premium',
			productShort: 'Premium',
			message: 'This is a Zedity Premium feature.<br/>'+linkMsg,
			feature: {
				linkOnBox: 'Premium feature: associate a link to the box.<br/>'+linkMsg,
				boxSize: 'Premium feature: view and set exact box size.<br/>'+linkMsg,
				textParagraph: 'Premium feature: SEO friendly tags, e.g. title, paragraph, etc.<br/>'+linkMsg,
				textLink: 'Premium feature: open link in a new tab.<br/>'+linkMsg,
				imageFilters: 'Premium feature: enhance images with special effects.<br/>'+linkMsg,
				colorButtons: 'Premium feature: set custom RGB or Hex colors',
				additionalMedia: linkMsg,
				additionalBoxes: true // this message is not shown anyway (disabled items in menu)
			}
		};
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
			}
			.zedity-mainmenu {
				position: fixed !important;
				top: 30px;
				width: 99%;
			}
			.zedity-editor {
				margin-top: 63px;
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
			.zedity-menu-imglayout-menu,
			.zedity-menu-imgqual-menu,
			.zedity-dialog-image .tabs li[aria-controls=tab-image-disk] {
				display: none !important;
			}
			/*status bar*/
			#statusbar {
				position: fixed;				
				margin: 0 auto;
				height: 26px;
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
			#statusbar .info {
				line-height: 26px;
				margin-left: 15px;
			}
			#statusbar .info.premiumfeat {
				color: white;
				background: darkgoldenrod;
				padding: 1px 4px 2px;
				border-radius: 10px;				
			}
			#statusbar .info .yes{
				color: darkgreen;
				font-weight: bold;
			}
			#statusbar .info .no{
				color: #40737a;
				font-weight: bold;
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
		<div id="statusbar"></div>
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
			responsive: <?php echo $options['responsive'] ? 'true' : 'false'?>,
			savemode: '<?php echo $options['save_mode']?>', // 1: isolated (iframe); 2: standard (inline)
			alignment: '',
			needsPublish: false,
			//get content from tinymce editor
			getFromTinyMCE: function(){
				var content = '';
				//get TinyMCE reference
				this.mce = parent.tinyMCE.get('content');
				//get the Zedity content element
				this.element = this.mce.selection.getNode();
				this.element = this.mce.dom.getParent(this.element,function(elem){
					var $elem = $(elem);
					if ($elem.hasClass('zedity-editor') && $elem.parent().hasClass('zedity-wrapper')) return false;
					return $elem.hasClass('zedity-editor') || $elem.hasClass('zedity-wrapper');
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
				var $content = $(content);
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
				} else if ($content.length) {
					alert('The content may have been manually modified and got corrupted.');
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
				this.watermarkposition = $div.find('.zedity-watermark').attr('data-pos') || this.watermarkposition;
				//get alignment
				var $el = $div.find('.zedity-editor') || $div.find('.zedity-iframe-container');
				if ($el.hasClass('alignleft')) this.alignment='left';
				if ($el.hasClass('alignright')) this.alignment='right';
				if ($el.hasClass('aligncenter')) this.alignment='center';
				//remove <p> around images (inserted automatically by WP)
				$div.find('.zedity-box-Image').each(function(){
					$(this).find('p img').unwrap();
				});
				//convert target attributes
				$div.find('[target=_top],a:not([target])').each(function(){
					$(this).attr('target','_self').attr('data-target','_self');
				});
				$div.find('[data-target=_top]').each(function(){
					$(this).attr('data-target','_self');
				});
				return $div.html();
			},
			//set content into Zedity editor
			setContentInEditor: function(content){
				this.responsive = $(content).hasClass('zedity-responsive');
				content = this.convert(content);
				zedityEditor.page.content(content);
				//reset undo data
				Zedity.core.store.delprefix('zedUn');
				Zedity.core.gc.flushData();
				zedityEditor.page._saveUndo();
				zedityEditor.contentChanged = false;
			},
			//load content from file (via ajax direct url)
			loadFromFile: function(url){
				zedityEditor.lock('<p>Loading content.<br/>Please wait...</p>');
				console.log('Loading content from file via cached direct url='+url);
				$.ajax({
					type: 'GET',
					url: url, //use direct url because is already cached
					dataType: 'html',
					success: $.proxy(function(data){
						//get content between <body></body> (jQuery can't handle it)
						//data = data.replace(/^[\s\S]*<body.*?>|<\/body>[\s\S]*$/g,'');
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
				var url = 'index.php?page=zedity_ajax'; // ajax helper url
				console.log('Loading content from file (now via ajx helper), url='+url);
				zedityEditor.lock('<p>Loading content.<br/>Please wait...</p>');
				$.ajax({
					type: 'GET',
					url: url,
					data: {
						action: 'load',
						id: this.id
					},
					dataType: 'json html',
					success: $.proxy(function(data){
						if (data.error) {
							alert('Error during content load:\n'+data.error);
							return;
						}
						//get content between <body></body> (jQuery can't handle it)
						//data = data.content.replace(/^[\s\S]*<body.*?>|<\/body>[\s\S]*$/g,'');
						//let's not use jQuery and avoid reg exp
						var docfrag = document.createDocumentFragment();
						var d = document.createElement("div");
						d.innerHTML = data.content; //here data.content
						docfrag.appendChild(d);
						data = docfrag.querySelector('.zedity-editor');

						this.setContentInEditor(data);
					},this),
					error: function(xhr,status,error){
						alert('Unexpected error during content load:\n'+error.toString());
					},
					complete: function(){
						zedityEditor.unlock();
					}
				});
			},
			//save content to file
			saveToFile: function(content){
				zedityEditor.lock('<p>Uploading content.<br/>Please wait...</p>');
				$.ajax({
					type: 'POST',
					url: 'index.php?page=zedity_ajax',
					data: {
						action: 'save',
						id: this.id,
						post_id: parent.post_id,
						title: this.title,
						content: content
					},
					dataType: 'json',
					success: $.proxy(function(data){
						if (!data) {
							alert('Unexpected error during content save:\nNo data received from the server.');
							return;
						}
						if (data.error) {
							alert('Error during content save:\n'+data.error);
							return;
						}
						var size = zedityEditor.page.size();
						var align = this.alignment==='' ? '' : ' align'+this.alignment;
						var responsive = this.responsive ? ' zedity-responsive' : '';
						//construct <iframe> and wrappers
						content = $(
							'<div class="zedity-wrapper'+align+'" id="'+zedityEditor.id+'">'+
							'<div class="zedity-iframe-wrapper'+responsive+align+'" style="max-width:'+size.width+'px;max-height:'+size.height+'px" data-origw="'+size.width+'" data-origh="'+size.height+'">'+
							'<iframe class="zedity-iframe" src="'+data.url+'?'+Zedity.core.genId('')+'" width="'+size.width+'" height="'+size.height+'" scrolling="no" data-id="'+data.id+'"></iframe>'+
							'</div></div>'
						).find('.zedity-iframe').attr('title',this.title).end().get(0).outerHTML;
						this.sendToTinyMCE(content);
					},this),
					error: function(xhr,status,error){
						if (error.name=='SyntaxError') {
							alert('Unexpected error during content save.');
						} else {
							alert('Unexpected error during content save:\n'+error.toString());
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

					default: // none
						datapos = 'none';
						css = "display:none;top:0;left:0;";
					break;
				}

				//construct watermark
				$html.find('.zedity-editor').append(
					'<div class="zedity-watermark" style="'+css+'" data-pos="'+datapos+'">'+
					'<span style="color:#ffd6ba;font-size:11px;font-family:Tahoma,Arial,sans-serif">Powered by <a href="http://zedity.com" target="_blank" style="font-size:11px;font-weight:bold;color:white;font-family:Verdana,Tahoma;text-decoration:none;">Zedity</a></span>'+'</div>'
				);
				return $html.html();
			},
			//save content from editor
			save: function(){
				//scroll up
				$('html,body').scrollTop(0);
				var maxSize = <?php echo $this->MAX_UPLOAD_SIZE ?>;
				this.size = zedityEditor.page.size();
				//convert target attributes (avoid opening links inside the iframe)
				zedityEditor.$this.find('[target=_self],a:not([target])').each(function(){
					$(this).attr('target','_top').attr('data-target','_top');
				});
				zedityEditor.$this.find('[data-target=_self]').each(function(){
					$(this).attr('data-target','_top');
				});
				//alignment
				if (content.alignment) zedityEditor.$this.addClass('align'+content.alignment);
				<?php if ($this->is_premium()) { ?>
					//responsive
					zedityEditor.$this.toggleClass('zedity-responsive',this.responsive);
				<?php } ?>
				//size
				zedityEditor.$this.attr('data-origw',this.size.width).attr('data-origh',this.size.height);
				zedityEditor.save($.proxy(function(html){
					if (html.length > maxSize) {
						alert('The content you have created exceeds the maximum upload size for this site ('+Math.round(maxSize/1000000)+'MB).\n\nPlease review your content and try again.');
						return;
					}
					$(parent.document).find('#TB_iframeContent').removeClass('zedity-editor-iframe');
					html = this.setWatermark(html);
					setTimeout($.proxy(function(){
						if (this.savemode==2) {
							//save inline
							this.needsPublish = false;
							var size = zedityEditor.page.size();
							var align = this.alignment==='' ? '' : ' align'+this.alignment;
							this.sendToTinyMCE(
								'<div class="zedity-wrapper'+align+'" style="max-width:'+size.width+'px;max-height:'+size.height+'px">'+
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
			var ew = Math.max(editor.page.size().width,580);
			var bw = $('body').width();
			$('.zedity-mainmenu').css('width', Math.min(ew,bw)-4);
			editor.$container.css('margin-left', (ew<bw) ? (bw-ew)/2 : '');
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
				editor.$container.find('.zedity-mainmenu .zedity-menu-SavePage a').attr('title', 'Save ('+(content.responsive ? 'Responsive' : 'Not responsive')+')');
				//force content alignment to center if content is responsive (#623)
				var pamenu = editor.$container.find('.zedity-mainmenu .zedity-menu-PageAlignMain');
				if (content.responsive && !pamenu.hasClass('ui-state-disabled')) {
					pamenu.addClass('ui-state-disabled');
					editor.$container.find('.zedity-mainmenu .zedity-menu-PageAlign[data-type=center]').trigger('click');
				} else {
					pamenu.toggleClass('ui-state-disabled',content.responsive);
				}
			<?php } ?>
			//refresh status bar			
			var ttMsg = content.savemode == 1 ? 
				"<b>Isolated mode</b>: the HTML content is saved into a file in your Media Library and loaded inside an iframe into your page. This is useful to prevent the theme or other plugins from causing undesired modifications to your designs." : 
				"<b>Standard mode</b>: the HTML content is saved inline, just like if it was created with the WordPress editor (and, as such, other plugins or the theme may modify it). This is preferred mode for SEO and social sharing.";		    
			
			var $sb = $('#statusbar');									
			$sb.html('<span class="info">Content mode: <select id="ddSaveMode"><option value="1" '+(content.savemode==1?'selected="selected"':'')+'>Isolated</option><option value="2" '+(content.savemode==2?'selected="selected"':'')+'>Standard</option></select> <span id="statusBarContentModeTT" class="zedity-tooltip" title="'+ttMsg+'">?</span></span>');
			<?php if ($this->is_premium()) { ?>
				$sb.append('<span class="info">Responsive: <select id="ddResponsive" ><option value="1" '+(content.responsive?'selected="selected"':'')+'>Yes</option><option value="0" '+(!content.responsive?'selected="selected"':'')+'>No</option></select></span>');
			<?php } else { ?>
				$sb.append('<span class="info">Responsive: <select><option disabled="disabled">Premium feature</option></select></span>'+
				'<span class="info premiumfeat" style="display:none">Premium feature</span>');
			<?php } ?>
									
			$('#statusBarContentModeTT').tooltip();			
		};
		
		$('#statusbar').on('change','#ddSaveMode',function(){
			content.savemode = parseInt($(this).val());
			resizeEditor(zedityEditor);
		});
		$('#statusbar').on('change','#ddResponsive',function(){
			content.responsive = parseInt($(this).val())==1;
			content.needsPublish = true;
			resizeEditor(zedityEditor);
		});


		//-----------------------------------------------------------------------------------------
		//Media Library
		
		var zedity_ML_frame_image = null;
		jQuery(document).on('click.zedity_media_library', '.zedity-propbar-Image .zedity-bar-icon.zedity-icon-image', function(event){
			if (!zedity_ML_frame_image) {
				var $tabs = $('.zedity-dialog-image .tabs');
				$tabs.find('ul').prepend('<li><a href="#tab-image-ML">Media library</a></li>');
				$tabs.append(
					'<div id="tab-image-ML">'+
					'<p>Insert an image from the WordPress Media Library.</p>'+
					'<p>You can choose among the images you already have in your library, or upload a new one.</p>'+
					'<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only zedity-open-ML"><span class="ui-button-text">Open Media Library...</span></button>'+
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
					console.log('file',file);
					$dialog.find('.tabs').tabs('selected','tab-image-link');
					$dialog.find('#zedity-txtImageLink').val(file.url);
					$dialog.find('#zedity-txtImageDescription').val(file.alt||file.description||file.title||file.caption);
				});
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
							'<li data-value="0" class="ui-menu-item ui-menu-item-promo ui-state-disabled" role="presentation"><a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><small>More sizes in Zedity Premium</small></a></li>'
						);
					}
				<?php } ?>
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
				'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="ui-menu-icon ui-icon ui-icon-carat-1-e"></span><span class="zedity-menu-icon zedity-icon-none"></span>Alignment</a>'+
				'<ul class="ui-menu ui-widget ui-widget-content ui-corner-all" role="menu" aria-expanded="false" style="display:none" aria-hidden="true">'+
					'<li class="zedity-menu-PageAlign ui-menu-item" role="presentation" data-type="">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-yes"></span>None</a>'+
					'</li>'+
					'<li class="zedity-menu-PageAlign ui-menu-item" role="presentation" data-type="left">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-none"></span>Left</a>'+
					'</li>'+
					'<li class="zedity-menu-PageAlign ui-menu-item" role="presentation" data-type="center">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-none"></span>Center</a>'+
					'</li>'+
					'<li class="zedity-menu-PageAlign ui-menu-item" role="presentation" data-type="right">'+
						'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-none"></span>Right</a>'+
					'</li>'+
				'</ul>'+
			'</li>'
		);
		//add Theme style to menu
		zedityMenu.find('li.ui-menubar:first-child > ul').append(
			'<li class="ui-menu-item zedity-menu-ThemeStyleMain" role="presentation">'+
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
		//save
		zedityMenu.find('.zedity-menu-SavePage').on('click',function(){
			if (content.title || content.savemode==2) {
				content.save();
			} else {
				Zedity.core.dialog({
					question: 'Please provide a title for this Zedity content: <span class="zedity-tooltip" title="A short description used as the name of the file into which the content is saved in your Media Library.">?</span>',
					mandatory: 'Please insert a title.',
					ok: function(answer){
						content.title = answer;
						content.save();
					}
				});
			}
			return false;
		});

		<?php if (!$this->is_premium()) { ?>
			//show "premium feature" notice in status bar			
			Zedity.core.shortcuts.add('up down left right',zedityEditor,function(event){
				if (this.$this.children('.zedity-box.zedity-selected').length>0) {					
					$('#statusbar .info.premiumfeat').stop(true,true).fadeIn(100).delay(1000).fadeOut(500);
					return true;
				}
			},true);
		<?php } ?>


		//-----------------------------------------------------------------------------------------
		//resizing

		parent.resizeForZedity();

		$(parent.document).find('#TB_iframeContent').addClass('zedity-editor-iframe').css('width','100%');
		</script>
		
		<?php $this->additional_editor_js($options); ?>
		
		<script type="text/javascript">
		//set content
		try {
			content.getFromTinyMCE();
		} catch(e) {}
		resizeEditor(zedityEditor);
		</script>
	</body>
	
</html>
