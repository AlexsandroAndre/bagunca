<?php get_header(); 
get_template_part('breadcrumb'); ?>
<div class="container">
	<div class="row bhumi_blog_wrapper">
	<div class="col-md-8">
	<?php woocommerce_content(); ?>
	</div>
	<?php get_sidebar(); ?>	
	</div>
</div>	
<?php get_footer(); ?>