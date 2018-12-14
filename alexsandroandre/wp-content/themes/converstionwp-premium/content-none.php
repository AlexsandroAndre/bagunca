<?php
/**
 * The template for displaying a "No posts found" message.
 *
 * @package Odin
 * @since 2.2.0
 */
?>

<div class="page-content">
	<?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>

		<p><?php printf( __( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'odin' ), admin_url( 'post-new.php' ) ); ?></p>

	<?php elseif ( is_search() ) : ?>
                <article id="post-<?php the_ID(); ?>" class="row row-margin-0 artigo artigo-search">
                    <h1 class="page-title"><?php _e( 'Nothing Found', 'odin' ); ?></h1>
                    <p><?php _e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'odin' ); ?></p>
                    <?php get_search_form(); ?>
                </article>

	<?php else : ?>

		<p><?php _e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'odin' ); ?></p>
		<?php get_search_form(); ?>

	<?php endif; ?>
</div><!-- .page-content -->
