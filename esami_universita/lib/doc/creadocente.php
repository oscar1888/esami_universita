<?php
	$error_msg = '';
	$success_msg = '';
    $generated_data = null;

    if(isset($_POST) && isset($_POST['doc'])) {
        $doc = $_POST['doc'];
       	
        $uff = null;
        if(!empty($doc['uff']))
            $uff = $doc['uff'];
        else
            $error_msg = "Errore. E' necessario inserire l'indirizzo dell'ufficio del docente.";

        $istr = null;
        if(!empty($doc['istr']))
            $istr = $doc['istr'];
        else
            $error_msg = "Errore. E' necessario inserire il livello d'istruzione raggiunto dal docente.";

        $data_nascita = null;
        if(!empty($doc['data_nascita']))
        	$data_nascita = $doc['data_nascita'];
        else
        	$error_msg = "Errore. E' necessario selezionare la data di nascita del docente.";

        $cognome = null;
        if(!empty($doc['cognome']))
        	$cognome = $doc['cognome'];
        else
        	$error_msg = "Errore. E' necessario inserire il cognome del docente.";

        $nome = null;
        if(!empty($doc['nome']))
        	$nome = $doc['nome'];
        else
        	$error_msg = "Errore. E' necessario inserire il nome del docente.";
        
        $sesso = null;
        $sesso = $doc['sesso'];

        if (empty($error_msg)) {
			
        	$generated_data = gen_data('doc', $nome, $cognome);

            $db = open_pg_connection();

            $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

			$sql = "INSERT INTO profilo_utente VALUES ($1, md5($2), $3)";

            $params = array();
            $params[] = $generated_data['username'];
            $params[] = $generated_data['password'];
            $params[] = 'Docente';

            $result = pg_prepare($db, "ins_query1", $sql);
            $result = pg_execute($db, "ins_query1", $params);

            $sql = "INSERT INTO docente VALUES ($1, $2, $3, $4, $5, $6, $7)";
            
            $params = array();
            $params[] = $generated_data['username'];
            $params[] = $nome;
            $params[] = $cognome;
            $params[] = $sesso;
            $params[] = $data_nascita;
            $params[] = $istr;
            $params[] = $uff;

            $result = pg_prepare($db, "ins_query2", $sql);
            $result = pg_execute($db, "ins_query2", $params);
            
            if ($result)
                $success_msg = "Docente creato correttamente.";
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
    <h5 class="uk-margin-remove-top"><b>Informazioni sul nuovo docente:</b>
    <ul class="uk-margin-remove-top uk-margin-remove-bottom uk-text-default">
        <li><u>Username</u>: <?php echo $generated_data['username']; ?></li>
        <li><u>Password</u>: <?php echo $generated_data['password']; ?></li>
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
	<legend class="uk-legend">Crea un nuovo docente</legend>

	<div class="uk-margin">     
		<label class="uk-form-label" for="doc-nome">Nome</label>
		<div class="uk-form-controls">
			<input class="uk-input" id="doc-nome" type="text" placeholder="Inserisci il nome" name="doc[nome]">
		</div>
	</div>
	<div class="uk-margin">     
		<label class="uk-form-label" for="doc-cognome">Cognome</label>
		<div class="uk-form-controls">
			<input class="uk-input" id="doc-cognome" type="text" placeholder="Inserisci il cognome" name="doc[cognome]">
		</div>
	</div>
	<div class="uk-margin">
		<label class="uk-form-label" for="doc-sesso">Sesso</label>
		<div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
            <label><input class="uk-radio" type="radio" name="doc[sesso]" checked value="M"> M</label>
            <label><input class="uk-radio" type="radio" name="doc[sesso]" value="F"> F</label>
        </div>
	</div>
	<div class="uk-margin">
		<label class="uk-form-label" for="doc-datanasc">Data di nascita</label>
		<div class="uk-form-controls">
			<input class="uk-input" id="doc-datanasc" type="date" name="doc[data_nascita]">
		</div>
	</div>
	<div class="uk-margin">     
        <label class="uk-form-label" for="doc-istr">Istruzione</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="doc-istr" type="text" placeholder="Inserisci il livello d'istruzione raggiunto dal docente" name="doc[istr]">
        </div>
    </div>
    <div class="uk-margin">     
        <label class="uk-form-label" for="doc-uff">Sede ufficio</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="doc-uff" type="text" placeholder="Inserisci l'indirizzo dell'ufficio del docente" name="doc[uff]">
        </div>
    </div>
	<button class="uk-button uk-button-primary uk-align-right uk-margin-remove-bottom">Crea</button>
</form>