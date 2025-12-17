<?php
    $error_msg = '';
    $success_msg = '';
    $ins = null;
    $cdl = null;
    $tipo_cdl = null;
    $id = null;
    $cod_interno = null;
    $nome = null;
    $info_cdl = null;

    if(isset($_POST) && isset($_POST['ins'])) {
        $ins = $_POST['ins'];

        if (isset($ins['cdl'])) {

            $cdl = $ins['cdl'];

            if ($_POST['status'] == '3a1') {

                $desc = null;
                if(!empty($ins['desc']))
                    $desc = $ins['desc'];
                else
                    $error_msg = "Errore. E' necessario inserire la nuova descrizione dell'insegnamento.";
                
                $lingua = null;
                if(!empty($ins['lingua']))
                    $lingua = $ins['lingua'];
                else
                    $error_msg = "Errore. E' necessario inserire la nuova lingua dell'insegnamento.";

                $ore = $ins['ore'];

                $doc = null;
                if(!empty($ins['doc']))
                    $doc = $ins['doc'];
                else
                    $error_msg = "Errore. E' necessario inserire il nuovo docente responsabile dell'insegnamento.";

                if(!empty($ins['nome']))
                    $nome = $ins['nome'];
                else
                    $error_msg = "Errore. E' necessario inserire il nuovo nome dell'insegnamento.";

                if (empty($error_msg)) {

                    $id = $ins['id'];

                    $db = open_pg_connection();

                    $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

                    $sql = "UPDATE insegnamento SET nome = $1, docente = $2, ore_totali = $3, lingua = $4, descrizione_testuale = $5 WHERE id = $6";

                    $params = array();
                    $params[] = $nome;
                    $params[] = $doc;
                    $params[] = $ore;
                    $params[] = $lingua;
                    $params[] = $desc;
                    $params[] = $id;

                    $result = pg_prepare($db, "upd_query1", $sql);
                    $result = pg_execute($db, "upd_query1", $params);

                    if ($result) {
                        $success_msg = "Insegnamento modificato correttamente.";
                    }
                    else {
                        $error_msg = pg_last_error($db);
                        if (strpos($error_msg, '3 insegnamenti')) {
                            $error_msg = 'Non è possibile modificare un insegnamento con un docente che è già responsabile di 3 insegnamenti.';
                        } else if (strpos($error_msg, 'insegnamento_corso_di_laurea_nome_key')) {
                            $error_msg = 'Esiste già un insegnamento con questo nome in questo corso di laurea.';
                        }
                    }

                }

                if (!empty($error_msg)) {
                    $_POST['status'] = '2a3';
                }
            }

            if ($_POST['status'] == '2a3') {

                $db = open_pg_connection();

                $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

                $sql = "SELECT tipo FROM corso_di_laurea WHERE id = $1";

                $result = pg_prepare($db, "ins_query1", $sql);
                $result = pg_execute($db, "ins_query1", array($cdl));

                $tipo_cdl = pg_fetch_assoc($result)['tipo'];

                $sql = "SELECT nome, docente, anno_previsto, semestre, ore_totali, lingua, descrizione_testuale FROM insegnamento WHERE id = $1";

                $result = pg_prepare($db, "ins_query2", $sql);
                $result = pg_execute($db, "ins_query2", array($ins['id']));

                $info_cdl = pg_fetch_assoc($result);

                close_pg_connection($db);

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
    <h5 class="uk-margin-remove-top"><b>Informazioni sull'insegnamento modificato:</b>
    <ul class="uk-margin-remove-top uk-margin-remove-bottom uk-text-default">
        <li><u>Id insegnamento</u>: <?php echo $id; ?></li>
        <li><u>Corso di laurea</u>: <?php echo $cdl; ?></li>
        <li><u>Codice interno</u>: <?php echo $cod_interno; ?></li>
        <li><u>Nome</u>: <?php echo $nome; ?></li>
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
<?php 
    if (isset($_POST) && isset($_POST['status'])) {
        switch ($_POST['status']) {
            case '2a3':
                two2three:?>
                <form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
                    <legend class="uk-legend">Modifica un insegnamento</legend>
                    <legend class="uk-text-small">Ricorda che puoi modificare insegnamenti solamente in corsi di laurea non ancora attivati.</legend>
                    <div class="uk-margin">     
                        <label class="uk-form-label" for="ins-nome">Nome</label>
                        <div class="uk-form-controls">
                            <input class="uk-input" id="ins-nome" type="text" placeholder="Inserisci il nome dell'insegnamento" name="ins[nome]" value="<?php echo $info_cdl['nome']; ?>">
                        </div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="ins-doc">Docente</label>
                        <div class="uk-form-controls">
                            <input class="uk-input" id="ins-doc" type="text" placeholder="Inserisci il nome del docente" name="ins[doc]" value="<?php echo $info_cdl['docente']; ?>">
                        </div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="ins[prevyear]">Anno previsto</label>
                        <div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
                            <label><input class="uk-radio" type="radio" value="1" disabled <?php if ($info_cdl['anno_previsto'] == 1) {echo 'checked';} ?>> 1</label>
                            <label><input class="uk-radio" type="radio" value="2" disabled <?php if ($info_cdl['anno_previsto'] == 2) {echo 'checked';} ?>> 2</label>
                            <?php if ($tipo_cdl == 'Triennale') {
                            ?>
                                <label><input class="uk-radio" type="radio" value="3" disabled <?php if ($info_cdl['anno_previsto'] == 3) {echo 'checked';} ?>> 3</label>
                            <?php
                            } ?>
                        </div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="ins[sem]">Semestre</label>
                        <div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
                            <label><input class="uk-radio" type="radio" value="1" disabled <?php if ($info_cdl['semestre'] == 1) {echo 'checked';} ?>> Primo</label>
                            <label><input class="uk-radio" type="radio" value="2" disabled <?php if ($info_cdl['semestre'] == 2) {echo 'checked';} ?>> Secondo</label>
                        </div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="ins-ore">Ore totali</label>
                        <div class="uk-form-controls">
                            <input class="uk-input" id="ins-ore" min="1" max="200" value="1" step="1" type="number" placeholder="Inserire le ore totali dell'insegnamento" name="ins[ore]" value="<?php echo $info_cdl['ore_totali']; ?>">
                        </div>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="ins-lingua">Lingua</label>
                        <div class="uk-form-controls">
                            <input class="uk-input" id="ins-lingua" type="text" value="Italiano" placeholder="Inserisci la lingua dell'insegnamento" name="ins[lingua]" value="<?php echo $info_cdl['lingua']; ?>">
                        </div>
                    </div>
                    <div class="uk-margin">     
                        <label class="uk-form-label" for="ins-desc">Descrizione testuale</label>
                        <div class="uk-form-controls">
                            <textarea class="uk-textarea" rows="5" placeholder="Inserisci la descrizione dell'insegnamento" name="ins[desc]"><?php echo $info_cdl['descrizione_testuale']; ?></textarea>
                        </div>
                    </div>
                    <input type="hidden" name="ins[cdl]" value="<?php echo $cdl; ?>">
                    <input type="hidden" name="ins[id]" value="<?php echo $ins['id']; ?>">
                    <input type="hidden" name="status" value="3a1">
                    <button class="uk-button uk-button-primary uk-align-right uk-margin-remove-bottom">Modifica</button>
                </form>
                <form action="<?php echo $pagelink; ?>" method="POST">
                    <input type="hidden" name="status" value="3a2">
                    <input type="hidden" name="ins[cdl]" value="<?php echo $cdl; ?>">
                    <button type="submit" class="uk-button uk-button-primary uk-align-left">Indietro</button>
                </form>

                
<?php       
                break;
            case '1a2':
            case '3a2':
?>
                <form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
                    <legend class="uk-legend">Modifica un insegnamento</legend>
                    <legend class="uk-text-small">Ricorda che puoi modificare insegnamenti solamente in corsi di laurea non ancora attivati.</legend>
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
                    <input type="hidden" name="status" value="2a3">
                    <button class="uk-button uk-button-primary uk-align-right uk-margin-remove-bottom" <?php if (count($ins_keys) == 0) {echo 'disabled';} ?>>Avanti</button>
                </form>
                <form action="<?php echo $pagelink; ?>" method="POST">
                    <input type="hidden" name="status" value="2a1">
                    <button type="submit" class="uk-button uk-button-primary uk-align-left">Indietro</button>
                </form>
<?php
                break;
            
            case '3a1':
                if (!empty($error_msg)) {
                    goto two2three;
                }
?>
<?php
            case '2a1':
            default:
?>
                <form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
                    <legend class="uk-legend">Modifica un insegnamento</legend>
                    <legend class="uk-text-small">Ricorda che puoi modificare insegnamenti solamente in corsi di laurea non ancora attivati.</legend>
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
                    <input type="hidden" name="status" value="1a2">
                    <button class="uk-button uk-button-primary uk-align-right uk-margin-remove-bottom" <?php if (count($cdl_keys) == 0) {echo 'disabled';} ?>>Avanti</button>
                </form>
<?php
                break;
        }
    } else {
?>
                <form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
                    <legend class="uk-legend">Modifica un insegnamento</legend>
                    <legend class="uk-text-small">Ricorda che puoi modificare insegnamenti solamente in corsi di laurea non ancora attivati.</legend>
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
                    <input type="hidden" name="status" value="1a2">
                    <button class="uk-button uk-button-primary uk-align-right uk-margin-remove-bottom" <?php if (count($cdl_keys) == 0) {echo 'disabled';} ?>>Avanti</button>
                </form>
<?php
    }
?>