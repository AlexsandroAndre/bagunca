<?php

    get_header();

    $captura = get_option('caixa_captura'); 

?>

<?php 

    if ($captura['exibir_acima'])

        if ($captura['minima_artigos'])

            require("inc/captura-minima.php");

        else      

            require("inc/captura.php");  

?>

<div class="container">

    <?php

    $geral = get_option('geral');

    $artigos = get_option('artigos');

    $posicao = $geral['posicao_sidebar'];

    $share = $artigos['exibir_share'];

    $share_final = $artigos['exibir_share_final'];

    $ocultar_bio = $artigos['ocultar_biografia'];

    $ocultar_meta = $artigos['ocultar_dados_artigo'];

    $ocultar_autor = $artigos['ocultar_autor_meta'];

    $ocultar_data = $artigos['ocultar_data_meta'];

    $ocultar_categoria = $artigos['ocultar_categoria_meta'];

    $banners = get_option('banners');

    require('inc/share.php');

    if ($posicao)

        $class = 'alpha';

    else

        $class = 'omega';

    if (!$posicao)

        get_sidebar();

    ?>

    <div id="primary" class="col-md-8 <?php echo $class; ?>">

        <div id="content" class="site-content" role="main">

            <?php

            // Start the Loop.

            while (have_posts()) : the_post();

                ?>



                <article id="post-<?php the_ID(); ?>" class="artigo artigo-single">

                    <h1><?php the_title(); ?></h1>

                    <div class="entry-meta">

                        <?php 

                            if (!$ocultar_meta) odin_posted_on($ocultar_autor, $ocultar_data, $ocultar_categoria); ?>

                    </div><!-- .entry-meta -->

                    <?php exibir_share($share); ?>

                    <?php 

                        exibir_banner($banners['exibir_banner_inicio'], $banners['conteudo_banner_inicio'], $banners['alinhamento_banner_inicio']);

                        the_content('');

                        exibir_banner($banners['exibir_banner_final'], $banners['conteudo_banner_final'], $banners['alinhamento_banner_final']);

                        echo '<div class="clearfix"></div>';

                        exibir_share($share_final);

                        if ($artigos['mostrar_relacionados'])

                            include('inc/related-posts.php');

                    ?>

                    <div class="clearfix"></div>

                    

                    <?php

                    

                endwhile;

                ?>

            </article><!-- #post-## -->



            <?php echo do_shortcode('[caixa_captura]'); ?>

            <?php if (!$ocultar_bio) bio_autor(); ?>

            <?php

            // If comments are open or we have at least one comment, load up the comment template.

                    if (comments_open() || get_comments_number()) :

                        echo comments_template();

                    endif;

            ?>



        </div><!-- #content -->

    </div><!-- #primary -->



    <?php

    if ($posicao)

        get_sidebar();

    ?>

</div>

<?php

get_footer();

