<?php
$_POST      = array_map( 'stripslashes_deep', $_POST );
$_GET       = array_map( 'stripslashes_deep', $_GET );
$_COOKIE    = array_map( 'stripslashes_deep', $_COOKIE );
$_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
			
global $anderson_makiyama, $wpdb, $user_ID, $user_level, $user_login;

wp_get_current_user();//get_currentuserinfo();


if ($user_level < 10) { //Limita acesso para somente administradores

	return;

}	

$table_projetos = $wpdb->prefix . self::CLASS_NAME . "_proj";

$url_site = self::get_site_url();


if (isset($_POST['proj_name'])) {
	
	if(!wp_verify_nonce( $_POST[self::CLASS_NAME], 'up_proj' ) ){
		
		print 'Sorry, your nonce did not verify.';
		exit;

	}

	$_POST['proj_name'] = trim($_POST['proj_name']);

	$_POST['descricao'] = htmlspecialchars($_POST['descricao']);


	if(empty($_POST['proj_name'])){	
		
		$alerta = self::get_alert("O campo Nome do Projeto precisa ser Preenchido!","error");echo $alerta;
		
	}else{
		
		//Verifica se outro projeto já existe
		$projs = $wpdb->get_row( $wpdb->prepare( 
			"
				SELECT id_projeto FROM $table_projetos
				where nm_projeto = %s
				AND id_projeto != %d
			", 
			$_POST["proj_name"],
			$_REQUEST["idproj"]
		), ARRAY_A );		
		
			
		if($projs){
			
			$alerta = self::get_alert("Já existe outro Projeto usando esse Nome!","error");echo $alerta;
			
			$duplicado = true;
			
		}elseif($_REQUEST["idproj"] == 1){
			
			$alerta = self::get_alert("O Projeto Geral não Pode ser Editado!","error");echo $alerta;
						
		}else{ //Atualiza projeto
		
				
			$wpdb->query( $wpdb->prepare( 
				"
				UPDATE $table_projetos
				set nm_projeto = %s, descricao = %s 
				WHERE id_projeto = %d
				", 
				$_POST["proj_name"],
				$_POST["descricao"], 
				$_REQUEST["idproj"]
			) );
				
			$alerta = self::get_alert("Projeto Atualizado com Sucesso!","success");echo $alerta;
                    
			
		}


	}


}


$proj = $wpdb->get_row( $wpdb->prepare( 
	"
		SELECT * FROM $table_projetos
		where id_projeto = %d
	", 
	$_REQUEST["idproj"]
), ARRAY_A );	


$admin_url = get_admin_url();
$admin_url.= 'admin.php?page=' . self::CLASS_NAME . "_Projetos";
		
?>
<?php require("header-top.php");?>

<div class="wrap">  

	<h1>Edite esse Projeto/Categoria</h1>
<p>
		  <label style="color:red;">Veja Mais Plugins Incríveis da  <a href="http://admiyn.com.br" target="_blank">~&gt; Admiyn - Softwares Inteligentes</a></label></p>
    
		<div class="panel panel-danger"  >
             <div class="panel-heading">
              <h3 class="panel-title">
            	Atualize o Projeto
              </h3>
             </div>
             
        	<div class="panel-body">

 

 		<form action="" method="post">
				<input type="hidden" name="idproj" value="<?php echo $_REQUEST["idproj"]?>" />
        
				<?php
                 wp_nonce_field('up_proj',self::CLASS_NAME);
				?>

                 <p>

                <label>Nome do Projeto:</label>
                <input type="text" name="proj_name" class="form-control" value="<?php echo $proj["nm_projeto"]?>" />

                <label><?php _e('Description',self::CLASS_NAME)?>:</label> <textarea name="descricao" class="form-control" ><?php echo $proj["descricao"]?></textarea>
                

                </p> 
                
                 <button type="submit" name="submit" class="btn btn-danger btn-sm"><i class="glyphicon glyphicon-refresh"></i> Atualizar</button> <a href="<?php echo $admin_url?>&projdel=<?php echo $_REQUEST["idproj"]?>" class="btn btn-danger btn-sm"><i class="glyphicon glyphicon-remove"></i> Excluir</a>
                 
          </form> 
                  <span class="help-block">Quando você Exclui um Projeto/Categoria, os Links que estavam nele são trasferidos para o Projeto Geral!</span>
				
 
 			</div>
            </div><!--end of panel body-->






<hr />


</div><!--end of wrap-->

<?php require("author.php");?>