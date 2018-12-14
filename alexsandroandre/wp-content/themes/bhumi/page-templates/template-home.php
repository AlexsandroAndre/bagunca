<?php
/**
*
* Template Name: Home
*
**/

get_header();
$cpm_theme_options = bhumi_get_options();


    get_template_part('frontpage/home','slideshow');
    get_template_part('frontpage/home','services');
    $cpm_theme_options = bhumi_get_options();
    if($cpm_theme_options['fc_home'] == "1" && $cpm_theme_options['fc_radio'] == 'top') {
       get_template_part('frontpage/footer','callout');
    }
    if($cpm_theme_options['portfolio_home'] == "1") {
       get_template_part('frontpage/home','portfolio');
    }
    if($cpm_theme_options['fc_home'] == "1" && $cpm_theme_options['fc_radio'] == 'middle') {
       get_template_part('frontpage/footer','callout');
    }
    if($cpm_theme_options['show_blog'] == "1") {
       get_template_part('frontpage/home','blog');
    }
    if($cpm_theme_options['fc_home'] == "1" && $cpm_theme_options['fc_radio'] == 'bottom') {
       get_template_part('frontpage/footer','callout');
    }
    get_footer();