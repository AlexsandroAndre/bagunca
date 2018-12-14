<?php
/**
 * The template for displaying Search Form.
 *
 * @package Odin
 * @since 2.2.0
 */
?>

<form method="get" id="searchform" class="" action="<?php echo esc_url( home_url( '/' ) ); ?>" role="search">
	<div class="form-group">
		<label for="s" class="sr-only"><?php _e( 'Search', 'odin' ); ?></label>
                <input type="text" class="form-control" name="s" id="s" placeholder="Buscar por: " />
	</div>
</form>
