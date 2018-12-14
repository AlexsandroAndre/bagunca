<?php
global $anderson_makiyama, $user_level, $wpdb;

wp_get_current_user();//get_currentuserinfo();

if ($user_level < 10) { //Limita acesso para somente administradores

	return;

}	

$table_links = $wpdb->prefix . self::CLASS_NAME . "_links";
$table_projetos = $wpdb->prefix . self::CLASS_NAME . "_proj";
$table_configs = $wpdb->prefix . self::CLASS_NAME . "_configs";


if(isset($_GET["lkdel"])){

	$wpdb->query( $wpdb->prepare( 
		"
			DELETE FROM $table_links
			WHERE id_link = %d
		", 
		$_GET["lkdel"]
	) );		
	
	$alerta = self::get_alert("Link Excluído com Sucesso!","success");echo $alerta;
	
}

$configs = $wpdb->get_row(  
	"
		SELECT * FROM $table_configs
	", ARRAY_A );	

$current_proj = isset($_REQUEST["cproj"])?$_REQUEST["cproj"]:$configs["proj_padrao"];

$editar_nova_aba = $configs["editar_nova_aba"]==0?'':' target=\'_blank\' ';

if(empty($current_proj)){

	$links = $wpdb->get_results("
		SELECT link.*, proj.nm_projeto FROM $table_links link
		INNER JOIN $table_projetos proj
		ON proj.id_projeto = link.id_projeto
		ORDER BY link.id_link DESC
	", ARRAY_A );
		
}else{

	//Pega os links
	$links = $wpdb->get_results($wpdb->prepare("
		SELECT link.*, proj.nm_projeto FROM $table_links link
		INNER JOIN $table_projetos proj
		ON proj.id_projeto = link.id_projeto
		WHERE link.id_projeto = %d
		ORDER BY link.id_link DESC
	", $current_proj
	), ARRAY_A );

}
	

$url_site = self::get_site_url();

$admin_url = get_admin_url();
$admin_url.= 'admin.php?page=' . self::CLASS_NAME;


$projetos = $wpdb->get_results( 
"	SELECT * FROM $table_projetos
	ORDER BY nm_projeto ASC
", ARRAY_A );

?>

<?php require("header-top.php");?>
<div class="wrap my-wrap">
        
<h1>Meus Links</h1>
<p>
		  <label style="color:red;">Veja Mais Plugins Incríveis da  <a href="http://admiyn.com.br" target="_blank">~&gt; Admiyn - Softwares Inteligentes</a></label></p>
<div class="panel panel-danger"  >

   

    <div id="toolbar">
                 <form action="" method="post" id="filtro">
                 
                 <div class="form-group">
                 <div class="col-xs-2">
                <label>Filtre por Projeto/Categoria</label>
                </div>
                <div class="col-xs-10">
                <select name="cproj" onchange="javascript:submit_form('filtro');" class="form-control">
                <?php
				echo "<option value='' ". selected('', $current_proj) .">Todos as Categorias/Projetos</option>";
               foreach($projetos as $proj){
				   echo "<option value='". $proj["id_projeto"] ."' ". selected($proj["id_projeto"], $current_proj) .">". $proj["nm_projeto"] ."</option>";
			   }
				?>
                </select>
                </div>
                </div>
                </form>
    </div>
        
    <table id="table" data-search="true" data-show-columns="true" data-pagination="true" data-height="auto" data-page-list="[10, 25, 50, 100, ALL]">
        <thead>
            <tr>
                <th data-field="nm_projeto" data-sortable="true">Projeto/Categoria</th>
                <th data-field="nm_link" data-sortable="true" width="200">( ID ) Nome do Link</th>
                <th data-field="link_divulgacao" data-sortable="true">Link para Compartilhar</th>
                <th data-field="url_afiliado" data-sortable="true">Url Destino</th>
                <th data-field="total_acessos" data-sortable="true">Acessos</th>
                <th data-field="total_acessos_unicos" data-sortable="true">Acessos Únicos</th>
                <th data-field="acoes" data-sortable="false"></th>
            </tr>
        </thead>
    </table>



</div><!--end of panel danger-->


</div> <!--end of Wrap-->

<?php require("author.php");?>


<style>
.row-index {
    width: 50px;
    display: inline-block;
}
</style>


<script>

function submit_form(id_form){
	the_form = document.getElementById(id_form);
	the_form.submit();
}



var data = [
<?php
foreach($links as $link){
    
	echo'
	{
		"nm_projeto": "'.str_replace(array('"','\''),"´",$link["nm_projeto"]).'",
			"nm_link": "'. "<input type='text' value='" . $link["id_link"]."' onclick='this.select();' readonly='readonly' class='small-text'> " .str_replace(array('"','\''),"´",$link["nome_link"]).'",
			"link_divulgacao": "'. "<input type='text' value='" . $url_site.$link["palavra_chave"]."' onclick='this.select();' readonly='readonly' class='regular-text'> <a href='". $url_site.$link["palavra_chave"] ."' target='_blank'><span class='glyphicon glyphicon-send'></span></a>" .'",
			"url_afiliado": "<p class=\'over-auto\'>'.$link["url_afiliado"].'</p>",
			"total_acessos": "'.$link["total_acessos"].'",
			"total_acessos_unicos": "'.$link["total_acessos_unicos"].'",
			"acoes": "<a href=\''. $admin_url .'_Links&lk='. $link["id_link"] .'\' '. $editar_nova_aba .'><span class=\'glyphicon glyphicon-pencil\' title=\'Editar\'></span></a>&nbsp;&nbsp;<a href=\''. $admin_url .'_Links&lkdel='. $link["id_link"] .'\' ><span class=\'glyphicon glyphicon-remove\' title=\'Excluir\'></span></a>&nbsp;&nbsp;<a href=\''. $admin_url .'&copy='. $link["id_link"] .'\' ><span class=\'glyphicon glyphicon-duplicate\' title=\'Clonar\'></span></a>"
			
	},';

}

?>
];


jQuery(function ($) {
    $('#table').bootstrapTable({
        data: data
    });

    $(".mybtn-top").click(function () {
        $('#table').bootstrapTable('scrollTo', 0);
    });
    
    $(".mybtn-row").click(function () {
        var index = +$('.row-index').val(),
            top = 0;
        $('#table').find('tbody tr').each(function (i) {
        	if (i < index) {
            	top += $(this).height();
            }
        });
        $('#table').bootstrapTable('scrollTo', top);
    });
    
    $(".mybtn-btm").click(function () {
        $('#table').bootstrapTable('scrollTo', 'bottom');
    });

});

</script>