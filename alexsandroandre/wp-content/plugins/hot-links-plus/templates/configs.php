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

$table_configs = $wpdb->prefix . self::CLASS_NAME . "_configs";
$table_projetos = $wpdb->prefix . self::CLASS_NAME . "_proj";


if (isset($_POST['limite_acessos'])) {
	
	if(!wp_verify_nonce( $_POST[self::CLASS_NAME], 'up_configs' ) ){
		
		print 'Sorry, your nonce did not verify.';
		exit;

	}

	
	$_POST['url_home'] = trim($_POST['url_home']);
	$_POST["ips_bloqueados"] = trim($_POST["ips_bloqueados"]);
	$_POST["ids_no_refer"] = trim($_POST["ids_no_refer"]);	
			
	$wpdb->query( $wpdb->prepare( 
		"
		UPDATE $table_configs
		set limite_acessos = %d, tipo_home = %d, url_home = %s, ips_bloqueados = %s, ativar_webservices=%d,acao_ip_sem=%d, codigo_cabecalho=%s, codigo_rodape=%s, acao_mob=%d, proj_padrao=%s, editar_nova_aba=%d, apos_editar_voltar=%d, param_google=%d, acao_block=%d, ids_no_refer=%s 
		", 
		$_POST["limite_acessos"],
		$_POST["tipo_home"], 
		$_POST["url_home"],
		$_POST["ips_bloqueados"],
		$_POST["ativar_webservices"],
		$_POST["acao_ip_sem"],
		$_POST["codigo_cabecalho"],
		$_POST["codigo_rodape"],
		$_POST["acao_mob"],
		$_POST["proj_padrao"],
		$_POST["editar_nova_aba"],
		$_POST["apos_editar_voltar"],
		$_POST["param_google"],
		$_POST["acao_block"],
		$_POST["ids_no_refer"]
	) );
		
	$alerta = self::get_alert("Configurações Atualizadas com Sucesso!","success");echo $alerta;

}


$configs = $wpdb->get_row(  
	"
		SELECT * FROM $table_configs
	", ARRAY_A );	

$projs = $wpdb->get_results(  
	"
		SELECT * FROM $table_projetos
	", ARRAY_A );	
	
$admin_url = get_admin_url();
$admin_url.= 'admin.php?page=' . self::CLASS_NAME . "_Configs";
		
?>

<?php require("header-top.php");?>


<div class="wrap">
        

	<h1>Configurações Globais</h1>
<p>
		  <label style="color:red;">Veja Mais Plugins Incríveis da  <a href="http://admiyn.com.br" target="_blank">~&gt; Admiyn - Softwares Inteligentes</a></label></p>
    	<form action="" method="post">
        
        	<p style="text-align:right;">

               <button type="submit" name="submit" class="btn btn-danger btn-sm"><i class="glyphicon glyphicon-refresh"></i> Atualizar</button>
            </p>
        
		<div class="panel panel-danger"  >
             <div class="panel-heading">
              <h3 class="panel-title">
            	Configurações Gerais</h3>
             </div>
             
        	<div class="panel-body">

 

 		
        
				<?php
                 wp_nonce_field('up_configs',self::CLASS_NAME);
				?>

                 <p>

                <label>Limitar Registros de Acessos do Relatório:</label> <a class="popoverData" class="btn" href="javascript::" data-content="Limitar o quantidade de Acessos ajuda a poupar espaço em seu Banco de Dados. Quando o Limite for atingido, os registros mais antigos serão excluídos!" rel="popover" data-placement="bottom" data-original-title="Limitar Registros de Acessos" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a>
                <select class="form-control" name="limite_acessos">
                	<option value="1" <?php selected(1,$configs["limite_acessos"]);?>>Mil Acessos</option>
                    <option value="0" <?php selected(0,$configs["limite_acessos"]);?>>10 Mil Acessos</option>
                    <option value="100" <?php selected(100,$configs["limite_acessos"]);?>>100 Mil Acessos</option>
                    <option value="300" <?php selected(300,$configs["limite_acessos"]);?>>300 Mil Acessos</option>
                    <option value="500" <?php selected(500,$configs["limite_acessos"]);?>>500 Mil Acessos</option>
                    <option value="10" <?php selected(10,$configs["limite_acessos"]);?>>Ilimitado</option>
                </select>

				</p>
                
                <p>
                <div class="row">
                
                <div class="col-xs-2">
              <label>A Entrada do Site Vai Abrir:</label> 
                </div>
                <div class="col-xs-2">
              <select class="form-control" name="tipo_home">
                	<option value="0" <?php selected(0,$configs["tipo_home"]);?>>Normal</option>
                    <option value="1" <?php selected(1,$configs["tipo_home"]);?>>Abrir Como o Link de ID ~&gt;</option>
                    <option value="2" <?php selected(2,$configs["tipo_home"]);?>>Redirecionar para ~&gt;</option>
                </select>
                </div>
                <div class="col-xs-8">
                <input type="text" name="url_home" class="form-control" value="<?php echo $configs["url_home"];?>" />
                </div>
                
				</div>
                </p> 
               

                <p>
               
               <label>Códigos para Inserir no Cabeçalho de Todos os Links: </label> <textarea name="codigo_cabecalho" class="form-control"><?php echo $configs["codigo_cabecalho"]?></textarea> <span class="help-block">Use para Injetar Pixel de  Remarketing do Facebook, Código de Acompanhanto do Google Analytics, Scripts, etc., no Cabeçalho (Entre &lt;head&gt; e &lt;/head&gt;)  da Página</span>
             
				</p>
                <p>
                
				<label>Códigos para Inserir no Rodapé de Todos os Links: </label> <textarea name="codigo_rodape" class="form-control"><?php echo $configs["codigo_rodape"]?></textarea> <span class="help-block">Use para Injetar Código de  Remarketing do Google Adwords, scripts, etc., no Rodapé (Antes de &lt;/body&gt;) da Página</span>

				</p>  
                


 				<!-- Row Start-->
                 <p>
                <div class="row">
                
                <div class="col-xs-2">
              <label>Remover Referências das Páginas:</label><a class="popoverData" class="btn" href="javascript::" data-content="Útil quando se usa uma Página/Post do Blog como safepage, nesse caso é interessante desativar qualquer referência do HLP da Página/Post" rel="popover" data-placement="bottom" data-original-title="Remover Referências dessas Páginas" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a> 
                </div>
                <div class="col-xs-10">
              <textarea class="form-control" name="ids_no_refer"><?php echo $configs["ids_no_refer"];?></textarea>
              <small>(Separe cada ID de Post/Página com Vírgula)</small>
                </div>
                
				</div>
                </p> 
                <!-- ENd of Row-->
                
                 
 			</div><!--end of panel body-->
            </div><!--end of panel danger-->




		<div class="panel panel-danger"  >
             <div class="panel-heading">
              <h3 class="panel-title">
            	Configurações do Cloak</h3>
             </div>
             
        	<div class="panel-body">


 				<!-- Row Start-->
                 <p>
                <div class="row">
                
                <div class="col-xs-3">
              <label>Ativar busca de Origem de IPs em WebServices:</label><a class="popoverData" class="btn" href="javascript::" data-content="Alguns IPs podem não ter sua origem Identificada, ativar essa opção vai fazer o plugin tentar descobrir o Pais do IP consultando WebServices" rel="popover" data-placement="bottom" data-original-title="Ativar busca de País de IPs em WebServices" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a> 
                </div>
                <div class="col-xs-9">
              <select class="form-control" name="ativar_webservices">
                	<option value="0" <?php selected(0,$configs["ativar_webservices"]);?>>Não! Buscar só no Banco do Plugin</option>
                    <option value="1" <?php selected(1,$configs["ativar_webservices"]);?>>Sim! Bucar também em WebServices</option>
                </select>
                </div>
                
				</div>
                </p> 
                <!-- ENd of Row-->


 				<!-- Row Start-->
                 <p>
                <div class="row">
                
                <div class="col-xs-3">
              <label>Quando o País de um IP não for Identificado:</label><a class="popoverData" class="btn" href="javascript::" data-content="Alguns IPs podem não ter sua origem Identificada, Então Decida como o plugin deve se Comportar nesses Casos" rel="popover" data-placement="bottom" data-original-title="Quando o País de um IP não for Identificado" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a> 
                </div>
                <div class="col-xs-9">
              <select class="form-control" name="acao_ip_sem">
                	<option value="0" <?php selected(0,$configs["acao_ip_sem"]);?>>Redirecionar para URL 01 do Cloak</option>
                    <option value="1" <?php selected(1,$configs["acao_ip_sem"]);?>>Redirecionar para URL 02 do Cloak</option>
                </select>
                </div>
                
				</div>
                </p> 
                <!-- ENd of Row-->
                


 				<!-- Row Start-->
                 <p>
                <div class="row">
                
                <div class="col-xs-3">
              <label>Se Cloak Ativo, Tratar todos os Acessos Mobile como:</label><a class="popoverData" class="btn" href="javascript::" data-content="Alguns IPs Acessos de Celular podem apresentar IP de outros países, por isso pode ser interessante tratar todos os Acessos Mobile como Seguros para não perder acessos reais" rel="popover" data-placement="bottom" data-original-title="Tratar todos os Acessos Mobile como" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a> 
                </div>
                <div class="col-xs-9">
              <select class="form-control" name="acao_mob">
                	<option value="0" <?php selected(0,$configs["acao_mob"]);?>>Abrir Url 01 do Cloak</option>
                    <option value="2" <?php selected(2,$configs["acao_mob"]);?>>Abrir Url 02 do Cloak</option>
                    <option value="1" <?php selected(1,$configs["acao_mob"]);?>>Opção Inativa (Vai tratar Mobile Igual as demais Conexões)</option>
                </select>
                </div>
                
				</div>
                </p> 
                <!-- ENd of Row-->
                
                                                
                
 				<!-- Row Start-->
                 <p>
                <div class="row">
                
                <div class="col-xs-2">
              <label>Bloquear esses IPs:</label><a class="popoverData" class="btn" href="javascript::" data-content="Os IPs bloqueados são sempre redirecionados para o URL 02 configurado nas opções de Cloaker" rel="popover" data-placement="bottom" data-original-title="Bloquear esses IPs" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a> 
                </div>
                <div class="col-xs-10">
              <textarea class="form-control" name="ips_bloqueados"><?php echo $configs["ips_bloqueados"];?></textarea>
              <small>(Separe cada IP com Vírgula)</small>
                </div>
                
				</div>
                </p> 
                <!-- ENd of Row-->
                
 
 
  				<!-- Row Start-->
                 <p>
                <div class="row">
                
                <div class="col-xs-3">
              <label>Tratar os IPs Bloqueados Acima e os Robôs como:</label><a class="popoverData" class="btn" href="javascript::" data-content="Permite você definir qual dos urls do Cloak os robôs e os ips bloqueadores devem abrir" rel="popover" data-placement="bottom" data-original-title="Tratar os IPs Bloqueados Acima e os Robôs como:" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a> 
                </div>
                <div class="col-xs-9">
              <select class="form-control" name="acao_block">
                	<option value="0" <?php selected(0,$configs["acao_block"]);?>>Abrir Url 02 do Cloak</option>
                    <option value="1" <?php selected(1,$configs["acao_block"]);?>>Abrir Url 01 do Cloak</option>
                </select>
                </div>
                
				</div>
                </p> 
                <!-- ENd of Row-->               
  
                                                            
            </div><!--end of panel body-->
            
       </div><!--end of panel danger-->





		<div class="panel panel-danger"  >
             <div class="panel-heading">
              <h3 class="panel-title">
            	Configurações do Tracker</h3>
             </div>
             
        	<div class="panel-body">

 				<!-- Row Start-->
                 <p>
                <div class="row">
                
                <div class="col-xs-3">
              <label>Repassar Parâmetros ValueTrack do Google para SRC?</label>
                </div>
                <div class="col-xs-9">
              	<select class="form-control" name="param_google">
                	<option value="0" <?php selected(0,$configs["param_google"]);?>>Não (Padrão)</option>
                    <option value="1" <?php selected(1,$configs["param_google"]);?>>Sim (Repassará os Parâmetros do Google para SRC)</option>
                    
                    <option value="2" <?php selected(2,$configs["param_google"]);?>>Sim (Repassar e Ocultar a Keyword)</option>
                    
                    <option value="3" <?php selected(3,$configs["param_google"]);?>>Ocultar SRC Inteiro</option>
                    
                </select>
                </div>
                
				</div>
                </p> 
                <!-- ENd of Row-->
  
                                                            
            </div><!--end of panel body-->
            
       </div><!--end of panel danger-->
       
       
       

		<div class="panel panel-danger"  >
             <div class="panel-heading">
              <h3 class="panel-title">
            	Configurações de Usabilidade</h3>
             </div>
             
        	<div class="panel-body">

 				<!-- Row Start-->
                 <p>
                <div class="row">
                
                <div class="col-xs-3">
              <label>Abrir Edição de Links em Nova Aba?</label>
                </div>
                <div class="col-xs-9">
              <select class="form-control" name="editar_nova_aba">
                	<option value="0" <?php selected(0,$configs["editar_nova_aba"]);?>>Não (Padrão)</option>
                    <option value="1" <?php selected(1,$configs["editar_nova_aba"]);?>>Sim (Abrir em Nova Aba)</option>
                </select>
                </div>
                
				</div>
                </p> 
                <!-- ENd of Row-->
  
  
 				<!-- Row Start-->
                 <p>
                <div class="row">
                
                <div class="col-xs-3">
              <label>Após Edição de Links, Voltar para Listagem da Categoria?</label>
                </div>
                <div class="col-xs-9">
              <select class="form-control" name="apos_editar_voltar">
                	<option value="0" <?php selected(0,$configs["apos_editar_voltar"]);?>>Não (Padrão)</option>
                    <option value="1" <?php selected(1,$configs["apos_editar_voltar"]);?>>Sim (Retornar pra Listagem da Categoria)</option>
                </select>
                </div>
                
				</div>
                </p> 
                <!-- ENd of Row-->

 				<!-- Row Start-->
                 <p>
                <div class="row">
                
                <div class="col-xs-3">
              <label>Na Listagem de Links Exibir por Padrão Links de qual Categoria</label>
                </div>
                <div class="col-xs-9">
              <select class="form-control" name="proj_padrao">
                	<option value="" <?php selected('',$configs["proj_padrao"]);?>>Todas as Categorias</option>
                    <?php
                    foreach($projs as $proj){
						echo '<option value="'.$proj['id_projeto'].'" ' . selected($proj['id_projeto'],$configs["proj_padrao"],false).'>'.$proj['nm_projeto'].'</option>';
					}
					?>
                </select>
                </div>
                
				</div>
                </p> 
                <!-- ENd of Row-->
                                                            
            </div><!--end of panel body-->
            
       </div><!--end of panel danger-->


	<button type="submit" name="submit" class="btn btn-danger btn-sm"><i class="glyphicon glyphicon-refresh"></i> Atualizar</button>

	</form>

</div><!--end of wrap-->


<?php require("author.php");?>


<script>
window.onload = function($){
	jQuery('.popoverData').popover();
	jQuery('#popoverOption').popover({ trigger: "hover" });
}
</script>