<?php 
    $cores = get_option('cores');
    $topo = get_option('caixa_topo');
    $fontes = get_option('fonts');
    $captura = get_option('caixa_captura');
    $geral = get_option('geral');

    require_once('fonts.php');
    $fonts = get_fonts();
$css = "<style>
    .author-title i, .author-bio .icons a:hover, a, .navbar-default .navbar-nav>li>a,
    #header .dropdown-menu> li > a:hover, .comment-reply-link, #submit, .captura .icon-fa-arrow-down{
        color: " . $cores['cor_primaria']. " !important;
    }

    #top-aviso, .widget-paginas a.destaque, .widget-paginas a.destaque:hover,
    .navbar-default .navbar-nav > .open > a, .navbar-default .navbar-nav > .open > a:hover, .navbar-default .navbar-nav > .open > a:focus,
    .readmore:hover, a.page-numbers:hover, span.page-numbers, .navbar-default .navbar-nav>li>a:hover,
    .navbar-nav>li>.dropdown-menu, .comment-reply-link:hover, #submit:hover,
    .captura .submit-captura, .captura-artigos .submit-captura, .navbar-nav .open .dropdown-menu, .current{
        background: " . $cores['cor_primaria']. " !important;
    }

    .navbar-nav .open .dropdown-menu{
        color: white !important;
    }

    .author-bio, #footer{
        border-bottom-color: " . $cores['cor_primaria']. " !important;
    }

    .comment-reply-link, #submit, .submit-captura:hover{
        border-color: " . $cores['cor_primaria']. " !important;  
    }

    /* COR SECUNDÁRIA*/

    .readmore, a.page-numbers, span.page-numbers, .captura, .btn1, .btn1:hover{
        background-color: " . $cores['cor_secundaria']. " !important;
    }

    a:hover{
        color: " . $cores['cor_secundaria']. " !important;
    }

    .widget-paginas a.destaque{
        border-bottom: 6px solid " . $cores['cor_secundaria']. " !important;
    }

    span.current{
        background: " . $cores['cor_primaria']. " !important;
    }
    
    /* COR TERCIARIA */
    
    .author-bio, #secondary .widget, #footer, .captura-artigos{
        background: " . $cores['cor_terciaria']. ";
    }
    
    /* COR QUARTENARIA */
    
    #secondary .widget h3, .widget-paginas a:hover{
        background: " . $cores['cor_quartenaria']. " !important;
    }
    
    .widget-paginas a, .captura-artigos, .widget .redes-sociais a, .widget_bioautor{
        border-color: " . $cores['cor_quartenaria']. " !important;
    }

    #top-aviso .btn, .read-more a, .bio-autor a, .widget-paginas a.destaque, .nav a:hover,
    .navbar-default .navbar-nav>li>a:hover, #header .dropdown-menu> li > a:hover,
    .navbar-default .navbar-nav > .open > a, .navbar-default .navbar-nav > .open > a:hover, .navbar-default .navbar-nav > .open > a:focus, .btn-primary,
    a.share-popup, #header .dropdown-menu> li > a, .comment-reply-link:hover, #submit:hover{
        color: white !important;
    }";

    if ($topo['cor_barra'] <> '')
       $css .= "#top-aviso{background: " . $topo['cor_barra'] . "!important;}";
    if ($topo['cor_link_cta'] <> '')
        $css .= "#top-aviso .btn{background: " . $topo['cor_link_cta'] . "!important;}";
    
    if ($cores['cor_fundo'] <> '')
        $css .= "body{background: " . $cores['cor_fundo'] . "!important;}";

    if ($cores['cor_fundo_artigos'] <> '')
        $css .= ".artigo{background: " . $cores['cor_fundo_artigos'] . "!important;}";

    if ($cores['cor_texto'] <> '')
        $css .= "body{color: " . $cores['cor_texto'] . "!important;}";

    if ($cores['cor_fundo_cabecalho'] <> '')
        $css .= "#header{background: " . $cores['cor_fundo_cabecalho'] . "!important;}";

    if ($captura['background'] <> ''){
        $image = wp_get_attachment_image_src($captura['background'], 'full');
        $css .= ".captura{background: url(" .  $image[0] . ")!important;}";
    }
    
    if ($fontes['fonte_titulos'])
        $css .= "h1, h2, h3, h4, h5, h6, .widget-paginas a{font-family: " . $fonts[$fontes['fonte_titulos']] . ", arial, sans-serif !important;}";
     if ($fontes['fonte_texto'])
        $css .= "body{font-family: " . $fonts[$fontes['fonte_texto']] . ", arial, sans-serif !important;}";
    if ($fontes['font_size_titulos']){
        $css .= "article h1{font-size: " . $fontes['font_size_titulos'] . "px !important;}";
        $css .= "body.home article h2{font-size: " . $fontes['font_size_titulos'] . "px !important;}";
    }
    if ($fontes['font_size_artigo'])
        $css .= "article p, article ol, article ul{font-size: " . $fontes['font_size_artigo'] . "px !important;}";


    if ($geral['ocultar_sidebar_mobile'])
        $css .= "@media (max-width: 768px){#secondary{display: none;}";
    if ($geral['thumb_mobile'])
        $css .= "@media (max-width: 450px){.thumb-img{width: 100%;}";
$css .= "</style>";
echo $css;
