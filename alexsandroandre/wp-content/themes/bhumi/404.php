<?php get_header(); ?>
<div class="bhumi_header_breadcrumb_title">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<h1><?php esc_html_e('404 Error','bhumi'); ?></h1>
				<ul class="breadcrumb">
					<li><a href="<?php echo esc_url(home_url( '/' )); ?>"><?php _e('Home','bhumi'); ?></a></li>
					<li><?php esc_html_e('404 Error','bhumi'); ?></li>

				</ul>
			</div>
		</div>
	</div>
</div>

<div class="container">
	<div class="row bhumi_blog_wrapper">

			<div class="col-md-8 hc_404_error_section">
				<div class="error_404">
					<h2><?php esc_html_e('404','bhumi'); ?></h2>
					<h4><?php esc_html_e('Whoops... Page Not Found !!!','bhumi'); ?></h4>
					<p><?php esc_html_e('We\'re sorry, but the page you are looking for doesn\'t exist. Maybe try a search?','bhumi'); ?></p>
				</div>
			</div>
		<div class="col-md-4 bhumi-sidebar">
			<div class="bhumi_sidebar_widget">
				<p><a href="<?php echo esc_url(home_url( '/' )); ?>" class="bhumi_send_button"><?php esc_html_e('Go To Homepage','bhumi'); ?></a></p>
			</div>
			<div class="bhumi_sidebar_widget">
				<?php get_search_form(); ?>
			</div>

		</div>

	</div>
</div>

<?php get_footer(); ?>