<?php get_header(); ?>

<div class="bhumi_header_breadcrumb_title">
	<div class="container">
		<div class="row">
		<?php if(have_posts()) :?>
			<div class="col-md-12">
			<h1><?php printf( __( 'Search Results for: %s', 'bhumi' ), '<span>' . get_search_query() . '</span>'  ); ?>
			</h1>
			</div>
		<?php endif; ?>
		<?php rewind_posts(); ?>
		</div>
	</div>
</div>

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
				get_template_part('template-parts/content', get_post_format() ); ?>
				<?php endwhile;
				bhumi_navigation();
			else :
				get_template_part('nocontent');
			endif; ?>
		</div>

			<?php get_sidebar(); ?>

	</div>
</div>

<?php get_footer(); ?>