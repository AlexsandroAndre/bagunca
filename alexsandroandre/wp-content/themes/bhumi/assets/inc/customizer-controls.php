<?php /*font customizer controls */

if( ! class_exists('WP_Customize_Control') ){
  return NULL;
}

class Bhumi_Support_Control extends WP_Customize_Control {

      /**
       * Render the content on the theme customizer page
       */
       public $type = "bhumi-support";

         public function enqueue() {
         wp_enqueue_style( 'customizer-sort-style', trailingslashit( get_template_directory_uri() ) . 'assets/inc/customizer-control.css' );
        /* js */
      }

      public function render_content() {
         //Add Theme instruction, Support Forum, Demo Link, Rating Link

         ?><p>
              <a class="bhumi_support" target="_blank" href="<?php echo  esc_url('https://codethemes.co/bhumi-lite-documentation/') ?>"><span class="dashicons dashicons-book-alt"></span><?php echo  __('Documentation', 'bhumi') ?></a>

              <a class="bhumi_support" target="_blank" href="<?php echo  esc_url('https://codethemes.co/my-tickets/') ?>"><span class="dashicons dashicons-edit"></span><?php echo   __('Create a Ticket', 'bhumi') ?></a>

              <a class="bhumi_support" target="_blank" href="<?php echo  esc_url('http://themepalace.com/downloads/bhumi-pro/') ?>"><span class="dashicons dashicons-star-filled"></span><?php echo   __('Buy Premium', 'bhumi') ?></a>

              <a class="support-image bhumi_support" target="_blank" href="<?php echo  esc_url('https://codethemes.co/support/#customization_support') ?>"><img src = "<?php echo esc_url(get_template_directory_uri() . '/assets/images/wparmy.png') ?>" /> <?php echo __('Request Customization', 'bhumi'); ?></a>
            </p>
         <?php
      }
}