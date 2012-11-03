<div class=wrap>
	<div class="icon32" id="icon-edit"><br /></div>
	<h2><?php _e('Wiki Loves Monuments latest pictures', 'wlmlatest') ?></h2>
	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
		<div class="postbox " id="postexcerpt">
			<h3><?php _e('Parameters:', 'wlmlatest') ?></h3>
			<div class="inside">
				<p>
					<label for="category_name"><?php _e('Category name:', 'wlmlatest') ?></label>
					<input type="text" id="category_name" name="category_name" value="<?php echo $options['category_name']; ?>" size="60" /><br />
					<em><?php _e('Should be something like "Images from Wiki Loves Monuments 2012 in [Country]".', 'wlmlatest'); ?></em>
				</p>
				<p>
					<label for="language"><?php _e('Language code:', 'wlmlatest') ?></label>
					<input type="text" id="language" name="language" value="<?php echo $options['language']; ?>" size="10" /><br />
				</p>
				<p>
					<label for="pics_number"><?php _e('Number of thumbnails in the gallery:', 'wlmlatest') ?></label>
					<input type="text" id="pics_number" name="pics_number" value="<?php echo $options['pics_number']; ?>" />
				</p>
				<p>
					<label for="width"><?php _e('Width of thumbnails in the gallery:', 'wlmlatest') ?></label>
					<input type="text" id="width" name="width" value="<?php echo $options['width']; ?>" />
				</p>
				<p>
					<label for="widget_width"><?php _e('Width of the thumbnail in the widget:', 'wlmlatest') ?></label>
					<input type="text" id="widget_width" name="widget_width" value="<?php echo $options['widget_width']; ?>" />
				</p>
			</div>
        </div>
        <div class="submit">
            <input type="submit" name="update_wlm_latestSettings" value="<?php _e('Update', 'wlmlatest') ?>" />
        </div>
</div>
