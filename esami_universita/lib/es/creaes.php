<?php
	$error_msg = '';
	$success_msg = '';

    if(isset($_POST) && isset($_POST['es'])) {
        $es = $_POST['es'];

        $luogo = null;
        if(!empty($es['luogo']))
            $luogo = $es['luogo'];
        else
            $error_msg = "Errore. E' necessario inserire il luogo dove si svolgerà l'esame.";
       	
        $tipo = $es['tipo'];

        $ora = null;
        if(!empty($es['ora']))
            $ora = $es['ora'];
        else
            $error_msg = "Errore. E' necessario inserire l'orario dell'esame.";

        $data = null;
        if(!empty($es['data']))
            $data = $es['data'];
        else
            $error_msg = "Errore. E' necessario inserire la data dell'esame.";

        $ins = $es['ins'];

        if (empty($error_msg)) {

            $db = open_pg_connection();

            $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

			$sql = "INSERT INTO esame VALUES ($1, $2, $3, $4, $5)";

            $params = array();
            $params[] = $ins;
            $params[] = $data;
            $params[] = $ora;
            $params[] = $tipo;
            $params[] = $luogo;

            $result = pg_prepare($db, "ins_query1", $sql);
            $result = pg_execute($db, "ins_query1", $params);
            
            if ($result) {
                $success_msg = "Appello d'esame creato correttamente.";
            }
            else {
                $error_msg = pg_last_error($db);
                if (strpos($error_msg, 'Non si può programmare un esame nella stessa giornata')) {
                    $error_msg = 'Non si può programmare un esame nella stessa giornata di un altro esame di un insegnamento dello stesso corso di laurea previsto per lo stesso anno.';
                }
                if (strpos($error_msg, "La data dell'esame deve essere posteriore al giorno corrente")){
                    $error_msg = "La data dell'esame deve essere posteriore al giorno corrente.";
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
	<legend class="uk-legend">Crea un nuovo appello d'esame</legend>

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
	<div class="uk-margin">
        <label class="uk-form-label" for="es-data">Data</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="es-data" type="date" name="es[data]">
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="es-ora">Orario</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="es-ora" type="time" name="es[ora]">
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="es-tipo">Tipo</label>
        <div class="uk-form-controls">
            <select class="uk-select" name="es[tipo]">
                <option value="Scritto">Scritto</option>
                <option value="Orale">Orale</option>
                <option value="Laboratorio">Laboratorio</option>
            </select>
        </div>
    </div>
    <div class="uk-margin">     
        <label class="uk-form-label" for="es-luogo">Luogo</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="es-luogo" type="text" placeholder="Inserisci il luogo dove si svolgerà l'esame" name="es[luogo]">
        </div>
    </div>
	<button class="uk-button uk-button-primary uk-align-right uk-margin-remove-bottom" <?php if (count($ins) == 0) {echo 'disabled';} ?>>Crea</button>
</form>