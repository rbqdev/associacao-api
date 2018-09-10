<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 
	
// Require funcoes de Segurança da API
require_once('helper-functions/security-functions.php');
require_once('helper-functions/helper-functions.php');

// Inicio usuarios FUnctions
function usuarios_endpoint_init() {
    
    $namespace = API_VERSAO;
    
    register_rest_route( $namespace, '/usuario/auth/', 
        
	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'get_usuario_auth',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
    	)

    );
     
}

add_action( 'rest_api_init', 'usuarios_endpoint_init' );

function get_usuario_auth() {
	
	$current_user = wp_get_current_user();

	if (!empty($current_user)) {
	
		$current_user_id = $current_user->ID;
		$current_user_meta_info = get_user_meta( $current_user_id, 'associado_info' , true );

		if( !empty($current_user_meta_info) && verifica_permissao_usuario_atual() ){
			
			$cargo = '';
			if( $current_user_meta_info['tipo'] === 'diretor' ) $cargo = 'Diretor(a)';
			if( $current_user_meta_info['tipo'] === 'conselho_etica' ) $cargo = 'Conselheiro(a) de Etica';
			if( $current_user_meta_info['tipo'] === 'conselho_fiscal' ) $cargo = 'Conselheiro(a) Fiscal';
			if( $current_user_meta_info['tipo'] === 'estagiario' ) $cargo = 'Estágiario(a)';
			if( $current_user_meta_info['tipo'] === 'coordenador' ) $cargo = 'Coordenador(a)';
			
			$saida = array(
			    'id' 				=> $current_user_id,
			    'nome' 			 	=> $current_user->data->display_name,
			    'email' 		 	=> $current_user->data->user_email,
			    'status'			=> $current_user_meta_info['status_desc'],
				'tipo'				=> $current_user_meta_info['tipo'],
				'admin'				=> $current_user_meta_info['admin'],
				'cargo'				=> $cargo
			);
			
			wp_reset_query();
				
			return $saida;
			
		} else { 
				
			// Se o usuario for do tipo ADMINISTRATOR
			if( verifica_permissao_usuario_atual() ){
			
				$saida = array(
				    'id' 				=> $current_user_id,
				    'nome' 			 	=> $current_user->data->display_name,
				    'email' 		 	=> $current_user->data->user_email,
					'tipo'				=> $current_user->roles[0],
					'cargo'				=> 'Administrador Geral'
				);
				
				wp_reset_query();
				
				return $saida;
				
			}
		
		}

	}
	
	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );
	
}