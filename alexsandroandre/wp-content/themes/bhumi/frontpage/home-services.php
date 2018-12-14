<!-- service section -->
<?php $cpm_theme_options = bhumi_get_options();
$service_category_slug = ($cpm_theme_options['service_category'] == 'bhumi_default'?'':$cpm_theme_options['service_category']);

$bhumi_service_arg = array(
	'post_type'      => 'post',
	'posts_per_page' => 4,
	'post_status'    => 'publish',
	'order'          => 'desc',
	'orderby'        => 'date',
	'ignore_sticky_posts' => 1,
	'category_name' => $service_category_slug,
	);
$bhumi_service_query = new WP_Query($bhumi_service_arg);
if($bhumi_service_query->have_posts()):


?>
<div class="bhumi_service">
<?php if($cpm_theme_options['home_service_heading'] !='') { ?>
<div class="container">
	<div class="row">
		<div class="col-sm-12">
			<div class="bhumi_heading_title">
				<h3><?php echo esc_html($cpm_theme_options['home_service_heading']); ?></h3>
			</div>
		</div>
	</div>
</div>
<?php } ?>
<div class="container">
		<div class="row isotope" id="isotope-service-container">
			<?php
			while($bhumi_service_query->have_posts()):
				$bhumi_service_query->the_post();
					$service_cat_image_id = get_post_thumbnail_id();
					$service_cat_image = wp_get_attachment_image_src( $service_cat_image_id, 'thumbnail' );

				    ?>
						<div class=" col-md-3 service">
							<div class="bhumi_service_area appear-animation bounceIn appear-animation-visible">
									<?php if($service_cat_image !='') { ?>
										<img src="<?php echo esc_url($service_cat_image[0]); ?>" alt="<?php the_title(); ?>">
									<?php } ?>
									<div class="bhumi_service_detail media-body">
										<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
										</h3>
										 <p>
										<?php the_excerpt(); ?></p>
									</div>
							</div>
						</div>
			        <?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</div>
<!-- /Service section -->
<?php endif;