<?php 

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

require_once('helper-functions/security-functions.php');
require_once('helper-functions/helper-functions.php');

/*
Notas sobre o Post Type:
- [post_content_filtered] usado para o nome do author da advertencia
- [post_mime_type] usado para a prioridade da tarefa. Valore = alto, medio, baixo
*/

function advertencias_endpoint_init() {
	
	$namespace = API_VERSAO;
    
    register_rest_route( $namespace, '/advertencias/', 
    
	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'get_advertencias',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
	    )

    );
    register_rest_route( $namespace, '/advertencias/(?P<id>\d+)', 
        
    	    array(
    	        'methods' 	=> 'GET',
    	        'callback' 	=> 'get_advertencias_by_id',
    	        'permission_callback' => function () {
    	          	return is_user_logged_in();
    	        }
    	    )
    
        );
    register_rest_route( $namespace, '/advertencia/(?P<id>\d+)', 
    
	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'get_advertencia',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
	    )

    );
    
    register_rest_route( $namespace, '/advertencias/create/',
	    
	    array(
			'methods'   => 'POST',
			'callback'  => 'adicionar_advertencia',
			'permission_callback' => function () {
			  	return is_user_logged_in();
			}
		) 
    
    );
    
    register_rest_route( $namespace, '/advertencias/update/',
	    
	    array(
			'methods'   => 'POST',
			'callback'  => 'editar_advertencia',
			'permission_callback' => function () {
			  	return is_user_logged_in();
			}
		) 
    
    );
    
    register_rest_route( $namespace, '/advertencias/delete/',
	    
	    array(
			'methods'   => 'POST',
			'callback'  => 'deletar_advertencia',
			'permission_callback' => function () {
			  	return is_user_logged_in();
			}
		) 
    
    );
        
}

add_action( 'rest_api_init', 'advertencias_endpoint_init' );

function get_advertencias( $data ) {

	if( verifica_permissao_usuario_atual() ){
		
		$args = array(
		  'post_type'   => 'advertencias',
		  'posts_per_page' => -1 
		);
		 
		$advertencias = get_posts( $args );  
		
		$saida = array();
		foreach ( $advertencias as $advertencia ):
		
			$saida[] = array(
				
				'id' 				=> $advertencia->ID,
				'id_advertido' 		=> $advertencia->post_parent,
				'id_autor' 			=> $advertencia->post_author,
				'nome_autor'		=> $advertencia->post_content_filtered,
				'advertencia'		=> $advertencia->post_content,
				'grau'  			=> $advertencia->post_mime_type,
				'data'				=> $advertencia->post_date
			);
		
		endforeach;
		
		return $saida;
	
	}

	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );

}

function get_advertencias_by_id( $data ){

	if( verifica_permissao_usuario_atual() ){

		$args = array( 
			'post_parent'   => $data['id'],
			'post_type' => 'advertencias', 
			'posts_per_page' => -1 
		);                   
		                        
		$advertencias = get_posts( $args );
		
		$saida = array();
		foreach ( $advertencias as $advertencia ):
		
			$saida[] = array(
				
				'id' 				=> $advertencia->ID,
				'id_advertido' 		=> $advertencia->post_parent,
				'id_autor' 			=> $advertencia->post_author,
				'nome_autor'		=> $advertencia->post_content_filtered,
				'advertencia'		=> $advertencia->post_content,
				'grau'  			=> $advertencia->post_mime_type,
				'data'				=> $advertencia->post_date
			);
		
		endforeach;
		
		return $saida;
	}
	
	return new WP_Error( 'usuario_nao_permitido', 'O usuário não tem permissão para acessar o conteudo!', array( 'status' => 400 ) );

}

function get_advertencia( $data ) {

	if( verifica_permissao_usuario_atual() ){
			                        
		$advertencia = get_post( $data['id'] );
		
		if( $advertencia->post_type === 'advertencias' ){
			return $advertencia;
		}  else { 
			return false;
		}

	}
	
	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );

}

function adicionar_advertencia( $data ) {

	if( verifica_permissao_usuario_atual() ){
		
		$post_id = wp_insert_post( array (   
		   'post_type' 				=> 'advertencias',
		   'post_parent'    		=> $data['id_advertido'], 
		   'post_title' 			=> 'Advertência - '. $data['nome_advertido'],
		   'post_author' 			=> $data['id_autor'],
		   'post_content_filtered' 	=> $data['nome_autor'],
		   'post_content' 			=> $data['advertencia'],
		   'post_mime_type' 		=> $data['grau'],
		   'post_status'			=> 'publish'
		));
		
		if( !is_wp_error($post_id) ){

			$acao_desc = 'O usuário ' . $data['nome_autor'] . ', atribuiu uma advertência ao ' . $data['objeto'] . ' ' . $data['objeto_nome'];
			$log = adicionarLogDeUsuario( $data, 'adicao', $acao_desc );

			return true;
		
		} else {
			return false;
		}
		
	}
	
	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );
	
		
}

function editar_advertencia( $data ) {

	if( verifica_permissao_usuario_atual() ){

		$args = array(
		  'ID'           	=> $data['id'],
		  'post_content' 	=> $data['advertencia'],
		  'post_mime_type' 	=> $data['grau'],
		);
		
		$post_id = wp_update_post( $args );
		
		if( !is_wp_error($post_id) ){
			
			$acao_desc = 'O usuário ' . $data['nome_autor'] . ', editou uma advertência referente ao ' . $data['objeto'] . ' ' . $data['objeto_nome'];
			$log = adicionarLogDeUsuario( $data, 'edicao', $acao_desc );

			return true;
		
		} else {

			return false;

		}
		
	}
	
	return new WP_Error( 'usuario_nao_permitido', 'O usuário não tem permissão para acessar o conteudo!', array( 'status' => 400 ) );
	
}

function deletar_advertencia( $data ) {

	if( verifica_permissao_usuario_atual() ){
	
		$post_id = wp_delete_post( $data['id'], true );

		if( !is_wp_error($post_id) ){
			
			$acao_desc = 'O usuário ' . $data['nome_autor'] . ', deletou uma advertência referente ao ' . $data['objeto'] . ' ' . $data['objeto_nome'];
			$log = adicionarLogDeUsuario( $data, 'remocao', $acao_desc );
	
			return true;
			
		} else {

			return false;

		}
		
	}
	
	return new WP_Error( 'usuario_nao_permitido', 'O usuário não tem permissão para acessar o conteudo!', array( 'status' => 400 ) );
	
}