<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

// Require funcoes de Segurança da API
require_once('helper-functions/security-functions.php');
require_once('helper-functions/helper-functions.php');


// Endpoint Para sincronizar as informaçoes iniciais e informacoes de outros componentes em tempo real!
function sync_endpoint_init() {
    
    $namespace = API_VERSAO;

    register_rest_route( $namespace, '/sync/', 
    
	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'get_sync',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
	    )

    );  
}

add_action( 'rest_api_init', 'sync_endpoint_init' );

function get_sync( $data ) {

	if( verifica_permissao_usuario_atual() ){
	
		global $wpdb;
		
		// Custom Tables
		$wpdb->cursos = $wpdb->prefix . 'cursos';
		$saida['cursos'] = $wpdb->get_results( "SELECT * FROM {$wpdb->cursos} ORDER BY title" );

		// Custom Post Types
		$saida['onibus'] = get_all_onibus( null );
		
		return $saida;
	
	}
	
	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );

}
