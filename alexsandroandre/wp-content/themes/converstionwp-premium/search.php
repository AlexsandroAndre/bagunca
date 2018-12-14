<?php
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
                <h1 class="page-title title-cat"><?php printf(__('Search Results for: %s', 'odin'), get_search_query()); ?></h1>
                <?php get_template_part('content-loop'); ?>
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
