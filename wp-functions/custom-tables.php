<?php 

// Custom Table PAGAMENTOS
function create_table_pagamentos () {
	
	global $wpdb;
	
	$table_name = $wpdb->prefix.'pagamentos';
	
	if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) {
		
		$wpdb->pagamentos = $wpdb->prefix . 'pagamentos';
		array_push( $wpdb->tables, $wpdb->pagamentos );
		
		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->pagamentos}` (
			`id` bigint(20) unsigned NOT NULl AUTO_INCREMENT,
			`id_pessoa` varchar(20) NOT NULL,
			`nome_pessoa` varchar(255) NOT NULL,
			`valor` varchar(30) NOT NULL,
			`mes` varchar(20) NOT NULL,
			`ano` varchar(4) NOT NULL,
			`data_registro` varchar(50) NOT NULL,
			`data_pagamento` varchar(50) NOT NULL,
			`img` varchar(255) NOT NULL,
			`tipo_pagamento` varchar(50) NOT NULL,
			`pago` varchar(8) NOT NULL DEFAULT 'true',
			PRIMARY KEY (`id`)
		)";
		require_once ( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbdelta( $sql );
	
	}
	
}

// Custom Table DOCUMENTOS
function create_table_documentos () {
	
	global $wpdb;
	
	$table_name = $wpdb->prefix.'documentos';
	
	if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) {
		
		$wpdb->documentos = $wpdb->prefix . 'documentos';
		array_push( $wpdb->tables, $wpdb->documentos );
		
		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->documentos}` (
			`id` bigint(20) unsigned NOT NULl AUTO_INCREMENT,
			`id_pessoa` varchar(20) NOT NULL,
			`nome_pessoa` varchar(255) NOT NULL,
			`tipo_documento` varchar(50) NOT NULL,
			`data` varchar(50) NOT NULL,
			`img` varchar(255) NOT NULL,
			`documento_desc` varchar(255),
			PRIMARY KEY (`id`)
		)";
		require_once ( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbdelta( $sql );
	
	}
	
}

// Custom Table Cursos 
function create_table_cursos () {
	
	global $wpdb;
	
	$table_name = $wpdb->prefix.'cursos';
	
	if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) {
		
		$wpdb->cursos = $wpdb->prefix . 'cursos';
		array_push( $wpdb->tables, $wpdb->cursos );
		
		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->cursos}` (
			`id` bigint(20) unsigned NOT NULl AUTO_INCREMENT,
			`title` varchar(128) NOT NULL,
			`slug` varchar(128) NOT NULL,
			PRIMARY KEY (`id`)
		)";
		require_once ( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbdelta( $sql );
	
	}
	
}

// Custom Table Log de Usuários
function create_table_logs () {
	
	global $wpdb;
	
	$table_name = $wpdb->prefix.'logs';
	
	if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) {
		
		$wpdb->logs = $wpdb->prefix . 'logs';
		array_push( $wpdb->tables, $wpdb->logs );
		
		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->logs}` (
			`id` bigint(20) unsigned NOT NULl AUTO_INCREMENT,
			`data` varchar(80) NOT NULL,
			`mes` varchar(50) NOT NULL,
			`ano` varchar(20) NOT NULL,
			`id_autor` varchar(20) NOT NULL,
			`nome_autor` varchar(128) NOT NULL,
			`acao` varchar(20) NOT NULL,
			`acao_desc` varchar(255) NOT NULL,
			`objeto` varchar(128) NOT NULL,
			`objeto_desc` varchar(128),
			`objeto_nome` varchar(128),
			PRIMARY KEY (`id`)
		)";
		require_once ( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbdelta( $sql );
	
	}
	
}