<?php
/* Template Name: Página sem Sidebar */
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
	<div id="primary" class="col-md-12">
		<div id="content" class="site-content" role="main">
			<?php
				// Start the Loop.
				while ( have_posts() ) : the_post(); ?>

					<article id="post-<?php the_ID(); ?>" class="row row-margin-0 artigo pagina">
                        <h1><?php the_title(); ?></h1>
                        
                            <?php the_content(''); ?>
                        <div class="clearfix"></div>
                        <?php
				endwhile;
			?>
                    </article><!-- #post-## -->
                    
		</div><!-- #content -->
	</div><!-- #primary -->

</div>
<?php
get_footer();
