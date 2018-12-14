<?php

/**
 * Widget Top Artigos
 * 
 * @author Jair Rebello <jair@rebello.blog.br>
 */
class topArtigos extends WP_Widget {

    /**
     * Construtor
     */
    public function topArtigos() {
        parent::__construct(false, $name = 'Top Artigos');
    }

    /**
     * Exibição final do Widget (já no sidebar)
     *
     * @param array $argumentos Argumentos passados para o widget
     * @param array $instancia Instância do widget
     */
    public function widget($argumentos, $instancia) {
        wp_reset_postdata();
        echo $argumentos['before_widget'];

        echo $argumentos['before_title'] . '<i class="fa ' . $instancia['icone-fa'] . '"></i>' . $instancia['titulo'] . $argumentos['after_title'];

        if ($instancia['destaque'])
            $class = 'destaque';
        else
            $class = "no-destaque";
        $posts = array($instancia['artigo-1'], $instancia['artigo-2'], $instancia['artigo-3'], $instancia['artigo-4'], $instancia['artigo-5']);
        echo '<ul id="top-artigos">';
        foreach ($posts as $p) {
            $query = new WP_Query('p=' . $p);
            if ($query->have_posts())
                while ($query->have_posts()) {
                    $query->the_post();
                    echo '<li><i class="fa fa-arrow-circle-o-right"></i><a class="' . $class . '" href="' . get_the_permalink() . '">' . get_the_title() . '</a></li>';
                }
        }
        echo '</ul>';
        echo $argumentos['after_widget'];
    }

    /**
     * Salva os dados do widget no banco de dados
     *
     * @param array $nova_instancia Os novos dados do widget (a serem salvos)
     * @param array $instancia_antiga Os dados antigos do widget
     * 
     * @return array $instancia Dados atualizados a serem salvos no banco de dados
     */
    public function update($nova_instancia, $instancia_antiga) {
        $instancia = array_merge($instancia_antiga, $nova_instancia);
        return $instancia;
    }

    /**
     * Formulário para os dados do widget (exibido no painel de controle)
     *
     * @param array $instancia Instância do widget
     */
    public function form($instancia) {
        ?>
        <p><input name="<?php echo $this->get_field_name('icone-fa'); ?>" id="<?php echo $this->get_field_id('icone-fa'); ?>" type="hidden" value="<?php echo $instancia['icone-fa']; ?>"/></p>  
        <div><a id="a-<?php echo $this->get_field_id('icone-fa'); ?>" class="geticon" href="#data"><i class="fa  <?php echo $instancia['icone-fa']; ?> escolher-icone"></i>Escolher ícone</a></div>
        <p>Título: <input class="titulo-top-artigos" name="<?php echo $this->get_field_name('titulo'); ?>" id="<?php echo $this->get_field_id('titulo'); ?>" type="text" value="<?php echo $instancia['titulo']; ?>"/></p>
        <?php
        $args = array('posts_per_page' => 10000, 'orderby' => 'post_title', 'order' => 'ASC',);
        $posts = get_posts($args);
        ?>        

        <p>Artigo 1: <select id="select-pages" name="<?php echo $this->get_field_name('artigo-1'); ?>">
        <?php
        foreach ($posts as $p) {
            $selected = '';
            if ($p->ID == $instancia['artigo-1'])
                $selected = 'selected';
            echo '<option ' . $selected . ' value="' . $p->ID . '">' . $p->post_title . '</option>';
        }
        ?>
            </select></p>
        <p>Artigo 2: <select id="select-pages" name="<?php echo $this->get_field_name('artigo-2'); ?>">
                <?php
                foreach ($posts as $p) {
                    $selected = '';
                    if ($p->ID == $instancia['artigo-2'])
                        $selected = 'selected';
                    echo '<option ' . $selected . ' value="' . $p->ID . '">' . $p->post_title . '</option>';
                }
                ?>
            </select></p>
        <p>Artigo 3: <select id="select-pages" name="<?php echo $this->get_field_name('artigo-3'); ?>">
                <?php
                foreach ($posts as $p) {
                    $selected = '';
                    if ($p->ID == $instancia['artigo-3'])
                        $selected = 'selected';
                    echo '<option ' . $selected . ' value="' . $p->ID . '">' . $p->post_title . '</option>';
                }
                ?>
            </select></p>
        <p>Artigo 4: <select id="select-pages" name="<?php echo $this->get_field_name('artigo-4'); ?>">
                <?php
                foreach ($posts as $p) {
                    $selected = '';
                    if ($p->ID == $instancia['artigo-4'])
                        $selected = 'selected';
                    echo '<option ' . $selected . ' value="' . $p->ID . '">' . $p->post_title . '</option>';
                }
                ?>
            </select></p>
        <p>Artigo 5: <select id="select-pages" name="<?php echo $this->get_field_name('artigo-5'); ?>">
        <?php
        foreach ($posts as $p) {
            $selected = '';
            if ($p->ID == $instancia['artigo-5'])
                $selected = 'selected';
            echo '<option ' . $selected . ' value="' . $p->ID . '">' . $p->post_title . '</option>';
        }
        ?>
            </select></p>

        <?php
        $widget['destaque'] = (boolean) $instancia['destaque'];
        if ($widget['destaque'])
            $value = '1';
        else
            $value = '0';
        ?>
        <p>Destacar?
            <select name="<?php echo $this->get_field_name('destaque'); ?>" id="<?php echo $this->get_field_id('destaque'); ?>">
                <option value="1" <?php if ($value == '1') echo 'selected'; ?>>Sim</option>
                <option value="0" <?php if ($value == '0') echo 'selected'; ?>>Não</option>
            </select>
        </p>


        <?php
    }

}

add_action('widgets_init', create_function('', 'return register_widget("topArtigos");'));
?>