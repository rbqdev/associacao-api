<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Funçoes para salvar imagem no servidor
function adicionar_img ( $associado_id, $associado_nome, $image64, $pasta_base = '/associados', $pasta_destino ) {

  $image = $image64;

  if( !$image ) return false;

  $check = getimagesize( $image );

  // Confere se é realmente uma imagem
  if($check !== false) {

	  $tipo = $check["mime"];

	  $extensao = '';

	  if ( $tipo == "image/png" )
	  $extensao = ".png";

	  if ( $tipo == "image/jpeg" || $tipo == "image/jpg" )
	  $extensao = ".jpg";

	  if( $extensao ) {

	      $imagedata = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image));

	      $uploadDir = wp_upload_dir();

	      $random_img_id = generateRandomString();

	      $diretorio = $uploadDir['basedir'] . $pasta_base . '/' . $associado_nome . '-ID-' . $associado_id . $pasta_destino . $uploadDir['subdir'] . '/' . $random_img_id . $extensao;

	      file_force_contents( $diretorio, $imagedata );

	      $image = wp_get_image_editor( $diretorio );

	      $diretorio_150 = $diretorio = $uploadDir['basedir'] . $pasta_base . '/'. $associado_nome . '-ID-' . $associado_id . $pasta_destino . $uploadDir['subdir'] . '/' . $random_img_id .'-150x150'. $extensao;
	      $diretorio_600 = $diretorio = $uploadDir['basedir'] . $pasta_base . '/' . $associado_nome . '-ID-' . $associado_id . $pasta_destino . $uploadDir['subdir'] . '/' . $random_img_id .'-600x600'. $extensao;


	      if ( ! is_wp_error( $image ) ) {

	          $image->resize( 600, 600, true );
	          $image->save( $diretorio_600 );
	          $image->resize( 150, 150, true );
	          $image->save( $diretorio_150 );

	      } else {

	        unlink ( $diretorio );
	        return false;

	      }

		  return $uploadDir['baseurl'] . $pasta_base . '/' . $associado_nome . '-ID-' . $associado_id . $pasta_destino . $uploadDir['subdir'] . '/' . $random_img_id . $extensao;

    }

  }

  return false;

}

function generateRandomString ($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function file_force_contents($dir, $contents){
    $parts = explode('/', $dir);
    $file = array_pop($parts);
    $dir = '';
    foreach($parts as $part)
        if(!is_dir($dir .= "/$part")) mkdir($dir);
    file_put_contents("$dir/$file", $contents);
}


// Funcoes de Log de usuários
function adicionarLogDeUsuario( $data, $acao, $acao_desc ) {

	global $wpdb;
	$wpdb->logs = $wpdb->prefix . 'logs';

	$timestamp = date(time());
	$mes = date('m');
	$ano = date('Y');

	$log_inserido = $wpdb->insert(
		$wpdb->logs,
		array(
			'data' 			=> $timestamp,
			'mes'  			=> $mes,
			'ano'  			=> $ano,
			'id_autor'  	=> validacao_dados( $data['id_autor'] ),
			'nome_autor'  	=> validacao_dados( $data['nome_autor']),
			'objeto'  		=> validacao_dados( $data['objeto'] ),
			'objeto_desc'  	=> validacao_dados( $data['objeto_desc'] ),
			'objeto_nome'  	=> validacao_dados( $data['objeto_nome'] ),
			'acao'  		=> validacao_dados( $acao ),
			'acao_desc'  	=> $acao_desc
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
			'%s'
		)
	);

	if( $log_inserido ){

		return true;

	} else {

		return false;

	}

}