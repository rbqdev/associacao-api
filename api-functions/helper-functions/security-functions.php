<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
* Veficia permissão do usuario no sistema
*/
function verifica_permissao_usuario_atual(){

	$current_user = wp_get_current_user();
	$meta_values = get_user_meta( $current_user->ID, 'associado_info', true );

	if( !is_wp_error($current_user->ID) ){

		if( $current_user->roles[0] === 'administrator' || ( $current_user->roles[0] === 'associado' && $meta_values['admin']  === true || $meta_values['admin']  === 'true') ) {
			return true;
		}

		return false;
	}

	return false;

}


/*
* Validacao dos dados que vem do APP
*/
function validacao_dados ( $value, $type = '' ) {

	$new_value = '';

	switch ( $type ){

		case 'email':
			$new_value = sanitize_email( $value );
			break;

		case 'textarea':
			$new_value = wp_strip_all_tags( $value );
			break;

		default:
			$new_value = sanitize_text_field( $value );
			break;

	}

	if($new_value === null ) $new_value = '';

	return $new_value;

}