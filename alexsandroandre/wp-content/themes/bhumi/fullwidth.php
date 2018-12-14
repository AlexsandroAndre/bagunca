<?php //Template Name:Full-Width Page
get_header(); 
get_template_part('breadcrumb'); ?>
<div class="container">
	<div class="row bhumi_blog_wrapper">
	<div class="col-md-12">
	<?php get_template_part('post','page'); ?>	
	</div>		
	</div>
</div>	
<?php get_footer(); ?>