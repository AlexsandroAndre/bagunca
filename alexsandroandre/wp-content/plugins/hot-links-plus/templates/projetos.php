<?php
global $anderson_makiyama, $wpdb, $user_ID, $user_level, $user_login;

wp_get_current_user();//get_currentuserinfo();


if ($user_level < 10) { //Limita acesso para somente administradores

	return;

}	

date_default_timezone_set("America/Sao_Paulo");

$_POST      = array_map( 'stripslashes_deep', $_POST );
$_GET       = array_map( 'stripslashes_deep', $_GET );
$_COOKIE    = array_map( 'stripslashes_deep', $_COOKIE );
$_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );

$options = get_option(self::CLASS_NAME . "_options");

$table_projetos = $wpdb->prefix . self::CLASS_NAME . "_proj";
$table_links = $wpdb->prefix . self::CLASS_NAME . "_links";

$url_site = self::get_site_url();



if (isset($_POST['proj_name'])) {
	
	if(!wp_verify_nonce( $_POST[self::CLASS_NAME], 'add_proj' ) ){
		
		print 'Sorry, your nonce did not verify.';
		exit;

	}

	$_POST['proj_name'] = trim($_POST['proj_name']);
	$_POST['descricao'] = htmlspecialchars($_POST['descricao']);


	if(empty($_POST['proj_name'])){
		
		$alerta = self::get_alert("Informe o Nome do Projeto!","error");echo $alerta;	
	
		
	}else{
		
		//Verifica se o projeto Ja Existe
		$proj = $wpdb->get_row( $wpdb->prepare( 
			"
				SELECT id_projeto FROM $table_projetos
				where nm_projeto = %s
			", 
			$_POST["proj_name"]
		), ARRAY_A );		
		
			
		if($proj){
			
			$alerta = self::get_alert("Já existe um projeto com esse Nome!","error");echo $alerta;
			
			$duplicado = true;
			
		}else{ //Adiciona o novo projeto
		
//-----
			$wpdb->query( $wpdb->prepare( 
				"
				INSERT INTO $table_projetos
				(nm_projeto, descricao )
				VALUES ( %s, %s)
				", 
				$_POST["proj_name"], 
				$_POST["descricao"]
			) );


	//$wpdb->show_errors(); 
	//$wpdb->print_error();
//---		
			
			$alerta = self::get_alert("Projeto/Categoria Cadastrado com Sucesso!","success");echo $alerta;		

				
		}


	}


}

if(isset($_GET["projdel"])){
	

	if($_GET["projdel"] == 1){

		$alerta = self::get_alert("O Projeto Geral não Pode ser Excluído!","error");echo $alerta;		
		
	}else{
	
		//Move Links para Projeto Geral
		$proj = $wpdb->query( $wpdb->prepare( 
			"
				UPDATE $table_links
				set id_projeto = 1
				where id_projeto = %d
			", 
			$_POST["projdel"]
		), ARRAY_A );
				
	
		//Exclui Projeto
		$proj = $wpdb->query( $wpdb->prepare( 
			"
				DELETE FROM $table_projetos
				where id_projeto = %d
			", 
			$_GET["projdel"]
		), ARRAY_A );	
	
		$alerta = self::get_alert("Projeto Excluído com Sucesso!","success");echo $alerta;	
			
	}
			
}


//Pega os Projetos
$projetos = $wpdb->get_results( 
"	SELECT *, (select count(*) from $table_links where id_projeto=tproj.id_projeto) as total_links FROM $table_projetos tproj
	ORDER BY id_projeto DESC
", ARRAY_A );
	
$admin_url = get_admin_url();
$admin_url.= 'admin.php?page=' . self::CLASS_NAME;
		
?>
<?php require("header-top.php");?>

<div class="wrap my-wrap">

<h1>Organize seus links em Projetos ou Categorias</h1>

 <p>
		  <label style="color:red;">Veja Mais Plugins Incríveis da  <a href="http://admiyn.com.br" target="_blank">~&gt; Admiyn - Softwares Inteligentes</a></label></p>   

		<div class="panel panel-danger"  >
             <div class="panel-heading">
              <h3 class="panel-title">
            	Crie Novos Projetos/Categorias
              </h3>
             </div>
             
        	<div class="panel-body">

			
 		<form action="<?php echo $admin_url . "_Projetos"?>" method="post">

        
				<?php
                 wp_nonce_field('add_proj',self::CLASS_NAME);
				?>

                

                 <p>
                 
                <label>Nome do Projeto:</label>
                <input type="text" name="proj_name" class="form-control" value="<?php if(isset($_POST['proj_name'])) echo $_POST['proj_name']?>" />

                <label><?php _e('Description',self::CLASS_NAME)?>:</label> <textarea name="descricao" class="form-control" ><?php if(isset($_POST['descricao'])) echo $_POST['descricao']?></textarea>

                </p> 

       			 <p>

            <button type="submit" name="submit" class="btn btn-danger btn-sm"><i class="glyphicon glyphicon-plus-sign"></i> Adicionar</button>

				</p>        
 		</form>
        
        </div>
        </div>

          

  
		<div class="panel panel-danger"  >
                
            
             <div class="panel-heading">
              <h3 class="panel-title">
            	Listagem de Projetos/Categorias
              </h3>
             </div>
             
             <div class="panel-body"> 
            
                
                <span class="help-block">Quando você Exclui um Projeto/Categoria, seus Links vão para a Categoria Geral!</span>
                

    <table id="table" data-search="true" data-show-columns="true" data-pagination="true" data-height="auto" data-page-list="[10, 25, 50, 100, ALL]">
        <thead>
            <tr>
                <th data-field="nm_projeto" data-sortable="true">Nome do Projeto</th>
                <th data-field="total_links" data-sortable="true" width="100">QTD Links</th>
                <th data-field="descricao" data-sortable="true" width="200">Descrição</th>
                <th data-field="acoes" data-sortable="true"></th>
            </tr>
        </thead>
    </table>
                   

		</div>
        </div>
        

</div><!--end of wrap-->

<?php require("author.php");?>


<script>


var data = [
<?php
foreach($projetos as $proj){
	
	$total_de_links = "&nbsp;&nbsp;<small>0 links</small>";
	if($proj["total_links"]>0){
		$total_de_links = '&nbsp;&nbsp;<small><a href=\''. $admin_url .'_Links&cproj='. $proj["id_projeto"] .'\' >'. $proj["total_links"] .' links</a></small>';	
	}
    
	echo'
	{
		"nm_projeto": "'. str_replace(array('"','\''),"´",$proj["nm_projeto"]) . '",
			"descricao": "<textarea class=\'form-control\' readonly=\'readonly\'>'. str_replace(array('"','\''),"´",str_replace(array("\n\r","\n","\r"),array("\\n\r","\\n","\\r"), $proj["descricao"])) .'</textarea>",
			"total_links": "'. $total_de_links .'",
			"acoes": "<a href=\''. $admin_url .'_Projetos&idproj='. $proj["id_projeto"] .'\'><span class=\'glyphicon glyphicon-pencil\' title=\'Editar Projeto\'></span></a>&nbsp;&nbsp;<a href=\''. $admin_url .'_Projetos&projdel='. $proj["id_projeto"] .'\' ><span class=\'glyphicon glyphicon-remove\'  title=\'Excluir Projeto\'></span></a>&nbsp;&nbsp;<a href=\''. $admin_url .'_Links&cproj='. $proj["id_projeto"] .'\' ><span class=\'glyphicon glyphicon-eye-open\' title=\'Ver Links do Projeto\'></span></a>"
			
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