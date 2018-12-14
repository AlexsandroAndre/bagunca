<?php
/** Theme Name	: Bhumi Lite
* 	Theme Core Functions and Codes
*/
	/**Includes required resources here**/
	define('BHUMI_TEMPLATE_DIR_URI', get_template_directory_uri());
	define('BHUMI_TEMPLATE_DIR', get_template_directory());
	define('BHUMI_TEMPLATE_DIR_CORE' , BHUMI_TEMPLATE_DIR . '/core');
	define( 'BHUMI_TEMPLATE_DIR_ASSETS' , BHUMI_TEMPLATE_DIR . '/assets' );
	require( BHUMI_TEMPLATE_DIR_CORE . '/menu/default_menu_walker.php' );
	require( BHUMI_TEMPLATE_DIR_CORE . '/menu/bhumi_nav_walker.php' );
	require( BHUMI_TEMPLATE_DIR_CORE . '/scripts/css_js.php' ); //Enquiring Resources here
	require( BHUMI_TEMPLATE_DIR_CORE . '/comment-function.php' );
	require( BHUMI_TEMPLATE_DIR_ASSETS . '/inc/customizer.php');
	require( BHUMI_TEMPLATE_DIR_ASSETS . '/inc/customizer-controls.php' );

	//Sane Defaults
	function bhumi_default_settings() {
		$cpm_theme_options=array(
				'site_color'						 =>'#A13F40',
				'breadcrumb_show'					 =>'',
				'upload_image_logo'                  =>'',
				'height'                             =>'55',
				'width'                              =>'150',
				'custom_css'                         =>'',
				'slider_category'                    => 'default',
				'slider_number'						 => 5,
				'service_category'                   => 'default',
				'portfolio_category'                 => 'default',

				// Footer Call-Out
				'fc_home'                            =>'1',
				'fc_title'                           => '',
				'fc_btn_txt'                         => '',
				'fc_btn_link'                        =>'',
				'fc_radio'                           =>'bottom',
				//Social media links
				'header_social_media_in_enabled'     =>'1',
				'footer_section_social_media_enbled' =>'1',
				'twitter_link'                       =>"",
				'fb_link'                            =>"",
				'linkedin_link'                      =>"",
				'youtube_link'                       =>"",
				'instagram'                          =>"",
				'gplus'                              =>"",
				'skype'								 =>"",

				'email_id'                           => '',
				'phone_no'                           => '',
				'home_service_heading'               => '',

				'enable_pre_footer'						=> 1,


				//Portfolio Settings:
				'portfolio_home'                     =>'1',
				'port_heading'                       => '',

				//BLOG Settings
				'show_blog'                          => '1',
				'show_blog_meta'                     => 1,
				'blog_title'                         => '',
				'blog_show_posts'					 => '',
				'bhumi_latest_show_posts'		     => 'all_posts',
				'blog_category'						 => '',
				'blog_excerpt_length'				=> 40,
				'blog_read_more'					=> __( 'Read More', 'bhumi' )

			);
			return apply_filters( 'bhumi_options', $cpm_theme_options );
    }

	function bhumi_get_options() {
	    // Options API
	    return wp_parse_args(
	        get_option( 'bhumi_options', array() ),
	        bhumi_default_settings()
	    );
	}


	/*After Theme Setup*/
	add_action( 'after_setup_theme', 'bhumi_head_setup' );
	function bhumi_head_setup()
	{
		global $content_width;
		//content width
		if ( ! isset( $content_width ) ) $content_width = 550; //px

	    //Blog Thumb Image Sizes
		add_image_size('bhumi_home_post_thumb',340,210,true);
		//Blogs thumbs
		add_image_size('bhumi_cpm_page_thumb',730,350,true);
		add_image_size('bhumi_blog_2c_thumb',570,350,true);
		add_image_size( 'bhumi_portfolio',524,364,array( 'center', 'center' ) );
		add_theme_support( 'title-tag' );
		// Load text domain for translation-ready
		load_theme_textdomain( 'bhumi' . get_template_directory() . '/core/lang');

		register_nav_menu( 'primary', __( 'Primary Menu', 'bhumi' ) );
		// theme support
		add_theme_support( 'post-thumbnails' ); //supports featured image
		$args = array('default-color' => '#ffffff',);
		add_theme_support( 'custom-background', $args);
		add_theme_support( 'automatic-feed-links');
		add_theme_support( 'post-formats', array(
			'aside', 'image', 'video', 'quote', 'link', 'gallery', 'status', 'audio', 'chat'
			) );
		add_theme_support( 'custom-logo', array(
				'height'      => 80,
				'width'       => 200,
				'flex-height' => true,
				'flex-width'  => true,
			) );

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );
	}

	/**
	 * Registers an editor stylesheet for the theme.
	 */
	if ( ! function_exists( 'bhumi_theme_add_editor_styles' ) ) :
		function bhumi_theme_add_editor_styles() {
		    add_editor_style( 'assets/css/editor-style.css' );
		}
		add_action( 'admin_init', 'bhumi_theme_add_editor_styles' );
	endif;


	// Read more tag to formatting in blog page
	function bhumi_content_more($more)
	{
	   return '<div class="blog-post-details-item"><a class="bhumi_blog_read_btn" href="'.esc_url(get_permalink()).'">'.__('Read More', 'bhumi' ).'</a></div>';
	}
	add_filter( 'the_content_more_link', 'bhumi_content_more' );


	// Replaces the excerpt "more" text by a link
	function bhumi_excerpt_more($more) {
	return esc_html__('&hellip;','bhumi');
	}
	add_filter('excerpt_more', 'bhumi_excerpt_more');
	/*
	* bhumi widget area
	*/
	add_action( 'widgets_init', 'bhumi_widgets_init');
	function bhumi_widgets_init() {
	/*sidebar*/
	register_sidebar( array(
			'name' => __( 'Sidebar', 'bhumi' ),
			'id' => 'sidebar-primary',
			'description' => __( 'The primary widget area', 'bhumi' ),
			'before_widget' => '<div class="bhumi_sidebar_widget">',
			'after_widget' => '</div>',
			'before_title' => '<div class="bhumi_sidebar_widget_title"><h2>',
			'after_title' => '</h2></div>'
		) );

	register_sidebar( array(
			'name' => __( 'Footer Widget Area', 'bhumi' ),
			'id' => 'footer-widget-area',
			'description' => __( 'Footer widget area', 'bhumi' ),
			'before_widget' => '<div class="col-md-3 col-sm-6 bhumi_footer_widget_column">',
			'after_widget' => '</div>',
			'before_title' => '<div class="bhumi_footer_widget_title">',
			'after_title' => '<div class="bhumi-footer-separator"></div></div>',
		) );
	}

	/* Breadcrumbs  */
	function bhumi_breadcrumbs() {
		    $delimiter = '';
		    $home = __('Home', 'bhumi' ); // text for the 'Home' link
		    $before = '<li>'; // tag before the current crumb
		    $after = '</li>'; // tag after the current crumb
		    echo '<ul class="breadcrumb">';
		    global $post;
		    $homeLink = home_url();
		    echo '<li><a href="' . esc_url($homeLink) . '">' . $home . '</a></li>' . $delimiter . ' ';
		    if (is_category()) {
		        global $wp_query;
		        $cat_obj = $wp_query->get_queried_object();
		        $thisCat = $cat_obj->term_id;
		        $thisCat = get_category($thisCat);
		        $parentCat = get_category($thisCat->parent);
		        if ($thisCat->parent != 0)
		            echo(get_category_parents($parentCat, TRUE, ' ' . $delimiter . ' '));
		        echo $before . ' esc_html__("Archive by category","bhumi") "' . single_cat_title('', false) . '"' . $after;
		    } elseif (is_day()) {
		        echo '<li><a href="' . esc_url(get_year_link(get_the_time('Y'))) . '">' . get_the_time('Y') . '</a></li> ' . $delimiter . ' ';
		        echo '<li><a href="' . esc_url(get_month_link(get_the_time('Y'), get_the_time('m'))) . '">' . get_the_time('F') . '</a></li> ' . $delimiter . ' ';
		        echo $before . get_the_time('d') . $after;
		    } elseif (is_month()) {
		        echo '<li><a href="' . esc_url(get_year_link(get_the_time('Y'))) . '">' . get_the_time('Y') . '</a></li> ' . $delimiter . ' ';
		        echo $before . get_the_time('F') . $after;
		    } elseif (is_year()) {
		        echo $before . get_the_time('Y') . $after;
		    } elseif (is_single() && !is_attachment()) {
		        if (get_post_type() != 'post') {
		            $post_type = get_post_type_object(get_post_type());
		            $bhumi_post_type_archive = get_post_type_archive_link($post_type->name);
		            if(!empty($bhumi_post_type_archive)):
		            	echo '<li><a href="' . esc_url($bhumi_post_type_archive). '/">' . $post_type->labels->singular_name . '</a></li> ' . $delimiter . ' ';
		        	endif;
		            echo $before . get_the_title() . $after;
		        } else {
		            $cat = get_the_category();
		            $cat = $cat[0];
		            echo $before . get_the_title() . $after;
		        }

		    } elseif (!is_single() && !is_page() && get_post_type() != 'post') {
		        $post_type = get_post_type_object(get_post_type());
		        echo $before . $post_type->labels->singular_name . $after;
		    } elseif (is_attachment()) {
		        $parent = get_post($post->post_parent);
		        $cat = get_the_category($parent->ID);
		        $cat = $cat[0];
		        echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
		        echo '<li><a href="' . esc_url(get_permalink($parent)) . '">' . $parent->post_title . '</a></li> ' . $delimiter . ' ';
		        echo $before . get_the_title() . $after;
		    } elseif (is_page() && !$post->post_parent) {
		        echo $before . get_the_title() . $after;
		    } elseif (is_page() && $post->post_parent) {
		        $parent_id = $post->post_parent;
		        $breadcrumbs = array();
		        while ($parent_id) {
		            $page = get_page($parent_id);
		            $breadcrumbs[] = '<li><a href="' . esc_url(get_permalink($page->ID)) . '">' . get_the_title($page->ID) . '</a></li>';
		            $parent_id = $page->post_parent;
		        }
		        $breadcrumbs = array_reverse($breadcrumbs);
		        foreach ($breadcrumbs as $crumb)
		            echo $crumb . ' ' . $delimiter . ' ';
		        echo $before . get_the_title() . $after;
		    } elseif (is_search()) {
		        echo $before . esc_html__("Search results for","bhumi")  . get_search_query() . '"' . $after;

		    } elseif (is_tag()) {
				echo $before . esc_html__('Tag','bhumi') . single_tag_title(' ', false) . $after;
		    } elseif (is_author()) {
		        global $author;
		        $userdata = get_userdata($author);
		        echo $before . esc_html__("Articles posted by","bhumi") . $userdata->display_name . $after;
		    } elseif (is_404()) {
		        echo $before . esc_html__("Error 404","bhumi") . $after;
		    }

		    echo '</ul>';
	}

	/*===================================================================================
	* Add Class Gravtar
	* =================================================================================*/
	add_filter('get_avatar','bhumi_gravatar_class');

	function bhumi_gravatar_class($class) {
	    $class = str_replace("class='avatar", "class='author_detail_img", $class);
	    return $class;
	}
	/****--- Navigation for Author, Category , Tag , Archive ---***/
	function bhumi_navigation() { ?>
		<div class="bhumi_blog_pagination">
		<div class="bhumi_blog_pagi">
		<?php //posts_nav_link(); ?>
			<div class="navigation">
				<div class="alignleft"><?php previous_posts_link( '&laquo; Previous Entries' ); ?></div>
				<div class="alignright"><?php next_posts_link( 'Next Entries &raquo;', '' ); ?></div>
			</div>
		</div>
		</div>
	<?php }

	/****--- Navigation for Single ---***/
	function bhumi_navigation_posts() { ?>
		<div class="navigation_en">
			<nav id="cpm_nav">
				<span class="nav-previous">
					<?php previous_post_link('&laquo; %link'); ?>
				</span>
				<span class="nav-next">
					<?php next_post_link('%link &raquo;'); ?>
				</span>
			</nav>
		</div>
    <?php }

/*class for blog's next button*/
add_filter('next_posts_link_attributes', 'bhumi_posts_link_attributes');
function bhumi_posts_link_attributes() {
    return 'class="next"';
}

/*class for blog's previous button*/
add_filter('previous_posts_link_attributes', 'bhumi_post_link_previous');
function bhumi_post_link_previous() {
    return 'class="previous"';
}

if ( ! function_exists( 'bhumi_the_featured_video' ) ) {
    function bhumi_the_featured_video( $content ) {
        $ori_url = explode( "\n", $content );
        $url = $ori_url[0];
        $w = get_option( 'embed_size_w' );
        if ( !is_single() )
            $url = str_replace( '448', $w, $url );

        if ( 0 === strpos( $url, 'https://' ) ) {

            echo apply_filters( 'bhumi_lite_video_content', $url );
            $content = trim( str_replace( $url, '', $content ) );
        }
        elseif ( preg_match ( '#^<(script|iframe|embed|object)#i', $url ) ) {
            $h = get_option( 'embed_size_h' );
            echo $url;
            if ( !empty( $h ) ) {

                if ( $w === $h ) $h = ceil( $w * 0.75 );
                $url = preg_replace(
                    array( '#height="[0-9]+?"#i', '#height=[0-9]+?#i' ),
                    array( sprintf( 'height="%d"', $h ), sprintf( 'height=%d', $h ) ),
                    $url
                    );
                echo $url;
            }

            $content = trim( str_replace( $url, '', $content ) );

        }
    }
}

function bhumi_excerpt_length( $length ) {
	$cpm_theme_options = bhumi_get_options();
	$blog_excerpt_length = absint($cpm_theme_options['blog_excerpt_length']);
	return $blog_excerpt_length;
}
add_filter( 'excerpt_length', 'bhumi_excerpt_length', 999 );

if ( function_exists( 'wp_update_custom_css_post' ) ) {
	$cpm_theme_options = bhumi_get_options();
	$custom_css = $cpm_theme_options['custom_css'];
    $core_css = wp_get_custom_css();
    if ( !empty($custom_css) && empty($core_css) ) {
        $return = wp_update_custom_css_post( $core_css . $custom_css );
    }
}