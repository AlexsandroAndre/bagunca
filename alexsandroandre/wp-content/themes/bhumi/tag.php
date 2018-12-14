<?php get_header();
get_template_part('breadcrumb'); ?>
<div class="container">
	<div class="row bhumi_blog_wrapper">
	<div class="col-md-8">
	<?php
	if ( have_posts()):
	while ( have_posts() ): the_post();
		$format = get_post_format();
				if ( false === $format ) {
					$format = 'standard';
				}
		get_template_part('template-parts/content',$format ); ?>
	<?php endwhile;
	endif;
	bhumi_navigation(); ?>
	</div>
	<?php get_sidebar(); ?>
	</div>
</div>
<?php get_footer(); ?>