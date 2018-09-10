<?php

/*
*  Funçòes de Agendamento
*/
function add_new_intervals($schedules)
{
	$schedules['monthly'] = array(
		'interval' => 2635200,
		'display' => __('Once a month')
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'add_new_intervals');

/*
*  Funçao inserir pagamento pendente
*/

function add_schedule_function() {

    $date = date('d');
    if ( ! wp_next_scheduled( 'lancar_pagamento_pendente' ) ) {
        wp_schedule_event( $date, 'daily', 'lancar_pagamento_pendente' );
    }
    add_action( 'lancar_pagamento_pendente', 'lancar_pagamento_pendente_function' );

    function lancar_pagamento_pendente_function() {

        $date = date('d');

        if( $date === '10' ){

            global $wpdb;
            $wpdb->pagamentos = $wpdb->prefix . 'pagamentos';

            // Data atual
            $meses = array('janeiro', 'fevereiro', 'marco', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro' );
            $timestamp = date(time());
            $mes_atual = $meses[date('m') - 1]; // Pega o mes atual pelo nome em extenso
            $ano_atual = date('Y');

            // Busca todos os Usuarios
            $args_users = array(
                'role__in'     => array( 'associado' ),
                'orderby'      => array(' display_name '),
                'order'        => 'ASC'
            );
            $users = get_users($args_users);

            foreach ( $users as $user ):

                // Recebe todo os metas do usuario
                $user_meta_info = get_userdata( $user->ID )->associado_info;

                if( $user_meta_info['status'] === 'regular' || $user_meta_info['status'] === 'pendente' || $user_meta_info['status'] === 'inadimplente' ){

                    // Busca todos os pagamentos do usuario
                    $lista_pagamentos = $wpdb->get_results("
                        SELECT *
                        FROM $wpdb->pagamentos
                        WHERE id_pessoa = $user->ID
                    ");

                    // Verifica se nao existe nenhum pagamento do associado
                    // Verifica se o associado nao possui pagamento no mes atual do ano atual
                    $existe_pagamento_pendente = false;
                    $nao_existe_nenhum_pagamento = false;
                    if( count($lista_pagamentos) != 0 ){
                        foreach( $lista_pagamentos as $pagamento ):
                            if( $pagamento->mes !== $mes_atual && $pagamento->ano === $ano_atual ){
                                $existe_pagamento_pendente = true;
                            }
                        endforeach;
                    } else {
                        $nao_existe_nenhum_pagamento = true;
                    }

                    if( $existe_pagamento_pendente || $nao_existe_nenhum_pagamento ){

                        // Insere um pagamento pendente ao associado que nao possui pagamento do mes atual
                        $pagamento_inserido = $wpdb->insert(
                            $wpdb->pagamentos,
                            array(
                                'id_pessoa' 		=> $user->ID,
                                'nome_pessoa' 		=> $user->display_name,
                                'tipo_pagamento' 	=> 'mensalidade',
                                'valor' 			=> 'R$110.00',
                                'mes' 				=> $mes_atual,
                                'ano' 				=> $ano_atual,
                                'data_registro' 	=> $timestamp,
                                'data_pagamento' 	=> $timestamp,
                                'img' 				=> 'false',
                                'pago'				=> 'false'
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

                        if( $pagamento_inserido ){

                            //Modifica o status dos associado de acordo o status atual do mesmo
                            switch ($user_meta_info['status']) {

                                case "regular":
                                    $user_meta_info['status'] = 'pendente';
                                break;

                                case "pendente":
                                    $user_meta_info['status'] = 'inadimplente';
                                break;

                            }

                            $updated_meta_info = update_user_meta( $user->ID, 'associado_info', $user_meta_info );

                        }
                    }
                }

            endforeach;

            wp_reset_query();

            return true;

        }

    }

}

// Chama funcao
add_schedule_function();

function remove_schedule_function() {
    wp_clear_scheduled_hook('lancar_pagamento_pendente');
}