<?php $cpm_theme_options = bhumi_get_options();
if(!empty($cpm_theme_options['fc_title']) || !empty($cpm_theme_options['fc_btn_txt']) || !empty($cpm_theme_options['fc_btn_link'] )): ?>
	<div class="bhumi_callout_area">
		<div class="container">
			<div class="row">
			<?php if($cpm_theme_options['fc_title'] !='') { ?>
				<div class="col-md-9">
				<p><i class="fa fa-life-bouy"></i><?php echo esc_html($cpm_theme_options['fc_title']);?></p>
				</div>
				<?php } ?>
				<?php if($cpm_theme_options['fc_btn_txt'] !='') { ?>
				<div class="col-md-3">
				<a href="<?php echo esc_url($cpm_theme_options['fc_btn_link']); ?>" class="bhumi_callout_btn"><?php echo esc_html($cpm_theme_options['fc_btn_txt']); ?></a>
				</div>
				<?php } ?>
			</div>

		</div>
		<div class="bhumi_callout_shadow"></div>
	</div>
<?php endif; ?>