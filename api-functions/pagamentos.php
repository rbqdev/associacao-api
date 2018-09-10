<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Require funcoes de Segurança da API
require_once('helper-functions/security-functions.php');
require_once('helper-functions/helper-functions.php');

function pagamentos_endpoint_init() {

    $namespace = API_VERSAO;

    register_rest_route( $namespace, '/pagamentos',

	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'get_pagamentos',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
	    )

    );
    register_rest_route( $namespace, '/pagamentos/(?P<id>\d+)',

	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'get_pagamentos_id',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
	    )

    );

    register_rest_route( $namespace, '/pagamentos/create/',

	    array(
			'methods'   => 'POST',
			'callback'  => 'adicionar_pagamento',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
		)

    );

    register_rest_route( $namespace, '/pagamentos/update/',

	    array(
			'methods'   => 'POST',
			'callback'  => 'editar_pagamento',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
		)

    );

    register_rest_route( $namespace, '/pagamentos/delete/',

        array(
    		'methods'   => 'POST',
    		'callback'  => 'deletar_pagamento',
           'permission_callback' => function () {
             	return is_user_logged_in();
           }
    	)

    );

}

add_action( 'rest_api_init', 'pagamentos_endpoint_init' );


// Funçoes REST
function get_pagamentos( $data ) {

	if( verifica_permissao_usuario_atual() ){

		global $wpdb;
		$wpdb->pagamentos = $wpdb->prefix . 'pagamentos';

		$saida = $wpdb->get_results( "SELECT * FROM {$wpdb->pagamentos} ORDER BY nome_pessoa" );

		return $saida;
	}

	return new WP_Error( 'usuario_nao_permitido', 'O usuário não tem permissão para acessar o conteudo!', array( 'status' => 400 ) );

}

function get_pagamentos_id( $data ){

	if( verifica_permissao_usuario_atual() ){

		global $wpdb;

		$wpdb->pagamentos = $wpdb->prefix . 'pagamentos';

		$id_pessoa = $data['id'];

		$saida = $wpdb->get_results( "SELECT * FROM {$wpdb->pagamentos} WHERE id_pessoa = $id_pessoa ORDER BY ano DESC, data_registro DESC" );

		return $saida;
	}

	return new WP_Error( 'usuario_nao_permitido', 'O usuário não tem permissão para acessar o conteudo!', array( 'status' => 400 ) );

}

function adicionar_pagamento( $data ) {

	if( verifica_permissao_usuario_atual() ){

		global $wpdb;
		$wpdb->pagamentos = $wpdb->prefix . 'pagamentos';

		$path_imagem = 'false';

		// Verifica se o pagamento é valido, ou é um registro de divida do pessoa, de pago == false, sendo assim nenhuma imagem é inserida
		if( $data['pago'] !== 'false' ) {

			$nome_pasta = strtolower(str_replace(' ', '_', $data['nome_pessoa']));
			$imagem = $data['img'];
			$path_imagem = adicionar_img( $data['id_pessoa'], $nome_pasta, $imagem, $data['pasta'], '/pagamentos' );

		}

		if( $path_imagem && $path_imagem !== null && $path_imagem !== '' ){

			$pagamento = $wpdb->insert(
				$wpdb->pagamentos,
				array(
					'id_pessoa' 		=> $data['id_pessoa'],
					'nome_pessoa' 		=> $data['nome_pessoa'],
					'tipo_pagamento' 	=> $data['tipo_pagamento'],
					'valor' 			=> $data['valor'],
					'mes' 				=> $data['mes'],
					'ano' 				=> $data['ano'],
					'data_registro' 	=> $data['data_registro'],
					'data_pagamento' 	=> $data['data_pagamento'],
					'img' 				=> $path_imagem,
					'pago'				=> $data['pago']
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s'
				)
			);

			if( $pagamento ){

				$acao_desc = 'O usuário ' . $data['nome_autor'] . ', adicionou um pagamento referente ao ' . $data['objeto'] . ' ' . $data['objeto_nome'];
				$log = adicionarLogDeUsuario( $data, 'adicao', $acao_desc );

				return true;

			} else {

				return new WP_Error( 'error_ao_inserir', 'Erro ao inserir!', array( 'status' => 400 ) );

			}

		}

	}

	return new WP_Error( 'usuario_nao_permitido', 'O usuário não tem permissão para acessar o conteudo!', array( 'status' => 400 ) );

}

function editar_pagamento( $data ) {

	if( verifica_permissao_usuario_atual() ){

		global $wpdb;
		$wpdb->pagamentos = $wpdb->prefix . 'pagamentos';

		// Se existe uma nova imagem
		if( $data['img'] && $data['img'] !== '' && strpos($data['img'], 'data:image') !== false ) {
			$nome_pasta = strtolower(str_replace(' ', '_', $data['nome_pessoa']));
			$path_imagem = adicionar_img( $data['id_pessoa'], $nome_pasta, $data['img'], $data['pasta'], '/pagamentos' );
			$data['img'] = $path_imagem;
		}

		$pagamento = $wpdb->update(
			$wpdb->pagamentos,
			array(
				'pago' 				=> $data['pago'],
				'valor' 			=> $data['valor'],
				'data_pagamento' 	=> $data['data_pagamento'],
				'img'				=> $data['img']
			),
			array(
				'ID' 	=> $data['id'],
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s'
			),
			array(
				'%s',
			)
		);

		if( $pagamento ){

			$acao_desc = 'O usuário ' . $data['nome_autor'] . ', editou um pagamento referente ao ' . $data['objeto'] . ' ' . $data['objeto_nome'];
			$log = adicionarLogDeUsuario( $data, 'edicao', $acao_desc );

			// Atualiza o status do associado para regular, caso exista a variavel
			if( $data['status_para_regular'] && ( $data['status_para_regular'] === true || $data['status_para_regular'] === 'true' ) ) {

				$meta_values = get_user_meta( $data['id_pessoa'], 'associado_info', true );
				$meta_values['status'] = 'regular';
				$meta_values = update_user_meta( $data['id_pessoa'], 'associado_info', $meta_values );

			}

			return true;

		} else {

			return new WP_Error( 'error_ao_inserir', 'Erro ao inserir!', array( 'status' => 400 ) );

		}

	}

	return new WP_Error( 'usuario_nao_permitido', 'O usuário não tem permissão para acessar o conteudo!', array( 'status' => 400 ) );

}

function deletar_pagamento( $data ) {

	if( verifica_permissao_usuario_atual() ){

		global $wpdb;
		$wpdb->pagamentos = $wpdb->prefix . 'pagamentos';

		$pagamento = $wpdb->delete(
			$wpdb->pagamentos,
			array(
				'ID' => $data['id']
			),
			array(
				'%s',
			)
		);

		if( $pagamento ){

			$acao_desc = 'O usuário ' . $data['nome_autor'] . ', deletou um pagamento referente ao ' . $data['objeto'] . ' ' . $data['objeto_nome'];
			$log = adicionarLogDeUsuario( $data, 'remocao', $acao_desc );

			return true;

		} else {

			return new WP_Error( 'error_ao_deletar', 'Erro ao deletar!', array( 'status' => 400 ) );

		}

	}

	return new WP_Error( 'usuario_nao_permitido', 'O usuário não tem permissão para acessar o conteudo!', array( 'status' => 400 ) );

}