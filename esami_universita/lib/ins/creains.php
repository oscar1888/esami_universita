<?php
    $error_msg = '';
    $success_msg = '';
    $ins = null;
    $cdl = null;
    $tipo_cdl = null;
    $id = null;
    $cod_interno = null;
    $nome = null;

    if(isset($_POST) && isset($_POST['ins'])) {
        $ins = $_POST['ins'];

        if (isset($ins['cdl'])) {

            $cdl = $ins['cdl'];

            if (isset($ins['nome'])) {

                $desc = null;
                if(!empty($ins['desc']))
                    $desc = $ins['desc'];
                else
                    $error_msg = "Errore. E' necessario inserire la descrizione dell'insegnamento.";
                
                $lingua = null;
                if(!empty($ins['lingua']))
                    $lingua = $ins['lingua'];
                else
                    $error_msg = "Errore. E' necessario inserire la lingua dell'insegnamento.";

                $ore = $ins['ore'];

                $sem = $ins['sem'];

                $annoprev = $ins['prevyear'];

                $doc = null;
                if(!empty($ins['doc']))
                    $doc = $ins['doc'];
                else
                    $error_msg = "Errore. E' necessario inserire il docente responsabile dell'insegnamento.";

                if(!empty($ins['nome']))
                    $nome = $ins['nome'];
                else
                    $error_msg = "Errore. E' necessario inserire il nome dell'insegnamento.";

                if (empty($error_msg)) {

                    $cod_interno = gen_cod_interno($cdl);

                    $cod_interno = str_pad($cod_interno, 3, "0", STR_PAD_LEFT);

                    $id = $cdl . $cod_interno;

                    $db = open_pg_connection();

                    $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

                    $sql = "INSERT INTO insegnamento VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)";

                    $params = array();
                    $params[] = $id;
                    $params[] = $cdl;
                    $params[] = $cod_interno;
                    $params[] = $nome;
                    $params[] = $doc;
                    $params[] = $annoprev;
                    $params[] = $sem;
                    $params[] = $ore;
                    $params[] = $lingua;
                    $params[] = $desc;

                    $result = pg_prepare($db, "ins_query1", $sql);
                    $result = pg_execute($db, "ins_query1", $params);

                    if ($result) {
                        $success_msg = "Insegnamento creato correttamente.";
                    }
                    else {
                        $error_msg = pg_last_error($db);
                        if (strpos($error_msg, '3 insegnamenti')) {
                            $error_msg = 'Non è possibile inserire un insegnamento con un docente che è già responsabile di 3 insegnamenti.';
                        } else if (strpos($error_msg, 'insegnamento_corso_di_laurea_nome_key')) {
                            $error_msg = 'Esiste già un insegnamento con questo nome in questo corso di laurea.';
                        }
                    }

                }
            }

                $db = open_pg_connection();

                $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

                $sql = "SELECT tipo FROM corso_di_laurea WHERE id = $1";

                $result = pg_prepare($db, "ins_query1", $sql);
                $result = pg_execute($db, "ins_query1", array($cdl));

                $tipo_cdl = pg_fetch_assoc($result)['tipo'];

                close_pg_connection($db);

            
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
    <h5 class="uk-margin-remove-top"><b>Informazioni sul nuovo insegnamento:</b>
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
<?php if (isset($ins) && empty($success_msg)) {?>
<form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
    <legend class="uk-legend">Crea un insegnamento</legend>
    <legend class="uk-text-small">Ricorda che puoi creare insegnamenti solamente in corsi di laurea non ancora attivati.</legend>
    <div class="uk-margin">     
        <label class="uk-form-label" for="ins-nome">Nome</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="ins-nome" type="text" placeholder="Inserisci il nome dell'insegnamento" name="ins[nome]">
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="ins-doc">Docente</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="ins-doc" type="text" placeholder="Inserisci il nome del docente" name="ins[doc]">
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="ins[prevyear]">Anno previsto</label>
        <div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
            <label><input class="uk-radio" type="radio" name="ins[prevyear]" checked value="1"> 1</label>
            <label><input class="uk-radio" type="radio" name="ins[prevyear]" value="2"> 2</label>
            <?php if ($tipo_cdl == 'Triennale') {
            ?>
                <label><input class="uk-radio" type="radio" name="ins[prevyear]" value="3"> 3</label>
            <?php
            } ?>
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="ins[sem]">Semestre</label>
        <div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
            <label><input class="uk-radio" type="radio" name="ins[sem]" checked value="1"> Primo</label>
            <label><input class="uk-radio" type="radio" name="ins[sem]" value="2"> Secondo</label>
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="ins-ore">Ore totali</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="ins-ore" min="1" max="200" value="1" step="1" type="number" placeholder="Inserire le ore totali dell'insegnamento" name="ins[ore]">
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label" for="ins-lingua">Lingua</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="ins-lingua" type="text" value="Italiano" placeholder="Inserisci la lingua dell'insegnamento" name="ins[lingua]">
        </div>
    </div>
    <div class="uk-margin">     
        <label class="uk-form-label" for="ins-desc">Descrizione testuale</label>
        <div class="uk-form-controls">
            <textarea class="uk-textarea" rows="5" placeholder="Inserisci la descrizione dell'insegnamento" name="ins[desc]"></textarea>
        </div>
    </div>
    <input type="hidden" name="ins[cdl]" value="<?php echo $cdl; ?>">
    <button class="uk-button uk-button-primary uk-align-right uk-margin-remove-bottom">Crea</button>
</form>
<form action="<?php echo $pagelink; ?>" method="POST">
    <button type="submit" class="uk-button uk-button-primary uk-align-left">Indietro</button>
</form>
<?php } else { ?>

<form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
    <legend class="uk-legend">Crea un insegnamento</legend>
    <legend class="uk-text-small">Ricorda che puoi creare insegnamenti solamente in corsi di laurea non ancora attivati.</legend>
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