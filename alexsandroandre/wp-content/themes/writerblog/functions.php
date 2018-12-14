<?php

# Enqueue Scripts & Styles
if( !function_exists( 'writerblog_enqueue_scripts' ) ) {
    function writerblog_enqueue_scripts() {

        wp_dequeue_script('amadeus-body-fonts');
        wp_dequeue_script('amadeus-headings-fonts');

        if ( get_theme_mod('body_font_name') !='' ) {
            wp_enqueue_style( 'writerblog-body-fonts', '//fonts.googleapis.com/css?family=' . esc_attr( get_theme_mod('body_font_name' ) ) );
        } else {
            wp_enqueue_style( 'writerblog-body-fonts', '//fonts.googleapis.com/css?family=Lato:400,300,700');
        }

        if ( get_theme_mod('headings_font_name') !='' ) {
            wp_enqueue_style( 'writerblog-headings-fonts', '//fonts.googleapis.com/css?family=' . esc_attr(get_theme_mod('headings_font_name')) );
        }

        wp_enqueue_style( 'writerblog-stylesheet', trailingslashit( get_template_directory_uri() ) . 'style.css',false );

        $primary_color = get_theme_mod( 'primary_color', '#ff7f66' );

        $custom_css = '';
        $custom_css .= "a, a:hover, .main-navigation a:hover, .nav-next a:hover, .nav-previous a:hover, .social-navigation li a:hover, .widget a:hover, .entry-title a:hover { color:" . esc_attr($primary_color) . "}"."\n";
        $custom_css .= "button, .button, input[type=\"button\"], input[type=\"reset\"], input[type=\"submit\"], .entry-thumb-inner { background-color:" . esc_attr($primary_color) . "}"."\n";

        $body_fonts = get_theme_mod('body_font_family');
        $headings_fonts = get_theme_mod('headings_font_family');

        if ( $body_fonts !='' ) {
            $custom_css .= "body { font-family:" . esc_attr($body_fonts) . ";}"."\n";
        }

        if ( $headings_fonts !='' ) {
            $custom_css .= "h1, h2, h3, h4, h5, h6 { font-family:" . esc_attr($headings_fonts) . ";}"."\n";
        }

        wp_add_inline_style( 'writerblog-stylesheet', $custom_css );

    } // function writerblog_enqueue_scripts end

    add_action( 'wp_enqueue_scripts', 'writerblog_enqueue_scripts' );

} // function exists (writerblog_enqueue_scripts) check


# writerblog: Numeric Page Navigation
if( !function_exists( 'writerblog_numeric_posts_nav' ) ) {
    function writerblog_numeric_posts_nav() {

        if( is_singular() )
            return;

        global $wp_query;

        /** Stop execution if there's only 1 page */
        if( $wp_query->max_num_pages <= 1 )
            return;

        $paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
        $max   = intval( $wp_query->max_num_pages );

        /** Add current page to the array */
        if ( $paged >= 1 )
            $links[] = $paged;

        /** Add the pages around the current page to the array */
        if ( $paged >= 3 ) {
            $links[] = $paged - 1;
            $links[] = $paged - 2;
        }

        if ( ( $paged + 2 ) <= $max ) {
            $links[] = $paged + 2;
            $links[] = $paged + 1;
        }

        echo '<div class="navigation posts-navigation"><ul>' . "\n";

        /** Previous Post Link */
        if ( get_previous_posts_link() )
            printf( '<li>%s</li>' . "\n", get_previous_posts_link('&lt;') );

        /** Link to first page, plus ellipses if necessary */
        if ( ! in_array( 1, $links ) ) {
            $class = 1 == $paged ? ' class="active"' : '';

            printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( 1 ) ), '1' );

            if ( ! in_array( 2, $links ) )
                echo '<li>…</li>';
        }

        /** Link to current page, plus 2 pages in either direction if necessary */
        sort( $links );
        foreach ( (array) $links as $link ) {
            $class = $paged == $link ? ' class="active"' : '';
            printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $link ) ), $link );
        }

        /** Link to last page, plus ellipses if necessary */
        if ( ! in_array( $max, $links ) ) {
            if ( ! in_array( $max - 1, $links ) )
                echo '<li>…</li>' . "\n";

            $class = $paged == $max ? ' class="active"' : '';
            printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $max ) ), $max );
        }

        /** Next Post Link */
        if ( get_next_posts_link() )
            printf( '<li>%s</li>' . "\n", get_next_posts_link('&gt;') );

        echo '</ul></div>' . "\n";
    }
}

# Change default customizer values
if( !function_exists( 'writerblog_customize_register' ) ) {
    function writerblog_customize_register( $wp_customize ) {

        # Customizer remove panels
        $wp_customize->remove_section( 'amadeus_menu' );

        # Header Area: Animated down arrow
        $wp_customize->remove_setting( 'hide_scroll' );        
        $wp_customize->remove_control( 'hide_scroll' );

        # Header Area: Site branding padding
        $wp_customize->remove_setting( 'branding_padding' );
        $wp_customize->remove_control( 'branding_padding' );

        # Font Styling
    	$wp_customize->get_setting( 'body_font_name' )->default = esc_html__('Lato:400,300,700', 'writerblog');
    	$wp_customize->get_setting( 'body_font_family' )->default = esc_html__('Lato', 'writerblog');
    	$wp_customize->get_setting( 'headings_font_name' )->default = esc_html__('Lato:400,300,700', 'writerblog');
    	$wp_customize->get_setting( 'headings_font_family' )->default = esc_html__('Lato', 'writerblog');
        $wp_customize->get_setting( 'h1_size' )->default = 34;
        $wp_customize->get_setting( 'body_size' )->default = 15;

        # Hide the sidebar on single blog posts by default
        $wp_customize->get_setting( 'hide_sidebar_single')->default = 1;
        $wp_customize->get_setting( 'hide_banner')->default = 1;

        # Remove custom favicon from parent theme
        $wp_customize->remove_section( 'amadeus_general' );

        # Fixed small space missing from string in parent theme
        $wp_customize->get_control( 'logo_style' )->choices = array(
            'hide-title'  => __( 'Only logo', 'amadeus' ),
            'show-title'  => __( 'Logo plus site title &amp; description', 'amadeus' ),
        );

        # Remove custom logo from parent theme ( replaced with Wordpress default logo option)
        $wp_customize->remove_setting( 'site_logo' );
        $wp_customize->remove_control( 'site_logo' );
    }
    add_action( 'customize_register', 'writerblog_customize_register', 1000 );
}


# Customize the Entry Footer
# Adds Author Area
function writerblog_entry_footer() {

    if ( 'post' == get_post_type() && !get_theme_mod('meta_singles') ) {

    	/* translators: used between list items, there is a space after the comma */
    	$tags_list = get_the_tag_list( '', __( ', ', 'writerblog' ) );
    	if ( $tags_list ) {
    		printf( '<span class="tags-links">' . __( 'Tagged %1$s', 'writerblog' ) . '</span>', $tags_list );
    	}
    }

    # Check if we have an author description
    if( get_the_author_meta( 'description' ) ) {

        echo '<div class="row amadeus-child-author-entry-footer">';

            # Author Avatar
            echo '<div class="amadeus-child-author-avatar col-xs-2">';
                echo get_avatar( get_the_author_meta( 'ID' ), 110 );
            echo '</div>';

            # Author description
            echo '<div class="amadeus-child-author-description col-xs-10">';

                # Author title
                #  &#124; -> | ( vertical pipe )
                echo '<div class="amadeus-child-author-title">';
                    echo '<span>'. __('Author', 'writerblog') .'</span>';
                    echo ' &#124; ';
                    echo '<a href="' . get_author_posts_url( get_the_author_meta( 'ID' ) ). '">' . get_the_author() . '</a>';
                echo '</div>';

                echo '<div class="amadeus-child-author-bio">';
                    echo esc_html( get_the_author_meta('description') );
                echo '</div>';

            echo '</div>';

        echo '</div>';

    }
}

// Remove Formulário de Comentários no Front-end
function cwp_desativa_comentarios_frontend() {
return false;
}
add_filter('comments_open', 'cwp_desativa_comentarios_frontend', 20, 2);
add_filter('pings_open', 'cwp_desativa_comentarios_frontend', 20, 2);


# Replaces the excerpt "more" text by a link
if( !function_exists( 'writerblog_excerpt_more' ) ) {

    function writerblog_excerpt_more( $more ) {
           global $post;

           # Start building the return string
           $return = '<div class="moretag">';
            $return .= '<br />';
                $return .= '<a href="' . get_permalink( $post->ID ) . '" title="' . get_the_title() . '">';
                    $return .= __('Read more', 'writerblog');
                    $return .= '&nbsp;&rarr;&nbsp;';
                $return .= '</a>';
           $return .= '</div>';

        # Actually return it
     	return $return;
    }
    add_filter('excerpt_more', 'writerblog_excerpt_more');
}