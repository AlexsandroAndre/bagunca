<!DOCTYPE html>
<!--[if IE 7]>
<html class="no-js ie ie7 lt-ie9 lt-ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="no-js ie ie8 lt-ie9" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8) ]><!-->
<html class="no-js" <?php language_attributes(); ?>>
    <!--<![endif]-->
    <head>
        <?php $geral = get_option( 'geral' ); ?>
        <meta charset="<?php bloginfo('charset'); ?>" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title><?php wp_title('|', true, 'right'); ?></title>
        <link rel="profile" href="http://gmpg.org/xfn/11" />
        <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
        <?php if($geral['ico'] <> ''){ ?>
        <link href="<?php $ico = wp_get_attachment_image_src($geral['ico'], 'full'); echo $ico[0]; ?>" rel="shortcut icon" />    
        <?php } ?>
        <!--[if lt IE 9]>
        <script async src="<?php echo get_template_directory_uri(); ?>/assets/js/html5.js"></script>
        <![endif]-->
        <?php wp_head(); ?>
        <link href='<?php echo get_template_directory_uri(); ?>/assets/css/conversion.css' rel='stylesheet' async="true" type='text/css'>
        <!--<link href='<?php echo get_template_directory_uri(); ?>/assets/css/flat.css' async="true" rel='stylesheet' type='text/css'> -->
        <?php $captura = get_option( 'caixa_captura' ); 
        require(get_template_directory() . '/inc/color.php'); 
        echo '<style>' . $geral['css'] . '</style>';
        echo $geral['scripts_head'];
        ?>
    </head>

    <body <?php body_class(); ?>>
        
        <div class="row margin-0">
            <?php require_once('inc/top-aviso.php');?>
            <header id="header" role="banner">
                <div class="container container-header">
                <div class="col-md-4">
                    <?php if (is_home()){ ?>
                    <h1 id="logo" title="<?php bloginfo('name');?>"><a href="<?php bloginfo('url'); ?>">
                        <?php if ($geral['logo'] <> ''){ ?>
                            <img alt="<?php bloginfo('name');?>" src="<?php $image = wp_get_attachment_image_src($geral['logo'], 'full'); echo $image[0]; ?>" title="" />
                        <?php }else{ ?>
                            <h1><?php bloginfo('name');?>
                        <?php } ?>
                        </a></h1>
                    <?php }else{?>
                    <div id="logo" title="<?php bloginfo('name');?>"><a href="<?php bloginfo('url'); ?>">
                        <?php if ($geral['logo'] <> ''){ ?>
                            <img alt="<?php bloginfo('name');?>" src="<?php $image = wp_get_attachment_image_src($geral['logo'], 'full'); echo $image[0]; ?>" title="" />
                        <?php }else{ ?>
                            <div><?php bloginfo('name');?></div>
                        <?php } ?>
                        </a></div>
                    <?php }?>
                </div>
                <div class="col-md-8 menu">
                    <nav id="main-navigation" class="navbar navbar-default" role="navigation">
                    
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-navigation">
                            <i class="fa fa-plus-square"></i>
                            MENU
                        </button>
                        <?php /*

                          <a class="navbar-brand" href="<?php echo home_url(); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>

                         */ ?>
                    </div>

                    <div class="collapse navbar-collapse navbar-main-navigation">
                        <?php
                        wp_nav_menu(
                                array(
                                    'theme_location' => 'main-menu',
                                    'depth' => 2,
                                    'container' => false,
                                    'menu_class' => 'nav navbar-nav',
                                    'fallback_cb' => 'Odin_Bootstrap_Nav_Walker::fallback',
                                    'walker' => new Odin_Bootstrap_Nav_Walker()
                                )
                        );
                        ?>

                        
                    </div><!-- .navbar-collapse -->
                </nav><!-- #main-menu -->
                </div>
                        
                </div>
                
            </header><!-- #header -->
        </div>

            <div id="main" class="site-main row margin-0">
