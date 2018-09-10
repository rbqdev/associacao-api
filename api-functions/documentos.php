<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

// Require funcoes de Segurança da API
require_once('helper-functions/security-functions.php');
require_once('helper-functions/helper-functions.php');

function documentos_endpoint_init() {
    
    $namespace = API_VERSAO;

    register_rest_route( $namespace, '/documentos/', 
    
	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'get_documentos',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
	    )

    );
    register_rest_route( $namespace, '/documentos/(?P<id>\d+)', 
    
	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'get_documentos_id',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
	    )

    );
    
    register_rest_route( $namespace, '/documentos/create/',
	    
	    array(
			'methods'   => 'POST',
			'callback'  => 'adicionar_documento',
			'permission_callback' => function () {
			  	return is_user_logged_in();
			}
		) 
    
    );
    
    register_rest_route( $namespace, '/documentos/update/',
	    
	    array(
			'methods'   => 'POST',
			'callback'  => 'editar_documento',
			'permission_callback' => function () {
			  	return is_user_logged_in();
			}
		) 
    
    );
    
    register_rest_route( $namespace, '/documentos/delete/',
	    
	    array(
			'methods'   => 'POST',
			'callback'  => 'deletar_documento',
			'permission_callback' => function () {
			  	return is_user_logged_in();
			}
		) 
    
    );
        
}

add_action( 'rest_api_init', 'documentos_endpoint_init' );

function get_documentos( $data ) {

	if( verifica_permissao_usuario_atual() ){
	
		global $wpdb;
		$wpdb->documentos = $wpdb->prefix . 'documentos';
			
		$saida = array();
		
		$saida = $wpdb->get_results( "SELECT * FROM {$wpdb->documentos} ORDER BY id_pessoa" );
		
		return $saida;
	
	}
	
	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );

}

function get_documentos_id( $data ){

	if( verifica_permissao_usuario_atual() ){
	
		global $wpdb;
		
		$wpdb->documentos = $wpdb->prefix . 'documentos';
		
		$id_pessoa = intval( $data['id'] );
		
		$saida = $wpdb->get_results( "SELECT * FROM {$wpdb->documentos} WHERE id_pessoa = $id_pessoa ORDER BY tipo_documento" );
		
		return $saida;
	}
	
	return new WP_Error( 'usuario_nao_permitido', 'O usuário não tem permissão para acessar o conteudo!', array( 'status' => 400 ) );

}

function adicionar_documento( $data ) {

	if( verifica_permissao_usuario_atual() ){
		
		global $wpdb;
		$wpdb->documentos = $wpdb->prefix . 'documentos';
		
		$nome_pasta = strtolower(str_replace(' ', '_', $data['nome_pessoa']));
		$path_imagem = adicionar_img( $data['id_pessoa'], $nome_pasta, $data['img'], $data['pasta'], '/documentos' );
		$data['img'] = $path_imagem;

		if( $path_imagem && $path_imagem !== null && $path_imagem !== '' ){
			$documento = $wpdb->insert(
				$wpdb->documentos,
				array(
					'id_pessoa' 		=> $data['id_pessoa'],
					'nome_pessoa' 		=> $data['nome_pessoa'],
					'img'				=> $data['img'],
					'data' 				=> $data['data'],
					'tipo_documento' 	=> $data['tipo_documento'],
					'documento_desc'	=> $data['documento_desc']		
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s'
				)
			);
			
			if( $documento ){

				$acao_desc = 'O usuário ' . $data['nome_autor'] . ', adicionou um documento referente ao ' . $data['objeto'] . ' ' . $data['objeto_nome'];
				$log = adicionarLogDeUsuario( $data, 'adicao', $acao_desc );
			
				return true;
				
			} else {
			
				return new WP_Error( 'error_ao_inserir', 'Erro ao inserir!', array( 'status' => 400 ) );
				
			}
		}
		
	}
	
	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );
	
		
}

function editar_documento( $data ) {

	if( verifica_permissao_usuario_atual() ){
	
		global $wpdb;
		$wpdb->documentos = $wpdb->prefix . 'documentos';

		if( $data['img'] && $data['img'] !== '' && strpos($data['img'], 'data:image') !== false ) {
			$nome_pasta = strtolower(str_replace(' ', '_', $data['nome_pessoa']));
			$path_imagem = adicionar_img( $data['id_pessoa'], $nome_pasta, $data['img'], $data['pasta'], '/documentos' );
			$data['img'] = $path_imagem;			
		}

		$documento = $wpdb->update(
			$wpdb->documentos,
			array(
				'tipo_documento' 	=> $data['tipo_documento'],
				'documento_desc'	=> $data['documento_desc'],
				'img'				=> $data['img']
			),
			array(
				'ID' => $data['id'],
			),
			array(
				'%s',
				'%s',
				'%s'
			),
			array(
				'%s',
			)
		);
		
		if( $documento ){

			$acao_desc = 'O usuário ' . $data['nome_autor'] . ', editou um documento referente ao ' . $data['objeto'] . ' ' . $data['objeto_nome'];
			$log = adicionarLogDeUsuario( $data, 'edicao', $acao_desc );
		
			return true;
			
		} else {
		
			return new WP_Error( 'error_ao_editar', 'Erro ao editar!', array( 'status' => 400 ) );
			
		}
				
	}
	
	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );
	
}

function deletar_documento( $data ) {

	if( verifica_permissao_usuario_atual() ){
		
		global $wpdb;
		$wpdb->documentos = $wpdb->prefix . 'documentos';
		
		$documento = $wpdb->delete( 
			$wpdb->documentos, 
			array( 
				'ID' => $data['id'] 
			) 
		);
		
		if( $documento ){

			$acao_desc = 'O usuário ' . $data['nome_autor'] . ', deletou um documento referente ao ' . $data['objeto'] . ' ' . $data['objeto_nome'];
			$log = adicionarLogDeUsuario( $data, 'remocao', $acao_desc );

			return true;

		} else {

			return new WP_Error( 'error_ao_deletar', 'Erro ao deletar!', array( 'status' => 400 ) );

		}
	
	}
	
	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );

}