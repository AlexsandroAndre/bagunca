<?php 
    $captura = get_option('caixa_captura'); 
    $rotulos[] = $captura['rotulo_1'];
    if (!isset($captura['exibir_campos']))
        $captura['exibir_campos'] = '';
    $paginas_ignoradas = explode(',', $captura['paginas_ignoradas']);
    if (! in_array($post->ID, $paginas_ignoradas)){
?>
        <div class="captura captura-minima">
            <div class="container">
                <div class="row">
                    <?php if (!$captura['tipo_captura_topo']) { ?>
                        <div class="ebook col-md-2">
                            <img src="<?php $image = wp_get_attachment_image_src($captura['ebook'], 'full');
            echo $image[0];
                        ?>" />
                        </div>
                        <div class="col-md-5">
                            <h2><?php echo $captura['titulo']; ?></h2>

                        </div>
                    <div class="col-md-5">
            <?php echo criar_formulario_captura($captura['form'], $captura['botao'], $captura['placeholder'], $captura['exibir_campos'], $rotulos, 0,0, "ga('send', 'event', 'Pagina', 'Captura', 'Top-Artigos');", "botao-top-artigos", $captura["target"]); ?>
                        </div>
        <?php } else { ?>

                        <div class="col-md-5">
                            <h2 class="titulo-texto"><?php echo $captura['titulo']; ?></h2>
                        </div>
                        <div class="col-md-2 icon-captura-topo">
                            <i class="fa <?php echo $captura['icone']; ?>"></i>
                        </div>
                        <div class="col-md-5 center">         
                            <?php echo criar_formulario_captura($captura['form'], $captura['botao'], $captura['placeholder'], $captura['exibir_campos'], $rotulos, 0,0, "ga('send', 'event', 'Pagina', 'Captura', 'Top-Artigos');", "botao-top-artigos", $captura["target"]); ?>
                        </div>
                        
        <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>