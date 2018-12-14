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
							if ( is_day() ) :
								printf( __( 'Daily Archives: %s', 'odin' ), get_the_date() );

							elseif ( is_month() ) :
								printf( __( 'Monthly Archives: %s', 'odin' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'odin' ) ) );

							elseif ( is_year() ) :
								printf( __( 'Yearly Archives: %s', 'odin' ), get_the_date( _x( 'Y', 'yearly archives date format', 'odin' ) ) );

							else :
								_e( 'Archives', 'odin' );

							endif;
						?>
					</h1>
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