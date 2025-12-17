<?php
    $error_msg = '';
    $success_msg = '';

    if(isset($_POST) && isset($_POST['cdl'])) {
        $cdl = $_POST['cdl'];

        if (empty($error_msg)) {

            $db = open_pg_connection();

            $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

            $sql = "UPDATE corso_di_laurea SET attivato = TRUE WHERE id = $1";

            $params = array();
            $params[] = $cdl;

            $result = pg_prepare($db, "ins_query1", $sql);
            $result = pg_execute($db, "ins_query1", $params);
            
            if ($result)
                $success_msg = "Corso di laurea attivato correttamente.";
            else
                $error_msg = pg_last_error($db);
        }
    }

    $pagelink = $_SERVER['PHP_SELF'] . '?mod=attiva';
            
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
    <legend class="uk-legend">Attiva un corso di laurea</legend>

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
    <button class="uk-button uk-button-primary uk-align-right uk-margin-remove-bottom" <?php if (count($cdl_keys) == 0) {echo 'disabled';} ?>>Attiva</button>
</form>