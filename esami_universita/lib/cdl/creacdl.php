<?php
	$error_msg = '';
	$success_msg = '';
    $cdl_code = null;

    if(isset($_POST) && isset($_POST['cdl'])) {
        $cdl = $_POST['cdl'];

        $desc = null;
        if(!empty($cdl['desc']))
            $desc = $cdl['desc'];
        else
            $error_msg = "Errore. E' necessario inserire la descrizione del corso di laurea.";
       	
        $accesso = $cdl['accesso'];

        $sede = null;
        if(!empty($cdl['sede']))
            $sede = $cdl['sede'];
        else
            $error_msg = "Errore. E' necessario inserire la sede del corso di laurea.";

        $fac = null;
        if(!empty($cdl['fac']))
        	$fac = $cdl['fac'];
        else
        	$error_msg = "Errore. E' necessario inserire la facoltà del corso di laurea.";

        $annoacc = null;
        if(!empty($cdl['annoacc'])) {
            if (validate_acc_year($cdl['annoacc'])) {
        	   $annoacc = $cdl['annoacc'];
            } else {
                $error_msg = "L'anno accademico inserito non rispetta il formato richiesto.";
            }
        } else {
        	$error_msg = "Errore. E' necessario inserire l'anno accademico dell'offerta.";
        }

        $nome = null;
        if(!empty($cdl['nome']))
        	$nome = $cdl['nome'];
        else
        	$error_msg = "Errore. E' necessario inserire il nome del corso di laurea.";
        
        $tipo = $cdl['tipo'];

        if (empty($error_msg)) {

            $db = open_pg_connection();

            $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

            while (true) {

                $cdl_code = gen_cdl_code();

                $sql = "SELECT * FROM corso_di_laurea WHERE id = $1";

                $result = pg_prepare($db, "check1", $sql);
                $result = pg_execute($db, "check1", array($cdl_code));

                if (!pg_fetch_assoc($result)) {
                    break;
                }
            }

			$sql = "INSERT INTO corso_di_laurea VALUES ($1, $2, $3, $4, $5, $6, $7, $8)";

            $params = array();
            $params[] = $cdl_code;
            $params[] = $nome;
            $params[] = $tipo;
            $params[] = $annoacc;
            $params[] = $fac;
            $params[] = $desc;
            $params[] = $sede;
            $params[] = $accesso;

            $result = pg_prepare($db, "ins_query1", $sql);
            $result = pg_execute($db, "ins_query1", $params);
            
            if ($result) {
                $success_msg = "Corso di laurea creato correttamente.";
            }
            else {
                $error_msg = pg_last_error($db);
                if (strpos($error_msg, 'duplicate key value')) {
                    $error_msg = 'Il corso di laurea che hai inserito è già esistente.';
                }
            }
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
    <h5 class="uk-margin-remove-top"><b>Informazioni sul nuovo corso di laurea:</b>
    <ul class="uk-margin-remove-top uk-margin-remove-bottom uk-text-default">
        <li><u>Codice corso di laurea</u>: <?php echo $cdl_code; ?></li>
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
	<legend class="uk-legend">Crea un nuovo corso di laurea</legend>

	<div class="uk-margin">     
		<label class="uk-form-label" for="cdl-nome">Nome</label>
		<div class="uk-form-controls">
			<input class="uk-input" id="cdl-nome" type="text" placeholder="Inserisci il nome del corso di laurea" name="cdl[nome]">
		</div>
	</div>
	<div class="uk-margin">
		<label class="uk-form-label" for="cdl-tipo">Tipo</label>
		<div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
            <label><input class="uk-radio" type="radio" name="cdl[tipo]" checked value="Triennale"> Triennale</label>
            <label><input class="uk-radio" type="radio" name="cdl[tipo]" value="Magistrale"> Magistrale</label>
        </div>
	</div>
	<div class="uk-margin">
		<label class="uk-form-label" for="cdl-annoacc">Anno accademico offerta</label>
		<div class="uk-form-controls">
			<input class="uk-input" id="cdl-annoacc" type="text" placeholder="Inserisci l'anno accademico dell'offerta" name="cdl[annoacc]">
		</div>
	</div>
	<div class="uk-margin">
        <label class="uk-form-label" for="cdl-fac">Facoltà</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="cdl-fac" type="text" placeholder="Inserisci la facoltà del corso di laurea" name="cdl[fac]">
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="cdl-sede">Sede</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="cdl-sede" type="text" placeholder="Inserisci la sede del corso di laurea" name="cdl[sede]">
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="cdl-accesso">Accesso</label>
        <div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
            <label><input class="uk-radio" type="radio" name="cdl[accesso]" checked value="Programmato"> Programmato</label>
            <label><input class="uk-radio" type="radio" name="cdl[accesso]" value="Libero"> Libero</label>
        </div>
    </div>
    <div class="uk-margin">     
        <label class="uk-form-label" for="cdl-desc">Descrizione</label>
        <div class="uk-form-controls">
            <textarea class="uk-textarea" rows="5" placeholder="Inserisci la descrizione del corso di laurea" name="cdl[desc]"></textarea>
        </div>
    </div>
	<button class="uk-button uk-button-primary uk-align-right uk-margin-remove-bottom">Crea</button>
</form>