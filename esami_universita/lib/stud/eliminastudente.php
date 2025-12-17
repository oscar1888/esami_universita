<?php
	$error_msg = '';
	$success_msg = '';

    if(isset($_POST) && isset($_POST['stud'])) {
        $stud = $_POST['stud'];
       	
        $matricola = null;
        if(!empty($stud['matricola']))
        	$matricola = $stud['matricola'];
        else
        	$error_msg = "Errore. E' necessario inserire la matricola dello studente che si desidera eliminare.";

        $motivo = $stud['motivo'];

        if (empty($error_msg)) {

        	$db = open_pg_connection();

        	$result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

        	$sql = "SELECT * FROM studente_corrente WHERE matricola = $1;";

            $params = array();
            $params[] = $matricola;

            $result = pg_prepare($db, "info_stud1", $sql);
            $result = pg_execute($db, "info_stud1", $params);

            if ($row = pg_fetch_assoc($result)) {
            	$sql = "DELETE FROM studente_corrente WHERE matricola = $1;";

	            $params = array();
	            $params[] = $matricola;

	            $result = pg_prepare($db, "del_stud1", $sql);
	            $result = pg_execute($db, "del_stud1", $params);

	            $sql = "UPDATE storico_studenti SET motivo = $1 WHERE matricola = $2;";

	            $params = array();
	            $params[] = $motivo;
	            $params[] = $matricola;

	            $result = pg_prepare($db, "del_stud2", $sql);
	            $result = pg_execute($db, "del_stud2", $params);

	            if ($result)
	                $success_msg = "Studente eliminato correttamente.";
	            else
	                $error_msg = remove_error_context(pg_last_error($db));
            } else {
            	$error_msg = 'Non esiste alcun studente corrente con la matricola inserita.';
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
	<legend class="uk-legend">Elimina uno studente</legend>

	<div class="uk-margin">     
		<label class="uk-form-label" for="stud-matr">Matricola</label>
		<div class="uk-form-controls">
			<input class="uk-input" id="stud-matr" type="text" placeholder="Inserisci la matricola" name="stud[matricola]">
		</div>
	</div>
	<div class="uk-margin">     
		<label class="uk-form-label" for="stud-motivo">Motivo</label>
		<div class="uk-form-controls">
			<select class="uk-select" name="stud[motivo]">
				<option value="Laurea">Laurea</option>
				<option value="Rinuncia">Rinuncia</option>
				<option value="Altro">Altro</option>
			</select>
		</div>
	</div>
	<button class="uk-button uk-button-danger uk-align-right uk-margin-remove-bottom">Elimina</button>
</form>