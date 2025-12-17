<?php
    $error_msg = '';
    $success_msg = '';
    $ins = null;
    $cdl = null;

    if(isset($_POST) && isset($_POST['ins'])) {
        $ins = $_POST['ins'];

        if (isset($ins['cdl'])) {

            $cdl = $ins['cdl'];

            if (isset($ins['id'])) {

                if (empty($error_msg)) {

                    $db = open_pg_connection();

                    $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

                    $sql = "DELETE FROM insegnamento WHERE id = $1";

                    $params = array();
                    $params[] = $ins['id'];

                    $result = pg_prepare($db, "del_query1", $sql);
                    $result = pg_execute($db, "del_query1", $params);

                    if ($result) {
                        $success_msg = "Insegnamento eliminato correttamente.";
                    }
                    else {
                        $error_msg = pg_last_error($db);
                    }

                }
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
<?php if (isset($ins) && empty($success_msg)) {?>
<form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
    <legend class="uk-legend">Elimina un insegnamento</legend>
    <legend class="uk-text-small">Ricorda che puoi eliminare insegnamenti solamente in corsi di laurea non ancora attivati.</legend>
    <div class="uk-margin">
        <label class="uk-form-label" for="ins-id">Insegnamento</label>
        <div class="uk-form-controls">
                <?php
                $ins_keys = get_not_activated_insegnamenti($cdl);

                if (count($ins_keys) != 0) {
                ?>
                <select class="uk-select" name="ins[id]">
                <?php
                foreach ($ins_keys as $k => $v) {
                ?>
                <option value="<?php print($k); ?>"><?php print($v); ?></option>
                <?php
                }
                ?>
            </select>
            <?php } else {?>
                    <p><u>Non ci sono insegnamenti disponibili su questo corso di laurea in archivio.</u></p>
                <?php
                } ?>
        </div>
    </div>
    <input type="hidden" name="ins[cdl]" value="<?php echo $cdl; ?>">
    <button class="uk-button uk-button-primary uk-button-danger uk-align-right uk-margin-remove-bottom" <?php if (count($ins_keys) == 0) {echo 'disabled';} ?>>Elimina</button>
</form>
<form action="<?php echo $pagelink; ?>" method="POST">
    <button type="submit" class="uk-button uk-button-primary uk-align-left">Indietro</button>
</form>
<?php } else { ?>

<form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
    <legend class="uk-legend">Elimina un insegnamento</legend>
    <legend class="uk-text-small">Ricorda che puoi eliminare insegnamenti solamente in corsi di laurea non ancora attivati.</legend>
    <div class="uk-margin">
        <label class="uk-form-label" for="ins-cdl">Corso di laurea</label>
        <div class="uk-form-controls">
                <?php
                $cdl_keys = get_not_activated_cdl_entries();

                if (count($cdl_keys) != 0) {
                ?>
                <select class="uk-select" name="ins[cdl]">
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