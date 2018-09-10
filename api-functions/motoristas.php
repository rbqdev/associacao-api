<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 
	
// Require funcoes de Segurança da API
require_once('helper-functions/security-functions.php');
require_once('helper-functions/helper-functions.php');

function motoristas_endpoint_init() {
    
    $namespace = API_VERSAO;

    register_rest_route( $namespace, '/motoristas/', 
    
	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'get_motoristas',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
	    )

    );
    register_rest_route( $namespace, '/motoristas/(?P<id>\d+)', 
    
	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'get_motorista',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
	    )

    );
    
    
    register_rest_route( $namespace, '/motoristas/create/',
	    
	    array(
			'methods'   => 'POST',
			'callback'  => 'adicionar_motorista',
	        'permission_callback' => function () {
             	return is_user_logged_in();
            }
		) 
    
    );
    
    register_rest_route( $namespace, '/motoristas/update/',
	    
	    array(
			'methods'   => 'POST',
			'callback'  => 'editar_motorista',
	        'permission_callback' => function () {
             	return is_user_logged_in();
            }
		) 
    
    );
        
}

add_action( 'rest_api_init', 'motoristas_endpoint_init' );


function get_motoristas( $data ) {

	if( verifica_permissao_usuario_atual() ){
	
		$args = array(
			'role__in'     => array( 'motorista' ),
			'orderby'      => 'display_name',
			'order'        => 'ASC',
		 );
		 
		$motoristas = get_users($args);
		
		$saida = array();
		
		foreach ( $motoristas as $motorista ):
		
			//Recupera informaçoes do motorista
			$motorista_data = get_userdata( $motorista->ID );
			$motorista_info = $motorista_data->motorista_info;
			
			$saida[] = array(
			    'id' 				 	=> $motorista->ID,
			    'nome' 			 		=> $motorista->display_name,
				'rg' 			 		=> $motorista_info['rg'],
			    'cpf'			 		=> $motorista_info['cpf'],
			    'contato_1' 		 	=> $motorista_info['contato_1'],
			    'turno_1' 		 		=> $motorista_info['turno_1'],
				'inst_1' 		 		=> $motorista_info['inst_1'],
				'contato_2' 		 	=> $motorista_info['contato_2'],
			    'turno_2' 		 		=> $motorista_info['turno_2'],
			    'inst_2' 		 		=> $motorista_info['inst_2'],
				'qtdTurnos'				=> $motorista_info['qtdTurnos'],
			    'status' 				=> $motorista_info['status'],
			    'tipo'					=> $motorista_info['tipo'],
			    'foto'					=> $motorista_info['foto'],
			    'data_registro_sis'		=> $motorista->user_registered,
			    'data_registro_assoc'	=> $motorista_info['data_registro_assoc'],
			);
			
	  	endforeach;
	  	
	  	wp_reset_query();
	  	
	  	return $saida;
	
	}
	
	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );
	
}

function get_motorista( $data ) {

	if( verifica_permissao_usuario_atual() ){
		
		$motorista = get_user_by('id', $data['id']);
		
		if( !empty($motorista) && $motorista->roles[0] === 'motorista' ){

			$motorista_data = get_userdata( $motorista->ID );
			$motorista_info = $motorista_data->motorista_info;
			
			$saida = array(
			    'id' 				 	=> $motorista->ID,
			    'nome' 			 		=> $motorista->display_name,
				'rg' 			 		=> $motorista_info['rg'],
			    'cpf'			 		=> $motorista_info['cpf'],
			    'contato_1' 		 	=> $motorista_info['contato_1'],
			    'turno_1' 		 		=> $motorista_info['turno_1'],
				'inst_1' 		 		=> $motorista_info['inst_1'],
				'contato_2' 		 	=> $motorista_info['contato_2'],
			    'turno_2' 		 		=> $motorista_info['turno_2'],
			    'inst_2' 		 		=> $motorista_info['inst_2'],
				'qtdTurnos'				=> $motorista_info['qtdTurnos'],
			    'status' 				=> $motorista_info['status'],
			    'tipo'					=> $motorista_info['tipo'],
			    'foto'					=> $motorista_info['foto'],
			    'data_registro_sis'		=> $motorista->user_registered,
			    'data_registro_assoc'	=> $motorista_info['data_registro_assoc'],
			);
			
			wp_reset_query();
				
			return $saida;
		}

		return new WP_Error( 'error_ao_listar_motorista', 'Nao foi possivel listar os motorista!', array( 'status' => 400 ) );
		
	}
	
	return new WP_Error( 'error_ao_listar_motorista', 'Nao foi possivel listar os motorista!', array( 'status' => 400 ) );
	
}

function adicionar_motorista ( $data ) {

	if( verifica_permissao_usuario_atual() ){

		$data['nome'] = validacao_dados( $data['nome'] );	
		$primeiroNome = explode(" ", $data['nome']);

		$args = array(
			'user_nicename' => $data['nome'],
			'display_name'	=> $data['nome'],
			'first_name'	=> strtolower($primeiroNome[0]),
			'user_login'   	=> $data['nome'],
			'user_pass'   	=> md5($data['nome']),
			'role'			=> 'motorista'
		);

		$id_motorista = wp_insert_user( $args );
		
		if( !is_wp_error($id_motorista) ){

			$motorista_info = array(
				'rg' 			 		=> validacao_dados( $data['rg'] ),
				'cpf' 		 			=> validacao_dados( $data['cpf'] ),
				'contato_1' 		 	=> validacao_dados( $data['contato_1'] ),
				'contato_2' 		 	=> validacao_dados( $data['contato_2'] ),
				'turno_1' 		 		=> validacao_dados( $data['turno_1'] ),
				'inst_1' 		 		=> validacao_dados( $data['inst_1'] ),
				'turno_2' 		 		=> validacao_dados( $data['turno_2'] ),
				'inst_2' 		 		=> validacao_dados( $data['inst_2'] ),
				'qtdTurnos'				=> validacao_dados( $data['qtdTurnos'] ),
				'status' 				=> validacao_dados( $data['status'] ),
				'tipo' 					=> validacao_dados( $data['tipo'] ),
				'data_registro_assoc'	=> validacao_dados( $data['data_registro_assoc'] )
			);
			
			if( $data['foto'] && $data['foto'] !== '' && strpos($data['foto'], 'data:image') !== false ){

				$nome_pasta = strtolower( str_replace(' ', '_', $data['nome'] ));
				$data['foto'] = adicionar_img( $id_motorista, $nome_pasta, $data['foto'], '/motoristas' , '/avatars' );
				$motorista_info['foto'] = $data['foto'];
				
			} else {
			
				$motorista_info['foto'] = $data['foto'];
				
			}

			$meta_info = add_user_meta( $id_motorista, 'motorista_info', $motorista_info );

			if( $meta_info ) {

				$acao_desc = 'O usuário ' . $data['nome_autor'] . ', adicionou um novo motorista. '. $data['objeto_nome'];
				$log = adicionarLogDeUsuario( $data, 'adicao', $acao_desc );

				return true;

			}
	
		} else {

			return new WP_Error( 'error_ao_inserir', 'Erro ao inserir!', array( 'status' => 400 ) );

		}
	
	}
		
	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );
	
}

function editar_motorista ( $data ) {

	if( verifica_permissao_usuario_atual() ){
		
		$args = array(
			'ID'           	=> validacao_dados( $data['id'] ),
			'display_name'	=> validacao_dados( $data['nome'] ),
			'role'			=> 'motorista'
		);

		$id_motorista = wp_update_user( $args );

		$motorista = get_user_meta( $id_motorista, 'motorista_info', true );
		
		if( $data['rg'] !== $motorista['rg'] ) $motorista['rg'] = validacao_dados( $data['rg'] );
		if( $data['cpf'] !== $motorista['cpf'] ) $motorista['cpf'] = validacao_dados( $data['cpf'] );
		if( $data['contato_1'] !== $motorista['contato_1'] ) $motorista['contato_1'] = validacao_dados( $data['contato_1']);
		if( $data['turno_1'] !== $motorista['turno_1'] ) $motorista['turno_1'] = validacao_dados( $data['turno_1']);
		if( $data['inst_1'] !== $motorista['inst_1'] ) $motorista['inst_1'] = validacao_dados( $data['inst_1']);
		if( $data['status'] !== $motorista['status'] ) $motorista['status'] = validacao_dados( $data['status']);
		if( $data['tipo'] !== $motorista['tipo'] ) $motorista['tipo'] = validacao_dados( $data['tipo']);
		if( $data['contato_2'] !== $motorista['contato_2'] ) $motorista['contato_2'] = validacao_dados( $data['contato_2']);
		if( $data['inst_2'] !== $motorista['inst_2'] ) $motorista['inst_2'] = validacao_dados( $data['inst_2']);
		if( $data['turno_2'] !== $motorista['turno_2'] ) $motorista['turno_2'] = validacao_dados( $data['turno_2']);

		if( $data['foto'] && $data['foto'] !== '' && strpos($data['foto'], 'data:image') !== false ){
			
			$nome_pasta = strtolower( str_replace(' ', '_', $data['nome'] ));
			$data['foto'] = adicionar_img( $id_motorista, $nome_pasta, $data['foto'], '/motoristas' , '/avatars' );
			$motorista['foto'] = $data['foto'];
			
		} 
				
		$motorista = update_user_meta( $id_motorista, 'motorista_info', $motorista );

		if( $motorista ) {

			$acao_desc = 'O usuário ' . $data['nome_autor'] . ', editou informações do motorista '. $data['objeto_nome'];
			$log = adicionarLogDeUsuario( $data, 'edicao', $acao_desc );

			return true;
		}

	}
		
	return new WP_Error( 'error_ao_autenticar', 'Usuario invalido', array( 'status' => 400 ) );
	
}