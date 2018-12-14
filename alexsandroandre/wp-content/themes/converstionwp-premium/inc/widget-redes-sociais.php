<?php

/**
 * Widget Redes Sociais
 * 
 * @author Jair Rebello <jair@rebello.blog.br>
 */
class redesSociais extends WP_Widget {

    /**
     * Construtor
     */
    public function redesSociais() {
        parent::__construct(false, $name = 'Redes Sociais');
    }

    /**
     * Exibição final do Widget (já no sidebar)
     *
     * @param array $argumentos Argumentos passados para o widget
     * @param array $instancia Instância do widget
     */
    public function widget($argumentos, $instancia) {
        echo $argumentos['before_widget'];
        echo '<div class="redes-sociais">';
        if ($instancia['facebook'] <> '')
            echo '<a class="facebook" target="_blank" href="' . $instancia['facebook'] .'"><i class="fa fa-facebook"></i></a>';
        if ($instancia['twitter'] <> '')
        echo '<a class="twitter-side" target="_blank" href="' . $instancia['twitter'] .'"><i class="fa fa-twitter"></i></a>';
        if ($instancia['plus'] <> '')
            echo '<a class="plus" target="_blank" href="' . $instancia['plus'] .'"><i class="fa fa-google-plus"></i></a>';
        if ($instancia['youtube'] <> '')
            echo '<a class="youtube" target="_blank" href="' . $instancia['youtube'] .'"><i class="fa fa-youtube"></i></a>';
        if ($instancia['instagram'] <> '')
            echo '<a class="instagram" target="_blank" href="' . $instancia['instagram'] .'"><i class="fa fa-instagram"></i></a>';
        if ($instancia['pinterest'] <> '')
            echo '<a class="pinterest" target="_blank" href="' . $instancia['pinterest'] .'"><i class="fa fa-pinterest"></i></a>';
        if ($instancia['snapchat'] <> '')
            echo '<a class="snapchat" target="_blank" href="' . $instancia['snapchat'] .'"><i class="fa fa-snapchat"></i></a>';
        if ($instancia['telegram'] <> '')
            echo '<a class="telegram" target="_blank" href="' . $instancia['telegram'] .'"><i class="fa fa-paper-plane"></i></a>';
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
        ?>
        <p>Coloque o endereço completo de suas redes sociais (com http://)</p>
        <p>Facebook: <br /><input type="text" name="<?php echo $this->get_field_name('facebook'); ?>" id="<?php echo $this->get_field_id('facebook'); ?>" value="<?php echo $instancia['facebook']; ?>" /></p>
        <p>Twitter: <br /><input type="text" name="<?php echo $this->get_field_name('twitter'); ?>" id="<?php echo $this->get_field_id('twitter'); ?>" value="<?php echo $instancia['twitter']; ?>" /></p>
        <p>Google Plus: <br /><input type="text" name="<?php echo $this->get_field_name('plus'); ?>" id="<?php echo $this->get_field_id('plus'); ?>" value="<?php echo $instancia['plus']; ?>" /></p>
        <p>Youtube: <br /><input type="text" name="<?php echo $this->get_field_name('youtube'); ?>" id="<?php echo $this->get_field_id('youtube'); ?>" value="<?php echo $instancia['youtube']; ?>" /></p>
        <p>Instagram: <br /><input type="text" name="<?php echo $this->get_field_name('instagram'); ?>" id="<?php echo $this->get_field_id('instagram'); ?>" value="<?php echo $instancia['instagram']; ?>" /></p>
        <p>Pinterest: <br /><input type="text" name="<?php echo $this->get_field_name('pinterest'); ?>" id="<?php echo $this->get_field_id('pinterest'); ?>" value="<?php echo $instancia['pinterest']; ?>" /></p>
        <p>Snapchat: <br /><input type="text" name="<?php echo $this->get_field_name('snapchat'); ?>" id="<?php echo $this->get_field_id('snapchat'); ?>" value="<?php echo $instancia['snapchat']; ?>" /></p>
        <p>Telegram: <br /><input type="text" name="<?php echo $this->get_field_name('telegram'); ?>" id="<?php echo $this->get_field_id('telegram'); ?>" value="<?php echo $instancia['telegram']; ?>" /></p>
        <?php
    }

}

add_action('widgets_init', create_function('', 'return register_widget("redesSociais");'));