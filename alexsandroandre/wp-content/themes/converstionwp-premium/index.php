<?php
    get_header();
    $captura = get_option('caixa_captura'); 
?>

<?php 
    if ($captura['exibir_acima'])
        if (isset($captura['minima_home']))
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
    <section id="primary" class="col-md-8">
        <div id="content" class="site-content" role="main">
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
