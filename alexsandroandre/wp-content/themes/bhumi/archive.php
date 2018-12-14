<?php get_header(); ?>
<div class="bhumi_header_breadcrumb_title">
	<div class="container">
		<div class="row">
		<?php if(have_posts()) :?>
			<div class="col-md-12">
			<h1><?php the_archive_title(); ?>
			</h1></div>
		<?php endif; ?>
		</div>
	</div>
</div>
<div class="container">
	<div class="row bhumi_blog_wrapper">
	<div class="col-md-8">
	<?php
	if ( have_posts()):
	while ( have_posts() ): the_post();
	get_template_part('template-parts/content', get_post_format() ); ?>
	<?php endwhile;
	endif;
	bhumi_navigation(); ?>
	</div>
	<?php get_sidebar(); ?>
	</div>
</div>
<?php get_footer(); ?>
