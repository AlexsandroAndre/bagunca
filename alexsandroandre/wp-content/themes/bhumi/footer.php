<!-- bhumi Callout Section -->
<?php $cpm_theme_options = bhumi_get_options(); ?>
<!-- Footer Widget Secton -->
<?php if ($cpm_theme_options['enable_pre_footer'] == 1 ) :?>
	<div class="bhumi_footer_widget_area">
		<div class="container">
			<div class="row">
				<?php
						if ( is_active_sidebar( 'footer-widget-area' ) ){
							dynamic_sidebar( 'footer-widget-area' );
						} else
						{
							$args = array(
							'before_widget' => '<div class="col-md-3 col-sm-6 bhumi_footer_widget_column">',
							'after_widget'  => '</div>',
							'before_title'  => '<div class="bhumi_footer_widget_title">',
							'after_title'   => '<div class="bhumi-footer-separator"></div></div>' );
							the_widget('WP_Widget_Pages', null, $args);
						}
				?>
			</div>
		</div>
	</div>
<?php endif; ?>
<div class="bhumi_footer_area">
		<div class="container">
			<div class="col-md-12">
			<p class="bhumi_footer_copyright_info cpm_rtl" >
			<?php esc_html_e('Theme Developed By ', 'bhumi'); ?>
			<a target="_blank" rel="nofollow" href="<?php echo esc_url('https://codethemes.co');?>"><?php echo esc_html__('Code Themes', 'bhumi');?></a></p>
			<?php if($cpm_theme_options['footer_section_social_media_enbled'] == '1') { ?>
					<div class="bhumi_footer_social_div">
						<ul class="social">
							<?php if($cpm_theme_options['fb_link']!='') { ?>
							   <li class="facebook" data-toggle="tooltip" data-placement="top" title="<?php echo esc_attr__('Facebook','bhumi'); ?>"><a  href="<?php echo esc_url($cpm_theme_options['fb_link']); ?>"><i class="fa fa-facebook"></i></a></li>
							<?php } if($cpm_theme_options['twitter_link']!='') { ?>
								<li class="twitter" data-toggle="tooltip" data-placement="top" title="<?php echo esc_attr__('Twitter','bhumi'); ?>"><a href="<?php echo esc_url($cpm_theme_options['twitter_link']) ; ?>"><i class="fa fa-twitter"></i></a></li>
							<?php } if($cpm_theme_options['linkedin_link']!='') { ?>
								<li class="linkedin" data-toggle="tooltip" data-placement="top" title="<?php echo esc_attr__('LinkedIn','bhumi')?>"><a href="<?php echo esc_url($cpm_theme_options['linkedin_link']) ; ?>"><i class="fa fa-linkedin"></i></a></li>
							<?php } if($cpm_theme_options['youtube_link']!='') { ?>
								<li class="youtube" data-toggle="tooltip" data-placement="top" title="<?php echo esc_attr__('Youtube','bhumi'); ?>"><a href="<?php echo esc_url($cpm_theme_options['youtube_link']) ; ?>"><i class="fa fa-youtube"></i></a></li>
			                <?php } if($cpm_theme_options['gplus']!='') { ?>
								<li class="gplus" data-toggle="tooltip" data-placement="top" title="<?php echo esc_attr__('Google Plus','bhumi')?>"><a href="<?php echo esc_url($cpm_theme_options['gplus']) ; ?>"><i class="fa fa-google-plus"></i></a></li>
			                <?php } if($cpm_theme_options['instagram']!='') { ?>
								<li class="facebook" data-toggle="tooltip" data-placement="top" title="<?php echo esc_attr__('Instagram','bhumi')?>"><a href="<?php echo esc_url($cpm_theme_options['instagram']) ; ?>"><i class="fa fa-instagram"></i></a></li>
			                <?php }
			                if($cpm_theme_options['skype']!='') { ?>
		                        <li class="facebook" data-toggle="tooltip" data-placement="bottom" title="<?php echo esc_html('Skype','bhumi')?>"><a href="<?php echo esc_url($cpm_theme_options['skype']) ; ?>"><i class="fa fa-skype"></i></a></li>
		                    <?php } ?>
						</ul>
					</div>
			<?php } ?>
			</div>
		</div>
</div>
<!-- /Footer Widget Secton -->
</div>
<a href="#" title="<?php echo esc_attr__('Go Top','bhumi')?>" class="bhumi_scrollup" style="display: inline;"><i class="fa fa-chevron-up"></i></a>
<?php wp_footer(); ?>
</body>
</html>