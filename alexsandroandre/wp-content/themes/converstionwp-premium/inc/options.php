<?php

$odin_theme_options = new Odin_Theme_Options(
        'opcoes-do-tema', // Slug/ID da página (Obrigatório)
        __('Opções do tema', 'odin'), // Titulo da página (Obrigatório)
        'manage_options' // Nível de permissão (Opcional) [padrão é manage_options]
);

$odin_theme_options->set_tabs(
        array(
            array(
                'id' => 'geral', // ID da aba e nome da entrada no banco de dados.
                'title' => 'Configurações Gerais'
            ),
            array(
                'id' => 'artigos', // ID da aba e nome da entrada no banco de dados.
                'title' => 'Artigos'
            ),
            array(
                'id' => 'cores',
                'title' => 'Configurações de Cor'
            ),
            array(
                'id' => 'fonts',
                'title' => 'Fontes'
            ),
            array(
                'id' => 'caixa_captura',
                'title' => 'Caixa de Captura'
            ),
            array(
                'id' => 'banners',
                'title' => 'Banners'
            ),
            array(
                'id' => 'caixa_topo',
                'title' => 'Caixa de Avisos'
            ),
        )
);

require_once('fonts.php');
$fonts = get_fonts();

$odin_theme_options->set_fields(
        array(
            'licencialmento' => array(
                'tab' => 'geral', // Sessão da aba odin_general
                'title' => 'Licenciamento',
                'fields' => array(
                    array(
                        'id' => 'key', // Obrigatório
                        'label' => 'Chave do ConversionWP Premium',
                        'type' => 'text', // Obrigatório
                        'description' => 'Coloque aqui a licença gerada na área de membros do ConversionWP Premium'
                    ),
                ),
            ),
            'general_section' => array(
                'tab' => 'geral', // Sessão da aba odin_general
                'title' => __('Cabeçalho', 'odin'),
                'fields' => array(
                    array(
                        'id' => 'logo', // Obrigatório
                        'label' => 'Logo',
                        'type' => 'image', // Obrigatório
                        'description' => 'Faça o Upload da sua logo aqui'
                    ),
                    array(
                        'id' => 'ico', // Obrigatório
                        'label' => 'FavIcon',
                        'type' => 'image', // Obrigatório
                        'description' => 'Faça o Upload de seu favico aqui'
                    ),
                    
                    array(
                        'id' => 'imagem_post', // Obrigatório
                        'label' => 'Tamanho das miniaturas dos artigos',
                        'type' => 'select', // Obrigatório
                        'options' => array(// Obrigatório (adicione aque os ids e títulos)
                            '0' => '335 X 320',
                            '1' => '300 X 200',
                        ),
                    ),

                    array(
                        'id' => 'thumb_mobile', // Obrigatório
                        'label' => 'Exibir miniaturas de artigos em dispositivos móveis',
                        'type' => 'select', // Obrigatório
                        'options' => array(// Obrigatório (adicione aque os ids e títulos)
                            '0' => 'Não',
                            '1' => 'Sim',
                        ),
                    ),
                    
                    array(
                        'id' => 'copy-footer',
                        'label' => 'Texto de Copyright do rodapé',
                        'type' => 'editor',
                    ),
                )
            ),
            'sidebar' => array(
                'tab' => 'geral', // Sessão da aba odin_general
                'title' => 'Sidebar',
                'fields' => array(
                    array(
                        'id' => 'posicao_sidebar', // Obrigatório
                        'label' => 'Posição da Sidebar',
                        'type' => 'select', // Obrigatório
                        'options' => array(// Obrigatório (adicione aque os ids e títulos)
                            '0' => 'Esquerda',
                            '1' => 'Direita',
                        ),
                    ),
                    array(
                        'id' => 'tamanho_foto_bio', // Obrigatório
                        'label' => 'Tamanho da Foto - BioAutor',
                        'type' => 'text',
                        'description' => 'Insira um valor inteiro, Deixe em branco para o valor padrão (120)',
                    ),

                    array(
                        'id' => 'ocultar_sidebar_mobile', // Obrigatório
                        'label' => 'Ocultar Sidebar em dispositivos móveis?',
                        'type' => 'text',
                        'type' => 'select', // Obrigatório
                        'options' => array(// Obrigatório (adicione aque os ids e títulos)
                            '0' => 'Não',
                            '1' => 'Sim',
                        ),
                    ),
                ),
            ),
            'codigos' => array(
                'tab' => 'geral', // Sessão da aba odin_general
                'title' => 'Inclusão de Códigos',
                'fields' => array(
                    array(
                        'id' => 'css',
                        'label' => 'CSS (Use este campo para realizar alguma customização no CSS)',
                        'type' => 'textarea',
                    ),
                    array(
                        'id' => 'scripts_head',
                        'label' => 'Scripts incluídos ao início do carregamento da página',
                        'description' => 'Coloque aqui os seus Scripts como o Google Analytics, Facebook, etc',
                        'type' => 'textarea',
                    ),
                    array(
                        'id' => 'scripts',
                        'label' => 'Scripts incluídos ao final do carregamento da página',
                        'description' => 'Coloque aqui os seus Scripts como o Google Analytics, Facebook, etc',
                        'type' => 'textarea',
                    ),
                ),
            ),
            'opcoes_artigo' => array(
                'tab' => 'artigos', // Sessão da aba odin_general
                'title' => __('Opções do Artigo', 'odin'),
                'fields' => array(
                    array(
                        'id' => 'texto_link_artigo', // Obrigatório
                        'label' => 'Texto do [Leia Mais]',
                        'type' => 'text', // Obrigatório
                        'description' => 'Se preencher, irá substituir o leia mais na listagem de artigos'
                    ),
                    array(
                        'id' => 'exibir_share', // Obrigatório
                        'label' => 'Exibir Compartilhamento em Redes Sociais no início dos artigos?',
                        'type' => 'select', // Obrigatório
                        'options' => array(// Obrigatório (adicione aque os ids e títulos)
                            '0' => 'Não',
                            '1' => 'Sim',
                        ),
                    ),
                    array(
                        'id' => 'exibir_share_final', // Obrigatório
                        'label' => 'Exibir Compartilhamento em Redes Sociais no final dos artigos?',
                        'type' => 'select', // Obrigatório
                        'options' => array(// Obrigatório (adicione aque os ids e títulos)
                            '0' => 'Não',
                            '1' => 'Sim',
                        ),
                    ),
                    array(
                        'id' => 'ocultar_biografia', // Obrigatório
                        'label' => 'Ocultar a Biografia do Autor no artigo?',
                        'type' => 'select', // Obrigatório
                        'options' => array(// Obrigatório (adicione aque os ids e títulos)
                            '0' => 'Não',
                            '1' => 'Sim',
                        ),
                    ),
                    array(
                        'id' => 'mostrar_relacionados', // Obrigatório
                        'label' => 'Mostrar artigos relacionados ao fim do artigo?',
                        'type' => 'select', // Obrigatório
                        'options' => array(// Obrigatório (adicione aque os ids e títulos)
                            '0' => 'Não',
                            '1' => 'Sim',
                        ),
                    ),
                    array(
                        'id' => 'ocultar_dados_artigo', // Obrigatório
                        'label' => 'Ocultar Metadados do Artigo (Data, autor, categoria)?',
                        'type' => 'select', // Obrigatório
                        'options' => array(// Obrigatório (adicione aque os ids e títulos)
                            '0' => 'Não',
                            '1' => 'Sim',
                        ),
                    ),
                    array(
                        'id' => 'ocultar_autor_meta', // Obrigatório
                        'label' => 'Ocultar Autor dos metadados (Dados exibidos abaixo do título)?',
                        'type' => 'select', // Obrigatório
                        'options' => array(// Obrigatório (adicione aque os ids e títulos)
                            '0' => 'Não',
                            '1' => 'Sim',
                        ),
                    ),
                    array(
                        'id' => 'ocultar_data_meta', // Obrigatório
                        'label' => 'Ocultar Data dos metadados (Dados exibidos abaixo do título)?',
                        'type' => 'select', // Obrigatório
                        'options' => array(// Obrigatório (adicione aque os ids e títulos)
                            '0' => 'Não',
                            '1' => 'Sim',
                        ),
                    ),
                    array(
                        'id' => 'ocultar_categoria_meta', // Obrigatório
                        'label' => 'Ocultar Categoria dos metadados (Dados exibidos abaixo do título)?',
                        'type' => 'select', // Obrigatório
                        'options' => array(// Obrigatório (adicione aque os ids e títulos)
                            '0' => 'Não',
                            '1' => 'Sim',
                        ),
                    ),
                )
            ),
            'adsense_top_section' => array(
                'tab' => 'caixa_captura', // Sessão da aba odin_adsense
                'title' => 'Caixa de Captura Superior',
                'fields' => array(
                    array(
                        'id' => 'tipo_captura_topo', // Obrigatório
                        'label' => 'Tipo de captura',
                        'type' => 'select', // Obrigatório
                        'options' => array(// Obrigatório (adicione aque os ids e títulos)
                            '0' => 'Imagem (Ebook) ao lado do texto e formulário',
                            '1' => 'Apenas o texto e o formulário',
                        ),
                    ),
                    array(
                        'id' => 'background', // Obrigatório
                        'label' => 'Background',
                        'type' => 'image', // Obrigatório
                        'description' => 'Faça o Upload da imagem do background (se houver)'
                    ),
                    array(
                        'id' => 'ebook', // Obrigatório
                        'label' => 'E-book',
                        'type' => 'image', // Obrigatório
                        'description' => 'Faça o Upload da image da recompensa (E-book, vídeo, etc)'
                    ),
                    array(
                        'id' => 'icone',
                        'label' => 'Ícone',
                        'type' => 'text',
                    ),
                    array(
                        'id' => 'titulo',
                        'label' => 'Título',
                        'type' => 'editor',
                    ),
                    array(
                        'id' => 'subtitulo',
                        'label' => 'Subtítulo',
                        'type' => 'editor',
                    ),
                    array(
                        'id' => 'form',
                        'label' => 'Formulário de Optin',
                        'type' => 'textarea',
                    ),
                    array(
                        'id' => 'target',
                        'label' => 'Abrir o formulário em uma nova página?',
                        'type' => 'checkbox',
                    ),
                    array(
                        'id' => 'placeholder',
                        'label' => 'Placeholder do e-mail (Rótulo)',
                        'type' => 'text',
                    ),
                    array(
                        'id' => 'exibir_campos', // Obrigatório
                        'label' => __('Exibir campos além do e-mail?', 'odin'), // Obrigatório
                        'type' => 'checkbox', // Obrigatório
                        // 'attributes' => array(), // Opcional (atributos para input HTML/HTML5)
                        'description' => 'Se você marcar está opção, seu formulário terá outros campos além do e-mail' // Opcional
                    ),
                    array(
                        'id' => 'rotulo_1', // Obrigatório
                        'label' => 'Rótulo - Campo 1', // Obrigatório
                        'type' => 'text', // Obrigatório
                        'attributes' => array(// Opcional (atributos para input HTML/HTML5)
                            'placeholder' => __('Rótulo - Campo 1')
                        ),
                        'description' => __('Rótulo do campo 1 (Ex: Primeiro Nome)', 'odin'), // Opcional
                    ),
                    array(
                        'id' => 'botao',
                        'label' => 'Texto do botão (CTA)',
                        'type' => 'text',
                    ),
                    array(
                        'id' => 'privacidade',
                        'label' => 'Texto antiSpam - Privacidade',
                        'type' => 'text',
                    ),
                    array(
                        'id' => 'exibir_acima', // Obrigatório
                        'label' => 'Exibir no topo?',
                        'type' => 'checkbox', // Obrigatório
                        // 'attributes' => array(), // Opcional (atributos para input HTML/HTML5)
                        'default' => '', // Opcional (utilize 1 para deixar marcado como padrão)
                        'description' => 'Exibir caixa de captura no topo'
                    ),
                    array(
                        'id' => 'minima_home', // Obrigatório
                        'label' => 'Versão mínima na página principal?',
                        'type' => 'checkbox', // Obrigatório
                        // 'attributes' => array(), // Opcional (atributos para input HTML/HTML5)
                        'default' => '', // Opcional (utilize 1 para deixar marcado como padrão)
                        'description' => 'Exibe uma caixa menor na homepage?'
                    ),
                    array(
                        'id' => 'minima_artigos', // Obrigatório
                        'label' => 'Versão mínima em artigos?',
                        'type' => 'checkbox', // Obrigatório
                        // 'attributes' => array(), // Opcional (atributos para input HTML/HTML5)
                        'default' => '', // Opcional (utilize 1 para deixar marcado como padrão)
                        'description' => 'Exibe uma caixa menor acima dos artigos mais discreta'
                    ),
                    array(
                        'id' => 'minima_categorias', // Obrigatório
                        'label' => 'Versão mínima em categorias?',
                        'type' => 'checkbox', // Obrigatório
                        // 'attributes' => array(), // Opcional (atributos para input HTML/HTML5)
                        'default' => '', // Opcional (utilize 1 para deixar marcado como padrão)
                        'description' => 'Exibe uma caixa menor acima das categorias mais discreta'
                    ),
                    array(
                        'id' => 'minima_paginas', // Obrigatório
                        'label' => 'Versão mínima em páginas?',
                        'type' => 'checkbox', // Obrigatório
                        // 'attributes' => array(), // Opcional (atributos para input HTML/HTML5)
                        'default' => '', // Opcional (utilize 1 para deixar marcado como padrão)
                        'description' => 'Exibe uma caixa menor acima das páginas mais discreta'
                    ),
                    array(
                        'id' => 'paginas_ignoradas', // Obrigatório
                        'label' => 'Não exibir a caixa de captura nas seguintes páginas',
                        'type' => 'text', // Obrigatório
                        // 'attributes' => array(), // Opcional (atributos para input HTML/HTML5)
                        'default' => '', // Opcional (utilize 1 para deixar marcado como padrão)
                        'description' => 'Coloque aqui os IDs das páginas em que não será exibida a caixa de captura, separe os IDs por vírgula'
                    ),
                    array(
                        'id' => 'exibir_artigos',
                        'label' => 'Exibir caixa abaixo dos artigos?',
                        'type' => 'checkbox', // Obrigatório
                        'description' => 'Exibir caixa de captura no final dos artigos'
                    ),
                    array(
                        'id' => 'exibir_final_paginas',
                        'label' => 'Exibir caixa abaixo das páginas?',
                        'type' => 'checkbox', // Obrigatório
                        'description' => 'Exibir caixa de captura no final das páginas'
                    ),
                    array(
                        'id' => 'ocultar_seta',
                        'label' => 'Ocultar seta da caixa de captura?',
                        'type' => 'checkbox', // Obrigatório
                        'description' => 'Marque essa opção para ocultar seta da caixa de captura'
                    )
                )
            ),
            'cor_section' => array(
                'tab' => 'cores', // Sessão da aba odin_general
                'title' => 'Configurações de Cor - Básico',
                'fields' => array(
                    array(
                        'id' => 'cor_primaria', // Obrigatório
                        'label' => 'Cor primária',
                        'type' => 'color', // Obrigatório
                        // 'attributes' => array(), // Opcional (atributos para input HTML/HTML5)
                        'default' => '#F4733D', // Opcional (cor em hexadecimal)
                        'description' => ''
                    ),
                    array(
                        'id' => 'cor_secundaria', // Obrigatório
                        'label' => 'Cor sencundária',
                        'type' => 'color', // Obrigatório
                        // 'attributes' => array(), // Opcional (atributos para input HTML/HTML5)
                        'default' => '#38595E', // Opcional (cor em hexadecimal)
                        'description' => ''
                    ),
                    array(
                        'id' => 'cor_terciaria', // Obrigatório
                        'label' => 'Cor terciaria',
                        'type' => 'color', // Obrigatório
                        // 'attributes' => array(), // Opcional (atributos para input HTML/HTML5)
                        'default' => '#e2e7e9', // Opcional (cor em hexadecimal)
                        'description' => ''
                    ),
                    array(
                        'id' => 'cor_quartenaria', // Obrigatório
                        'label' => 'Cor Quartenaria',
                        'type' => 'color', // Obrigatório
                        // 'attributes' => array(), // Opcional (atributos para input HTML/HTML5)
                        'default' => '#C8D1D5', // Opcional (cor em hexadecimal)
                        'description' => ''
                    ),
                    
                )
            ),
            'cor_avancado' => array(
                'tab' => 'cores', // Sessão da aba odin_general
                'title' => 'Configurações de Cor - Avançado',
                'fields' => array(
                    array(
                        'id' => 'cor_fundo', // Obrigatório
                        'label' => 'Cor do fundo da página',
                        'type' => 'color', // Obrigatório
                        // 'attributes' => array(), // Opcional (atributos para input HTML/HTML5)
                        'default' => '#f8f8f8', // Opcional (cor em hexadecimal)
                        'description' => ''
                    ),
                    array(
                        'id' => 'cor_fundo_artigos', // Obrigatório
                        'label' => 'Cor do fundo dos artigos e páginas',
                        'type' => 'color', // Obrigatório
                        // 'attributes' => array(), // Opcional (atributos para input HTML/HTML5)
                        'default' => '#ffffff', // Opcional (cor em hexadecimal)
                        'description' => ''
                    ),
                    array(
                        'id' => 'cor_texto', // Obrigatório
                        'label' => 'Cor do texto nas páginas',
                        'type' => 'color', // Obrigatório
                        // 'attributes' => array(), // Opcional (atributos para input HTML/HTML5)
                        'default' => '#333', // Opcional (cor em hexadecimal)
                        'description' => ''
                    ),
                    array(
                        'id' => 'cor_fundo_cabecalho', // Obrigatório
                        'label' => 'Cor do fundo do cabeçalho',
                        'type' => 'color', // Obrigatório
                        // 'attributes' => array(), // Opcional (atributos para input HTML/HTML5)
                        'default' => '#fff', // Opcional (cor em hexadecimal)
                        'description' => ''
                    ),
                ),
            ),
            'fonts_section' => array(
                'tab' => 'fonts', // Sessão da aba odin_general
                'title' => __('Fontes', 'odin'),
                'fields' => array(
                    array(
                        'id' => 'fonte_titulos', // Obrigatório
                        'label' => 'Fonte dos títulos',
                        'type' => 'select', // Obrigatório
                        'options' => $fonts,
                    ),
                    array(
                        'id' => 'fonte_texto', // Obrigatório
                        'label' => 'Fonte dos textos',
                        'type' => 'select', // Obrigatório
                        'options' => $fonts,
                    ),
                )
            ),

            'font_size_section' => array(
                'tab' => 'fonts', // Sessão da aba odin_general
                'title' => 'Tamanho de Fontes',
                'fields' => array(
                    array(
                        'id' => 'font_size_titulos',
                        'label' => 'Tamanho da Fonte dos títulos',
                        'type' => 'number',
                        'attributes' => array(
                            'type' => 'number',
                            'min' => 0,
                            'step' => 1,
                            'pattern'=> '[0-9]{10}'
                        ),
                        'description' => 'Em (Px). Coloque o tamanho da fonte que deseja, deixe em branco para deixar o padrão'
                    ),
                    array(
                        'id' => 'font_size_artigo', // Obrigatório
                        'label' => 'Tamanho da Fonte dos artigos',
                        'type' => 'number', // Obrigatório
                        'attributes' => array(
                            'type' => 'number',
                            'min' => 0,
                            'step' => 1,
                            'pattern'=> '[0-9]{10}'
                        ),
                        'description' => 'Em (Px). Coloque o tamanho da fonte que deseja, deixe em branco para deixar o padrão'
                    ),
                )
            ),
            
            'banner_inicio_artigo' => array(
                'tab' => 'banners',
                'title' => 'Banners no Início do artigo',
                'fields' => array(
                    array(
                        'id' => 'exibir_banner_inicio',
                        'label' => 'Exibir Banner no início do artigo?',
                        'type' => 'select',
                        'options' => array(
                            '0' => 'Não',
                            '1' => 'Sim',
                        ),
                    ),
                    array(
                        'id' => 'conteudo_banner_inicio',
                        'label' => 'Coloque aqui o banner para o início do artigo',
                        'type' => 'editor',
                    ),
                    array(
                        'id' => 'alinhamento_banner_inicio',
                        'label' => 'Qual alinhamento utilizar?',
                        'type' => 'select',
                        'options' => array(
                            'left' => 'Esquerda',
                            'center' => 'Centro',
                            'right' => 'Direita',
                        ),
                    ),
                )
            ),
            
            'banner_final_artigo' => array(
                'tab' => 'banners',
                'title' => 'Banners no Final do artigo',
                'fields' => array(
                    array(
                        'id' => 'exibir_banner_final',
                        'label' => 'Exibir Banner no final do artigo?',
                        'type' => 'select',
                        'options' => array(
                            '0' => 'Não',
                            '1' => 'Sim',
                        ),
                    ),
                    array(
                        'id' => 'conteudo_banner_final',
                        'label' => 'Coloque aqui o banner para o final do artigo',
                        'type' => 'editor',
                    ),
                    array(
                        'id' => 'alinhamento_banner_final',
                        'label' => 'Qual alinhamento utilizar?',
                        'type' => 'select',
                        'options' => array(
                            'left' => 'Esquerda',
                            'center' => 'Centro',
                            'right' => 'Direita',
                        ),
                    ),
                )
            ),
            
            
            'informacoes_topo' => array(
                'tab' => 'caixa_topo', // Sessão da aba odin_adsense
                'title' => 'Informações do Topo',
                'fields' => array(
                    array(
                        'id' => 'exibir_caixa_topo', // Obrigatório
                        'label' => 'Exibir caixa de avisos no topo?',
                        'type' => 'select', // Obrigatório
                        'options' => array(// Obrigatório (adicione aque os ids e títulos)
                            '0' => 'Não',
                            '1' => 'Sim',
                        ),
                    ),
                    array(
                        'id' => 'fixar_caixa_topo', // Obrigatório
                        'label' => 'Fixar caixa de avisos?',
                        'type' => 'select', // Obrigatório
                        'options' => array(// Obrigatório (adicione aque os ids e títulos)
                            '0' => 'Não',
                            '1' => 'Sim',
                        ),
                    ),
                    array(
                        'id' => 'texto_topo',
                        'label' => 'Texto do topo',
                        'type' => 'text',
                    ),
                    array(
                        'id' => 'texto_cta',
                        'label' => 'Texto do Botão de ação (CTA)',
                        'type' => 'text',
                    ),
                    array(
                        'id' => 'link_cta',
                        'label' => 'Link do Botão de ação (CTA)',
                        'type' => 'text',
                    ),
                    
                    array(
                        'id' => 'cor_barra',
                        'label' => 'Cor da Barra',
                        'type' => 'color',
                        'description' => 'Preencha se desejar que a cor seja diferente da cor padrão do template'
                    ),
                    
                    array(
                        'id' => 'cor_link_cta',
                        'label' => 'Cor do botão',
                        'type' => 'color',
                        'description' => 'Preencha se desejar que a cor seja diferente da cor padrão do template'
                    ),
                )
            )
        )
);

