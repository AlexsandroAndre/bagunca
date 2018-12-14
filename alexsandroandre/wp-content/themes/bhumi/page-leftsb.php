<?php //Template Name:Page With Left Sidebar
get_header(); 
get_template_part('breadcrumb'); ?>
<div class="container">
	<div class="row bhumi_blog_wrapper">
	<?php get_sidebar(); ?>	
	<div class="col-md-8">
	<?php get_template_part('post','page'); ?>	
	</div>	
	</div>
</div>	
<?php get_footer(); ?>