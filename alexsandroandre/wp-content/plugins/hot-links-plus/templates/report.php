<?php
global $anderson_makiyama, $user_level, $wpdb;

wp_get_current_user();//get_currentuserinfo();

if ($user_level < 10) { //Limita acesso para somente administradores

	return;

}	

date_default_timezone_set("America/Sao_Paulo");


$table_links = $wpdb->prefix . self::CLASS_NAME . "_links";
$table_projetos = $wpdb->prefix . self::CLASS_NAME . "_proj";
$table_acessos = $wpdb->prefix . self::CLASS_NAME . "_acess";


if(isset($_GET["repdel"])){

	$wpdb->query( $wpdb->prepare( 
		"
			DELETE FROM $table_acessos
			WHERE id_acesso = %d
		", 
		$_GET["repdel"]
	) );		
	
	$alerta = self::get_alert("Acesso Excluído com Sucesso!","success");echo $alerta;
	
}

//Pega os links
$links = $wpdb->get_results("
	SELECT id_link, nome_link FROM $table_links 
	ORDER BY nome_link ASC
", ARRAY_A );


$url_site = self::get_site_url();

$admin_url = get_admin_url();
$admin_url.= 'admin.php?page=' . self::CLASS_NAME . "_Report";


//Pega os Projetos
$projetos = $wpdb->get_results( 
"	SELECT * FROM $table_projetos
	ORDER BY nm_projeto ASC
", ARRAY_A );


$current_proj = isset($_POST["cproj"])?(int)$_POST["cproj"]:0;
$current_link = isset($_POST["clink"])?(int)$_POST["clink"]:0;
$current_data = isset($_POST["cdata"])?trim($_POST["cdata"]):"";
$current_ip = isset($_POST["cip"])?trim($_POST["cip"]):"";
$current_tracker = isset($_POST["ctracker"])?trim($_POST["ctracker"]):"";

$all_filtros = '';

$data_array = array();
if(!empty($current_data)) $data_array = explode('/',$current_data);
$data_array_length = count($data_array);

$data_inicio = '2015-01-01';
$data_fim = '2020-12-31';

if($data_array_length >0){//Foi preenchido o Filtro de data

	switch($data_array_length){
		
		case 1://subentendo que seja ano
			$data_inicio = $data_array[0] . '-01-01';
			$data_fim = $data_array[0] .'-12-31';
		break;
		case 2://subentendo que seja mes/ano
			$data_inicio = $data_array[1] . '-' . $data_array[0] . '-01';
			$data_fim = $data_array[1] . '-' . $data_array[0] .'-31';
		break;
		case 3://subentendo que seja dia/mes/ano
			$data_inicio =  $data_array[2] .'-'. $data_array[1] . '-' . $data_array[0];
			$data_fim =  $data_array[2] .'-'. $data_array[1] . '-' . $data_array[0] . ' 23:59:59';
		break;				
	}
	
}

$all_filtros .= $current_proj>0?' WHERE tl.id_projeto = %d ':' WHERE tl.id_projeto <> %d ';

$all_filtros .= $current_link>0?' AND tl.id_link = %d ':' AND tl.id_link <> %d ';

$all_filtros .= ' AND ta.dt_acesso BETWEEN %s AND %s ';

$all_filtros .= !empty($current_ip)?' AND ta.ip_acesso = %s ':' AND ta.ip_acesso <> %s ';	

$all_filtros .= !empty($current_tracker)?' AND ta.tracker_id = %s ':' AND ta.tracker_id <> %s ';

//Exclusão Em massa
if(isset($_POST["mass_del"])){//Excluí em Massa

	$acessos = $wpdb->query($wpdb->prepare("
		DELETE ta.* FROM $table_acessos ta
		INNER JOIN $table_links tl
		ON tl.id_link = ta.id_link
		".$all_filtros."
	",$current_proj,$current_link,$data_inicio, $data_fim,$current_ip
	), ARRAY_A );
		
	$alerta = self::get_alert("Acessos Excluídos com Sucesso!","success");echo $alerta;		
	
}
	//$wpdb->show_errors(); 
	//$wpdb->print_error();
//---------------



//Pega os Acessos
$acessos = $wpdb->get_results($wpdb->prepare("
	SELECT ta.* FROM $table_acessos ta
	INNER JOIN $table_links tl
	ON tl.id_link = ta.id_link
	".$all_filtros."
	ORDER BY ta.dt_acesso DESC
	LIMIT 1000
",$current_proj,$current_link,$data_inicio, $data_fim,$current_ip,$current_tracker
), ARRAY_A );

	
//Pega Acessos para o Gráfico

$meses_31 = array("01", "03", "05", "07", "08", "10", "12");
$hoje = date('Y-m-d');

$esse_mes = date("m");
$passado_mes = self::make_data($hoje, 0,-1,0);

$passado_mes = self::get_data_array($passado_mes);

if(array_search($esse_mes,$meses_31)!== false && array_search($passado_mes,$meses_31)!== false){
	$mes_atual_array = array_fill ( 1 , 31 , 0 );
}else{
	$mes_atual_array = array_fill ( 1 , 30 , 0 );	
}

$mes_atual_array = array_fill ( 1 , 31 , 0 );
$mes_passado_array = $mes_atual_array;

//Acessos do mês atual
$hoje = date('Y-m');

$mes_ini = $hoje . "-01 00:00:00";
$mes_fim = $hoje . "-31 23:59:59";

$acessos_mes_atual = $wpdb->get_results($wpdb->prepare("
	SELECT dt_acesso, day(dt_acesso) as dia, count(*) as total_acessos FROM $table_acessos
	WHERE dt_acesso BETWEEN %s AND %s
	GROUP BY dia
	ORDER BY dia ASC
", $mes_ini, $mes_fim
), ARRAY_A );


foreach($acessos_mes_atual as $acesso_mes_atual){
	$mes_atual_array[$acesso_mes_atual["dia"]] = $acesso_mes_atual["total_acessos"];	
}
unset($acessos_mes_atual);
//-----

//Acessos do mês passado

$hoje = date('Y-m-d');

$mes_passado = self::make_data($hoje, 0,-1,0);

$mes_passado = self::get_data_array($mes_passado);

$mes_passado = $mes_passado["ano"] . "-" . $mes_passado["mes"];

$hoje = date("Y-m");

$mes_ini = $mes_passado . "-01 00:00:00";
$mes_fim = $mes_passado . "-31 23:59:59";


$acessos_mes_passado = $wpdb->get_results($wpdb->prepare("
	SELECT dt_acesso, day(dt_acesso) as dia, count(*) as total_acessos FROM $table_acessos
	WHERE dt_acesso BETWEEN %s AND %s
	GROUP BY dia
	ORDER BY dia ASC
", $mes_ini, $mes_fim
), ARRAY_A );

	//$wpdb->show_errors(); 
	//$wpdb->print_error();
	

foreach($acessos_mes_passado as $acesso_mes_passado){
	$mes_passado_array[$acesso_mes_passado["dia"]] = $acesso_mes_passado["total_acessos"];	
}
//-----		
unset($acessos_mes_passado);


?>
<?php require("header-top.php");?>

<div class="wrap my-wrap">

<div class"row">
<div class="col-sm-12 text-center">
  <label class="label label-success">Gráfico Comparativo do Mês Atual e Mês Passado</label>
  <div id="area-chart" ></div>
</div>

        
<h1>Filtros</h1>


    <div id="toolbar">
                 
                 
                 <form action="<?php echo $admin_url?>" method="post" id="filtro_proj">
                 <div class="row">
                 <div class="col-xs-2">
					
                    <label>+ Projeto/Categoria</label>

                </div>
                 <div class="col-xs-2">

                    <select name="cproj" class="form-control">
                    <option value=""></option>
                    <?php
                   foreach($projetos as $proj){
                       echo "<option value='". $proj["id_projeto"] ."' ". selected($proj["id_projeto"], $current_proj) .">". $proj["nm_projeto"] ."</option>";
                   }
                    ?>
                    </select>
                                     
                 </div>
                
				</div>
                
                <div class="row">
                 <div class="col-xs-2">
                <label>+ Link</label>
                </div>
                <div class="col-xs-2">
                <select name="clink" class="form-control">
                <option value=""></option>
                <?php
               foreach($links as $link){
				   echo "<option value='". $link["id_link"] ."' ". selected($link["id_link"], $current_link) .">". $link["nome_link"] ."</option>";
			   }
				?>
                </select>
                </div>
                
				</div>
                    
               <div class="row">
                 <div class="col-xs-2">
                 
                <label>+ Data Específica</label>
                
                 </div> 
                 <div class="col-xs-2">
                 	<input type="text" class="form-control" name="cdata" value="<?php echo $current_data;?>" id="datepicker" style="text-align:center;"/>
                 </div>
                                 
               </div>     
               
 
                <div class="row">
                 <div class="col-xs-2">
                 
                <label>+ IP</label>
                 
                </div>         
                <div class="col-xs-2">
                <input type="text" class="form-control" name="cip" value="<?php echo $current_ip;?>" style="text-align:center;"/>
                </div>
                
                
               </div> 
               
                <div class="row">
                 <div class="col-xs-2">
                 
                <label>+ Tracker ID</label>
                 
                </div>         
                <div class="col-xs-2">
                <input type="text" class="form-control" name="ctracker" value="<?php echo $current_tracker;?>" style="text-align:center;"/>
                </div>
                
                
               </div>
                              
               <p>
               <button type="submit" class="btn btn-danger btn-sm"><i class="glyphicon glyphicon-search"></i> Aplicar Filtro</button>
               <small style="color:red;font-weight:bold;">Atenção para Evitar Excesso de Tráfego no seu Servidor, somente os últimos mil regitros Correspondentes ao Filtro escolhido são retornados!</small>
               </p>
               
               </form>
                                                               
               
    </div>
        

    
<h1>Relatório de Acessos</h1>

        
    <table id="table" data-search="true" data-show-columns="true" data-pagination="true" data-height="auto" data-page-list="[10, 25, 50, 100, ALL]">
        <thead>
            <tr>
            	<th data-field="tracker_id" data-sortable="true">Tracker ID</th>
            	<th data-field="dt_acesso" data-sortable="true">Data e Hora</th>
                <th data-field="pais_acesso" data-sortable="true">País</th>
                <th data-field="ip_acesso" data-sortable="true">IP</th>
                <th data-field="dispositivo" data-sortable="true">Dispositivo</th>
                <th data-field="origem_acesso" data-sortable="true">Origem</th>                
                <th data-field="nome_link" data-sortable="true" width="200">Nome do Link</th>
                <th data-field="link_completo" data-sortable="true">Link c/ Parâmetros</th>
                <th data-field="url_destino" data-sortable="true">Url Destino</th>
                <th data-field="acoes" data-sortable="false"></th>
            </tr>
        </thead>
    </table>

<?php
//prepara link com filtro atual para exclusão

if($current_proj>0){
	$mass_del = '&mass_proj=' . $current_proj;
}elseif($current_link>0){
	$mass_del = '&mass_link=' . $current_link;
}elseif(!empty($current_periodo)){
	$mass_del = '&mass_periodo=' . $current_periodo;
}else{
	$mass_del = '&mass_tudo';
}
?>

</div> <!--end of Wrap-->


	<p>
     <form action="<?php echo $admin_url?>" method="post" id="filtro_proj"> 
    <button type="submit" class="btn btn-danger btn-sm"><i class="glyphicon glyphicon-trash"></i> Excluir Todo Relatório do Filtro Atual</button> <small style="color:red;font-weight:bold;">Atenção Essas Entadas Serão Definitivamente Excluídas</small>
   	<input type="hidden" name="mass_del" value="mass_del" />
    <input type="hidden" name="cip" value="<?php echo $current_ip?>" />
	<input type="hidden" name="cproj" value="<?php echo $current_proj?>" />
    <input type="hidden" name="clink" value="<?php echo $current_link?>" />
    <input type="hidden" name="cdata" value="<?php echo $current_data?>" />
   </form>
   </p> 
   
   
<?php require("author.php");?>


<style>
.row-index {
    width: 50px;
    display: inline-block;
}
</style>

<?php
add_thickbox();
?>
<script>

function submit_form(id_form){
	the_form = document.getElementById(id_form);
	the_form.submit();
}

var data_btable = [
<?php

$all_modal_params = '';

foreach($acessos as $acesso){
	
	$all_parametros = substr($acesso["link_completo"], strpos($acesso["link_completo"],'?')+1);
	$all_parametros = explode("&",$all_parametros);
	
	for($i=0;$i < count($all_parametros);$i++){
	
		$all_parametros[$i] = explode("=",$all_parametros[$i]);
	}
	
	
	$all_modal_params .='
	<div id="my-content-'.$acesso["id_acesso"].'" style="display:none;">

		 <div class="row">
			<div class="col-xs-4">
			<strong>
			Link c/ Parâmetros
			</strong>
			</div>
			<div class="col-xs-1">
			=
			</div>			
			<div class="col-xs-7">
			'.$acesso["link_completo"].'
			</div>
		</div>';
		
		foreach($all_parametros as $param){
		    $param[1] = isset($param[1])?$param[1]:'';
			
			$all_modal_params .= '<div class="row">
			<div class="col-xs-4">
			<strong>
			'.urldecode($param[0]).'
			</strong>
			</div>
			<div class="col-xs-1">
			=
			</div>
			<div class="col-xs-7">
			'.urldecode($param[1]).'
			</div>
		</div>';	
		
		}

	$all_modal_params .= '</div>';
	
	$a_data = self::get_data_array($acesso["dt_acesso"],"",true);
	$a_data = $a_data["dia"]."/".$a_data["mes"]."/".$a_data["ano"] . " " . $a_data["hora"] . ":" . $a_data["minuto"] . ":" . $a_data["segundo"];
	
	$acesso["url_destino"] = str_replace("hlp_trackerid",$acesso["tracker_id"],$acesso["url_destino"]);
	
	echo '
	{
		"tracker_id": "'.$acesso["tracker_id"].'",
		"dt_acesso": "'.$a_data.'",
			"pais_acesso": "<img src=\''.$anderson_makiyama[self::PLUGIN_ID]->plugin_url.'images/flags/'.strtolower($acesso["pais_acesso"]).'.png\'/> '. $acesso["pais_acesso"].'",
			"ip_acesso": "'.$acesso["ip_acesso"].'",
			"dispositivo": "'.$acesso["dispositivo"].'",
			"origem_acesso": "<p class=\'over-auto\'>'.$acesso["origem_acesso"].'</p>",
			"nome_link": "'.str_replace(array('"','\''),"´",$acesso["nome_link"]).'",
			"link_completo": "<p class=\'over-auto\'>'.$acesso["link_completo"].' <a href=\'#TB_inline?width=600&height=550&inlineId=my-content-'.$acesso["id_acesso"].'\' class=\'thickbox\'><i class=\'glyphicon glyphicon-eye-open\'></i></a></p>",
			"url_destino": "<p class=\'over-auto\'>'.$acesso["url_destino"].'</p>",
			"acoes": "<a href=\''. $admin_url .'&repdel='. $acesso["id_acesso"] .'\' ><span class=\'glyphicon glyphicon-remove\' title=\'Excluir\'></span></a>"
			
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


  $( function() {
    $( "#datepicker" ).datepicker({ dateFormat: 'dd/mm/yy' });
  } );
  
});

var data = [
      <?php
	  $data_mes_ano = date("m/Y");
	  foreach($mes_atual_array as $key=>$value){
	  		echo "{ \"period\": '". $key . "/" . $data_mes_ano ."', a: ".$mes_passado_array[$key].", b: ".$value."},";
	  }
	  ?>

    ],
    config = {	
      data: data,
      xkey: 'period',
      ykeys: ['a', 'b'],
      labels: ['Acessos do Dia do Mês Passado', 'Acessos do Dia do Mês Atual'],
      fillOpacity: 0.6,
      hideHover: 'auto',
      behaveLikeLine: true,
      resize: true,
      pointFillColors:['#ffffff'],
      pointStrokeColors: ['black'],
      lineColors:['red','green'],
	  parseTime: false
  };
config.element = 'area-chart';
Morris.Area(config);



</script>

<?php 
echo $all_modal_params;
?>