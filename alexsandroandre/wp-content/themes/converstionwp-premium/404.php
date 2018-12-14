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

            <article id="post-<?php the_ID(); ?>" class="row row-margin-0 artigo pagina">
                <h1 class="page-title"><?php _e('Not Found', 'odin'); ?></h1>
                <p><?php _e('It looks like nothing was found at this location. Maybe try a search?', 'odin'); ?></p>

                <?php get_search_form(); ?>
            </article>
        </div><!-- #content -->
    </section><!-- #primary -->


    <?php
    if ($posicao)
        get_sidebar();
    ?>
</div>
<?php
get_footer();
