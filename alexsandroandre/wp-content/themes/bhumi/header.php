<!DOCTYPE html>
<!--[if lt IE 7]>
    <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
    <!--[if IE 7]>
    <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
    <!--[if IE 8]>
    <html class="no-js lt-ie9"> <![endif]-->
    <!--[if gt IE 8]><!-->
<html <?php language_attributes(); ?>><!--<![endif]-->
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="<?php bloginfo('charset'); ?>" />
    <?php $cpm_theme_options = bhumi_get_options(); ?>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div>
    <!-- Header Section -->
    <div class="header_section" >
        <div class="container" >
            <!-- Logo & Contact Info -->
            <div class="row ">
                <div class="col-md-6 col-sm-12 cpm_rtl" >
                    <div claSS="logo">
                    <a href="<?php echo esc_url(home_url( '/' )); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
                        <?php
                            $version_wp = get_bloginfo('version');
                            $theme_option_logo = $cpm_theme_options['upload_image_logo'];
                            if($version_wp < 4.5){
                                if(!empty($theme_option_logo)): ?>
                                    <img class="img-responsive" src="<?php echo $cpm_theme_options['upload_image_logo']; ?>" style="height:<?php if($cpm_theme_options['height']!='') { echo $cpm_theme_options['height']; }  else { "80"; } ?>px; width:<?php if($cpm_theme_options['width']!='') { echo $cpm_theme_options['width']; }  else { "200"; } ?>px;" />
                                <?php
                                else:
                                    echo get_bloginfo('name'); ?>
                                <?php endif;
                            }
                            else{
                                if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
                                    the_custom_logo();
                                }
                                elseif( function_exists('the_custom_logo') && !empty($theme_option_logo)){
                                    $previous_logo = attachment_url_to_postid($theme_option_logo);
                                    if ( is_int( $previous_logo ) ) {
                                        set_theme_mod( 'custom_logo', $previous_logo );
                                        $customizer_values = get_option( 'bhumi_options');
                                        unset($customizer_values['upload_image_logo']);
                                        update_option( 'bhumi_options', $customizer_values );
                                     }
                                }
                                else{
                                    echo get_bloginfo('name' );
                                }
                            }
                            ?>
                    </a>
                    <p class="site-description"><?php bloginfo( 'description' ); ?></p>
                    </div>
                </div>
                <?php if($cpm_theme_options['header_social_media_in_enabled']=='1') { ?>
                <div class="col-md-6 col-sm-12">
                <?php if($cpm_theme_options['email_id'] || $cpm_theme_options['phone_no'] !='') { ?>
                <ul class="head-contact-info">
                        <?php if($cpm_theme_options['email_id'] !='') { ?><li><i class="fa fa-envelope"></i><a href="mailto:<?php echo $cpm_theme_options['email_id']; ?>"><?php echo esc_html($cpm_theme_options['email_id']); ?></a></li><?php } ?>
                        <?php if($cpm_theme_options['phone_no'] !='') { ?><li><i class="fa fa-phone"></i><a href="tel:<?php echo $cpm_theme_options['phone_no']; ?>"><?php echo esc_html($cpm_theme_options['phone_no']); ?></a></li><?php } ?>
                </ul>
                <?php } ?>
                    <ul class="social">
                    <?php if($cpm_theme_options['fb_link']!='') { ?>
                       <li class="facebook" data-toggle="tooltip" data-placement="bottom" title="<?php echo esc_html__('Facebook','bhumi'); ?>"><a  href="<?php echo esc_url($cpm_theme_options['fb_link']); ?>"><i class="fa fa-facebook"></i></a></li>
                    <?php } if($cpm_theme_options['twitter_link']!='') { ?>
                        <li class="twitter" data-toggle="tooltip" data-placement="bottom" title="<?php echo esc_html('Twitter','bhumi'); ?>"><a href="<?php echo esc_url($cpm_theme_options['twitter_link']); ?>"><i class="fa fa-twitter"></i></a></li>
                    <?php } if($cpm_theme_options['linkedin_link']!='') { ?>
                        <li class="linkedin" data-toggle="tooltip" data-placement="bottom" title="<?php echo esc_html('LinkedIn','bhumi')?>"><a href="<?php echo esc_url($cpm_theme_options['linkedin_link']); ?>"><i class="fa fa-linkedin"></i></a></li>
                    <?php } if($cpm_theme_options['youtube_link']!='') { ?>
                        <li class="youtube" data-toggle="tooltip" data-placement="bottom" title="<?php echo esc_html('Youtube','bhumi'); ?>"><a href="<?php echo esc_url($cpm_theme_options['youtube_link']) ; ?>"><i class="fa fa-youtube"></i></a></li>
                    <?php } if($cpm_theme_options['gplus']!='') { ?>
                        <li class="gplus" data-toggle="tooltip" data-placement="bottom" title="<?php echo esc_html('Google-plus','bhumi')?>"><a href="<?php echo esc_url($cpm_theme_options['gplus']) ; ?>"><i class="fa fa-google-plus"></i></a></li>
                    <?php } if($cpm_theme_options['instagram']!='') { ?>
                        <li class="facebook" data-toggle="tooltip" data-placement="bottom" title="<?php echo esc_html('Instagram','bhumi')?>"><a href="<?php echo esc_url($cpm_theme_options['instagram']) ; ?>"><i class="fa fa-instagram"></i></a></li>
                    <?php }
                    if($cpm_theme_options['skype']!='') { ?>
                        <li class="facebook" data-toggle="tooltip" data-placement="bottom" title="<?php echo esc_html('Skype','bhumi')?>"><a href="<?php echo esc_url($cpm_theme_options['skype']) ; ?>"><i class="fa fa-skype"></i></a></li>
                    <?php } ?>
                    </ul>
                </div>
                <?php } ?>
            </div>
            <!-- /Logo & Contact Info -->
        </div>
    </div>
    <!-- /Header Section -->
    <!-- Navigation  menus -->
    <div class="navigation_menu "  data-spy="affix" data-offset-top="95" id="bhumi_nav_top">
        <span id="header_shadow"></span>
        <div class="container navbar-container" >
            <nav class="navbar navbar-default " role="navigation">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#menu">

                      <span class="sr-only"><?php _e('Toggle navigation','bhumi');?></span>
                      <span class="icon-bar"></span>
                      <span class="icon-bar"></span>
                      <span class="icon-bar"></span>
                    </button>
                </div>
                <div id="menu" class="collapse navbar-collapse ">
                <?php wp_nav_menu( array(
                        'theme_location' => 'primary',
                        'menu_class' => 'nav navbar-nav',
                        'fallback_cb' => 'bhumi_fallback_page_menu',
                        'walker' => new bhumi_nav_walker(),
                        )
                        );  ?>
                </div>
            </nav>
        </div>
    </div>
