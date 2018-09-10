<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

require_once('helper-functions/security-functions.php');
require_once('helper-functions/helper-functions.php');

function cursos_endpoint_init() {
    
    $namespace = API_VERSAO;

    register_rest_route( $namespace, '/cursos/', 
    
	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'get_cursos',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
	    )

    );
    register_rest_route( $namespace, '/cursos/(?P<id>\d+)', 
    
	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'get_curso',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
	    )

    );
    
    register_rest_route( $namespace, '/cursos/create/',
	    
	    array(
			'methods'   => 'POST',
			'callback'  => 'novo_curso',
			'permission_callback' => function () {
			  	return is_user_logged_in();
			}
		) 
    
    );
    
    register_rest_route( $namespace, '/cursos/update/',
	    
	    array(
			'methods'   => 'POST',
			'callback'  => 'editar_curso',
			'permission_callback' => function () {
			  	return is_user_logged_in();
			}
		) 
    
    );
    
    register_rest_route( $namespace, '/cursos/delete/',
	    
	    array(
			'methods'   => 'POST',
			'callback'  => 'deletar_curso',
			'permission_callback' => function () {
			  	return is_user_logged_in();
			}
		) 
    
    );
        
}

add_action( 'rest_api_init', 'cursos_endpoint_init' );

function get_cursos( $data ) {

	if( verifica_permissao_usuario_atual() ){
	
		global $wpdb;
		$wpdb->cursos = $wpdb->prefix . 'cursos';
		
		$saida = $wpdb->get_results( "SELECT * FROM {$wpdb->cursos} ORDER BY title" );
		
		return $saida;
	
	}
	
	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );

}

function get_curso( $data ) {

	if( verifica_permissao_usuario_atual() ){
	
		global $wpdb;
		$wpdb->cursos = $wpdb->prefix . 'cursos';
		
		$saida = $wpdb->get_results( "SELECT * FROM {$wpdb->cursos} ORDER BY title" );
		
		return $saida;
	
	}
	
	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );

}

function novo_curso( $data ) {

	if( verifica_permissao_usuario_atual() ){
		
		global $wpdb;
		$wpdb->cursos = $wpdb->prefix . 'cursos';
		
		$curso = $wpdb->insert(
			$wpdb->cursos,
			array(
				'title' => $data['title'],
				'slug' 	=> $data['slug']
			),
			array(
				'%s',
				'%s'
			)
		);
		
		if( $curso ){
		
			$acao_desc = 'O usuário ' . $data['nome_autor'] . ', adicionou um novo curso. ' . $data['title'];
			$log = adicionarLogDeUsuario( $data, 'adicao', $acao_desc );

			return true;
			
		} else {
		
			return new WP_Error( 'error_ao_inserir', 'Erro ao inserir!', array( 'status' => 400 ) );
			
		}
		
	}
	
	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );
	
		
}

function editar_curso( $data ) {

	if( verifica_permissao_usuario_atual() ){

		global $wpdb;
		$wpdb->cursos = $wpdb->prefix . 'cursos';
		
		$curso = $wpdb->update(
			$wpdb->cursos,
			array(
				'title' => $data['title'],
				'slug' 	=> $data['slug']
			),
			array(
				'ID' 	=> $data['id'],
			),
			array(
				'%s',
				'%s'
			),
			array(
				'%s',
			)
		);

		$acao_desc = 'O usuário ' . $data['nome_autor'] . ', editou o curso. ' . $data['title'];
		$log = adicionarLogDeUsuario( $data, 'edicao', $acao_desc );
		
		return true;
		
	}
	
	return new WP_Error( 'usuario_nao_permitido', 'O usuário não tem permissão para acessar o conteudo!', array( 'status' => 400 ) );
	
}
function deletar_curso( $data ) {

	if( verifica_permissao_usuario_atual() ){

		global $wpdb;
		$wpdb->cursos = $wpdb->prefix . 'cursos';
		
		$curso = $wpdb->delete(
			$wpdb->cursos,
			array(
				'ID' 	=> $data['id']
			),
			array(
				'%s'
			)
		);

		$acao_desc = 'O usuário ' . $data['nome_autor'] . ', deletou o curso. ' . $data['title'];
		$log = adicionarLogDeUsuario( $data, 'remocao', $acao_desc );
		
		return true;
		
	}
	
	return new WP_Error( 'usuario_nao_permitido', 'O usuário não tem permissão para acessar o conteudo!', array( 'status' => 400 ) );
	
}