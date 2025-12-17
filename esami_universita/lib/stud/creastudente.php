<?php
	$error_msg = '';
	$success_msg = '';
    $generated_data = null;

    if(isset($_POST) && isset($_POST['stud'])) {
        $stud = $_POST['stud'];
       	
        $data_nascita = null;
        if(!empty($stud['data_nascita']))
        	$data_nascita = $stud['data_nascita'];
        else
        	$error_msg = "Errore. E' necessario selezionare la data di nascita dello studente";

        $cognome = null;
        if(!empty($stud['cognome']))
        	$cognome = $stud['cognome'];
        else
        	$error_msg = "Errore. E' necessario inserire il cognome dello studente";

        $nome = null;
        if(!empty($stud['nome']))
        	$nome = $stud['nome'];
        else
        	$error_msg = "Errore. E' necessario inserire il nome dello studente";
        
        $sesso = null;
        $sesso = $stud['sesso'];

        $corso_di_laurea = null;
        $corso_di_laurea = $stud['cdl'];

        if (empty($error_msg)) {
			
        	$generated_data = gen_data('stud', $nome, $cognome);

            $db = open_pg_connection();

            $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

			$sql = "INSERT INTO profilo_utente VALUES ($1, md5($2), $3)";

            $params = array();
            $params[] = $generated_data['username'];
            $params[] = $generated_data['password'];
            $params[] = 'Studente';

            $result = pg_prepare($db, "ins_query1", $sql);
            $result = pg_execute($db, "ins_query1", $params);

            $sql = "INSERT INTO studente_corrente VALUES ($1, $2, $3, $4, $5, $6, $7)";
            
            $params = array();
            $params[] = $generated_data['matricola'];
            $params[] = $generated_data['username'];
            $params[] = $nome;
            $params[] = $cognome;
            $params[] = $sesso;
            $params[] = $data_nascita;
            $params[] = $corso_di_laurea;

            $result = pg_prepare($db, "ins_query2", $sql);
            $result = pg_execute($db, "ins_query2", $params);
            
            if ($result)
                $success_msg = "Studente creato correttamente.";
            else
                $error_msg = pg_last_error($db);
        }
    }

    $pagelink = $_SERVER['PHP_SELF'] . '?mod=crea';
        	
?>
<?php
if (!empty($success_msg)) {
?>
<div class="uk-alert-success" uk-alert>
    <a class="uk-alert-close" uk-close></a>
    <p><b><?php echo $success_msg; ?></b></p>
    <h5 class="uk-margin-remove-top"><b>Informazioni sul nuovo studente:</b>
    <ul class="uk-margin-remove-top uk-margin-remove-bottom uk-text-default">
        <li><u>Username</u>: <?php echo $generated_data['username']; ?></li>
        <li><u>Password</u>: <?php echo $generated_data['password']; ?></li>
        <li><u>Matricola</u>: <?php echo $generated_data['matricola']; ?></li>
    </ul>
    </h5>
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
	<legend class="uk-legend">Crea un nuovo studente</legend>

	<div class="uk-margin">     
		<label class="uk-form-label" for="stud-nome">Nome</label>
		<div class="uk-form-controls">
			<input class="uk-input" id="stud-nome" type="text" placeholder="Inserisci il nome" name="stud[nome]">
		</div>
	</div>
	<div class="uk-margin">     
		<label class="uk-form-label" for="stud-cognome">Cognome</label>
		<div class="uk-form-controls">
			<input class="uk-input" id="stud-cognome" type="text" placeholder="Inserisci il cognome" name="stud[cognome]">
		</div>
	</div>
	<div class="uk-margin">
		<label class="uk-form-label" for="stud-sesso">Sesso</label>
		<div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
            <label><input class="uk-radio" type="radio" name="stud[sesso]" checked value="M"> M</label>
            <label><input class="uk-radio" type="radio" name="stud[sesso]" value="F"> F</label>
        </div>
	</div>
	<div class="uk-margin">
		<label class="uk-form-label" for="stud-datanasc">Data di nascita</label>
		<div class="uk-form-controls">
			<input class="uk-input" id="stud-datanasc" type="date" name="stud[data_nascita]">
		</div>
	</div>
	<div class="uk-margin">
		<label class="uk-form-label" for="stud-cdl">Corso di laurea</label>
		<div class="uk-form-controls">
			
                <?php
                $cdl_keys = get_cdl_entries(true);

                if (count($cdl_keys) != 0) {
                ?>
                <select class="uk-select" name="stud[cdl]">
                <?php
                foreach ($cdl_keys as $k => $v) {
                    $selected = '';
                    if ((!is_null($corso_di_laurea)) && ($corso_di_laurea == $k)) {
                        $selected = 'selected="selected"';
                    }
                ?>
                <option value="<?php print($k); ?>" <?php print($selected); ?>><?php print($v['nome'] . ' ' . $v['tipo'] . ', ' . $v['sede'] . ' (offerta ' . $v['anno_accademico_offerta'] . ')'); ?></option>
                <?php
                }
                ?>
            </select>
            <?php } else {?>
            <p><u>Non ci sono corsi di laurea disponibili in archivio.</u></p>
        <?php
        } ?>
		</div>
	</div>
	<button class="uk-button uk-button-primary uk-align-right uk-margin-remove-bottom" <?php if (count($cdl_keys) == 0) {echo 'disabled';} ?>>Crea</button>
</form>