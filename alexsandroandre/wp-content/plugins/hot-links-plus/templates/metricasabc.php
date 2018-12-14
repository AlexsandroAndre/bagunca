<?php
global $anderson_makiyama, $user_level, $wpdb;

wp_get_current_user();//get_currentuserinfo();

if ($user_level < 10) { //Limita acesso para somente administradores

	return;

}	

date_default_timezone_set("America/Sao_Paulo");


$table_metricas = $wpdb->prefix . self::CLASS_NAME . "_metric";


if(isset($_GET["metdel"])){

	$wpdb->query( $wpdb->prepare( 
		"
			DELETE FROM $table_metricas
			WHERE id_link = %d
		", 
		$_GET["metdel"]
	) );		
	
	$alerta = self::get_alert("Métrica Excluída com Sucesso!","success");echo $alerta;
	
}



$admin_url = get_admin_url();
$admin_url.= 'admin.php?page=' . self::CLASS_NAME . "_Metricas";


//Pega as Metricas
$metricas = $wpdb->get_results( 
"	SELECT * FROM $table_metricas
	ORDER BY nm_link ASC
", ARRAY_A );



?>
<?php require("header-top.php");?>

<div class="wrap my-wrap">

<div class"row">

<h2>Métricas de Testes A/B</h2>
 <p>
		  <label style="color:red;">Veja Mais Plugins Incríveis da  <a href="http://admiyn.com.br" target="_blank">~&gt; Admiyn - Softwares Inteligentes</a></label></p>      
    <table id="table" data-search="true" data-show-columns="true" data-pagination="true" data-height="auto" data-page-list="[10, 25, 50, 100, ALL]">
        <thead>
            <tr>
            	<th data-field="nm_link" data-sortable="true">Nome do Link</th>
                <th data-field="url_a" data-sortable="true">Url A</th>
                <th data-field="url_b" data-sortable="true">Url B</th>
                <th data-field="url_c" data-sortable="true">Url C</th>
                <th data-field="acoes" data-sortable="false"></th>
            </tr>
        </thead>
    </table>


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



var data_btable = [
<?php
foreach($metricas as $metrica){
	
	$percent_a = 0;$percent_b=0;$percent_c=0;
	
	if($metrica["acesso_a"] != 0){
		$percent_a = $metrica["conversao_a"] * 100;
		$percent_a = $percent_a/$metrica["acesso_a"];
		$percent_a = round($percent_a,2);
	}
	
	if($metrica["acesso_b"] != 0){
		$percent_b = $metrica["conversao_b"] * 100;
		$percent_b = $percent_b/$metrica["acesso_b"];
		$percent_b = round($percent_b,2);
	}
	
	if($metrica["acesso_c"] != 0){
		$percent_c = $metrica["conversao_c"] * 100;
		$percent_c = $percent_c/$metrica["acesso_c"];
		$percent_c = round($percent_c,2);
	}
			
	echo '
	{
		"nm_link": "<strong>'.$metrica["nm_link"].'</strong>",
			"url_a": "'. $metrica["acesso_a"] .' <strong>Acessos</strong><br>'.$metrica["conversao_a"].' <strong>Conversões</strong><br><strong><font color=\'#19b200\'> '.$percent_a.'%</font></strong>",
			"url_b": "'.$metrica["acesso_b"] .' <strong>Acessos</strong><br>'.$metrica["conversao_b"].' <strong>Conversões</strong><br><strong><font color=\'#19b200\'>'.$percent_b.'%</font></strong>",
			"url_c": "'.$metrica["acesso_c"] .' <strong>Acessos</strong><br>'.$metrica["conversao_c"].' <strong>Conversões</strong><br><strong><font color=\'#19b200\'>'.$percent_c.'%</font></strong>",
			"acoes": "<a href=\''. $admin_url .'&metdel='. $metrica["id_link"] .'\' ><span class=\'glyphicon glyphicon-remove\' title=\'Excluir\'></span></a>"
			
	},';

}

?>
];


jQuery(function ($) {
    $('#table').bootstrapTable({
        data: data_btable
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