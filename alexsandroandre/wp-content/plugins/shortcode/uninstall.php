<?php
/**
 * @package ShortCode
 */
if( !defined( 'WP_UNINSTALL_PLUGIN' ) ){
  die;
}
//clear database stored data
$books = get_posts( array( 'post_type' => 'book', 'numberposts' => -1 ) );

foreach ( $books as $book ) {
  wp_delete_post( $book->ID, false );
}
