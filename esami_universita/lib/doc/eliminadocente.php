<?php
	$error_msg = '';
	$success_msg = '';

    if(isset($_POST) && isset($_POST['doc'])) {
        $doc = $_POST['doc'];
       	
        $usr = null;
        if(!empty($doc['usr']))
        	$usr = $doc['usr'];
        else
        	$error_msg = "Errore. E' necessario inserire lo username del docente che si desidera eliminare.";

        if (empty($error_msg)) {

        	$db = open_pg_connection();

        	$result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

        	$sql = "SELECT * FROM docente WHERE username = $1;";

            $params = array();
            $params[] = $usr;

            $result = pg_prepare($db, "del_doc1", $sql);
            $result = pg_execute($db, "del_doc1", $params);

            if($row = pg_fetch_assoc($result)) {

				$sql = "DELETE FROM profilo_utente WHERE username = $1;";

	            $params = array();
	            $params[] = $usr;

	            $result = pg_prepare($db, "del_doc2", $sql);
	            $result = pg_execute($db, "del_doc2", $params);

	            if ($result)
	            	$success_msg = "Docente eliminato correttamente.";
	            else
	            	$error_msg = remove_error_context(pg_last_error($db));
	        } else {
	        	$error_msg = "Non esiste alcun docente con lo username '" . $usr . "'.";
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
	<legend class="uk-legend">Elimina un docente</legend>

	<div class="uk-margin">     
		<label class="uk-form-label" for="doc-usr">Username</label>
		<div class="uk-form-controls">
			<input class="uk-input" id="doc-usr" type="text" placeholder="Inserisci lo username" name="doc[usr]">
		</div>
	</div>
	<button class="uk-button uk-button-danger uk-align-right uk-margin-remove-bottom">Elimina</button>
</form>