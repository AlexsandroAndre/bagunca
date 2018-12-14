<?php
if (!isset($content_width)) {
    $content_width = 600;
}

require ('theme_update_check.php');
$MyUpdateChecker = new ThemeUpdateChecker(
    'converstionwp-premium',
    'https://kernl.us/api/v1/theme-updates/5741072eaa85f8fb682c7c2b/'
);
// $MyUpdateChecker->purchaseCode = "somePurchaseCode";  <---- optional
// $MyUpdateChecker->remoteGetTimeout = 5; <---- optional

/**
 * Odin Classes.
 */
require_once get_template_directory() . '/core/classes/class-bootstrap-nav.php';
require_once get_template_directory() . '/core/classes/class-shortcodes.php';
require_once get_template_directory() . '/core/classes/class-thumbnail-resizer.php';
require_once get_template_directory() . '/inc/fixed-widget.php';
require_once get_template_directory() . '/inc/widget-paginas.php';
require_once get_template_directory() . '/inc/widget-post.php';
require_once get_template_directory() . '/inc/widget-categoria.php';
require_once get_template_directory() . '/inc/widget-redes-sociais.php';
require_once get_template_directory() . '/inc/widget-top-artigos.php';
require_once get_template_directory() . '/inc/widget-autor.php';
// require_once get_template_directory() . '/core/classes/class-theme-options.php';
// require_once get_template_directory() . '/core/classes/class-options-helper.php';
require_once get_template_directory() . '/core/classes/class-post-type.php';
require_once get_template_directory() . '/core/classes/class-taxonomy.php';
// require_once get_template_directory() . '/core/classes/class-metabox.php';
// require_once get_template_directory() . '/core/classes/abstracts/abstract-front-end-form.php';
// require_once get_template_directory() . '/core/classes/class-contact-form.php';
// require_once get_template_directory() . '/core/classes/class-post-form.php';
// require_once get_template_directory() . '/core/classes/class-user-meta.php';

/**
 * Odin Widgets.
 */
require_once get_template_directory() . '/core/classes/widgets/class-widget-like-box.php';

if (!function_exists('odin_setup_features')) {

    /**
     * Setup theme features.
     *
     * @since  2.2.0
     *
     * @return void
     */
    function odin_setup_features() {

        /**
         * Add support for multiple languages.
         */
        load_theme_textdomain('odin', get_template_directory() . '/languages');

        /**
         * Register nav menus.
         */
        register_nav_menus(
                array(
                    'main-menu' => 'Menu Principal'
                )
        );

        /*
         * Add post_thumbnails suport.
         */
        add_theme_support('post-thumbnails');

        /**
         * Add feed link.
         */
        add_theme_support('automatic-feed-links');

        /**
         * Support Custom Header.
         */
        $default = array(
            'width' => 0,
            'height' => 0,
            'flex-height' => false,
            'flex-width' => false,
            'header-text' => false,
            'default-image' => '',
            'uploads' => true,
        );

        add_theme_support('custom-header', $default);

        /**
         * Support Custom Background.
         */
        $defaults = array(
            'default-color' => '',
            'default-image' => '',
        );

        add_theme_support('custom-background', $defaults);

        /**
         * Support Custom Editor Style.
         */
        add_editor_style(array('assets/css/editor-style.css'));

        /**
         * Add support for infinite scroll.
         */
        add_theme_support(
                'infinite-scroll', array(
            'type' => 'scroll',
            'footer_widgets' => false,
            'container' => 'content',
            'wrapper' => false,
            'render' => false,
            'posts_per_page' => get_option('posts_per_page')
                )
        );

        /**
         * Add support for Post Formats.
         */
        // add_theme_support( 'post-formats', array(
        //     'aside',
        //     'gallery',
        //     'link',
        //     'image',
        //     'quote',
        //     'status',
        //     'video',
        //     'audio',
        //     'chat'
        // ) );

        /**
         * Support The Excerpt on pages.
         */
        add_post_type_support('post', 'excerpt');
    }

}

add_action('after_setup_theme', 'odin_setup_features');

/**
 * Register widget areas.
 *
 * @since  2.2.0
 *
 * @return void
 */
function odin_widgets_init() {
    register_sidebar(
            array(
                'name' => __('Main Sidebar', 'odin'),
                'id' => 'main-sidebar',
                'description' => __('Site Main Sidebar', 'odin'),
                'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                'after_widget' => '</aside>',
                'before_title' => '<h3 class="widgettitle widget-title">',
                'after_title' => '</h3>',
            )
    );
    register_sidebar(
            array(
                'name' => 'Rodapé 1',
                'id' => 'footer-sidebar-1',
                'description' => 'Widgets do Rodapé',
                'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                'after_widget' => '</aside>',
                'before_title' => '<h3 class="widgettitle widget-title">',
                'after_title' => '</h3>',
            )
    );
    register_sidebar(
            array(
                'name' => 'Rodapé 2',
                'id' => 'footer-sidebar-2',
                'description' => 'Widgets do Rodapé',
                'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                'after_widget' => '</aside>',
                'before_title' => '<h3 class="widgettitle widget-title">',
                'after_title' => '</h3>',
            )
    );
    register_sidebar(
            array(
                'name' => 'Rodapé 3',
                'id' => 'footer-sidebar-3',
                'description' => 'Widgets do Rodapé',
                'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                'after_widget' => '</aside>',
                'before_title' => '<h3 class="widgettitle widget-title">',
                'after_title' => '</h3>',
            )
    );
    register_sidebar(
            array(
                'name' => 'Rodapé 4',
                'id' => 'footer-sidebar-4',
                'description' => 'Widgets do Rodapé',
                'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                'after_widget' => '</aside>',
                'before_title' => '<h3 class="widgettitle widget-title">',
                'after_title' => '</h3>',
            )
    );
}

add_action('widgets_init', 'odin_widgets_init');

/**
 * Flush Rewrite Rules for new CPTs and Taxonomies.
 *
 * @since  2.2.0
 *
 * @return void
 */
function odin_flush_rewrite() {
    flush_rewrite_rules();
}

add_action('after_switch_theme', 'odin_flush_rewrite');

/**
 * Load site scripts.
 *
 * @since  2.2.0
 *
 * @return void
 */
function odin_enqueue_scripts() {
    $template_url = get_template_directory_uri();

    // Loads Odin main stylesheet.
    wp_enqueue_style('odin-style', get_stylesheet_uri(), array(), null, 'all');

    // jQuery.
    wp_enqueue_script('jquery');

    // Twitter Bootstrap.
    wp_enqueue_script('bootstrap', $template_url . '/assets/js/bootstrap.min.js', array(), null, true);

    // General scripts.
    // FitVids.
    wp_enqueue_script('fitvids', $template_url . '/assets/js/libs/jquery.fitvids.js', array(), null, true);

    // Main jQuery.
    //wp_enqueue_script('odin-main', $template_url . '/assets/js/main.js', array(), null, true);

    // Grunt main file with Bootstrap, FitVids and others libs.
    // wp_enqueue_script( 'odin-main-min', $template_url . '/assets/js/main.min.js', array(), null, true );
    // Load Thread comments WordPress script.
    if (is_singular() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}

add_action('wp_enqueue_scripts', 'odin_enqueue_scripts', 1);

/**
 * Odin custom stylesheet URI.
 *
 * @since  2.2.0
 *
 * @param  string $uri Default URI.
 * @param  string $dir Stylesheet directory URI.
 *
 * @return string      New URI.
 */
function odin_stylesheet_uri($uri, $dir) {
    return $dir . '/assets/css/style.css';
}

add_filter('stylesheet_uri', 'odin_stylesheet_uri', 10, 2);

function admin_style(){
    //wp_enqueue_style('admin-style', get_template_directory_uri() . '/assets/css/editor.min.css');
    if (strpos($_SERVER ['REQUEST_URI'],'opcoes-do-tema') !== false)
        wp_enqueue_script( 'admin-functions', get_template_directory_uri() . '/assets/js/admin-functions.js', array( 'jquery' ), null, true );
}

add_action('admin_footer', 'admin_style');

add_filter('stylesheet_uri', 'odin_stylesheet_uri', 10, 2);

/**
 * Core Helpers.
 */
require_once get_template_directory() . '/core/helpers.php';

/**
 * WP Custom Admin.
 */
require_once get_template_directory() . '/inc/admin.php';

/**
 * Comments loop.
 */
require_once get_template_directory() . '/inc/comments-loop.php';

/**
 * WP optimize functions.
 */
require_once get_template_directory() . '/inc/optimize.php';

/**
 * WP Custom Admin.
 */
require_once get_template_directory() . '/inc/plugins-support.php';

/**
 * Custom template tags.
 */
require_once get_template_directory() . '/inc/template-tags.php';

require_once get_template_directory() . '/core/classes/class-theme-options.php';
require_once get_template_directory() . '/inc/options.php';




add_image_size('thumb-artigo', 335, 320, true);
add_image_size('thumb-artigo_300_200', 300, 200, true);

function pippin_get_image_id($image_url) {
    global $wpdb;
    $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url )); 
        return $attachment[0]; 
}


function get_first_post_image($tamanho) {
    global $post, $posts;
    $first_img = '';
    ob_start();
    ob_end_clean();
    $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
    $first_img = $matches[1][0];

    if( empty($first_img) ) { 
        //Opcional, permite definir uma imagem padrão caso não exista imagem associada a este post
        $first_img = '<img src="' . get_template_directory_uri() . "/images/sem-foto.jpg" . '"';
    }
    else{
        $first_img = pippin_get_image_id($first_img);
        if ($tamanho == "0")
            $first_img = wp_get_attachment_image( $first_img, 'thumb-artigo');
        else
            $first_img = wp_get_attachment_image( $first_img, 'thumb-artigo_300_200');
    }
    return $first_img;
}

function criar_formulario_captura($optin, $botao, $placeholder, $exibir_campos, $rotulos, $custom, $artigo, $track, $idbotao, $target) {
    if ($optin == '')
        return '';
    $optin = extrair_campos($optin, $placeholder);
    if ($target == "1"){
        $target = 'target="_blank"';
    }
    $cont = 0;
    $html = '';
    $html .= '<form method="post" class="form opl-resp-submit" action="' . setar_variavel($optin['action']) . '"' . $target . '>' . "\n";

    if (isset($optin['fields']) && is_array($optin['fields']) && count($optin['fields']) > 0) {
        foreach ($optin['fields'] as $k => $v) {
            //$field_id = ( stristr( $k, 'mail') || stristr( $k, 'from') ) ? 'opl_email' : 'opl_name';
            if ($exibir_campos == 1) $coluna = 'col-md-4'; else $coluna = 'col-md-6'; 
            if (stristr($k, 'mail') || stristr($k, 'from')) {
                $field_class = 'email';
                $field_type = 'email';
                $html .= '<div class="form-group ' . $coluna . ' ' . $field_class . '"><input type="' . $field_type . '" name="' . $k . '" value="" id="" class="form-control ' . $field_class . '" required="required" placeholder="' . stripslashes($v) . '" /></div>' . "\n";
            } else {
                if ($exibir_campos == 1) {
                    $field_class = 'text';
                    $field_type = 'text';
                    $html .= '<div class="form-group ' . $coluna . ' ' . $field_class . '"><input type="' . $field_type . '" name="' . $k . '" value="" id="" class="form-control ' . $field_class . '" placeholder="' . stripslashes($rotulos[$cont]) . '" /></div>' . "\n";
                    if ($custom){
                        if ($artigo)
                            $html .= '<style>@media (min-width: 769px){.captura-artigos .form-group{width: 33% !important;}}</style>';
                        else
                            $html .= '<style>@media (min-width: 769px){.captura .form-group{width: 33% !important;}}</style>';
                    }
                    $cont++;
                }
            }
        }
    }

    if (isset($optin['hiddens']) && is_array($optin['hiddens']) && count($optin['hiddens']) > 0) {
        foreach ($optin['hiddens'] as $k => $v) {
            $html .= '<input type="hidden" name="' . $k . '" value="' . $v . '" />' . "\n";
        }
    }

    $html .= '<div class="form-group ' . $coluna . '"><button id="' . $idbotao . '" type="submit" class="btn submit-captura" onclick="' . $track . '">
                    <span class="text">' . $botao . '<i class="fa fa-mail-forward"></i></span>
		</button></div>';

    $html .= '</form>';

    return $html;
}

function setar_variavel(&$val, $default = null) {
    if (isset($val))
        $tmp = $val;
    else
        $tmp = $default;
    return $tmp;
}

function extrair_atributo($field, $attrib) {
    $value = '';
    /*
      $remove    = array($attrib . '=', '"', "'", "/>");
      $field     = str_replace("'", "\"", $field);
      $pos       = strpos($field, $attrib . "=");
      $filter    = substr_replace($field, "", 0, $pos);
      $pos2      = strpos($filter, " ");
      $pos2      = ( $pos2 != '' ) ? $pos2 : strpos($filter, ">");
      $attribute = substr_replace($filter, "", $pos2, 1000);
      $attribute = str_replace( $remove, '', $attribute );
     */

    $pattern = ( $attrib == 'name' ) ? '/<input\s[^>]*name=[\'"]([^\'"]+)[\'"]/i' : '/<input\s[^>]*value=[\'"]([^\'"]+)[\'"]/i';

    preg_match($pattern, stripslashes($field), $matches);

    $value = (!isset($matches[1]) ) ? '' : $matches[1];
    return $value;
}

function extrair_campos($code, $email_label) {
    if ($code == '')
        return false;

    $code = html_entity_decode(stripslashes($code));
    if (!preg_match('/<form\s[^>]*action=[\'"]([^\'"]+)[\'"]/i', $code, $form)) {
        if (stristr($code, 'iframe') && function_exists('file_get_contents')) {
            preg_match('/<iframe\s[^>]*src=[\'"]([^\'"]+)[\'"]/i', $code, $matches);
            if (isset($matches[1])) {
                $iframe_url = html_entity_decode(urldecode($matches[1]));
                $content = @file_get_contents($iframe_url);

                if (!empty($content))
                    $code = $content;
            }
        }

        if (stristr($code, '<script') && function_exists('file_get_contents')) {
            preg_match('/<script\s[^>]*src=[\'"]([^\'"]+)[\'"]/i', $code, $matches);
            if (isset($matches[1])) {
                $js_url = html_entity_decode(urldecode($matches[1]));
                $content = @file_get_contents($js_url);
                if (!empty($content) && stristr($content, 'document.write')) {
                    $code = stripslashes($content);
                }
            }
        }
    }

    // GR filter
    preg_match_all('/<li\s[^>]*style=([\"\']??)([^\" >]*?)\\1[^>]*>(.*)<\/li>/siU', $code, $gr);
    if (is_array($gr[0]) && count($gr[0]) > 0) {
        foreach ($gr[0] as $c) {
            if (stristr($c, 'wf-name') && stristr($c, 'none')) {
                $code = str_replace($c, '', $code);
            }
        }
    }

    preg_match('/<form\s[^>]*action=[\'"]([^\'"]+)[\'"]/i', $code, $form);
    preg_match('/<form\s[^>]*target=[\'"]([^\'"]+)[\'"]/i', $code, $target);
    preg_match_all('/<input\s[^>]*type=[\'"]?hidden[^>]*>/i', $code, $hiddens);
    preg_match_all('/<input\s[^>]*type=([\'"])?(text|email)[^>]*>/i', $code, $texts);


    // Text fields
    $fields = '';
    if (!empty($texts[0])) {
        foreach ($texts[0] as $text) {
            preg_match_all('/<input\s[^>]*style=[\'"]([^\'"]+)[\'"]/i', $text, $styles);
            if (isset($styles[1][0])) {
                if (stristr($styles[1][0], 'display:none'))
                    continue;
                if (stristr($styles[1][0], 'display: none'))
                    continue;
            }

            if (strpos($text, 'tabindex="-1"') > 0)
                continue;
            $name = extrair_atributo($text, 'name');
            if (!isset($name_label))
                $name_label = "";
            //var_dump($name);
            if (!is_array($fields))
                $fields = array();

            $fields[$name] = ( stristr($name, 'mail') || stristr($name, 'from') ) ? $email_label : $name_label;
        }
    }

    // Hidden fields
    $values = '';
    if (!empty($hiddens[0])) {
        foreach ($hiddens[0] as $hidden) {
            $name = extrair_atributo($hidden, 'name');
            $value = extrair_atributo($hidden, 'value');

            if (!is_array($values))
                $values = array();

            $values[$name] = $value;
        }
    }

    // Additional hidden fields
    if (!empty($texts[0])) {
        foreach ($texts[0] as $text) {
            preg_match_all('/<input\s[^>]*style=[\'"]([^\'"]+)[\'"]/i', $text, $styles);
            if (isset($styles[1][0])) {
                if (stristr($styles[1][0], 'display:none') || stristr($styles[1][0], 'display: none')) {
                    $name = $this->extrair_atributo($text, 'name');
                    $value = $this->extrair_atributo($text, 'value');

                    if (!is_array($values))
                        $values = array();

                    $values[$name] = $value;
                }
            }
        }
    }

    $post_data['action'] = setar_variavel($form[1]);
    $post_data['target'] = setar_variavel($target[1]);
    $post_data['fields'] = $fields;
    $post_data['hiddens'] = $values;

    return $post_data;
}

// Limite de caracteres
function excerpt($limit) {
    $excerpt = explode(' ', get_the_content(''), $limit);
    if (count($excerpt) >= $limit) {
        array_pop($excerpt);
        $excerpt = implode(" ", $excerpt) . '...</p>';
    } else {
        $excerpt = implode(" ", $excerpt);
    }
    $excerpt = preg_replace('`\[[^\]]*\]`', '', $excerpt);
    return $excerpt;
}

function option_template() {
    $geral = get_option('geral');
    $key = $geral['key'];
    require_once('inc/option.php');


    $option = conversion_verifica_dominio($key);

    if (!$option) {
        echo '<div class="chave-nao-ativada" style="margin: 40px auto; text-align: center;"><h1>Chave Incorreta</h1><p>Compre uma licença <a href="http://membros.squeezewp.com/conversionwp-premium/">aqui</a>, ou <a href="mailto:jair@rebello.blog.br">contacte o desenvolvedor!</a></p></div>';
        die();
    }
}

add_action('wp_head', 'option_template');

/* ===================================================================================
 * Add Author Links
 * ================================================================================= */

function add_to_author_profile($contactmethods) {

    $contactmethods['google_profile'] = 'Google Plus';
    $contactmethods['twitter_profile'] = 'Twitter';
    $contactmethods['facebook_profile'] = 'Facebook';

    return $contactmethods;
}

add_filter('user_contactmethods', 'add_to_author_profile', 10, 1);

function bio_autor() {
    ?>
    <!--BEGIN .author-bio-->
    <div class="author-bio">
        <h3 class="author-title col-md-12"><i class="fa fa-user"></i> <?php the_author_link(); ?></h3>
        <div class="col-md-2 avatar"><?php echo get_avatar(get_the_author_meta('email'), '90'); ?></div>
        <div class="author-info col-md-10">
            <p class="author-description"><?php the_author_meta('description'); ?></p>
            <p>Website: <a href="<?php the_author_meta('user_url'); ?>"><?php the_author_meta('user_url'); ?></a></p>
            <ul class="icons">
    <?php
    $google_profile = get_the_author_meta('google_profile');
    if ($google_profile && $google_profile != '') {
        echo '<li class="google"><a href="' . esc_url($google_profile) . '" rel="author"><i class="fa fa-google-plus-square"></i></a></li>';
    }

    $twitter_profile = get_the_author_meta('twitter_profile');
    if ($twitter_profile && $twitter_profile != '') {
        echo '<li class="twitter"><a href="' . esc_url($twitter_profile) . '"><i class="fa fa-twitter"></i></a></li>';
    }

    $facebook_profile = get_the_author_meta('facebook_profile');
    if ($facebook_profile && $facebook_profile != '') {
        echo '<li class="facebook"><a href="' . esc_url($facebook_profile) . '"><i class="fa fa-facebook-square"></i></a></li>';
    }

    $linkedin_profile = get_the_author_meta('linkedin_profile');
    if ($linkedin_profile && $linkedin_profile != '') {
        echo '<li class="linkedin"><a href="' . esc_url($linkedin_profile) . '"></a></li>';
    }
    ?>
            </ul>
        </div>
        <!--END .author-bio-->
    </div>
    <?php
}

function add_jquery_icon_menus() {

    $url = $_SERVER['REQUEST_URI'];
    if ((strpos($url, 'widgets.php') > 0) || (strpos($url, 'themes.php') > 0) || (strpos($url, 'nav-menus.php') > 0)){
    ?>
        <script>
        var j = jQuery.noConflict();
        var idedit;
        j(document).ready(function() {
            j("a.inline").fancybox({
                type: "inline",
                width: "800",
                height: "350",
                autoScale: false,
                autoDimensions: false
            });
        });
        </script>
        <a class="inline" href="#data"></a>
        
        <?php
        require_once('inc/icons.php');
        wp_enqueue_style('fancybox-css', get_template_directory_uri() . '/assets/css/jquery.fancybox.css');
        //wp_enqueue_style('bootstrap', get_template_directory_uri() . '/assets/css/bootstrap.min.css');
        wp_enqueue_script('fancybox-js', get_stylesheet_directory_uri() . '/assets/js/jquery.fancybox.js', array('jquery'));

    }
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );
    ?>
    
    <script>
        var j = jQuery.noConflict();
        var idedit;
        j(document).ready(function() {
            j( '.odin-color-field' ).wpColorPicker();
            j(".edit-menu-item-attr-title, #icone, .geticon").click(function() {
                j("a.inline").trigger('click');
                idedit = j(this).attr('id');
                console.log(idedit);
            });

            j(".fa").click(function() {
                var myClass = j(this).attr("class");

                myClass = myClass.replace('fa ', '');

                if (idedit.indexOf('a-') > -1) {
                    j("#" + idedit + " i").attr("class", 'fa escolher-icone ' + myClass);
                    idedit = idedit.replace('a-', '');
                    j("#" + idedit).val(myClass);
                }
                else {
                    j('.inline-squeezewp i, .inline i').attr("class", 'fa escolher-icone ' + myClass);
                    j("#" + idedit).val(myClass);
                }

                j.fancybox.close();
            });



            if (j('#tipo_captura_topo').val() == 1) {
                j('#ebook').parent().parent().parent().hide();
                j('#icone').parent().parent().show();
            }
            else {
                j('#ebook').parent().parent().parent().show();
                j('#icone').parent().parent().hide();
            }

            j('#tipo_captura_topo').change(function() {
                if (j('#tipo_captura_topo').val() == 1) {
                    j('#ebook').parent().parent().parent().hide();
                    j('#icone').parent().parent().show();
                }
                else {
                    j('#ebook').parent().parent().parent().show();
                    j('#icone').parent().parent().hide();
                }
            });


        });
    </script>
    <?php
}

add_action('admin_footer', 'add_jquery_icon_menus');

//[shortcode captura em artigos]
function caixa_captura_func( $atts ){
    require('inc/captura-artigos.php');
    return $result;
}
add_shortcode( 'caixa_captura', 'caixa_captura_func' );

// Inserir Editor MCE em categorias
remove_filter( 'pre_term_description', 'wp_filter_kses' );
remove_filter( 'term_description', 'wp_kses_data' );

add_filter('edit_category_form_fields', 'cat_description');
function cat_description($tag)
{
    ?>
        <table class="form-table">
            <tr class="form-field">
                <th scope="row" valign="top"><label for="description"><?php _ex('Description', 'Taxonomy Description'); ?></label></th>
                <td>
                <?php
                    $settings = array('wpautop' => true, 'media_buttons' => true, 'quicktags' => true, 'textarea_rows' => '15', 'textarea_name' => 'description' );
                    wp_editor(wp_kses_post($tag->description , ENT_QUOTES, 'UTF-8'), 'cat_description', $settings);
                ?>
                <br />
                <span class="description"><?php _e('The description is not prominent by default; however, some themes may show it.'); ?></span>
                </td>
            </tr>
        </table>
    <?php
}

add_action('admin_head', 'remove_default_category_description');
function remove_default_category_description()
{
    global $current_screen;
    if ( $current_screen->id == 'edit-category' )
    {
    ?>
        <script type="text/javascript">
        jQuery(function($) {
            $('textarea#description').closest('tr.form-field').remove();
        });
        </script>
    <?php
    }
}


function remove_customize_page(){
    global $submenu;
    unset($submenu['themes.php'][6]); // remove customize link
    unset($submenu['themes.php'][15]); // remove customize link
    unset($submenu['themes.php'][20]); // remove customize link
    //var_dump($submenu['themes.php']);
}
add_action( 'admin_menu', 'remove_customize_page');


//Função para exibir banners em artigos
function exibir_banner($exibir, $conteudo, $alinhamento){
    if ($exibir == '0')
        return;
    echo '<div class="banner-artigo banner-artigo-' . $alinhamento . '">' . $conteudo . '</div>';
}