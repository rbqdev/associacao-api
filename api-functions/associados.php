<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once('helper-functions/security-functions.php');
require_once('helper-functions/helper-functions.php');

function associados_endpoint_init() {

    $version = API_VERSAO;

    register_rest_route( $version, '/associados',

	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'get_associados',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
	    )

    );
    register_rest_route( $version, '/associado/(?P<id>\d+)',

	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'get_associado',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
	    )

	);

	register_rest_route( $version, '/associados/pesquisa',

	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'get_associados_pesquisa',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
	    )

    );

    register_rest_route( $version, '/associados/create/',

	    array(
			'methods'   => 'POST',
			'callback'  => 'adicionar_associado',
	        'permission_callback' => function () {
             	return is_user_logged_in();
           }
		)

    );

    register_rest_route( $version, '/associados/update/',

	    array(
			'methods'   => 'POST',
			'callback'  => 'editar_associado',
	        'permission_callback' => function () {
             	return is_user_logged_in();
           }
		)

    );

}

add_action( 'rest_api_init', 'associados_endpoint_init' );


function get_associados( $data ) {

	if( verifica_permissao_usuario_atual() ){

		$args = array(
			'role__in'     => array( 'associado' ),
			'orderby'      => array('display_name'),
			'order'        => 'ASC',
			'number'	   => 15,
			'offset'	   => 0
		);

		if( $data['offset'] != 0 && $data['offset'] != null ) {
			$args['offset'] = intval(validacao_dados($data['offset']));
		}

		$users = get_users($args);
		$data['filtros'] = json_decode($data['filtros']);

		$saida = array();

		foreach ( $users as $user ):

			$user_meta = get_userdata( $user->ID );
			$user_meta_info = $user_meta->associado_info;
			$dias_da_semana =  maybe_unserialize($user_meta->dias_da_semana);

			if( $data['filtros'] && $data['filtros'] != null && $data['filtros']->filtro->slug != 'todos' ) {

				if( $user_meta_info['status'] === $data['filtros']->filtro->slug ) {

					$saida[] = array(
						'id' 				 	=> $user->ID,
						'nome' 			 		=> $user->display_name,
						'email' 		 		=> $user->user_email,
						'rg' 			 		=> $user_meta_info['rg'],
						'cpf'			 		=> $user_meta_info['cpf'],
						'matricula' 	 		=> $user_meta_info['matricula'],
						'inst' 		 			=> $user_meta_info['inst'],
						'curso_id' 		 		=> $user_meta_info['curso_id'],
						'turno' 		 		=> $user_meta_info['turno'],
						'contato' 		 		=> $user_meta_info['contato'],
						'status' 				=> $user_meta_info['status'],
						'status_desc'			=> $user_meta_info['status_desc'],
						'onibus_id'				=> $user_meta_info['onibus_id'],
						'tipo'					=> $user_meta_info['tipo'],
						'foto'					=> $user_meta_info['foto'],
						'dias_da_semana'		=> array(
							'segunda'	=> $dias_da_semana['segunda'],
							'terca'		=> $dias_da_semana['terca'],
							'quarta'	=> $dias_da_semana['quarta'],
							'quinta'	=> $dias_da_semana['quinta'],
							'sexta'		=> $dias_da_semana['sexta'],
							'sabado'	=> $dias_da_semana['sabado']
						),
						'admin'					=> $user_meta_info['admin'],
						'data_registro_sis'		=> $user->user_registered,
						'data_registro_assoc'	=> $user_meta_info['data_registro_assoc'],
					);
				}

			} else {

				$saida[] = array(
					'id' 				 	=> $user->ID,
					'nome' 			 		=> $user->display_name,
					'email' 		 		=> $user->user_email,
					'rg' 			 		=> $user_meta_info['rg'],
					'cpf'			 		=> $user_meta_info['cpf'],
					'matricula' 	 		=> $user_meta_info['matricula'],
					'inst' 		 			=> $user_meta_info['inst'],
					'curso_id' 		 		=> $user_meta_info['curso_id'],
					'turno' 		 		=> $user_meta_info['turno'],
					'contato' 		 		=> $user_meta_info['contato'],
					'status' 				=> $user_meta_info['status'],
					'status_desc'			=> $user_meta_info['status_desc'],
					'onibus_id'				=> $user_meta_info['onibus_id'],
					'tipo'					=> $user_meta_info['tipo'],
					'foto'					=> $user_meta_info['foto'],
					'dias_da_semana'		=> array(
						'segunda'	=> $dias_da_semana['segunda'],
						'terca'		=> $dias_da_semana['terca'],
						'quarta'	=> $dias_da_semana['quarta'],
						'quinta'	=> $dias_da_semana['quinta'],
						'sexta'		=> $dias_da_semana['sexta'],
						'sabado'	=> $dias_da_semana['sabado']
					),
					'admin'					=> $user_meta_info['admin'],
					'data_registro_sis'		=> $user->user_registered,
					'data_registro_assoc'	=> $user_meta_info['data_registro_assoc'],
				);

			}

	  	endforeach;

	  	wp_reset_query();

	  	return $saida;

	}

	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );

}

function get_associado( $data ) {

	if( verifica_permissao_usuario_atual() ){

		$user = get_user_by('id', $data['id']);

		if( !empty($user) && ( $user->roles[0] === 'associado' ) ){

			// Recupera informaçoes do associado
			$user_meta_info = get_user_meta( $user->ID, 'associado_info' , true );
			// Recupera os dias da semana que o associado ultiliza o serviço
			$dias_da_semana =  maybe_unserialize($user->dias_da_semana);

			$saida = array(
			    'id' 				 	=> $user->ID,
			    'nome' 			 		=> $user->display_name,
			    'email' 		 		=> $user->user_email,
			    'rg' 			 		=> $user_meta_info['rg'],
			    'cpf'			 		=> $user_meta_info['cpf'],
			    'matricula' 	 		=> $user_meta_info['matricula'],
			    'inst' 		 			=> $user_meta_info['inst'],
				'curso_id' 		 		=> $user_meta_info['curso_id'],
			    'turno' 		 		=> $user_meta_info['turno'],
			    'contato' 		 		=> $user_meta_info['contato'],
			    'status' 				=> $user_meta_info['status'],
			    'status_desc'			=> $user_meta_info['status_desc'],
				'onibus_id'				=> $user_meta_info['onibus_id'],
			    'tipo'					=> $user_meta_info['tipo'],
			    'foto'					=> $user_meta_info['foto'],
			    'dias_da_semana'		=> array(
			    	'segunda'	=> $dias_da_semana['segunda'],
			    	'terca'		=> $dias_da_semana['terca'],
			    	'quarta'	=> $dias_da_semana['quarta'],
			    	'quinta'	=> $dias_da_semana['quinta'],
			    	'sexta'		=> $dias_da_semana['sexta'],
			    	'sabado'	=> $dias_da_semana['sabado']
				),
				'admin'					=> $user_meta_info['admin'],
			    'data_registro_sis'		=> $user->user_registered,
			    'data_registro_assoc'	=> $user_meta_info['data_registro_assoc']
			);

			wp_reset_query();

			return $saida;
		}

		return new WP_Error( 'error_ao_listar_associado', 'Nao foi possivel listar os associado!', array( 'status' => 400 ) );

	}

	return new WP_Error( 'error_ao_listar_associado', 'Nao foi possivel listar os associado!', array( 'status' => 400 ) );

}

function get_associados_pesquisa( $data ) {

	if( verifica_permissao_usuario_atual() ){

		$users = new WP_User_Query( array(
			'search'         => '*'. validacao_dados( $data['termoPesquisa'] ) .'*',
			'search_columns' => array(
				'user_login',
				'user_nicename',
				'user_email'
			),
			'role__in'     => array( 'associado' ),
			'orderby'      => array('display_name'),
			'order'        => 'ASC'
		) );

		$users_found = $users->get_results();
		wp_reset_query();

		$saida = array();

		if( count($users_found) != 0 ){

			foreach ( $users_found as $user ):

				//Recupera informaçoes do associado
				$user_meta = get_userdata( $user->ID );
				$user_meta_info = $user_meta->associado_info;

				// Recupera os dias da semana que o associado ultiliza o serviço
				$dias_da_semana =  maybe_unserialize($user_meta->dias_da_semana);

				$saida[] = array(
					'id' 				 	=> $user->ID,
					'nome' 			 		=> $user->display_name,
					'email' 		 		=> $user->user_email,
					'rg' 			 		=> $user_meta_info['rg'],
					'cpf'			 		=> $user_meta_info['cpf'],
					'matricula' 	 		=> $user_meta_info['matricula'],
					'inst' 		 			=> $user_meta_info['inst'],
					'curso_id' 		 		=> $user_meta_info['curso_id'],
					'turno' 		 		=> $user_meta_info['turno'],
					'contato' 		 		=> $user_meta_info['contato'],
					'status' 				=> $user_meta_info['status'],
					'status_desc'			=> $user_meta_info['status_desc'],
					'onibus_id'				=> $user_meta_info['onibus_id'],
					'tipo'					=> $user_meta_info['tipo'],
					'foto'					=> $user_meta_info['foto'],
					'dias_da_semana'		=> array(
						'segunda'	=> $dias_da_semana['segunda'],
						'terca'		=> $dias_da_semana['terca'],
						'quarta'	=> $dias_da_semana['quarta'],
						'quinta'	=> $dias_da_semana['quinta'],
						'sexta'		=> $dias_da_semana['sexta'],
						'sabado'	=> $dias_da_semana['sabado']
					),
					'admin'					=> $user_meta_info['admin'],
					'data_registro_sis'		=> $user->user_registered,
					'data_registro_assoc'	=> $user_meta_info['data_registro_assoc'],
				);

			endforeach;

			return $saida;

		}

		return new WP_Error( 'error_ao_listar_associado', 'Nao foi possivel realizar a pesquisa!', array( 'status' => 400 ) );

	}

	return new WP_Error( 'error_ao_autenticar', 'Nao foi possivel realizar a pesquisa!', array( 'status' => 400 ) );

}

function adicionar_associado ( $data ) {

	if( verifica_permissao_usuario_atual() ){

		// Argumentos do associado
		$args = array(
			'user_nicename' => validacao_dados( $data['nome'] ),
			'display_name'	=> validacao_dados( $data['nome'] ),
			'first_name'	=> validacao_dados( $data['nome'] ),
		  	'user_login'   	=> validacao_dados( $data['email'], 'email' ),
		  	'user_pass'   	=> validacao_dados( $data['senha'] ),
			'user_email' 	=> validacao_dados( $data['email'], 'email' ),
			'role'			=> 'associado',
		);

		// Insere o usuario no banco de dados
		$id_associado = wp_insert_user( $args );

		if( !is_wp_error($id_associado) ){

			$associado_info = array(
				'rg' 			 		=> validacao_dados( $data['rg'] ),
				'cpf' 		 			=> validacao_dados( $data['cpf'] ),
				'matricula' 	 		=> validacao_dados( $data['matricula'] ),
				'inst' 		 			=> validacao_dados( $data['inst'] ),
				'curso_id' 		 		=> validacao_dados( $data['curso_id'] ),
				'turno' 		 		=> validacao_dados( $data['turno'] ),
				'contato' 		 		=> validacao_dados( $data['contato'] ),
				'status' 				=> validacao_dados( $data['status'] ),
				'status_desc' 			=> validacao_dados( $data['status_desc'] ),
				'onibus_id' 		 	=> validacao_dados( $data['onibus_id'] ),
				'tipo' 		 			=> validacao_dados( $data['tipo'] ),
				'data_registro_assoc'	=> validacao_dados( $data['data_registro_assoc'] ),
				'admin'					=> $data['admin'],
			);

			if( $data['foto'] && $data['foto'] !== '' && strpos($data['foto'], 'data:image') !== false ) {

				$nome_associado = strtolower( str_replace(' ', '_', $data['nome'] ));
				$data['foto'] = adicionar_img( $id_associado, $nome_associado, $data['foto'], '/associados' , '/avatars' );
				$associado_info['foto'] = $data['foto'];

			} else {

				$associado_info['foto'] = '';

			}

			$meta_info = add_user_meta( $id_associado, 'associado_info', $associado_info );
			$meta_dias_da_semana = add_user_meta( $id_associado, 'dias_da_semana', maybe_serialize( $data['dias_da_semana'] ));

			if( $meta_info && $meta_dias_da_semana ) {

				$acao_desc = 'O usuário ' . $data['nome_autor'] . ', adicionou um novo associado. '. $data['objeto_nome'];
				$log = adicionarLogDeUsuario( $data, 'adicao', $acao_desc );

				return true;
			}

		} else {

			return new WP_Error( 'error_ao_inserir', 'Erro ao inserir!', array( 'status' => 400 ) );

		}

	}

	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );

}

function editar_associado ( $data ) {

	if( verifica_permissao_usuario_atual() ){

		$args = array(
			'ID'           	=> validacao_dados( $data['id'] ),
			'display_name'	=> validacao_dados( $data['nome'] ),
			'user_pass'		=> validacao_dados( $data['nova_senha'] )
		);

		if( $data['nova_senha'] != '' )
			$args['user_pass'] = validacao_dados( $data['nova_senha'] );

		$id_associado = wp_update_user( $args );

		// Atualiza meta values associado
		$meta_values = get_user_meta( $id_associado, 'associado_info', true );

		if( $data['rg'] !== $meta_values['rg'] ) $meta_values['rg'] = validacao_dados( $data['rg']);
		if( $data['cpf'] !== $meta_values['cpf'] ) $meta_values['cpf'] = validacao_dados( $data['cpf']);
		if( $data['inst'] !== $meta_values['inst'] ) $meta_values['inst'] = validacao_dados( $data['inst']);
		if( $data['curso_id'] !== $meta_values['curso_id'] ) {
			$meta_values['curso_id'] = validacao_dados( $data['curso_id']);
		}
		if( $data['turno'] !== $meta_values['turno'] ) $meta_values['turno'] = validacao_dados( $data['turno']);
		if( $data['contato'] !== $meta_values['contato'] ) $meta_values['contato'] = validacao_dados( $data['contato']);
		if( $data['status'] !== $meta_values['status'] ) $meta_values['status'] = validacao_dados( $data['status']);
		if( $data['status_desc'] !== $meta_values['status_desc'] ) $meta_values['status_desc'] = validacao_dados( $data['status_desc']);
		if( $data['onibus_id'] !== $meta_values['onibus_id'] ) $meta_values['onibus_id'] = validacao_dados( $data['onibus_id']);
		if( $data['tipo'] !== $meta_values['tipo'] ) $meta_values['tipo'] = validacao_dados( $data['tipo']);
		if( $data['admin'] !== $meta_values['admin'] ) $meta_values['admin'] = $data['admin'];

		if( $data['foto'] && $data['foto'] !== '' && strpos($data['foto'], 'data:image') !== false ) {

			$nome_associado = strtolower( str_replace(' ', '_', $data['nome'] ));
			$data['foto'] = adicionar_img( $id_associado, $nome_associado, $data['foto'], '/associados' , '/avatars' );
			$meta_values['foto'] = $data['foto'];

		}

		$meta_info = update_user_meta( $data['id'], 'associado_info', $meta_values );

		// Atualiza dias da semana que ultiliza o serviço
		$meta_values = maybe_unserialize( get_user_meta( $id_associado, 'dias_da_semana', true ) );

		if( $data['dias_da_semana']['segunda'] !== $meta_values['segunda'] ) $meta_values['segunda'] = $data['dias_da_semana']['segunda'];
		if( $data['dias_da_semana']['terca'] !== $meta_values['terca'] ) $meta_values['terca'] = $data['dias_da_semana']['terca'];
		if( $data['dias_da_semana']['quarta'] !== $meta_values['quarta'] ) $meta_values['quarta'] = $data['dias_da_semana']['quarta'];
		if( $data['dias_da_semana']['quinta'] !== $meta_values['quinta'] ) $meta_values['quinta'] = $data['dias_da_semana']['quinta'];
		if( $data['dias_da_semana']['sexta'] !== $meta_values['sexta'] ) $meta_values['sexta'] = $data['dias_da_semana']['sexta'];
		if( $data['dias_da_semana']['sabado'] !== $meta_values['sabado'] ) $meta_values['sabado'] = $data['dias_da_semana']['sabado'];

		$meta_dias_da_semana = update_user_meta( $id_associado, 'dias_da_semana', maybe_serialize($meta_values) );

		$acao_desc = 'O usuário ' . $data['nome_autor'] . ', editou informações do associado '. $data['objeto_nome'];
		$log = adicionarLogDeUsuario( $data, 'edicao', $acao_desc );

		return true;

	}

	return new WP_Error( 'error_ao_autenticar', 'Usuario invalido', array( 'status' => 400 ) );

}