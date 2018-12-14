<?php get_header();
get_template_part('breadcrumb'); ?>
<div class="container">
	<div class="row bhumi_blog_wrapper">
	<div class="col-md-8">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<?php
		$format = get_post_format();
		if ( false === $format ) {
			$format = 'standard';
		}
		get_template_part( 'template-parts/content', $format );
	     // get_template_part('post','content');
		get_template_part('author','intro');
		endwhile;
		else :
		get_template_part('nocontent');
		endif;
		bhumi_navigation_posts();
		comments_template( '', true ); ?>
	</div>
	<?php get_sidebar(); ?>
	</div> <!-- row div end here -->
</div><!-- container div end here -->
<?php get_footer(); ?>