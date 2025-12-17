<?php
	$error_msg = '';
	$success_msg = '';

    if(isset($_POST) && isset($_POST['segr'])) {
        $segr = $_POST['segr'];
       	
        $usr = null;
        if(!empty($segr['usr'])) {
        	$usr = $segr['usr'];
        	if ($usr == 'segreteria1') {
        		$error_msg = "Errore. L'utente 'segreteria1' non può essere eliminato.";
        	} elseif ($usr == $logged) {
        		$error_msg = 'Errore. Non puoi eliminare il tuo stesso profilo utente.';
        	}
        } else {
        	$error_msg = "Errore. E' necessario inserire lo username dell'utente segreteria che si desidera eliminare.";
        }

        if (empty($error_msg)) {

        	$db = open_pg_connection();

        	$result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

        	$sql = "SELECT * FROM profilo_utente WHERE username = $1;";

            $params = array();
            $params[] = $usr;

            $result = pg_prepare($db, "del_segr1", $sql);
            $result = pg_execute($db, "del_segr1", $params);

            if($row = pg_fetch_assoc($result)) {

				$sql = "DELETE FROM profilo_utente WHERE username = $1;";

	            $params = array();
	            $params[] = $usr;

	            $result = pg_prepare($db, "del_segr2", $sql);
	            $result = pg_execute($db, "del_segr2", $params);

	            if ($result)
	            	$success_msg = "Utente segreteria eliminato correttamente.";
	            else
	            	$error_msg = remove_error_context(pg_last_error($db));
	        } else {
	        	$error_msg = "Non esiste alcun utente segreteria con lo username '" . $usr . "'.";
	        }

        }
    }

    $pagelink = $_SERVER['PHP_SELF'] . '?mod=elimina';
        	
?>
<?php
if (!empty($success_msg)) {
?>
<div class="uk-alert-success" uk-alert>
    <a class="uk-alert-close" uk-close></a>
    <p><?php echo $success_msg; ?></p>
</div>
<?php
}
?>
<?php
if (!empty($error_msg)) {
?>
<div class="uk-alert-danger" uk-alert>
    <a class="uk-alert-close" uk-close></a>
    <p><?php echo $error_msg; ?></p>
</div>
<?php
}
?>
<form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
	<legend class="uk-legend">Elimina un utente segreteria</legend>

	<div class="uk-margin">     
		<label class="uk-form-label" for="segr-usr">Username</label>
		<div class="uk-form-controls">
			<input class="uk-input" id="segr-usr" type="text" placeholder="Inserisci lo username" name="segr[usr]">
		</div>
	</div>
	<button class="uk-button uk-button-danger uk-align-right uk-margin-remove-bottom">Elimina</button>
</form>