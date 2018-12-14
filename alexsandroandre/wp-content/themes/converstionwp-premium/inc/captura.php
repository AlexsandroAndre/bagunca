<?php 
    $captura = get_option('caixa_captura'); 
    $rotulos[] = $captura['rotulo_1'];
    if (!isset($captura['exibir_campos']))
        $captura['exibir_campos'] = '';
    $paginas_ignoradas = explode(',', $captura['paginas_ignoradas']);
    if (! in_array($post->ID, $paginas_ignoradas)){
?>
        <div class="captura">
            <div class="container">
                <div class="row">
                    <?php if (!$captura['tipo_captura_topo']) { ?>
                        <div class="ebook col-md-5">
                            <img src="<?php $image = wp_get_attachment_image_src($captura['ebook'], 'full');
            echo $image[0];
                        ?>" />
                        </div>
                        <div class="col-md-7">
                            <h2><?php echo $captura['titulo']; ?></h2>
                            <h3><?php echo $captura['subtitulo']; ?></h3>
                            <div class="col-md-12 omega alpha"><?php echo criar_formulario_captura($captura['form'], $captura['botao'], $captura['placeholder'], $captura['exibir_campos'], $rotulos, 1,0, "ga('send', 'event', 'Pagina', 'Captura', 'Top-Home');", "botao-top-home", $captura["target"]); ?></div>
                            <p class="privacy"><i class="fa fa-lock"></i><?php echo $captura['privacidade']; ?></p>
                        </div>
        <?php } else { ?>

                        <div class="col-md-5">
                            <h2 class="titulo-texto"><?php echo $captura['titulo']; ?></h2>
                        </div>
                        <div class="col-md-2 icon-captura-topo">
                            <i class="fa <?php echo $captura['icone']; ?>"></i>
                        </div>
                        <div class="col-md-5 center">         
                            <h3><?php echo $captura['subtitulo']; ?></h3>
                            <?php if ($captura['ocultar_seta'] <> "1"){ ?>
                                <div class="icon-fa-arrow-down"><i class="fa fa-long-arrow-down"></i></div>
                            <?php } ?>
                        </div>
                        <div class="clearfix"></div>
                        <div class="formulario-content row margin-0">
                            <div class="col-md-4 privacy-texto">
                                <p class="privacy"><i class="fa fa-lock"></i><?php echo $captura['privacidade']; ?></p>
                            </div>

                            <div class="col-md-8 form-texto">
            <?php echo criar_formulario_captura($captura['form'], $captura['botao'], $captura['placeholder'], $captura['exibir_campos'], $rotulos, 1,0, "ga('send', 'event', 'Pagina', 'Captura', 'Top-Home');", "botao-top-home", $captura["target"]); ?>
                            </div>
                        </div>
        <?php } ?>
                </div>
            </div>
        </div>
<?php } ?>