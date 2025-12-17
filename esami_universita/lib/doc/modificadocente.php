<?php
	$error_msg = '';
	$success_msg = '';
    $doc = null;
    $flag = false;
    $info_doc = null;

    if(isset($_POST) && isset($_POST['doc'])) {
        $doc = $_POST['doc'];

        if (isset($doc['usr'])) {

            $usr = $doc['usr'];

            if (!empty($usr)) {

                if (isset($doc['uff'])) {

                    $db = open_pg_connection();

                    $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

                    $sql = "SELECT * FROM docente WHERE username = $1";

                    $result = pg_prepare($db, "upd_query3", $sql);
                    $result = pg_execute($db, "upd_query3", array($usr));

                    $info_doc = pg_fetch_assoc($result);

                    close_pg_connection($db);

                    $uff = null;
                    if(!empty($doc['uff']))
                        $uff = $doc['uff'];
                    else
                        $error_msg = "Errore. E' necessario inserire il nuovo indirizzo dell'ufficio del docente.";

                    $istr = null;
                    if(!empty($doc['istr']))
                        $istr = $doc['istr'];
                    else
                        $error_msg = "Errore. E' necessario inserire il nuovo livello d'istruzione raggiunto dal docente.";

                    if (empty($error_msg)) {

                        $db = open_pg_connection();

                        $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

                        $sql = "UPDATE docente SET istruzione = $1, sede_ufficio = $2 WHERE username = $3";

                        $params = array();
                        $params[] = $istr;
                        $params[] = $uff;
                        $params[] = $usr;

                        $result = pg_prepare($db, "upd_query1", $sql);
                        $result = pg_execute($db, "upd_query1", $params);

                        if ($result) {
                            $success_msg = "Docente modificato correttamente.";
                        }
                        else {
                            $error_msg = pg_last_error($db);
                        }

                    }
                } else {

                    $db = open_pg_connection();

                    $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

                    $sql = "SELECT * FROM docente WHERE username = $1";

                    $params = array();
                    $params[] = $usr;

                    $result = pg_prepare($db, "upd_query2", $sql);
                    $result = pg_execute($db, "upd_query2", $params);

                    if ($row = pg_fetch_assoc($result)){
                        $info_doc = $row;
                    } else {
                        $error_msg = "Non esiste alcun docente con lo username '" . $usr . "'.";
                        $flag = true;
                    }

                }
            } else {
                $error_msg = "Errore. E' necessario inserire lo username del docente che si vuole modificare.";
            }

        }
    }

    $pagelink = $_SERVER['PHP_SELF'] . '?mod=modifica';
        	
?>
<?php
if (!empty($success_msg)) {
?>
<div class="uk-alert-success" uk-alert>
    <a class="uk-alert-close" uk-close></a>
    <p><b><?php echo $success_msg; ?></b></p>
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
<?php if (isset($doc) && isset($doc['usr']) && !empty($doc['usr']) && !$flag && empty($success_msg)) {?>
<form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
	<legend class="uk-legend">Modifica un docente</legend>
	<div class="uk-margin">     
		<label class="uk-form-label" for="doc-nome">Nome</label>
		<div class="uk-form-controls">
			<input class="uk-input" id="doc-nome" type="text" placeholder="<?php echo $info_doc['nome']; ?>" disabled>
		</div>
	</div>
	<div class="uk-margin">     
		<label class="uk-form-label" for="doc-cognome">Cognome</label>
		<div class="uk-form-controls">
			<input class="uk-input" id="doc-cognome" type="text" placeholder="<?php echo $info_doc['cognome']; ?>" disabled>
		</div>
	</div>
	<div class="uk-margin">
		<label class="uk-form-label" for="doc-sesso">Sesso</label>
		<div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
            <label><input class="uk-radio" type="radio" <?php if ($info_doc['sesso'] == 'M') {echo 'checked';} ?> value="M" disabled> M</label>
            <label><input class="uk-radio" type="radio" <?php if ($info_doc['sesso'] == 'F') {echo 'checked';} ?> value="F" disabled> F</label>
        </div>
	</div>
	<div class="uk-margin">
		<label class="uk-form-label" for="doc-datanasc">Data di nascita</label>
		<div class="uk-form-controls">
			<input class="uk-input" id="doc-datanasc" type="date" value="<?php echo $info_doc['data_nascita']; ?>" disabled>
		</div>
	</div>
	<div class="uk-margin">     
        <label class="uk-form-label" for="doc-istr">Istruzione</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="doc-istr" type="text" placeholder="Inserisci il nuovo livello d'istruzione raggiunto dal docente" name="doc[istr]" value="<?php echo $info_doc['istruzione']; ?>">
        </div>
    </div>
    <div class="uk-margin">     
        <label class="uk-form-label" for="doc-uff">Sede ufficio</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="doc-uff" type="text" placeholder="Inserisci il nuovo indirizzo dell'ufficio del docente" name="doc[uff]" value="<?php echo $info_doc['sede_ufficio']; ?>">
        </div>
    </div>
    <input type="hidden" name="doc[usr]" value="<?php echo $usr; ?>">
	<button class="uk-button uk-button-primary uk-align-right uk-margin-remove-bottom">Modifica</button>
</form>
<form action="<?php echo $pagelink; ?>" method="POST">
    <button type="submit" class="uk-button uk-button-primary uk-align-left">Indietro</button>
</form>
<?php } else { ?>

<form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
    <legend class="uk-legend">Modifica un docente</legend>
    <div class="uk-margin">     
        <label class="uk-form-label" for="doc-usr">Username</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="doc-usr" type="text" placeholder="Inserisci lo username" name="doc[usr]">
        </div>
    </div>
    <button class="uk-button uk-button-primary uk-align-right uk-margin-remove-bottom">Avanti</button>
</form>

<?php } ?>