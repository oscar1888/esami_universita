<?php
	$error_msg = '';
	$success_msg = '';

    if(isset($_POST) && isset($_POST['es'])) {
        $es = $_POST['es'];

        if (isset($es['data']) && isset($es['ora'])) {

            $luogo = null;
            if(!empty($es['luogo']))
                $luogo = $es['luogo'];
            else
                $error_msg = "Errore. E' necessario inserire il nuovo luogo dove si svolgerà l'esame.";
            
            $tipo = $es['tipo'];

            $ora = null;
            if(!empty($es['ora']))
                $ora = $es['ora'];
            else
                $error_msg = "Errore. E' necessario inserire il nuovo orario dell'esame.";

            if (empty($error_msg)) {

                $db = open_pg_connection();

                $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

        		$sql = "UPDATE esame SET orario = $1, tipo = $2, luogo = $3 WHERE insegnamento = $4 AND data = $5";

                $params = array();
                $params[] = $es['ora'];
                $params[] = $es['tipo'];
                $params[] = $es['luogo'];
                $params[] = $es['ins'];
                $params[] = $es['data'];

                $result = pg_prepare($db, "upd_query1", $sql);
                $result = pg_execute($db, "upd_query1", $params);
                
                if ($result) {
                    $success_msg = "Appello d'esame modificato correttamente.";
                }
                else {
                    $error_msg = pg_last_error($db);
                }
            }
        }

        if (isset($es['data'])) {

            $db = open_pg_connection();

            $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

            $sql = "SELECT orario, tipo, luogo FROM esame WHERE insegnamento = $1 AND data = $2";

            $params = array();
            $params[] = $es['ins'];
            $params[] = $es['data'];

            $result = pg_prepare($db, "upd_query2", $sql);
            $result = pg_execute($db, "upd_query2", $params);

            if ($row = pg_fetch_assoc($result)) {
                $es['orario'] = $row['orario'];
                $es['tipo'] = $row['tipo'];
                $es['luogo'] = $row['luogo'];
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
<?php if (isset($_POST) && isset($_POST['es']) && !isset($_POST['es']['data'])) { ?>

<form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
    <legend class="uk-legend">Modifica un appello d'esame</legend>
    <legend class="uk-text-small">Ricorda che puoi solamente modificare esami sui quali non è stato ancora verbalizzato un voto.</legend>
    <div class="uk-margin">     
        <label class="uk-form-label" for="es-ins">Data</label>
        <div class="uk-form-controls">
            <?php 
                $db = open_pg_connection();

                $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

                $sql = "(SELECT data FROM esame WHERE insegnamento = $1) EXCEPT (SELECT DISTINCT data FROM carriera_completa WHERE id = $1) ORDER BY data DESC";

                $params = array();
                $params[] = $_POST['es']['ins'];

                $result = pg_prepare($db, "sear_query2", $sql);
                $result = pg_execute($db, "sear_query2", $params);

                $date = array();

                while ($row = pg_fetch_assoc($result)) {

                    $date[$row['data']] = $row['data'];

                }

                close_pg_connection($db);
?>

<?php
                if (count($date) != 0) {
                ?>
                <select class="uk-select" name="es[data]">
                <?php
                foreach ($date as $key => $value) {
?>

                    <option value="<?php print($key); ?>"><?php echo $value; ?></option>

<?php
                }

             ?>
                </select>
                <?php } else {?>
                    <p><u>Non ci sono date disponibili in archivio.</u></p>
                <?php
                } ?>

                <input type="hidden" name="es[ins]" value="<?php echo $es['ins']; ?>">
        </div>
    </div>
    <button class="uk-button uk-align-right uk-button-primary uk-margin-remove-bottom" <?php if (count($date) == 0) {echo 'disabled';} ?>>Avanti</button>
</form>

<form action="<?php echo $pagelink; ?>" method="POST">
    <button type="submit" class="uk-button uk-button-primary uk-align-left">Indietro</button>
</form>

<?php } elseif (isset($_POST) && isset($_POST['es']) && isset($_POST['es']['data']) && empty($success_msg)) { ?>

<form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
    <legend class="uk-legend">Modifica un appello d'esame</legend>
    <legend class="uk-text-small">Ricorda che puoi solamente modificare esami sui quali non è stato ancora verbalizzato un voto.</legend>
    <div class="uk-margin">     
        <label class="uk-form-label" for="es-ins">Insegnamento</label>
        <div class="uk-form-controls">
            <?php 
                $db = open_pg_connection();

                $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

                $sql = "SELECT i.nome, c.nome AS cdl, i.id FROM insegnamento i INNER JOIN corso_di_laurea c ON i.corso_di_laurea = c.id WHERE i.docente = $1 AND c.attivato = TRUE";

                $params = array();
                $params[] = $logged;

                $result = pg_prepare($db, "sear_query1", $sql);
                $result = pg_execute($db, "sear_query1", $params);

                $ins = array();

                while ($row = pg_fetch_assoc($result)) {

                    $ins[$row['id']] = array($row['id'], $row['nome'], $row['cdl']);

                }

                close_pg_connection($db);
?>
                <select class="uk-select" name="es[ins]" disabled>

<?php
                foreach ($ins as $key => $value) {
                    $selected = "";
                    if ($key == $_POST['es']['ins']) {
                        $selected = "selected";
                    }
?>

                    <option value="<?php print($key); ?>" <?php echo $selected; ?>><?php echo $value[1] . ', ' . $value[2] . ' (' . $value[0] . ')'; ?></option>

<?php
                }

             ?>
                </select>
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="es-data">Data</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="es-data" type="date" name="es[data]" value="<?php echo $es['data']; ?>" disabled>
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="es-ora">Orario</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="es-ora" type="time" value="<?php echo $es['orario']; ?>" name="es[ora]">
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="es-tipo">Tipo</label>
        <div class="uk-form-controls">
            <select class="uk-select" name="es[tipo]">
                <option value="Scritto" <?php if ($es['tipo'] == 'Scritto') {echo 'selected';} ?>>Scritto</option>
                <option value="Orale" <?php if ($es['tipo'] == 'Orale') {echo 'selected';} ?>>Orale</option>
                <option value="Laboratorio" <?php if ($es['tipo'] == 'Laboratorio') {echo 'selected';} ?>>Laboratorio</option>
            </select>
        </div>
    </div>
    <div class="uk-margin">     
        <label class="uk-form-label" for="es-luogo">Luogo</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="es-luogo" type="text" placeholder="Inserisci il luogo dove si svolgerà l'esame" name="es[luogo]" value="<?php echo $es['luogo']; ?>">
        </div>
    </div>
    <input type="hidden" name="es[ins]" value="<?php echo $es['ins']; ?>">
    <input type="hidden" name="es[data]" value="<?php echo $_POST['es']['data']; ?>">
    <button class="uk-button uk-button-primary uk-align-right uk-margin-remove-bottom">Modifica</button>
</form>

<form action="<?php echo $pagelink; ?>" method="POST">
    <input type="hidden" name="es[ins]" value="<?php echo $es['ins']; ?>">
    <button type="submit" class="uk-button uk-button-primary uk-align-left">Indietro</button>
</form>

<?php } else { ?>
<form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
    <legend class="uk-legend">Modifica un appello d'esame</legend>
    <legend class="uk-text-small">Ricorda che puoi solamente modificare esami sui quali non è stato ancora verbalizzato un voto.</legend>
    <div class="uk-margin">     
        <label class="uk-form-label" for="es-ins">Insegnamento</label>
        <div class="uk-form-controls">
            <?php 
                $db = open_pg_connection();

                $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

                $sql = "SELECT i.nome, c.nome AS cdl, i.id FROM insegnamento i INNER JOIN corso_di_laurea c ON i.corso_di_laurea = c.id WHERE i.docente = $1 AND c.attivato = TRUE";

                $params = array();
                $params[] = $logged;

                $result = pg_prepare($db, "sear_query1", $sql);
                $result = pg_execute($db, "sear_query1", $params);

                $ins = array();

                while ($row = pg_fetch_assoc($result)) {

                    $ins[$row['id']] = array($row['id'], $row['nome'], $row['cdl']);

                }

                close_pg_connection($db);
?>

<?php
                if (count($ins) != 0) {
                ?>
                <select class="uk-select" name="es[ins]">
                <?php
                foreach ($ins as $key => $value) {
?>

                    <option value="<?php print($key); ?>"><?php echo $value[1] . ', ' . $value[2] . ' (' . $value[0] . ')'; ?></option>

<?php
                }

             ?>
                </select>
                <?php } else {?>
            <p><u>Non ci sono insegnamenti disponibili in archivio.</u></p>
        <?php
        } ?>
        </div>
    </div>
    <button class="uk-button uk-button-primary uk-align-right uk-margin-remove-bottom" <?php if (count($ins) == 0) {echo 'disabled';} ?>>Avanti</button>
</form>
<?php } ?>