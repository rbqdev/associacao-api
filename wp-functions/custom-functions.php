<?php 

/* 
*  Usuários
*
*
* Adiciona custom roles de usuario no wordpress
*/
function create_roles_usuarios() {
	add_role('associado', __(
		 'Associado'),
		 array(
			// Nenhuma permissão atribuida para o wordpress
		 )
	);
	add_role('motorista', __(
		 'Motorista'),
		 array(
			// Nenhuma permissão atribuida para o wordpress
		 )
	);
}

// Remove custom roles de usuario no wordpress
function remove_roles_usuarios() {
	remove_role('associado');
	remove_role('motorista');
}


/* 
*  Associados
*
*  Cria a pasta '/associados/' dentro da pasta de uploads do wordpress
*/
function create_uploads_pasta( $nome_pasta ) {
 
    $upload = wp_upload_dir();
    $upload_dir = $upload['basedir'];
    $upload_dir = $upload_dir . '/'. $nome_pasta;
    if (! is_dir($upload_dir)) {
       mkdir( $upload_dir, 0700 );
    }
    
}