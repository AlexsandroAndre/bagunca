<?php
    get_header();
    $captura = get_option('caixa_captura'); 
?>
<?php 
    if ($captura['exibir_acima'])
        if ($captura['minima_paginas'])
            require("inc/captura-minima.php");
        else      
            require("inc/captura.php");  
?>
<div class="container">
<?php
$posicao = get_option('geral');
$posicao = $posicao['posicao_sidebar'];
if (!$posicao)
    get_sidebar();
?>
    <div id="primary" class="col-md-8">
        <div id="content" class="site-content" role="main">
<?php
// Start the Loop.
while (have_posts()) : the_post();
    ?>

                <article id="post-<?php the_ID(); ?>" class="row row-margin-0 artigo pagina">
                    <h1><?php the_title(); ?></h1>

                    <?php the_content(''); ?>
                    <div class="clearfix"></div>
                    <?php
                    // If comments are open or we have at least one comment, load up the comment template.
                    if (comments_open() || get_comments_number()) :
                        comments_template();
                    endif;
                endwhile;
                ?>
                <?php require('inc/captura-paginas.php'); ?>
            </article><!-- #post-## -->


        </div><!-- #content -->
    </div><!-- #primary -->
    <?php
    if ($posicao)
        get_sidebar();
    ?>

</div>
<?php
get_footer();
