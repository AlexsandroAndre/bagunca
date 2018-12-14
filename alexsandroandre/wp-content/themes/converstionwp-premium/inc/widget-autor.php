<?php
    


/**
 * Widget Bio Autor
 * 
 * @author Jair Rebello <jair@rebello.blog.br>
 */
class bioAutor extends WP_Widget {

    /**
     * Construtor
     */
    public function bioAutor() {
        parent::__construct(false, $name = 'Bio Autor');
    }

    /**
     * Exibição final do Widget (já no sidebar)
     *
     * @param array $argumentos Argumentos passados para o widget
     * @param array $instancia Instância do widget
     */
    public function widget($argumentos, $instancia) {
        $geral = get_option('geral');
        if ($geral['tamanho_foto_bio'] <> '')
            $tamanho = $geral['tamanho_foto_bio'];
        else
            $tamanho = 120;
        $id = $instancia['autor'];
        echo $argumentos['before_widget'];
        echo '<div id="bio-autor">';
        if ($instancia['destaque'])
            $class = 'destaque';
   
        echo get_avatar($id, $tamanho);
        echo '<h4>Sobre o Autor</h4>';
        echo '<p>' . get_the_author_meta('description', $id) . '</p>';
        echo '<div class="read-more"><a href="' . $instancia['saiba-mais'] . '" class="btn readmore">Saber mais <i class="fa fa-mail-forward"></i></a></div>';
        echo '</div>';
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
        $users = array();
	    $roles = array('author', 'editor', 'administrator');

	    foreach ($roles as $role) :
	        $users_query = new WP_User_Query( array( 
	            'fields' => 'all_with_meta', 
	            'role' => $role, 
	            'orderby' => 'display_name'
	            ) );
	        $results = $users_query->get_results();
	        if ($results) $users = array_merge($users, $results);
	    endforeach;
        ?>
<p>Escolha o Autor: 
    <select name="<?php echo $this->get_field_name('autor'); ?>">
        <?php foreach($users as $u){
            $selected = '';
            if ($u->ID == $instancia['autor'])
                $selected = 'selected';
            echo '<option ' . $selected . ' value="' . $u->ID . '">' . $u->display_name . '</option>';
        } ?>
    </select></p>
    <p>Link da página do autor: <input class="titulo-top-artigos" name="<?php echo $this->get_field_name('saiba-mais'); ?>" id="<?php echo $this->get_field_id('saiba-mais'); ?>" type="text" value="<?php echo $instancia['saiba-mais']; ?>"/></p>
<?php
    }

}

add_action('widgets_init', create_function('', 'return register_widget("bioAutor");'));
?>