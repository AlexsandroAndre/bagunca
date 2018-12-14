<div class="wrap">

	<form action="options.php" method="post">
		<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

		<table class="form-table">
			<tbody>


			<tr valign="top">
				<th scope="row">
					<label for="<?php $this->_settings_id('header-code'); ?>"><?php _e('Retargeting pixel, Facebook meta data, and google analytics'); ?></label>
				</th>
				<td>
					<textarea class="large-text"
							  rows="8" cols="60"
							  id="<?php $this->_settings_id('header-code'); ?>"
							  name="<?php $this->_settings_name('header-code'); ?>"><?php echo esc_textarea($settings['header-code']); ?></textarea>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="<?php $this->_settings_id('footer-code'); ?>"><?php _e('Footer Tracking Code'); ?></label>
				</th>
				<td>
					<textarea class="large-text"
							  rows="8" cols="60"
							  id="<?php $this->_settings_id('footer-code'); ?>"
							  name="<?php $this->_settings_name('footer-code'); ?>"><?php echo esc_textarea($settings['footer-code']); ?></textarea>
				</td>
			</tr>

			</tbody>
		</table>





		<p class="submit">
			<?php settings_fields($this->settings_name); ?>
			<input type="submit" class="button button-primary" value="<?php _e('Save Changes'); ?>" />
		</p>
	</form>
</div>


