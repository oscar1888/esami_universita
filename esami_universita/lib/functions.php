<?php

$menu_entries = Array (
	'completa' => 'Carriera completa',
	'valida' => 'Carriera valida'
);

$menu_entries_segr = Array (
	'completa' => 'Carriere complete',
	'valida' => 'Carriere valide'
);

function get_not_activated_insegnamenti($cdl) {
	$db = open_pg_connection();

	$output = array();

	$sql = "SELECT id, nome FROM esami_universita.insegnamento WHERE corso_di_laurea = $1";

	$params = array($cdl);

	$result = pg_prepare($db, "notactins", $sql);
	$result = pg_execute($db, "notactins", $params);

	while($row = pg_fetch_assoc($result)){
		$output[$row['id']] = $row['nome'];
	}

	close_pg_connection($db);

	return $output;
}

function remove_error_context($string) {
    $pattern = '/ERROR:/';
    $result1 = preg_replace($pattern, '', $string);
    $pattern = '/CONTEXT:.*/';
    $result1 = preg_replace($pattern, '', $result1);
    $res = strstr($result1, 'SQL', true);
    if ($res == false) {
        return $result1;
    }
    return $res;
}

function gen_password() {
  $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  $password = '';
  
  $length = 8;
  
  for ($i = 0; $i < $length; $i++) {
    $index = rand(0, strlen($characters) - 1);
    $password .= $characters[$index];
  }
  
  return $password;
}

function gen_matricola() {
  
  	$matricola = 0;
  
  	$db = open_pg_connection();

	$sql = "WITH all_matricole AS (SELECT matricola FROM esami_universita.studente_corrente UNION SELECT matricola FROM esami_universita.storico_studenti) SELECT max(matricola) AS matricola FROM all_matricole";

	$params = array();

	$result = pg_prepare($db, "retrieve_info_matr2", $sql);
	$result = pg_execute($db, "retrieve_info_matr2", $params);

	if($row = pg_fetch_assoc($result)){
		$matricola = $row['matricola'] + 1;
	}

	close_pg_connection($db);

  	return $matricola;
}

function gen_cdl_code() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $numbers = '0123456789';

    $char1 = $characters[rand(0, strlen($characters) - 1)];
    $char2 = $numbers[rand(0, strlen($numbers) - 1)];
    $char3 = $characters[rand(0, strlen($characters) - 1)];

    return $char1 . $char2 . $char3;
}

function gen_username($role, $nome, $cognome) {
  
  	$username = null;

  	$nome = strtolower($nome);

  	$cognome = strtolower($cognome);
  
  	$db = open_pg_connection();

  	if ($role == 'stud' || $role == 'doc') {

  		$sql = "SELECT username FROM esami_universita.profilo_utente WHERE username LIKE $1 ORDER BY substring(username, length($2)+1)::integer DESC LIMIT 1;";

		$params = array(
			$nome . '.' . $cognome . '%',
			$nome . '.' . $cognome
		);

		$result = pg_prepare($db, "retrieve_info_usr3", $sql);
		$result = pg_execute($db, "retrieve_info_usr3", $params);

		$temp = null;

		if($row = pg_fetch_assoc($result)){
			$temp = $row['username'];
		}

		$temp = intval(str_replace($nome . '.' . $cognome, '', $temp)) + 1;

		$username = $nome . '.' . $cognome . $temp;

  	} else {

  		$sql = "SELECT username FROM esami_universita.profilo_utente WHERE username LIKE $1 ORDER BY substring(username, length($2)+1)::integer DESC LIMIT 1;";

		$params = array(
			'segreteria%',
			'segreteria'
		);

		$result = pg_prepare($db, "retrieve_info_usr3", $sql);
		$result = pg_execute($db, "retrieve_info_usr3", $params);

		if($row = pg_fetch_assoc($result)){
			$temp = $row['username'];
		}

		$temp = intval(str_replace('segreteria', '', $temp)) + 1;

		$username = 'segreteria' . $temp;

  	}

	close_pg_connection($db);

  	return $username;
}

function gen_data($role, $nome, $cognome){

$output = array();

if ($role == 'stud') {

	$output['matricola'] = gen_matricola();
	$output['username'] = gen_username($role, $nome, $cognome);
	$output['password'] = gen_password();

} else {

	$output['username'] = gen_username($role, $nome, $cognome);
	$output['password'] = gen_password();

}

return $output;

}

function get_menu_entries_gest($role){

$entries = null;

if ($role == 'doc') {

$entries = array (
	'crea' => 'Creazione',
	'modifica' => 'Modificazione',
	'elimina' => 'Eliminazione'
);

} else {

$entries = array (
	'crea' => 'Creazione',
	'elimina' => 'Eliminazione'
);

}

return $entries;

}

function get_acc_years($cdl){

	$db = open_pg_connection();

	$sql = "SELECT nome, tipo, sede FROM esami_universita.corso_di_laurea WHERE id = $1";

    $params = array(
    	$cdl
    );

    $result = pg_prepare($db, "retrieve_info_cdl3", $sql);
    $result = pg_execute($db, "retrieve_info_cdl3", $params);

    $infocdl = null;

    if($row = pg_fetch_assoc($result)){
		$infocdl = $row;
	}

	$acc_y_entries = array();

	$sql = "SELECT anno_accademico_offerta FROM esami_universita.corso_di_laurea WHERE nome = $1 AND tipo = $2 AND sede = $3 ORDER BY anno_accademico_offerta";

	$params = array(
		$infocdl['nome'],
		$infocdl['tipo'],
		$infocdl['sede']
	);

	$result = pg_prepare($db, "retrieve_info_y", $sql);
	$result = pg_execute($db, "retrieve_info_y", $params);

	while($row = pg_fetch_assoc($result)){
		$acc_y_entries[$row['anno_accademico_offerta']] = $row['anno_accademico_offerta'];
	}

	close_pg_connection($db);

	return $acc_y_entries;
}

function gen_cod_interno($id) {
	$db = open_pg_connection();

	$sql = "SELECT count(*) FROM esami_universita.insegnamento WHERE corso_di_laurea = $1";

	$params = array($id);

	$result = pg_prepare($db, "gen_cod_interno", $sql);
	$result = pg_execute($db, "gen_cod_interno", $params);

	$output = null;

	if ($row = pg_fetch_assoc($result)) {
		$output = $row['count'] + 1;
	}

	close_pg_connection($db);

	return $output;
}

function get_not_activated_cdl_entries(){

	$db = open_pg_connection();

	$cdl_entries = array();

	$sql = "SELECT id, nome, tipo, sede, anno_accademico_offerta  FROM esami_universita.corso_di_laurea WHERE attivato = FALSE ORDER BY nome, tipo, sede, anno_accademico_offerta;";

	$params = array();

	$result = pg_prepare($db, "retrieve_info_cdl2", $sql);
	$result = pg_execute($db, "retrieve_info_cdl2", $params);

	while($row = pg_fetch_assoc($result)){
		$cdl_entries[$row['id']] = $row;
	}

	close_pg_connection($db);

	return $cdl_entries;

}

function get_all_cdl_entries() {

	$db = open_pg_connection();

	$cdl_entries = array();

	$sql = "SELECT DISTINCT ON (nome, tipo, sede) nome, tipo, sede, id  FROM esami_universita.corso_di_laurea ORDER BY nome, tipo, sede, anno_accademico_offerta;";

	$params = array();

	$result = pg_prepare($db, "retrieve_info_cdl3", $sql);
	$result = pg_execute($db, "retrieve_info_cdl3", $params);

	while($row = pg_fetch_assoc($result)){
		$cdl_entries[$row['id']] = $row;
	}

	close_pg_connection($db);

	return $cdl_entries;

}

function get_cdl_entries($complete){

	$db = open_pg_connection();

	$cdl_entries = array();

	if (!$complete) {

		$sql = "SELECT distinct on (nome, tipo, sede) nome, tipo, sede, id FROM esami_universita.corso_di_laurea WHERE attivato = TRUE ORDER BY nome;";

		$params = array();

		$result = pg_prepare($db, "retrieve_info_cdl2", $sql);
		$result = pg_execute($db, "retrieve_info_cdl2", $params);

		while($row = pg_fetch_assoc($result)){
			$cdl_entries[$row['id']] = $row;
		}

	} else {

		$sql = "SELECT id, nome, tipo, sede, anno_accademico_offerta  FROM esami_universita.corso_di_laurea WHERE attivato = TRUE ORDER BY nome, tipo, sede, anno_accademico_offerta;";

		$params = array();

		$result = pg_prepare($db, "retrieve_info_cdl2", $sql);
		$result = pg_execute($db, "retrieve_info_cdl2", $params);

		while($row = pg_fetch_assoc($result)){
			$cdl_entries[$row['id']] = $row;
		}

	}

	close_pg_connection($db);

	return $cdl_entries;

}

function get_info($user, $role, $is_in_storico){
	$db = open_pg_connection();

	$output = null;
	$nome_cdl = null;

	if ($role == 'Studente') {
		if (!$is_in_storico) {

			$sql = "SELECT matricola, nome, cognome, sesso, data_nascita, corso_di_laurea FROM esami_universita.studente_corrente WHERE username = $1";

		    $params = array(
		    	$user
		    );

		    $result = pg_prepare($db, "retrieve_info_sc", $sql);
		    $result = pg_execute($db, "retrieve_info_sc", $params);

		} else {

			$sql = "SELECT motivo, anno_rimozione, matricola, nome, cognome, sesso, data_nascita, corso_di_laurea FROM esami_universita.storico_studenti WHERE username = $1";

		    $params = array(
		    	$user
		    );

		    $result = pg_prepare($db, "retrieve_info_ss", $sql);
		    $result = pg_execute($db, "retrieve_info_ss", $params);

		}

		if($row = pg_fetch_assoc($result)){
			$output = $row;
		}

		$sql = "SELECT nome, tipo, anno_accademico_offerta, sede FROM esami_universita.corso_di_laurea WHERE id = $1";

	    $params = array(
	    	$output['corso_di_laurea']
	    );

	    $result = pg_prepare($db, "retrieve_info_cdl1", $sql);
	    $result = pg_execute($db, "retrieve_info_cdl1", $params);

	    $temp = pg_fetch_assoc($result);

		$output['corso_di_laurea'] = $temp['nome'] . ' ' . $temp['tipo'] . ', ' . $temp['sede'] . ' (offerta ' . $temp['anno_accademico_offerta'] . ')' ;
		
	} else {

		$sql = "SELECT nome, cognome, sesso, data_nascita, istruzione, sede_ufficio FROM esami_universita.docente WHERE username = $1";

	    $params = array(
	    	$user
	    );

	    $result = pg_prepare($db, "retrieve_info_d", $sql);
	    $result = pg_execute($db, "retrieve_info_d", $params);

	    if($row = pg_fetch_assoc($result)){
			$output = $row;
		}

	}

	

	close_pg_connection($db);

	return $output;

}

function get_menu_entries(){
global $menu_entries;

return $menu_entries;

}

function validate_acc_year($stringa) {
	
    if (strlen($stringa) != 9) {
        return false;
    }

    for ($i = 0; $i < 4; $i++) {
        if (!is_numeric($stringa[$i])) {
            return false;
        }
    }

    if ($stringa[4] != '-') {
        return false;
    }

    for ($i = 5; $i < 9; $i++) {
        if (!is_numeric($stringa[$i])) {
            return false;
        }
    }

    return true;
}

function get_menu_entries_segr(){
global $menu_entries_segr;

return $menu_entries_segr;

}

/*
Open connection with PostgreSQL server
*/
function open_pg_connection() {
	include('/var/www/esami_universita/conf/conf.php');
    
    $connection = "host=".myhost." dbname=".mydb." user=".myuser." password=".mypsw;
    
    return pg_connect ($connection);
    
}

/*
Close connection with PostgreSQL server
*/
function close_pg_connection($db) {
        
    return pg_close ($db);
    
}

/*
check the validity of given credentials
*/
function login($user, $psw) {
    
    $logged = null;
    $role = null;
    $output = null;

    $db = open_pg_connection();

    $sql = "SELECT username, ruolo FROM esami_universita.profilo_utente WHERE username = $1 AND password = $2";

    $params = array(
    	$user,
    	md5($psw)
    );

    $result = pg_prepare($db, "check_user", $sql);
    $result = pg_execute($db, "check_user", $params);

    if($row = pg_fetch_assoc($result)){
    	$logged = $row['username'];
    	$role = $row['ruolo'];
    }

    if ($role == 'Studente') {
    	$sql = "SELECT username FROM esami_universita.storico_studenti WHERE username = $1";

	    $params = array(
	    	$user
	    );

	    $result = pg_prepare($db, "check_storico", $sql);
	    $result = pg_execute($db, "check_storico", $params);

	    if($row = pg_fetch_assoc($result)) {
	    	$output = array(
	    		'username' => $logged,
	    		'ruolo' => $role,
	    		'storico' => true
	    	);
	    } else {
	    	$output = array(
	    		'username' => $logged,
	    		'ruolo' => $role,
	    		'storico' => false
	    	);
	    }

	    
    } else {
    	$output = array(
    		'username' => $logged,
    		'ruolo' => $role,
    		'storico' => false
    	);
    }

    close_pg_connection($db);

    return $output;
    
}

?>