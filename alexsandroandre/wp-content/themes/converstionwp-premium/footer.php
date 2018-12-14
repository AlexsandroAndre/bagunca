
<?php
	$geral = get_option('geral');
	$copy = $geral['copy-footer'];
	$scripts = $geral['scripts'];
	$fontes = get_option('fonts');
	$cont = 0;
	if (is_active_sidebar('footer-sidebar-1'))
		$cont++;
	if (is_active_sidebar('footer-sidebar-2'))
		$cont++;
	if (is_active_sidebar('footer-sidebar-3'))
		$cont++;
	if (is_active_sidebar('footer-sidebar-4'))
		$cont++;
	if ($cont == 0)
		$cont = 1;
	$col = 12 / $cont;
?>
</div><!-- #main -->

<footer id="footer" role="contentinfo">
    <div class="container">
        <a href="javascript:" id="return-to-top"><i class="fa fa-sort-asc"></i></a>
    	<?php if (is_active_sidebar('footer-sidebar-1')){ ?>
    	<div class="col-md-<?php echo $col; ?>">
    		<?php dynamic_sidebar('footer-sidebar-1'); ?>
    	</div>
    	<?php } ?>
    	<?php if (is_active_sidebar('footer-sidebar-2')){ ?>
    	<div class="col-md-<?php echo $col; ?>">
    		<?php dynamic_sidebar('footer-sidebar-2'); ?>
    	</div>
    	<?php } ?>
    	<?php if (is_active_sidebar('footer-sidebar-3')){ ?>
    	<div class="col-md-<?php echo $col; ?>">
    		<?php dynamic_sidebar('footer-sidebar-3'); ?>
    	</div>
    	<?php } ?>
    	<?php if (is_active_sidebar('footer-sidebar-4')){ ?>
    	<div class="col-md-<?php echo $col; ?>">
    		<?php dynamic_sidebar('footer-sidebar-4'); ?>
    	</div>
    	<?php } ?>
        <div class="site-info">
            <?php echo $copy; ?>
        </div><!-- .site-info -->
    </div>
</footer><!-- #footer -->
</div><!-- .container -->

<?php
require_once('inc/fonts.php');
$fonts = get_fonts();

if( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' )
    $http = "https://";
else
    $http = "http://";

if ($fontes['fonte_titulos'] <> '')
    wp_enqueue_style('font-titulo', $http . 'fonts.googleapis.com/css?family=' . $fonts[$fontes['fonte_titulos']] . ':400,700', array(), null, 'all');
if ($fontes['fonte_texto'] <> '')
    wp_enqueue_style('font-texto', $http . 'fonts.googleapis.com/css?family=' . $fonts[$fontes['fonte_texto']] . ':400,700', array(), null, 'all');
wp_enqueue_style('font-awesome', $http . 'maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css', array(), null, 'all');
wp_enqueue_script( 'fluidvids', get_template_directory_uri() . '/assets/js/fluidvids.js', array(), '1.0.0', true );
wp_enqueue_script( 'main', get_template_directory_uri() . '/assets/js/main.js', array(), '1.0.0', true );
echo $scripts;
wp_footer();
?>
</body>
</html>
