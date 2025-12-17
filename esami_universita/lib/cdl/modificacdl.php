<?php
	$error_msg = '';
	$success_msg = '';
    $cdl = null;
    $info_cdl = null;

    if(isset($_POST) && isset($_POST['cdl'])) {
        $cdl = $_POST['cdl'];

        if (isset($cdl['id'])) {

            $id = $cdl['id'];

            if (isset($cdl['nome'])) {

                $db = open_pg_connection();

                $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

                $sql = "SELECT * FROM corso_di_laurea WHERE id = $1";

                $result = pg_prepare($db, "upd_query3", $sql);
                $result = pg_execute($db, "upd_query3", array($id));

                $info_cdl = pg_fetch_assoc($result);

                close_pg_connection($db);

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

                    $sql = "UPDATE corso_di_laurea SET nome = $1, tipo = $2, anno_accademico_offerta = $3, facolta = $4, descrizione = $5, sede = $6, accesso = $7 WHERE id = $8";

                    $params = array();
                    $params[] = $nome;
                    $params[] = $tipo;
                    $params[] = $annoacc;
                    $params[] = $fac;
                    $params[] = $desc;
                    $params[] = $sede;
                    $params[] = $accesso;
                    $params[] = $id;

                    $result = pg_prepare($db, "upd_query1", $sql);
                    $result = pg_execute($db, "upd_query1", $params);

                    if ($result) {
                        $success_msg = "Corso di laurea modificato correttamente.";
                    }
                    else {
                        $error_msg = pg_last_error($db);
                    }

                }
            } else {

                $db = open_pg_connection();

                $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

                $sql = "SELECT * FROM corso_di_laurea WHERE id = $1";

                $params = array();
                $params[] = $id;

                $result = pg_prepare($db, "upd_query2", $sql);
                $result = pg_execute($db, "upd_query2", $params);

                $info_cdl = pg_fetch_assoc($result);
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
<?php if (isset($cdl) && empty($success_msg)) {?>
<form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
	<legend class="uk-legend">Modifica un corso di laurea</legend>
    <legend class="uk-text-small">Ricorda che puoi modificare solamente corsi di laurea non ancora attivati.</legend>
	<div class="uk-margin">     
        <label class="uk-form-label" for="cdl-nome">Nome</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="cdl-nome" type="text" placeholder="Inserisci il nuovo nome del corso di laurea" name="cdl[nome]" value="<?php echo $info_cdl['nome']; ?>">
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="cdl-tipo">Tipo</label>
        <div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
            <label><input class="uk-radio" type="radio" name="cdl[tipo]" <?php if ($info_cdl['tipo'] == 'Triennale') {echo 'checked';} ?> value="Triennale"> Triennale</label>
            <label><input class="uk-radio" type="radio" name="cdl[tipo]" <?php if ($info_cdl['tipo'] == 'Magistrale') {echo 'checked';} ?> value="Magistrale"> Magistrale</label>
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="cdl-annoacc">Anno accademico offerta</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="cdl-annoacc" type="text" placeholder="Inserisci il nuovo anno accademico dell'offerta" name="cdl[annoacc]" value="<?php echo $info_cdl['anno_accademico_offerta']; ?>">
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="cdl-fac">Facoltà</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="cdl-fac" type="text" placeholder="Inserisci la nuova facoltà del corso di laurea" name="cdl[fac]" value="<?php echo $info_cdl['facolta']; ?>">
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="cdl-sede">Sede</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="cdl-sede" type="text" placeholder="Inserisci la nuova sede del corso di laurea" name="cdl[sede]" value="<?php echo $info_cdl['sede']; ?>">
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="cdl-accesso">Accesso</label>
        <div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
            <label><input class="uk-radio" type="radio" name="cdl[accesso]" <?php if ($info_cdl['accesso'] == 'Programmato') {echo 'checked';} ?> value="Programmato"> Programmato</label>
            <label><input class="uk-radio" type="radio" name="cdl[accesso]" <?php if ($info_cdl['accesso'] == 'Libero') {echo 'checked';} ?> value="Libero"> Libero</label>
        </div>
    </div>
    <div class="uk-margin">     
        <label class="uk-form-label" for="cdl-desc">Descrizione</label>
        <div class="uk-form-controls">
            <textarea class="uk-textarea" rows="5" placeholder="Inserisci la nuova descrizione del corso di laurea" name="cdl[desc]"><?php echo $info_cdl['descrizione']; ?></textarea>
        </div>
    </div>
    <input type="hidden" name="cdl[id]" value="<?php echo $id; ?>">
	<button class="uk-button uk-button-primary uk-align-right uk-margin-remove-bottom">Modifica</button>
</form>
<form action="<?php echo $pagelink; ?>" method="POST">
    <button type="submit" class="uk-button uk-button-primary uk-align-left">Indietro</button>
</form>
<?php } else { ?>

<form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
    <legend class="uk-legend">Modifica un corso di laurea</legend>
    <legend class="uk-text-small">Ricorda che puoi modificare solamente corsi di laurea non ancora attivati.</legend>
    <div class="uk-margin">
        <label class="uk-form-label" for="cdl">Corso di laurea</label>
        <div class="uk-form-controls">
                <?php
                $cdl_keys = get_not_activated_cdl_entries();

                if (count($cdl_keys) != 0) {
                ?>
                <select class="uk-select" name="cdl[id]">
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
    <button class="uk-button uk-button-primary uk-align-right uk-margin-remove-bottom" <?php if (count($cdl_keys) == 0) {echo 'disabled';} ?>>Avanti</button>
</form>

<?php } ?>