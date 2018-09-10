<?php 

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

// Require funcoes de Segurança da API
require_once('helper-functions/security-functions.php');
require_once('helper-functions/helper-functions.php');

/*
Notas sobre o Post Type:
- [post_parent] usado para contabilizar se a terefa está concluida ou não! Valores 0 e 1.
- [post_content_filtered] usado para o nome do author
- [post_mime_type] usado para a prioridade da tarefa. Valore = alto, medio, baixo
*/

function tarefas_endpoint_init() {
    
    $namespace = API_VERSAO;

    register_rest_route( $namespace, '/tarefas/', 
    
	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'get_tarefas',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
	    )

    );
    register_rest_route( $namespace, '/tarefas/(?P<id>\d+)', 
        
    	    array(
    	        'methods' 	=> 'GET',
    	        'callback' 	=> 'get_tarefas_id',
    	        'permission_callback' => function () {
    	          	return is_user_logged_in();
    	        }
    	    )
    
        );
    register_rest_route( $namespace, '/tarefa/(?P<id>\d+)', 
    
	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'get_tarefa',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
	    )

    );
    
    register_rest_route( $namespace, '/tarefas/create/',
	    
	    array(
			'methods'   => 'POST',
			'callback'  => 'adicionar_tarefa',
			'permission_callback' => function () {
			  	return is_user_logged_in();
			}
		) 
    
    );
    
    register_rest_route( $namespace, '/tarefas/status/',
        
        array(
    		'methods'   => 'POST',
    		'callback'  => 'modifica_status_tarefa',
    		'permission_callback' => function () {
    		  	return is_user_logged_in();
    		}
    	) 
    
    );
    
    register_rest_route( $namespace, '/tarefas/update/',
	    
	    array(
			'methods'   => 'POST',
			'callback'  => 'editar_tarefa',
			'permission_callback' => function () {
			  	return is_user_logged_in();
			}
		) 
    
    );
    
    register_rest_route( $namespace, '/tarefas/delete/',
	    
	    array(
			'methods'   => 'POST',
			'callback'  => 'deletar_tarefa',
			'permission_callback' => function () {
			  	return is_user_logged_in();
			}
		) 
    
    );
        
}

add_action( 'rest_api_init', 'tarefas_endpoint_init' );

function get_tarefas( $data ) {

	if( verifica_permissao_usuario_atual() ){
		
		$args = array(
		  'post_type'   => 'tarefas',
		  'posts_per_page' => -1,
		  'post_status' 	 => 'all' 
		);
		 
		$tarefas = get_posts( $args );  
		
		$saida = array();
		foreach ( $tarefas as $tarefa ):
		
			$saida[] = array(
				
				'id' 				=> $tarefa->ID,
				'id_autor' 		=> $tarefa->post_author,
				'nome_autor'		=> $tarefa->post_content_filtered,
				'tarefa'			=> $tarefa->post_content,
				'prioridade'  		=> $tarefa->post_mime_type,
				'data'				=> $tarefa->post_date,
				'status'			=> $tarefa->post_status
			);
		
		endforeach;
		
		return $saida;
	
	}

	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );

}

function get_tarefas_id( $data ){

	if( verifica_permissao_usuario_atual() ){

		$args = array( 
			'post_author'   => $data['id_autor'],
			'post_type' => 'tarefas', 
			'posts_per_page' => -1,
			'post_status' 	 => 'all'
		);                   
		                        
		$tarefas = get_posts( $args );
		
		$saida = array();
		foreach ( $tarefas as $tarefa ):
		
			$saida[] = array(
				
				'id' 				=> $tarefa->ID,
				'id_autor' 			=> $tarefa->post_author,
				'nome_autor'		=> $tarefa->post_content_filtered,
				'tarefa'			=> $tarefa->post_content,
				'prioridade'  		=> $tarefa->post_mime_type,
				'data'				=> $tarefa->post_date,
				'status'			=> $tarefa->post_status
			);
		
		endforeach;
		
		return $saida;
	}
	
	return new WP_Error( 'usuario_nao_permitido', 'O usuário não tem permissão para acessar o conteudo!', array( 'status' => 400 ) );

}

function get_tarefa( $data ) {

	if( verifica_permissao_usuario_atual() ){
			                        
		$tarefa = get_post( $data['id'] );
		
		if( $tarefa->post_type === 'tarefas' ){
			return $tarefa;
		}  else { 
			return false;
		}

	}
	
	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );

}

function adicionar_tarefa( $data ) {

	if( verifica_permissao_usuario_atual() ){
		
		$post_id = wp_insert_post( array (   
		   'post_type' 				=> 'tarefas',
		   'post_title' 			=> 'Nova Tarefa por - '.$data['nome_autor'],
		   'post_content_filtered' 	=> $data['nome_autor'],
		   'post_author' 			=> $data['id_autor'],
		   'post_content' 			=> $data['tarefa'],
		   'post_mime_type' 		=> $data['prioridade'],
		   'post_status'			=> 'aberta'
		));
		
		if( !is_wp_error($post_id) ){

			$acao_desc = 'O usuário ' . $data['nome_autor'] . ' adicionou uma tarefa';
			$log = adicionarLogDeUsuario( $data, 'adicao', $acao_desc );

			return true;

		} else {

			return false;

		}
		
	}
	
	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );
	
		
}

function editar_tarefa( $data ) {

	if( verifica_permissao_usuario_atual() ){

		$args = array(
		  'ID'           	=> $data['id'],
		  'post_content' 	=> $data['tarefa'],
		  'post_mime_type' 	=> $data['prioridade'],
		);
		
		$post_id = wp_update_post( $args );

		if( !is_wp_error($post_id) ){
			
			$acao_desc = 'O usuário ' . $data['nome_autor'] . ' editou uma tarefa';
			$log = adicionarLogDeUsuario( $data, 'edicao', $acao_desc );

			return true;

		} else {

			return false;

		}
		
	}
	
	return new WP_Error( 'usuario_nao_permitido', 'O usuário não tem permissão para acessar o conteudo!', array( 'status' => 400 ) );
	
}

function modifica_status_tarefa( $data ) {
	
	if( verifica_permissao_usuario_atual() ){
	
		$args = array(
		  'ID'      => $data['id'],
		  'post_status' 	=> $data['status']
		);
		
		$post_id = wp_update_post( $args );
		
		if( !is_wp_error($post_id) ){
			
			return true;

		} else {

			return false;

		}
		
	}
	
	return new WP_Error( 'usuario_nao_permitido', 'O usuário não tem permissão para acessar o conteudo!', array( 'status' => 400 ) );
	
}

function deletar_tarefa( $data ) {

	if( verifica_permissao_usuario_atual() ){
	
		$post = wp_delete_post( $data['id'], true );
		
		if( !is_wp_error($post->ID) ){
			
			$acao_desc = 'O usuário ' . $data['nome_autor'] . ' deletou uma tarefa';
			$log = adicionarLogDeUsuario( $data, 'remocao', $acao_desc );

			return true;


		} else {

			return false;

		}
		
	}
	
	return new WP_Error( 'usuario_nao_permitido', 'O usuário não tem permissão para acessar o conteudo!', array( 'status' => 400 ) );
	
}