<?php
/*
* Plugin Name: Aeesp Sistema Gerenciador
* Plugin URI:
* Description: Plugin para gerenciamento do sistema da Aeesp - Associação de estudantes de ensino superior de Poções.
* Version: 1.0
* Author: Robson Braga de Queiroz
* Author URI: http://robsonbraga.com/
* License: GPL12
*/

/*
*  Requires WP Functions e REST Functions
*  Funçoes chamadas nos metodos abaixo a partir destes requires
*/
require_once('wp-functions/wp-index.php');
require_once('api-functions/api-index.php');


function init() {

	// Modificar a url/router base do wp-json para api
	add_filter( 'rest_url_prefix', 'modificar_router_base_api');

}

// Funcao para modificar a url da API REST
function modificar_router_base_api() {
 	return 'api';
}

/*
*  Funcçoes fornecidas de wp-functions.php
*
*  Funçoes de Ativação e Desativação do Sistema
*/
function activate_aeesp_sistema(){

	/*
	* Criação de tabelas customizadas
	*/
	create_table_cursos();
	create_table_pagamentos();
	create_table_documentos();
	create_table_logs();

	/*
	* Criação de Post types e Posts padrões
	*/
	create_post_type_advertencias();
	create_post_type_tarefas();
	create_post_type_onibus();

	/*
	* Criação das pastas de imagens
	*/
	create_uploads_pasta( 'associados' );
	create_uploads_pasta( 'motoristas' );

	/*
	* Criação de funcoes de usuarios do sistema
	*/
	create_roles_usuarios();
}

function deactivate_aeesp_sistema(){

	// Criação de funcoes de usuarios do sistema
	remove_roles_usuarios();

	// Remocao do evento de pagamento
	remove_schedule_function();

}


define( 'API_VERSAO', '/v1' );

add_action( 'plugins_loaded', 'init' );
register_activation_hook( __FILE__, 'activate_aeesp_sistema' );
register_deactivation_hook( __FILE__, 'deactivate_aeesp_sistema' );