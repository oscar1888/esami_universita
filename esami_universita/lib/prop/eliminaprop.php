<?php
    $error_msg = '';
    $success_msg = '';
    $cdl = null;

    if (isset($_POST) && isset($_POST['cdl'])) {
        $cdl = $_POST['cdl'];

        if (isset($_GET) && isset($_GET['id1']) && isset($_GET['id2'])) {

            $ins1 = $_GET['id1'];
            $ins2 = $_GET['id2'];

            $db = open_pg_connection();

            $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

            $sql = "DELETE FROM propedeuticita WHERE insegnamento_1 = $1 AND insegnamento_2 = $2";

            $params = array();
            $params[] = $ins1;
            $params[] = $ins2;

            $result = pg_prepare($db, "del_query1", $sql);
            $result = pg_execute($db, "del_query1", $params);

            if ($result) {
                $success_msg = "Propedeuticità eliminata correttamente.";
            }
            else {
                $error_msg = pg_last_error($db);
            }

            close_pg_connection($db);

        } else {

            $props = array();

            $db = open_pg_connection();

            $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

            $sql = "SELECT i1.id AS id1, i1.nome AS insegnamento_1, i2.id AS id2, i2.nome AS insegnamento_2 FROM propedeuticita p INNER JOIN insegnamento i1 ON p.insegnamento_1 = i1.id INNER JOIN insegnamento i2 ON p.insegnamento_2 = i2.id WHERE i1.corso_di_laurea = $1 ORDER BY i2.nome, i1.nome";

            $params = array();
            $params[] = $cdl;

            $result = pg_prepare($db, "del_query2", $sql);
            $result = pg_execute($db, "del_query2", $params);

            while ($row = pg_fetch_assoc($result)) {

                $props[$row['insegnamento_1'] . $row['insegnamento_2']] = array($row['id1'], $row['insegnamento_1'], $row['id2'], $row['insegnamento_2']);

            }

            close_pg_connection($db);

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
<?php if (isset($_POST) && isset($_POST['cdl']) && empty($success_msg)) { ?>
            <legend class="uk-legend">Elimina una propedeuticità</legend>
            <legend class="uk-text-small">Ricorda che puoi eliminare propedeuticità solamente tra insegnamenti di corsi di laurea non ancora attivati.</legend>
<!------------------------------------------------------------------------------------------------------>
            <?php if (count($props) == 0) {
                echo "<br>";
                echo "Non sono state definite propedeuticità in questo corso di laurea.";
            } else { ?>
            <table class="uk-table uk-table-divider">
            <thead>
                <tr>
                    <th>Insegnamento 1</th>
                    <th>Insegnamento 2</th>
                    <th>Eliminazione</th>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach($props as $id=>$values){
                $link = $_SERVER['PHP_SELF'] . '?mod=elimina&id1=' . $values[0] . '&id2=' . $values[2];
            ?>
                <tr>
                    <td><?php echo $values[1]; ?></td>
                    <td><?php echo $values[3]; ?></td>
                    <td><form action="<?php echo $link; ?>" method="POST">
                        <input type="hidden" name="cdl" value="<?php echo $cdl; ?>">
                        <button class="uk-button uk-button-primary uk-button-danger">Elimina</button>
                    </form></td>
                </tr>
            <?php
            }
            ?>
            </tbody>
            </table>
            <legend>Attenzione: L'insegnamento 1 è propedeutico all'insegnamento 2.</legend>
            <br>
            <?php } ?>
<!------------------------------------------------------------------------------------------------------>
        <form action="<?php echo $pagelink; ?>" method="POST">
            <br>
            <button type="submit" class="uk-button uk-button-primary uk-align-left">Indietro</button>
        </form>
<?php } else { ?>
        <form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
            <legend class="uk-legend">Elimina una propedeuticità</legend>
            <legend class="uk-text-small">Ricorda che puoi eliminare propedeuticità solamente tra insegnamenti di corsi di laurea non ancora attivati.</legend>
            <div class="uk-margin">
                <label class="uk-form-label" for="cdl">Corso di laurea</label>
                <div class="uk-form-controls">
                        <?php
                        $cdl_keys = get_not_activated_cdl_entries();

                        if (count($cdl_keys) != 0) {
                        ?>
                        <select class="uk-select" name="cdl">
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