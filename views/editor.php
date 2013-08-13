<html>
	<head>
		<title>Zedity Editor</title>

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
		foreach ($options['webfonts'] as $font) {
			$fontname = explode(',',$font);
			$fontname = urlencode($fontname[0]);
			?>
			<link href='//fonts.googleapis.com/css?family=<?php echo $fontname?>' rel='stylesheet' type='text/css'>
			<?php
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
		</style>
	</head>
	
	<body>
		<div id="zedityEditor"></div>
		<div id="filler"></div>


		<?php if (function_exists(get_premium_embedcodes)) get_premium_embedcodes() ?>


		<script type="text/javascript">
		//-----------------------------------------------------------------------------------------
		//helper functions

		function getZedityContent(){
			var mce = parent.tinyMCE.get('content');
			//select the Zedity content
			var element = mce.selection.getNode();
			element = mce.dom.getParent(element,function(elem){
				return $(elem).hasClass('zedity-editor');
			});
			mce.selection.select(element);
			//get content
			return mce.selection.getContent({format:'html'});
		};

		function convert(content){
			var $div = $('<div/>');
			$div.html(content);
			$div.find('.zedity-box-Image').each(function(){
				$(this).find('p img').unwrap();
			});
			return $div.html();
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

		fonts = fonts.concat(webfonts);
		fonts = fonts.filter(function(a){if(!this[a]){this[a]=1;return a;}},{});
		fonts.sort(function(a,b){
			a = a.split(',')[0];
			b = b.split(',')[0];
			if (a > b) return 1;
			if (a < b) return -1;
			return 0;
		});

		zedityEditor = new Zedity({
			container: '#zedityEditor',
			width: <?php echo $options['page_width']?>,
			height: <?php echo $options['page_height']?>,
			onchange: function(){
				this.contentChanged = true;
			},
			Text: {
				fonts: fonts
			}
		});

		//add Save to menu
		zedityEditor.$container.find('.zedity-mainmenu li.ui-menubar:first-child ul').append(
			'<li class="ui-state-disabled zedity-separator ui-menu-item" role="presentation" aria-disabled="true"><a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"></a></li>'+
			'<li class="zedity-menu-SavePage ui-menu-item" role="presentation">'+
				'<a href="javascript:;" class="ui-corner-all" tabindex="-1" role="menuitem"><span class="zedity-menu-icon zedity-icon-empty"></span>Save</a>'+
			'</li>'
		);
		zedityEditor.$container.find('.zedity-mainmenu .zedity-menu-SavePage').on('click',function(){
			zedityEditor.save(function(html){
				 $(parent.document).find('#TB_iframeContent').removeClass('zedity-iframe');
				parent.send_to_editor(html);
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
			} catch(e) {}
		})();

		zedityEditor.contentChanged = false;
		
		</script>

	</body>
	
</html>
