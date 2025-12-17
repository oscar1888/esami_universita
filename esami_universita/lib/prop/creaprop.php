<?php
    $error_msg = '';
    $success_msg = '';
    $cdl = null;

    if (isset($_POST) && isset($_POST['cdl'])) {
        $cdl = $_POST['cdl'];

        if (isset($_POST['ins'])) {

            $ins = $_POST['ins'];

            $db = open_pg_connection();

            $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

            $sql = "INSERT INTO propedeuticita VALUES ($1, $2)";

            $params = array();
            $params[] = $ins['id1'];
            $params[] = $ins['id2'];

            $result = pg_prepare($db, "ins_query1", $sql);
            $result = pg_execute($db, "ins_query1", $params);

            if ($result) {
                $success_msg = "Propedeuticità definita correttamente.";
            }
            else {
                $error_msg = pg_last_error($db);
                if (strpos($error_msg, "Non è possibile rendere propedeutico un insegnamento con anno previsto")) {
                    $error_msg = remove_error_context($error_msg);
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
<?php if (isset($_POST) && isset($_POST['cdl']) && empty($success_msg)) { ?>
        <form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
            <legend class="uk-legend">Definisci una propedeuticità</legend>
            <legend class="uk-text-small">Ricorda che puoi definire propedeuticità solamente su insegnamenti in corsi di laurea non ancora attivati.</legend>
            <div class="uk-margin">
                <label class="uk-form-label" for="ins-id1">Insegnamento 1</label>
                <div class="uk-form-controls">
                        <?php
                        $ins_keys = get_not_activated_insegnamenti($cdl);

                        if (count($ins_keys) != 0) {
                        ?>
                        <select class="uk-select" name="ins[id1]">
                        <?php
                        foreach ($ins_keys as $k => $v) {
                        ?>
                        <option value="<?php print($k); ?>"><?php print($v); ?></option>
                        <?php
                        }
                        ?>
                    </select>
                    <?php } else {?>
                        <p><u>Non ci sono insegnamenti disponibili in archivio per questo corso di laurea.</u></p>
                    <?php
                    } ?>
                </div>
            </div>
            <div class="uk-margin">
                <label class="uk-form-label" for="ins-id2">Insegnamento 2</label>
                <div class="uk-form-controls">
                        <?php
                        $ins_keys = get_not_activated_insegnamenti($cdl);

                        if (count($ins_keys) != 0) {
                        ?>
                        <select class="uk-select" name="ins[id2]">
                        <?php
                        foreach ($ins_keys as $k => $v) {
                        ?>
                        <option value="<?php print($k); ?>"><?php print($v); ?></option>
                        <?php
                        }
                        ?>
                    </select>
                    <?php } else {?>
                        <p><u>Non ci sono insegnamenti disponibili in archivio per questo corso di laurea.</u></p>
                    <?php
                    } ?>
                </div>
            </div>
            <input type="hidden" name="cdl" value="<?php echo $cdl; ?>">
            <button class="uk-button uk-button-primary uk-align-right uk-margin-remove-bottom" <?php if (count($ins_keys) == 0) {echo 'disabled';} ?>>Definisci</button>
        </form>
        <form action="<?php echo $pagelink; ?>" method="POST">
            <button type="submit" class="uk-button uk-button-primary uk-align-left">Indietro</button>
        </form>
<?php } else { ?>
        <form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
            <legend class="uk-legend">Definisci una propedeuticità</legend>
            <legend class="uk-text-small">Ricorda che puoi definire propedeuticità solamente su insegnamenti in corsi di laurea non ancora attivati.</legend>
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