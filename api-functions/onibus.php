<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

// Require funcoes de Segurança da API
require_once('helper-functions/security-functions.php');
require_once('helper-functions/helper-functions.php');

/*
Notas sobre o Post Type:
- [post_content_filtered]
- [post_mime_type]
*/

function onibus_endpoint_init() {

	$namespace = API_VERSAO;

    register_rest_route( $namespace, '/onibus/get-info-geral/',

	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'get_informacoes_gerais_onibus',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
	    )

    );
    register_rest_route( $namespace, '/onibus/set-info-geral/',

	    array(
	        'methods' 	=> 'POST',
	        'callback' 	=> 'set_informacoes_gerais_onibus',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
	    )

    );
    register_rest_route( $namespace, '/onibus/',

	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'get_all_onibus',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
	    )

    );
    register_rest_route( $namespace, '/onibus/(?P<id>\d+)',

	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'get_onibus_by_id',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
	    )

    );
    register_rest_route( $namespace, '/onibus/create/',

	    array(
			'methods'   => 'POST',
			'callback'  => 'adicionar_onibus',
			'permission_callback' => function () {
			  	return is_user_logged_in();
			}
		)

    );
    register_rest_route( $namespace, '/onibus/update/',

	    array(
			'methods'   => 'POST',
			'callback'  => 'editar_onibus',
			'permission_callback' => function () {
			  	return is_user_logged_in();
			}
		)

    );
    register_rest_route( $namespace, '/onibus/delete/',

	    array(
			'methods'   => 'POST',
			'callback'  => 'deletar_onibus',
			'permission_callback' => function () {
			  	return is_user_logged_in();
			}
		)

    );

}

add_action( 'rest_api_init', 'onibus_endpoint_init' );


/*
* Funções de Inicialização
*/
function create_post_informacoes_gerais_onibus( $informacoes ) {

	$args = array(
	  'post_type'   	=> 'onibus',
	  'posts_per_page' 	=> 1,
	  'post_status' 	=> 'private',
	  'post_content_filtered' => 'onibus_info_geral'
	);

	$post = get_posts( $args );

	if( count($post) === 0 ) {

		$post_id = wp_insert_post( array (
		   'post_type' 				=> 'onibus',
		   'post_parent'    		=> 0,
		   'post_title' 			=> 'Informações Gerais dos Onibus',
		   'post_author' 			=> 1,
		   'post_status' 			=> 'private',
		   'post_content_filtered' 	=> 'onibus_info_geral',
		   'post_content'			=> maybe_serialize($informacoes)
		));

		if( !is_wp_error($post_id) ){

			return true;

		} else {

			return false;
		}

	}

}

/*
* Funções da API
*/
function get_informacoes_gerais_onibus() {

	$args = array(
	  'post_type'   	=> 'onibus',
	  'posts_per_page' 	=> 1,
	  'post_status' 	=> 'private',
	  'post_content_filtered' => 'onibus_info_geral'
	);

	$post = get_posts( $args );
	$saida = array();

	if( count($post) != 0 ){
		$informacoes = maybe_unserialize($post[0]->post_content);
		$saida = array(
			'id' 			=> $post[0]->ID,
			'post_status'	=> $post[0]->post_content_filtered,
			'post_modified' => $post[0]->post_modified,
			'informacoes' 	=> $informacoes
		);
	}
	
	return $saida;

}
function set_informacoes_gerais_onibus( $data ) {

	if( verifica_permissao_usuario_atual() ){

		if( $data['id'] ){

			$args = array(
				'ID'           			=> $data['id'],
				'post_content' 			=> maybe_serialize($data['informacoes']),
				'post_status' 			=> 'private',
				'post_content_filtered' => 'onibus_info_geral',
				'post_author'			=> $data['admin_id'],
				'post_excerpt'			=> $data['admin_nome']
			);
	
			wp_update_post( $args );
			
		} else {
			// Call First time
			create_post_informacoes_gerais_onibus( $data['informacoes'] );
		}

		$acao_desc = 'O usuário ' . $data['nome_autor'] . ', editou as informações gerais dos onibus';
		$log = adicionarLogDeUsuario( $data, 'edicao', $acao_desc );
		
		return true;

	}

	return new WP_Error( 'usuario_nao_permitido', 'O usuário não tem permissão para acessar o conteudo!', array( 'status' => 400 ) );
}

function get_all_onibus( $data ) {

	if( verifica_permissao_usuario_atual() ){

		$args = array(
		  'post_type'   	=> 'onibus',
		  'post_status' 	=> 'publish'
		);

		$onibus_lista = get_posts( $args );

		$saida = array();

		foreach ( $onibus_lista as $onibus ) {

			//Recupera informaçoes do associado
			$onibus_meta 	= get_postdata( $onibus->ID );
			$onibus_info 	= get_post_meta( $onibus->ID, 'onibus_info', true );
			$onibus_rota_1 	= get_post_meta( $onibus->ID, 'onibus_rota_1', true );
			$onibus_rota_2 	= get_post_meta( $onibus->ID, 'onibus_rota_2', true );

			$saida[] = array(
			    'id' 				 	=> $onibus->ID,
			    'title' 			 	=> $onibus_info['title'],
			    'status' 			 	=> $onibus_info['status'],
			    'turno' 				=> $onibus_info['turno'],
			    'cargo' 				=> $onibus_info['cargo'],
			    'avisos' 				=> maybe_unserialize( $onibus_info['avisos'] ),
			    'contatos' 				=> maybe_unserialize( $onibus_info['contatos'] ),
			    'hora_saida' 			=> $onibus_info['hora_saida'],
			    'hora_retorno' 			=> $onibus_info['hora_retorno'],
			    'data_modificado'		=> $onibus->post_modified,
			    'qtd_rotas'				=> $onibus_info['qtd_rotas'],
			    'rota_1' 				=> maybe_unserialize( $onibus_rota_1 ),
			    'rota_2' 				=> maybe_unserialize( $onibus_rota_2 ),
			    'admin_id'				=> $onibus->post_author,
			    'admin_nome'			=> $onibus->post_excerpt
			);

		}

		return $saida;


	}

	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );

}

function get_onibus_by_id( $data ){

	if( verifica_permissao_usuario_atual() ){

		$args = array(
		  'ID'				=> $data['id'],
		  'post_type'   	=> 'onibus',
		  'post_status' 	=> 'publish',
		  'posts_per_page' 	=> 1
		);

		$onibus = get_posts( $args );

		$saida = array();

		//Recupera informaçoes do associado
		$onibus_meta 	= get_postdata( $onibus->ID );
		$onibus_info 	= get_post_meta( $onibus->ID, 'onibus_info', true );
		$onibus_rota_1 	= get_post_meta( $onibus->ID, 'onibus_rota_1', true );
		$onibus_rota_2 	= get_post_meta( $onibus->ID, 'onibus_rota_2', true );

		$saida = array(
		    'id' 				 	=> $onibus->ID,
		    'title' 			 		=> $onibus_info['title'],
		    'status' 			 	=> $onibus_info['status'],
		    'turno' 				=> $onibus_info['turno'],
		    'cargo' 				=> $onibus_info['cargo'],
		    'avisos' 				=> maybe_unserialize( $onibus_info['avisos'] ),
		    'contatos' 				=> maybe_unserialize( $onibus_info['contatos'] ),
		    'hora_saida' 			=> $onibus_info['hora_saida'],
		    'hora_retorno' 			=> $onibus_info['hora_retorno'],
		    'data_modificado'		=> $onibus->post_modified,
		    'qtd_rotas'				=> $onibus_info['qtd_rotas'],
		    'rota_1' 				=> maybe_unserialize( $onibus_rota_1 ),
		    'rota_2' 				=> maybe_unserialize( $onibus_rota_2 ),
		    'admin_id'				=> $onibus->post_author,
		    'admin_nome'			=> $onibus->post_excerpt
		);

		return $saida;
	}

	return new WP_Error( 'usuario_nao_permitido', 'O usuário não tem permissão para acessar o conteudo!', array( 'status' => 400 ) );

}

function adicionar_onibus( $data ) {

	if( verifica_permissao_usuario_atual() ){

		$id_onibus = wp_insert_post( array (
		   'post_type' 				=> 'onibus',
		   'post_status'			=> 'publish',
		   'post_title' 			=> validacao_dados( $data['title'] ),
		   'post_author' 			=> validacao_dados( $data['admin_id'] ),
		   'post_excerpt' 			=> validacao_dados( $data['admin_nome'] ),
		   'post_content_filtered'	=> validacao_dados( $data['status'] ),
		   'post_content'			=> $data['title'] . ' - ' . $data['turno']
		));

		if( !is_wp_error( $id_onibus ) ) {

			$onibus_info = array(
				'title' 			 	=> validacao_dados( $data['title'] ),
				'status' 		 		=> validacao_dados( $data['status'] ),
				'status_desc' 	 		=> validacao_dados( $data['status_desc'], 'textarea' ),
				'turno' 		 		=> validacao_dados( $data['turno'] ),
				'cargo' 		 		=> validacao_dados( $data['cargo'] ),
				'avisos' 		 		=> validacao_dados( maybe_serialize( $data['avisos'] ) ),
				'contatos' 		 		=> validacao_dados( maybe_serialize( $data['contatos'] ) ),
				'hora_saida' 			=> validacao_dados( $data['hora_saida'] ),
				'hora_retorno' 			=> validacao_dados( $data['hora_retorno'] ),
				'qtd_rotas' 		 	=> validacao_dados( $data['qtd_rotas'] )
			);

			$id_onibus_info = add_post_meta( $id_onibus, 'onibus_info', $onibus_info );

			$id_rota_1 = add_post_meta( $id_onibus, 'onibus_rota_1', maybe_serialize( $data['rota_1'] ));
			$id_rota_2 = add_post_meta( $id_onibus, 'onibus_rota_2', maybe_serialize( $data['rota_2'] ));

			if( !is_wp_error( $id_onibus_info ) && !is_wp_error( $id_rota_1 ) && !is_wp_error( $id_rota_2 ) ) {

				$acao_desc = 'O usuário ' . $data['nome_autor'] . ', adicionou um onibus '. $data['objeto_nome'];
				$log = adicionarLogDeUsuario( $data, 'adicao', $acao_desc );
				
				return true;
					

			} else {

				return new WP_Error( 'error_ao_inserir', 'Erro ao inserir!', array( 'status' => 400 ) );

			}

		} else {

			return new WP_Error( 'error_ao_inserir', 'Erro ao inserir!', array( 'status' => 400 ) );

		}

	}

	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );


}

function editar_onibus( $data ) {

	if( verifica_permissao_usuario_atual() ){

		$args = array(
			'ID'           			=> $data['id'],
			'post_type' 			=> 'onibus',
			'post_title' 			=> validacao_dados( $data['title'] ),
			'post_author' 			=> validacao_dados( $data['admin_id'] ),
			'post_excerpt' 			=> validacao_dados( $data['admin_nome'] ),
			'post_content_filtered'	=> validacao_dados( $data['status'] ),
			'post_content'			=> $data['title'] . ' - ' . $data['turno']
		);

		$id_onibus = wp_update_post( $args );

		// Atualiza meta values associado
		$meta_values = get_post_meta( $id_onibus, 'onibus_info', true );

		if( $data['title'] !== $meta_values['title'] ) $meta_values['title'] = validacao_dados( $data['title']);
		if( $data['status'] !== $meta_values['status'] ) $meta_values['status'] = validacao_dados( $data['status']);
		if( $data['status_desc'] !== $meta_values['status_desc'] ) $meta_values['status_desc'] = validacao_dados( $data['status_desc']);
		if( $data['turno'] !== $meta_values['turno'] ) $meta_values['turno'] = validacao_dados( $data['turno']);
		if( $data['cargo'] !== $meta_values['cargo'] ) $meta_values['cargo'] = validacao_dados( $data['cargo']);
		if( $data['hora_saida'] !== $meta_values['hora_saida'] ) $meta_values['hora_saida'] = validacao_dados( $data['hora_saida']);
		if( $data['hora_retorno'] !== $meta_values['hora_retorno'] ) $meta_values['hora_retorno'] = validacao_dados( $data['hora_retorno']);
		if( $data['qtd_rotas'] !== $meta_values['qtd_rotas'] ) $meta_values['qtd_rotas'] = validacao_dados( $data['qtd_rotas']);

		$meta_info = update_post_meta( $id_onibus, 'onibus_info', $meta_values );

		$id_rota_1 = update_post_meta( $id_onibus, 'onibus_rota_1', maybe_serialize( $data['rota_1'] ));
		$id_rota_2 = update_post_meta( $id_onibus, 'onibus_rota_2', maybe_serialize( $data['rota_2'] ));


		$acao_desc = 'O usuário ' . $data['nome_autor'] . ', editou informações do onibus '. $data['objeto_nome'];
		$log = adicionarLogDeUsuario( $data, 'edicao', $acao_desc );

		return true;

	}

	return new WP_Error( 'usuario_nao_permitido', 'O usuário não tem permissão para acessar o conteudo!', array( 'status' => 400 ) );

}

function deletar_onibus( $data ) {

	if( verifica_permissao_usuario_atual() ){

		$post_id = wp_delete_post( $data['id'], true );

		if( !is_wp_error($post_id) ){

			$acao_desc = 'O usuário ' . $data['nome_autor'] . ', deletou o onibus '. $data['objeto_nome'];
			$log = adicionarLogDeUsuario( $data, 'remocao', $acao_desc );
			return true;

		}

	}

	return new WP_Error( 'usuario_nao_permitido', 'O usuário não tem permissão para acessar o conteudo!', array( 'status' => 400 ) );

}
