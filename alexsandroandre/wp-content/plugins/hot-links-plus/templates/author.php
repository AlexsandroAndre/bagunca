
<div class="wrap">

<div class="alert alert-info">

<h3>Dados Informativos:</h3>

<ul>
<li>
- <strong>Seu IP Atual:</strong> <?php echo $_SERVER["REMOTE_ADDR"];?>
</li>
<li>
- <strong>Versão do PHP:</strong> <?php echo phpversion();?>
</li>
<li>
- <strong>Sistema Operacional:</strong> <?php echo php_uname();?>
</li>
<li>
- <strong>Versão do WP:</strong> <?php echo get_bloginfo('version');?>
</li>
<li>
- <strong>Versão do HotLinks+:</strong> <?php echo self::PLUGIN_VERSION;?>
</li>
</ul>

</div>