<!-- portfolio section -->
<?php $cpm_theme_options = bhumi_get_options();
$portfolio_category_slug = ($cpm_theme_options['portfolio_category'] == 'bhumi_default'?'':$cpm_theme_options['portfolio_category']);
$bhumi_portfolio_arg = array(
	'post_type'      => 'post',
	'posts_per_page' => 4,
	'post_status'    => 'publish',
	'order'          => 'desc',
	'orderby'        => 'date',
	'ignore_sticky_posts' => 1,
	'category_name' => $portfolio_category_slug,
	);
$bhumi_portfolio_query = new WP_Query($bhumi_portfolio_arg);
if($bhumi_portfolio_query->have_posts()):
	?>
	<div class="bhumi_project_section">

		<?php if($cpm_theme_options['port_heading'] !='') { ?>
			<div class="container">
				<div class="row">
					<div class="col-sm-12">
						<div class="bhumi_heading_title">
							<h3><?php echo esc_html($cpm_theme_options['port_heading']); ?></h3>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>

		<div class="container">
			<div class="row" >

				<div id="bhumi_portfolio_section" class="enima_photo_gallery">
					<?php
					while($bhumi_portfolio_query->have_posts()):
						$bhumi_portfolio_query->the_post();
							$portfolio_image_id = get_post_thumbnail_id();
							$porfolio_image = wp_get_attachment_image_src( $portfolio_image_id, 'bhumi_portfolio');
						   ?>
								<div class="col-lg-3 col-md-3 col-sm-6 pull-left fade-right d1 in">
									<div class="img-wrapper">

										<?php if(!empty($porfolio_image)) { ?>
												<div class="bhumi_home_portfolio_showcase">
													<img class="bhumi_img_responsive" alt="<?php the_title_attribute(); ?>" src="<?php echo esc_url($porfolio_image[0]); ?>">

													<div class="bhumi_home_portfolio_showcase_overlay">
														<div class="bhumi_home_portfolio_showcase_overlay_inner ">

															<div class="bhumi_home_portfolio_showcase_icons">
																<a title="<?php the_title(); ?>" href="<?php the_permalink() ?>"><i class="fa fa-link"></i></a>
															</div>
														</div>
													</div>

												</div>
										<?php } ?>

												<div class="bhumi_home_portfolio_caption">
													<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
												</div>

									</div>
									<div class="bhumi_portfolio_shadow"></div>
								</div>
						<?php
					   endwhile;
					   wp_reset_postdata();
					   ?>
				</div>

			</div>
		</div>

	</div>
	<!-- /portfolio section -->
<?php endif;