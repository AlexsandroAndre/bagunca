<div class="col-md-4 bhumi-sidebar">
	<?php if ( is_active_sidebar( 'sidebar-primary' ) )
	{ dynamic_sidebar( 'sidebar-primary' );	}
	else  { 
	$args = array(
	'before_widget' => '<div class="bhumi_sidebar_widget">',
	'after_widget'  => '</div>',
	'before_title'  => '<div class="bhumi_sidebar_widget_title"><h2>',
	'after_title'   => '</h2></div>' );
	the_widget('WP_Widget_Archives', null, $args);
	} ?>
</div>