<?php
	$error_msg = '';
	$success_msg = '';

    if(isset($_POST) && isset($_POST['es'])) {
        $es = $_POST['es'];

        if (isset($es['data'])) {

            $db = open_pg_connection();

            $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

    		$sql = "DELETE FROM esame WHERE insegnamento = $1 AND data = $2";

            $params = array();
            $params[] = $es['ins'];
            $params[] = $es['data'];

            $result = pg_prepare($db, "del_query1", $sql);
            $result = pg_execute($db, "del_query1", $params);
            
            if ($result) {
                $success_msg = "Appello d'esame eliminato correttamente.";
            }
            else {
                $error_msg = pg_last_error($db);
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
<?php if (isset($_POST) && isset($_POST['es']) && empty($success_msg)) { ?>

<form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
    <legend class="uk-legend">Elimina un appello d'esame</legend>
    <legend class="uk-text-small">Ricorda che puoi solamente eliminare esami sui quali non è stato ancora verbalizzato un voto.</legend>
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
    <button class="uk-button uk-align-right uk-button-danger uk-margin-remove-bottom" <?php if (count($date) == 0) {echo 'disabled';} ?>>Elimina</button>
</form>

<form action="<?php echo $pagelink; ?>" method="POST">
    <button type="submit" class="uk-button uk-button-primary uk-align-left">Indietro</button>
</form>

<?php } else { ?>

<form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
    <legend class="uk-legend">Elimina un appello d'esame</legend>
    <legend class="uk-text-small">Ricorda che puoi solamente eliminare esami sui quali non è stato ancora verbalizzato un voto.</legend>
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