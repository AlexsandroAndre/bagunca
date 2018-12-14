<?php
/**
 * The template for displaying Category pages.
 *
 * @link http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Odin
 * @since 2.2.0
 */
get_header();
?>

<div class="container">
    <?php
    $posicao = get_option('geral');
    $posicao = $posicao['posicao_sidebar'];
    if (!$posicao)
        get_sidebar();
    ?>
    <section id="primary" class="col-md-8">
        <div id="content" class="site-content" role="main">
        <h1 class="archive-title title-cat">
						<?php
							/*
							 * Queue the first post, that way we know what author
							 * we're dealing with (if that is the case).
							 *
							 * We reset this later so we can run the loop properly
							 * with a call to rewind_posts().
							 */
							the_post();

							printf( __( 'All posts by %s', 'odin' ), get_the_author() );
						?>
					</h1>
        <?php rewind_posts(); ?>
        <?php get_template_part('content-loop'); ?>
    </div><!-- #content -->
</section><!-- #primary -->


<?php
    if ($posicao)
        get_sidebar();
    ?>
</div>
<?php
get_footer();
