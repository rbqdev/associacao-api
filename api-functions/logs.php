<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once('helper-functions/security-functions.php');
require_once('helper-functions/helper-functions.php');

function logs_endpoint_init() {

    $namespace = API_VERSAO;

    register_rest_route( $namespace, '/logs',

	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'getLogsDeUsuarios',
	        'permission_callback' => function () {
	          	return is_user_logged_in();
	        }
    	)

	);

    register_rest_route( $namespace, '/logs/pesquisa',

	    array(
	        'methods' 	=> 'GET',
	        'callback' 	=> 'getLogsPesquisa',
	        // 'permission_callback' => function () {
	        //   	return is_user_logged_in();
	        // }
    	)

    );

}

add_action( 'rest_api_init', 'logs_endpoint_init' );

function getLogsDeUsuarios( $data ) {

	if( verifica_permissao_usuario_atual() ){

        global $wpdb;
		$wpdb->logs = $wpdb->prefix . 'logs';

		$offset = 0;
		if( $data['offset'] != 0 && $data['offset'] != null ) {
			$offset = intval(validacao_dados($data['offset']));
		}

		$data['filtros'] = json_decode($data['filtros']);

		if( $data['filtros'] != null && $data['filtros']->filtro->slug !== 'todos' ) {

			return $wpdb->get_results( "
				SELECT * FROM {$wpdb->logs} WHERE acao = '{$data['filtros']->filtro->slug}' ORDER BY data DESC LIMIT 25 OFFSET {$offset}"
			);

		} else {

			return $wpdb->get_results( "
				SELECT * FROM {$wpdb->logs} ORDER BY data DESC LIMIT 25 OFFSET {$offset}"
			);

		}

	}

	return new WP_Error( 'error_ao_autenticar_usuario', 'Usuario invalido', array( 'status' => 400 ) );

}

function getLogsPesquisa( $data ) {

	// if( verifica_permissao_usuario_atual() ){

		global $wpdb;
		$wpdb->logs = $wpdb->prefix . 'logs';

		$termoPesquisa = validacao_dados( $data['termoPesquisa'] );

		return $wpdb->get_results( "
			SELECT * FROM {$wpdb->logs} 
			WHERE nome_autor LIKE '%{$termoPesquisa}%'
			OR acao LIKE '%{$termoPesquisa}%'
			OR acao_desc LIKE '%{$termoPesquisa}%'
			OR objeto LIKE '%{$termoPesquisa}%'
			OR objeto_desc LIKE '%{$termoPesquisa}%'
			OR objeto_nome LIKE '%{$termoPesquisa}%'
			ORDER BY data DESC
			LIMIT 50"
		);

	// }

	// return new WP_Error( 'error_ao_autenticar', 'Nao foi possivel realizar a pesquisa!', array( 'status' => 400 ) );
}