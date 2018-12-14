<?php 
    $captura = get_option('caixa_captura'); 
    $rotulos[] = $captura['rotulo_1'];
    if (!isset($captura['exibir_campos']))
        $captura['exibir_campos'] = '';
?>
<?php if ($captura['exibir_artigos']) {
    $result = '<div class="captura-artigos">
        <div class="row">
            <div class="col-md-12">
                <h2>' . $captura['titulo'] . '</h2>
            </div>
            <div class="col-md-10 col-md-offset-1">
                ' . criar_formulario_captura($captura['form'], $captura['botao'], $captura['placeholder'],$captura['exibir_campos'], $rotulos, 1,1, "ga('send', 'event', 'Pagina', 'Captura', 'Artigos');", "botao-artigos", $captura["target"]) . '
            </div>
        </div>
    </div>';
}
