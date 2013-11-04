<?php
	if (!isset($options['webfonts'])) $options['webfonts'] = array();

	$allwebfonts = $this->get_webfonts();
	foreach ($allwebfonts as $font) {
		$fontname = explode(',',$font);
		$fontname = urlencode($fontname[0]);
?>
		<link href='//fonts.googleapis.com/css?family=<?php echo $fontname?>' rel='stylesheet' type='text/css'>
<?php
	}
?>

<div class="wrap">
	<?php screen_icon(); ?>
	<h2>Zedity Settings</h2>

	<form action="options.php" method="post">
		<hr/>
		<h3 class="title">Content</h3>
		<p>Enter the default size (in pixels) for your Zedity contents (you can also change the size while editing):</p>
		<table class="form-table"><tbody>
			<tr valign="top">
				<th scope="row"><label for="blogname">Content width:</label></th>
				<td>
					<input id="zedity_page_width" name="zedity_settings[page_width]" size="5" maxlength="5" type="text" value="<?php echo $options['page_width']?>" />
					(<?php echo self::MIN_WIDTH.'-'.self::MAX_WIDTH?>), numbers only.
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="blogname">Content height:</label></th>
				<td>
					<input id="zedity_page_width" name="zedity_settings[page_height]" size="5" maxlength="5" type="text" value="<?php echo $options['page_height']?>" />
					(<?php echo self::MIN_HEIGHT.'-'.self::MAX_HEIGHT?>), numbers only.
				</td>
			</tr>
		</tbody></table>

		<hr/>

		<h3 class="title">Watermark</h3>
		<p>If you like Zedity and want to support it, you can simply enable the "Powered by Zedity" watermark.</p>
		<p>Select the default watermark position (you can also change the position while creating your content):</p>
		<!--<p>Select position:</p>-->

		<div style="border:2px solid #ccc;width:300px;height:100px;padding:5px">
			<input type="radio" name="zedity_settings[watermark]" id="rbWM1" value="none" <?php echo ($options['watermark']=='none' ? 'checked="checked"':'') ?>>
			<label for="rbWM1">Disabled (no watermark is shown)</label><br/>
			<input type="radio" name="zedity_settings[watermark]" id="rbWM2" value="topleft" <?php echo ($options['watermark']=='topleft' ? 'checked="checked"':'') ?>>
			<label for="rbWM2">Top left</label><br/>
			<input type="radio" name="zedity_settings[watermark]" id="rbWM3" value="topright" <?php echo ($options['watermark']=='topright' ? 'checked="checked"':'') ?>>
			<label for="rbWM3">Top right</label><br/>
			<input type="radio" name="zedity_settings[watermark]" id="rbWM4" value="bottomleft" <?php echo ($options['watermark']=='bottomleft' ? 'checked="checked"':'') ?>>
			<label for="rbWM4">Bottom left</label><br/>
			<input type="radio" name="zedity_settings[watermark]" id="rbWM5" value="bottomright" <?php echo ($options['watermark']=='bottomright' ? 'checked="checked"':'') ?>>
			<label for="rbWM5">Bottom right</label><br/>
		</div>
		<br/>
		
		<hr/>

		<h3 class="title">Media embed</h3>
		<p>Supported media embed services.</p>

		<table class="form-table"><tbody>
			<tr valign="top">
				<th scope="row"><label for="blogname">Video:</label></th>
				<td>
					<?php
					$content = array();
					foreach($this->get_videoembeds() as $service=>$site) {
						$content[] = "<a href=\"$site\" target=\"_blank\">$service</a>";
					}
					echo implode(', ',$content);
					?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="blogname">Audio:</label></th>
				<td>
					<?php
					$content = array();
					foreach($this->get_audioembeds() as $service=>$site) {
						$content[] = "<a href=\"$site\" target=\"_blank\">$service</a>";
					}
					echo implode(', ',$content);
					?>
				</td>
			</tr>
		</tbody></table>

		<br/>

		<?php if (!$this->is_premium()) { ?>
		<p>Get <a href="http://zedity.com" target="_blank">Zedity Premium</a> with support to 20+ video and audio services embeds.</p>
		<?php } ?>
		
		<hr/>

		
		<h3 class="title">Webfonts</h3>
		<p>In addition to the standard fonts, you can select any of the following webfonts to give your content a distinctive style:</p>
		<p>(<b>Note:</b> webfonts are loaded from <a href="http://www.google.com/fonts" target="_blank">Google Fonts</a> service. Enabling many fonts at once may result in slower page load.)</p>
		<div style="border:2px solid #ccc;width:300px;height:200px;overflow-y:scroll;padding:5px">
			<?php
				$i = 0;
				foreach ($allwebfonts as $font) {
					$i++;
					$fontname = explode(',',$font);
					$fontname = $fontname[0];
			?>
				<input type="checkbox" id="cbWB_<?php echo $i ?>" name="zedity_settings[webfonts][]" value="<?php echo $font ?>" <?php checked(in_array($font,$options['webfonts'])); ?>/>
				<label for="cbWB_<?php echo $i ?>" style="font:16px <?php echo $font ?>"><?php echo $fontname ?></label>
				<br />
			<?php } ?>
		</div>
	
		<?php if (!$this->is_premium()) { ?>
		<p>Get <a href="http://zedity.com" target="_blank">Zedity Premium</a> with 100+ webfonts.</p>
		<?php } ?>

		<hr/>

		<?php
		if (method_exists($this,'additional_settings_page')) {
			$this->additional_settings_page($options);
		}
		?>
		
		<h4 class="title">License</h4>
		<p>The <?php echo $this->plugindata['Name'];?> WP plugin is available under the <a href="<?php echo $this->plugindata['LicenseURI'];?>"><?php echo $this->plugindata['License'];?></a> license.</p>
		
		<?php 
			settings_fields('wp_zedity_plugin');
			do_settings_fields('wp_zedity_plugin','zedity_settings');
			submit_button();
		?>
	</form>
</div>
