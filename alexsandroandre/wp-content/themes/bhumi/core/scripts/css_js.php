<?php
if ( ! function_exists( 'bhumi_fonts_url' ) ) :
/**
 * Register Google fonts for Bhumi.
 *
 * Create your own bhumi_fonts_url() function to override in a child theme.
 *
 *
 */
function bhumi_fonts_url() {
    $fonts_url = '';
    $fonts     = array();

    /* translators: If there are characters in your language that are not supported by Open San, translate this to 'off'. Do not translate into your own language. */
    if ( 'off' !== _x( 'on', 'Open Sans Regular font: on or off', 'bhumi' ) ) {
        $fonts[] = 'Open+Sans';
    }

    /* translators: If there are characters in your language that are not supported by Open San, translate this to 'off'. Do not translate into your own language. */
    if ( 'off' !== _x( 'on', 'Open Sans Bold font: on or off', 'bhumi' ) ) {
        $fonts[] = 'Open+Sans:700';
    }

    /* translators: If there are characters in your language that are not supported by Open San, translate this to 'off'. Do not translate into your own language. */
    if ( 'off' !== _x( 'on', 'Open Sans Semi Bold font: on or off', 'bhumi' ) ) {
        $fonts[] = 'Open+Sans:600';
    }
      /* translators: If there are characters in your language that are not supported by Roboto Regular, translate this to 'off'. Do not translate into your own language. */
    if ( 'off' !== _x( 'on', 'Roboto Regular font: on or off', 'bhumi' ) ) {
        $fonts[] = 'Roboto';
    }

    /* translators: If there are characters in your language that are not supported by Roboto Bold, translate this to 'off'. Do not translate into your own language. */
    if ( 'off' !== _x( 'on', 'Roboto Bold font: on or off', 'bhumi' ) ) {
        $fonts[] = 'Roboto:700';
    }

    /* translators: If there are characters in your language that are not supported by Raleway, translate this to 'off'. Do not translate into your own language. */
    if ( 'off' !== _x( 'on', 'Raleway font: on or off', 'bhumi' ) ) {
        $fonts[] = 'Raleway:600';
    }

    /* translators: If there are characters in your language that are not supported by Courgette, translate this to 'off'. Do not translate into your own language. */
    if ( 'off' !== _x( 'on', 'Courgette font: on or off', 'bhumi' ) ) {
        $fonts[] = 'Courgette';
    }

    if ( $fonts ) {
        $fonts_url = add_query_arg( array(
            'family' => urlencode( implode( '|', $fonts ) ),
        ), 'https://fonts.googleapis.com/css' );
    }

    return esc_url_raw($fonts_url);
}
endif;

function bhumi_scripts()
        {
                wp_enqueue_style( 'bhumi-fonts', bhumi_fonts_url() , array(), null);
                wp_enqueue_style('bhumi_bootstrap', BHUMI_TEMPLATE_DIR_URI .'/assets/css/bootstrap.css');
                wp_enqueue_style('bhumi_default', BHUMI_TEMPLATE_DIR_URI . '/assets/css/default.css');
                wp_enqueue_style('bhumi_theme', BHUMI_TEMPLATE_DIR_URI . '/assets/css/bhumi-theme.css');
                wp_enqueue_style('bhumi_media_responsive', BHUMI_TEMPLATE_DIR_URI . '/assets/css/media-responsive.css');
                wp_enqueue_style('bhumi_animations', BHUMI_TEMPLATE_DIR_URI . '/assets/css/animations.css');
                wp_enqueue_style('bhumi_theme-animtae', BHUMI_TEMPLATE_DIR_URI . '/assets/css/theme-animtae.css');
                wp_enqueue_style('bhumi_font_awesome', BHUMI_TEMPLATE_DIR_URI . '/assets/css/font-awesome-4.3.0/css/font-awesome.css');
                 wp_enqueue_style( 'bhumi_style', get_stylesheet_uri());
                 $cpm_theme_options = bhumi_get_options();
                 $version_wp = get_bloginfo('version');
                 if($version_wp < 4.7){
                    $bhumi_custom_css = $cpm_theme_options['custom_css'];
                }
                else{
                    $bhumi_custom_css = "";
                }
                 $site_bg_color = $cpm_theme_options['site_color'];
                 $custom_css = "

                 .breadcrumb li a{
                        color:$site_bg_color;
                    }
                    .progress-bar {
                        background-color: $site_bg_color;
                    }
                    .btn-search{
                        background:$site_bg_color;
                    }
                    .nav-pills > li  > a{
                        border: 3px solid $site_bg_color;
                        background-image: linear-gradient(to bottom, #fff 50%, $site_bg_color 50%);
                    }
                    .nav-pills > li.active > a, .nav-pills > li.active > a:hover, .nav-pills > li.active > a:focus {
                        background-image: linear-gradient(to bottom, $site_bg_color 50%, $site_bg_color 50%);
                    }
                    .nav-stacked > li  > a
                    {
                        border: 3px solid $site_bg_color;
                        background-image: linear-gradient(to bottom, #fff 50%, $site_bg_color 50%);

                    }
                    .nav-stacked > li.active > a, .nav-stacked > li.active > a:hover, .nav-stacked > li.active > a:focus {
                        background-image: linear-gradient(to bottom, $site_bg_color 50%, $site_bg_color 50%);
                    }
                    .bhumi_panel-blue {
                        border-color: $site_bg_color;
                    }
                    .bhumi_panel-blue > .panel-heading {
                        background-color: $site_bg_color;
                        border-color: $site_bg_color;
                    }
                    a{
                        color: $site_bg_color;
                    }
                    a:hover, a:focus {
                        color: $site_bg_color;
                    }
                    .header_section{
                        background-color:$site_bg_color;
                    }
                    .navbar-default .navbar-nav > .active > a, .navbar-default .navbar-nav > .active > a:hover, .navbar-default .navbar-nav > .active > a:focus,
                    .dropdown-menu .active a {
                        background-color: $site_bg_color;
                    }
                    .collapse ul.nav li.current-menu-parent .dropdown-toggle, .collapse ul.nav li.current-menu-item .dropdown-toggle,
                    .navbar-default .navbar-collapse ul.nav li.current-menu-parent .dropdown-toggle,.navbar-default .navbar-collapse ul.nav li.current-menu-item .dropdown-toggle
                    .collapse ul.nav li.current_page_ancestor .dropdown-toggle,.collapse ul.nav li.current_page_ancestor .dropdown-toggle,
                    .navbar-default .navbar-collapse ul.nav li.current_page_ancestor .dropdown-toggle,.navbar-default .navbar-collapse ul.nav li.current_page_ancestor .dropdown-toggle
                    {
                        background-color: $site_bg_color;
                    }
                    .navbar-default .navbar-nav > .open > a,
                    .navbar-default .navbar-nav > .open > a:hover,
                    .navbar-default .navbar-nav > .open > a:focus {
                        background-color: $site_bg_color;
                    }
                    .navbar-default .navbar-nav > li > a:hover,
                    .navbar-default .navbar-nav > li > a:focus {
                        background-color: $site_bg_color;
                    }
                    .dropdown-menu {
                        background-color: $site_bg_color;
                    }
                    /** headings titles css ***/
                    .bhumi_heading_title h3 {
                        border-bottom: 4px solid $site_bg_color;
                    }
                    .bhumi_heading_title2 h3 {
                        border-bottom: 2px solid $site_bg_color;
                    }
                    .bhumi_home_portfolio_showcase .bhumi_home_portfolio_showcase_icons a {
                        background-color: $site_bg_color;
                    }
                    .bhumi_home_portfolio_showcase .bhumi_home_portfolio_showcase_icons a:hover{
                        color:$site_bg_color;
                    }
                    .bhumi_home_portfolio_caption:hover{
                        background: $site_bg_color;
                    }
                    .img-wrapper:hover .bhumi_home_portfolio_caption{
                        background: $site_bg_color;
                        border-left:1px solid $site_bg_color;
                        border-bottom:1px solid $site_bg_color;
                        border-right:1px solid $site_bg_color;
                    }
                    .bhumi_project_button a {
                        border: 2px solid $site_bg_color;
                    }
                    .bhumi_project_button a:hover {
                        color: $site_bg_color;
                        border: 2px solid $site_bg_color;

                    }
                    .bhumi_carousel-prev, .bhumi_carousel-next {
                        border: 2px solid $site_bg_color;
                    }
                    .bhumi_carousel-prev:hover, .bhumi_carousel-next:hover {
                        background-color: $site_bg_color;
                    }
                    .bhumi_carousel-prev i,
                    .bhumi_carousel-next i {
                        color: $site_bg_color;
                    }
                    .bhumi_portfolio_detail_pagi li a {
                        border:2px solid $site_bg_color;
                        background-image: linear-gradient(to bottom, $site_bg_color 50%, #ffffff 50%);

                    }
                    .bhumi_portfolio_detail_pagi li a:hover {
                        border:2px solid $site_bg_color;
                    }
                    .bhumi-project-detail-sidebar .launch-bhumi-project a {
                        border: 2px solid $site_bg_color;
                        background-image: linear-gradient(to bottom, $site_bg_color 50%, #ffffff 50%);
                    }
                    .bhumi-project-detail-sidebar .launch-bhumi-project a:hover {
                        color: $site_bg_color;
                        border: 2px solid $site_bg_color;
                    }

                    .bhumi_gallery_showcase .bhumi_gallery_showcase_icons a {
                        background-color: $site_bg_color;
                        border:2px solid $site_bg_color;
                    }

                    .bhumi_gallery_showcase .bhumi_gallery_showcase_icons a:hover{
                        border:2px solid $site_bg_color;
                        color:$site_bg_color;
                    }
                    .bhumi_blog_thumb_wrapper h2 a{
                        color:$site_bg_color;
                    }
                    .bhumi_blog_thumb_wrapper h2:hover a{
                        color:$site_bg_color;
                    }
                    .bhumi_blog_thumb_date li i{
                        color: $site_bg_color;
                    }
                    .bhumi_tags a:hover , .bhumi_cats a:hover{
                        color: $site_bg_color;
                        border-color: $site_bg_color;
                    }
                    .bhumi_tags a i, .bhumi_cats a i{
                        color:$site_bg_color;
                    }
                    .bhumi_blog_thumb_wrapper span a i{
                        color: $site_bg_color;
                    }
                    .bhumi_blog_read_btn{
                        border: 1px solid $site_bg_color;
                        background-color:$site_bg_color;
                    }
                    .bhumi_blog_read_btn:hover{
                        color: $site_bg_color;
                    }
                    .bhumi_blog_thumb_wrapper_showcase .bhumi_blog_thumb_wrapper_showcase_icons a {
                        background-color: $site_bg_color;
                    }
                    .bhumi_blog_thumb_wrapper_showcase .bhumi_blog_thumb_wrapper_showcase_icons a:hover{
                        color: $site_bg_color;
                    }
                    .bhumi_post_date {
                        background: $site_bg_color;
                    }
                    .bhumi_blog_comment:hover i, .bhumi_blog_comment:hover h6{
                        color:$site_bg_color;
                    }
                    .bhumi_full_blog_detail_padding h2 a ,.bhumi_full_blog_detail_padding h2{
                        color:$site_bg_color;
                    }
                    .bhumi_full_blog_detail_padding h2  a:hover{
                        color:$site_bg_color;
                    }
                    .bhumi_recent_widget_post h3 a {
                        color:$site_bg_color;
                    }
                    .bhumi_sidebar_link p a:hover,
                    .bhumi_sidebar_widget ul li a:hover {
                        color: $site_bg_color;
                    }
                    .bhumi_widget_tags a , .tagcloud a {
                        background-image: linear-gradient(to bottom, #edf0f2 50%, $site_bg_color 50%);
                    }
                    .bhumi_widget_tags a:hover , .tagcloud a:hover {
                        border: 1px solid $site_bg_color;
                        background-color:$site_bg_color;
                    }
                    .bhumi_author_detail_wrapper{
                        background: $site_bg_color;
                    }
                    .bhumi_author_detail_wrapper{
                        border-left:2px solid $site_bg_color;
                    }
                    .reply a {
                        color: $site_bg_color;
                    }
                    .bhumi_con_input_control:focus, .bhumi_con_textarea_control:focus,
                    .bhumi_contact_input_control:focus, .bhumi_contact_textarea_control:focus {
                      border-color: $site_bg_color;
                      -webkit-box-shadow: inset 0 0px 0px $site_bg_color, 0 0 5px $site_bg_color;
                              box-shadow: inset 0 0px 0px $site_bg_color, 0 0 5px $site_bg_color;
                    }
                    .bhumi_send_button , #bhumi_send_button{
                        border-color: $site_bg_color;
                    }
                    .bhumi_send_button:hover ,#bhumi_send_button:hover{
                        border: 1px solid $site_bg_color;
                        background-color:$site_bg_color;
                    }
                    .bhumi_blog_pagi a:hover,
                    .bhumi_blog_pagi a:focus,
                    .bhumi_blog_pagi a.active{
                        border-color: $site_bg_color;
                        color: $site_bg_color;
                    }
                    .bhumi_testimonial_area i{
                        color:$site_bg_color;
                    }
                    .bhumi_testimonial_area img{
                        border: 10px solid $site_bg_color;
                    }
                    .pager a{
                        background: #fff;
                        border: 2px solid $site_bg_color;
                    }
                    .pager a.selected{
                        background: $site_bg_color;
                        border: 2px solid $site_bg_color;
                    }
                    .bhumi_client_wrapper:hover {
                        border: 4px solid $site_bg_color;

                    }
                    .bhumi_client_prev, .bhumi_client_next {
                        border: 2px solid $site_bg_color;
                    }
                    .bhumi_client_prev:hover, .bhumi_client_next:hover {
                        background-color: $site_bg_color;
                    }
                    .bhumi_carousel-prev i,
                    .bhumi_carousel-next i {
                        color: $site_bg_color;
                    }
                    .bhumi_team_showcase .bhumi_team_showcase_icons a {
                        background-color: $site_bg_color;
                        border:2px solid $site_bg_color;
                    }
                    .bhumi_team_showcase .bhumi_team_showcase_icons a:hover{
                        border:2px solid $site_bg_color;
                        color:$site_bg_color;
                    }

                    .bhumi_team_caption:hover{
                        background: $site_bg_color;
                    }
                    .bhumi_team_wrapper:hover .bhumi_team_caption{
                     background: $site_bg_color;
                    }
                    .bhumi_callout_area {
                        background-color: $site_bg_color;

                    }
                    .bhumi_footer_area{
                        background: $site_bg_color;
                    }
                    .bhumi_contact_info li .desc {
                        color: $site_bg_color;
                    }
                    .bhumi_dropcape_square span {
                        background-color: $site_bg_color;
                    }
                    .bhumi_dropcape_simple span {
                        color: $site_bg_color;
                    }
                    .bhumi_dropcape_circle span {
                        background-color: $site_bg_color;
                    }
                    .breadcrumb li a {
                    color: $site_bg_color !important;
                    }
                    .progress-bar {
                        background-color: $site_bg_color !important;
                    }
                    .btn-search{
                        background:$site_bg_color !important;
                    }
                    .nav-pills > li  > a{
                        border: 3px solid $site_bg_color !important;
                        background-image: linear-gradient(to bottom, #fff 50%, $site_bg_color 50%) !important;
                    }
                    .nav-pills > li.active > a, .nav-pills > li.active > a:hover, .nav-pills > li.active > a:focus {
                        background-image: linear-gradient(to bottom, $site_bg_color 50%, $site_bg_color 50%) !important;
                        background-color:$site_bg_color;
                    }
                    .nav-stacked > li  > a
                    {
                        border: 3px solid $site_bg_color !important;
                        background-image: linear-gradient(to bottom, #fff 50%, $site_bg_color 50%) !important;
                     }
                     .nav-stacked > li.active > a, .nav-stacked > li.active > a:hover, .nav-stacked > li.active > a:focus {
                        background-image: linear-gradient(to bottom, $site_bg_color 50%, $site_bg_color 50%) !important;
                        background-color:$site_bg_color;
                    }

                    .navbar-toggle {
                    background-color: $site_bg_color !important;
                    border: 1px solid $site_bg_color !important;
                    }

                    .navbar-default .navbar-toggle:hover, .navbar-default .navbar-toggle:focus {
                    background-color: $site_bg_color !important;
                    }
                    .bhumi_blockquote_section blockquote {
                    border-left: 5px solid $site_bg_color ;
                    }
                    #wp-calendar caption {
                    background-color: $site_bg_color;
                    }
                    .carousel-list li {
                        background: $site_bg_color;
                        opacity: 0.8;
                    }
                    $bhumi_custom_css;
                        ";
                wp_add_inline_style( 'bhumi_style', $custom_css );

                // Js
                wp_enqueue_script('bhumi_menu', BHUMI_TEMPLATE_DIR_URI .'/assets/js/menu.js', array('jquery'));
                wp_enqueue_script('bhumi_bootstrap_script', BHUMI_TEMPLATE_DIR_URI .'/assets/js/bootstrap.js');
                wp_enqueue_script('bhumi_theme_script', BHUMI_TEMPLATE_DIR_URI .'/assets/js/bhumi_theme_script.js');
                if(is_front_page()){
                        //Footer JS//
                        wp_enqueue_script('bhumi_footer_script', BHUMI_TEMPLATE_DIR_URI .'/assets/js/bhumi-footer-script.js','','',true);
                        wp_enqueue_script('bhumi_waypoints', BHUMI_TEMPLATE_DIR_URI .'/assets/js/waypoints.js','','',true);
                        wp_enqueue_script('bhumi_scroll', BHUMI_TEMPLATE_DIR_URI .'/assets/js/scroll.js','','',true);
                }
                if ( is_singular() ) wp_enqueue_script( "comment-reply" );

        }
        add_action('wp_enqueue_scripts', 'bhumi_scripts');
