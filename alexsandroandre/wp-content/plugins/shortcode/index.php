<?php
/**
 * Plugin name: Plugin para criar shortcode
 * Description: Formulário shortcode, para compartilhar links e posts.
 * Version: 0.1
 * Author: Alexsandro
 * Author URI: https://alexsandroandre.com
 */

defined( 'ABSPATH' ) or die( 'Não autorizado' );

class ShortCode{
  function __construct(){
    add_action( 'init', array( $this, 'custom_post_type' ) );
  }

  function activate(){
    $this->custom_post_type();
    flush_rewrite_rules();
  }

  function deactivate(){
    echo 'Plugin desativado';
  }

  function uninstall(){

  }

  function custom_post_type(){
    register_post_type( 'book', ['public' => true, 'label' => 'Livros'] );
  }
}

if( class_exists( 'ShortCode' ) ){
  $shortCode = new ShortCode();
}

//activation
register_activation_hook( __FILE__, array( $shortCode, 'activate' ) );
//deactivation
register_deactivation_hook( __FILE__, array( $shortCode, 'deactivate' ) );
//uninstall
register_uninstall_hook( __FILE__, array( $shortCode, 'uninstall' ) );
