<?php $topo = get_option( 'caixa_topo' );
if ($topo['exibir_caixa_topo'] == '1') {?>

<div id="top-aviso">
    <div class="container">
        <?php echo $topo['texto_topo'];?> <a class="btn btn1" href="<?php echo $topo['link_cta'];?>" target="_blank"><?php echo $topo['texto_cta'];?>  <i class="fa fa-arrow-circle-right"></i></a>
    </div>
    <a href="#" id="close-top-aviso"><i class="fa fa-close"></i></a>
</div>

<?php if ($topo['fixar_caixa_topo'] == '1'){?>
	<script>
		var j = jQuery.noConflict();
		var nav = j('#top-aviso');
		j(window).scroll(function () {
		if (j(this).scrollTop() > 50) {
			nav.addClass("top-aviso-fixed");
		} else {
			nav.removeClass("top-aviso-fixed");
		}
	});
	</script>
<?php } ?>
<?php } ?>
