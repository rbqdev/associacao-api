<?php 

/*
* Post types relacionados diretamente ao Sistema:
*
* Terefas
*/

function create_post_type_tarefas() {

	register_post_type( 'tarefas',
	    array(
	      'labels' => array(
	        'name' => __( 'Tarefas' ),
	        'singular_name' => __( 'Tarefa' )
	      )
	    )
	  );
	  
}


/*
* Post types relacionados diretamente aos Associados:
* 
* Advertências
* Onibus
*/

function create_post_type_advertencias() {

	register_post_type( 'advertencias',
	    array(
	      'labels' => array(
	        'name' => __( 'Advertências' ),
	        'singular_name' => __( 'Advertência' )
	      )
	    )
	  );  
}

function create_post_type_onibus() {

	register_post_type( 'onibus',
	    array(
	      'labels' => array(
	        'name' => __( 'Onibus' ),
	        'singular_name' => __( 'Onibus-Single' )
	      )
	    )
	  );  
}

