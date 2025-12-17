<?php include ('../checklogin.php');?>
<!DOCTYPE html>
<html>
    <head>
        <?php include_once ('../header.php'); ?>
        <title>UniEsami</title>
    </head>
    <body>
    <div class="uk-container uk-margin-bottom uk-margin-top">
        <h1 class="uk-article-title">UniEsami</h1>
    <?php
    if (isset($logged)) {
        $logout_link = $_SERVER['PHP_SELF'] . "?log=del";
    ?>
    <div class="uk-card uk-card-body uk-margin-remove uk-padding-remove uk-text-right">
    <p>
        <?php echo("Benvenuto $logged"); ?> - 
        <a href="<?php echo($logout_link); ?>">Logout</a> 
    </p>
    </div>
    <?php
    } 
    ?>

    <div class="uk-section uk-section-default">
    
    <?php

    if(!isset($logged)) {

    ?>

    <div class="uk-width-1-3@s uk-container">
    <div class="uk-panel uk-panel-space uk-text-center">
    <form class="uk-form-horizontal" action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
        <legend class="uk-legend">Autenticazione</legend>
        <br>
        <div class="uk-inline uk-width-1-1">     
            <input class="uk-input" type="text" placeholder="Username" name="usr">
        </div>
        <div class="uk-inline uk-width-1-1">
            <input class="uk-input" type="password" placeholder="Password" name="psw">
        </div>
        
        <button class="uk-width-1-1 uk-button uk-button-primary uk-button-large uk-margin-small-top">Esegui il login</button>
    </form>
    
    
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
    </div>
    </div>
    <?php
    } else {
    ?>
    <h2 class="uk-heading-divider">Piattaforma per la gestione degli esami universitari</h2>
    <?php
        include('../navigation.php');
    ?>
            <h3 class="uk-card-title">Informazioni sui corsi di laurea</h3>
<!--INIZIO-------------------------------------------------------------------------------------------------->
<?php
if ((isset($_POST)) && (!empty($_POST['cdl_choice1']))) {
    $cdl_choice1 = $_POST['cdl_choice1'];
} else {
    $cdl_choice1 = null;
}

if ((isset($_POST)) && (!empty($_POST['cdl_choice2']))) {
    $cdl_choice2 = $_POST['cdl_choice2'];
} else {
    $cdl_choice2 = null;
}

if ((isset($_POST)) && (array_key_exists('back', $_POST))) {
    $back = true;
} else {
    $back = null;
}

if ((isset($_POST)) && array_key_exists('next', $_POST)) {
    $next = true;
} else {
    $next = null;
}
?>
<div class="uk-width-1-1">
            <div class="uk-card uk-card-default uk-card-body uk-padding-small uk-text-left">
<form action="<?php print($_SERVER['PHP_SELF']); ?>?mod=cdl" method="POST">
<fieldset class="uk-fieldset">
    <?php if (!$next) { ?>
    <legend class="uk-legend uk-text-default">Seleziona il corso di laurea di interesse</legend>

    <div class="uk-margin">
        <div class="uk-form-controls">
            
                <?php
                $cdl_keys = null;

                if ($role == 'Segreteria') {
                    $cdl_keys = get_all_cdl_entries();
                } else {
                    $cdl_keys = get_cdl_entries(false);
                }

                if (count($cdl_keys) != 0) {
                ?>
                <select class="uk-select" name="cdl_choice1">
                <?php
                foreach ($cdl_keys as $k => $v) {
                    $selected = '';
                    if ((!is_null($cdl_choice1)) && ($cdl_choice1 == $k)) {
                        $selected = 'selected="selected"';
                    }

                ?>
                <option value="<?php print($k); ?>" <?php print($selected); ?>><?php print($v['nome'] . ' ' . $v['tipo'] . ', ' . $v['sede']); ?></option>
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
    <button type="submit" class="uk-button uk-button-primary uk-align-right" name="next" <?php if (count($cdl_keys) == 0) {echo 'disabled';} ?>>Avanti</button>
    </form>
    <?php } else { ?>

    <legend class="uk-legend uk-text-default">Seleziona l'anno accademico dell'offerta</legend>
    
    <div class="uk-margin">
        <div class="uk-form-controls">
            <select class="uk-select" name="cdl_choice2">
                <?php
                $acc_years_keys = get_acc_years($cdl_choice1);
                foreach ($acc_years_keys as $k => $v) {
                    $selected = '';
                    if ((!is_null($cdl_choice2)) && ($cdl_choice2 == $k)) {
                        $selected = 'selected="selected"';
                    }

                ?>
                <option value="<?php print($k); ?>" <?php print($selected); ?>><?php print($v); ?></option>
                <?php
                }
                ?>
            </select>
        </div>
    </div>
    <input type="hidden" name="cdl_choice1" value="<?php echo $cdl_choice1; ?>">
    <button type="submit" class="uk-button uk-button-primary uk-align-right" name="next">Mostra informazioni</button>
    </form>
    <form>
    <button type="submit" class="uk-button uk-button-primary uk-align-left" name="back">Indietro</button>
    </form>
    <?php } ?>
</form>


<?php
    if(!is_null($cdl_choice2)) {
        $db = open_pg_connection();

        $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');
        
        $sql = "SELECT nome, tipo, sede FROM esami_universita.corso_di_laurea WHERE id = $1";

        $params = array(
            $cdl_choice1
        );

        $result = pg_prepare($db, "retrieve_info_cdl4", $sql);
        $result = pg_execute($db, "retrieve_info_cdl4", $params);

        $infocdl = null;

        $infocdl2 = null;

        $id_cdl = null;

        if($row = pg_fetch_assoc($result)){
            $infocdl = $row;
        }

        $sql = "SELECT id FROM esami_universita.corso_di_laurea WHERE nome = $1 AND tipo = $2 AND sede = $3 AND anno_accademico_offerta = $4";

        $params = array(
            $infocdl['nome'],
            $infocdl['tipo'],
            $infocdl['sede'],
            $cdl_choice2
        );

        $result = pg_prepare($db, "retrieve_info_cdl5", $sql);
        $result = pg_execute($db, "retrieve_info_cdl5", $params);

        if($row = pg_fetch_assoc($result)){
            $infocdl = $row;
            $id_cdl = $row['id'];
        }
        
        $sql = "SELECT id_insegnamento, nome, descrizione_testuale, docente, anno_previsto, semestre, ore_totali, lingua FROM esami_universita.info_cdl WHERE corso_di_laurea = $1 ORDER BY id_insegnamento";

        $params = array(
            $infocdl['id']
        );

        $result = pg_prepare($db, "retrieve_info_cdl6", $sql);
        $result = pg_execute($db, "retrieve_info_cdl6", $params);

        $infocdl = null;

        while ($row = pg_fetch_assoc($result)){
            $infocdl[$row['id_insegnamento']] = $row;
        }

        $sql = "SELECT id, nome, tipo, anno_accademico_offerta, facolta, descrizione, sede, accesso, attivato FROM esami_universita.corso_di_laurea WHERE id = $1";

        $params = array(
            $id_cdl
        );

        $result = pg_prepare($db, "retrieve_info_cdl7", $sql);
        $result = pg_execute($db, "retrieve_info_cdl7", $params);

        if ($row = pg_fetch_assoc($result)) {
            $infocdl2 = $row;
        }

        $sql = "SELECT i1.nome AS insegnamento_1, i2.nome AS insegnamento_2 FROM propedeuticita p INNER JOIN insegnamento i1 ON p.insegnamento_1 = i1.id INNER JOIN insegnamento i2 ON p.insegnamento_2 = i2.id WHERE i1.corso_di_laurea = $1 ORDER BY i2.nome, i1.nome";

        $params = array(
            $id_cdl
        );

        $result = pg_prepare($db, "retrieve_info_cdl8", $sql);
        $result = pg_execute($db, "retrieve_info_cdl8", $params);

        $props = array();

        while ($row = pg_fetch_assoc($result)) {
            $props[$row['insegnamento_1'] . $row['insegnamento_2']] = array($row['insegnamento_1'], $row['insegnamento_2']);
        }

?>
</div>
<br>
<div class="uk-card uk-card-default uk-card-body">

<div>
    <h3>Corso di laurea</h3>
    <p>
    <b>Codice: </b> <?php echo $infocdl2['id']; ?>
    <br>
    <b>Nome:</b> <?php echo $infocdl2['nome']; ?>
    <br>
    <b>Tipo:</b> <?php echo $infocdl2['tipo']; ?>
    <br>
    <b>Anno accademico offerta:</b> <?php echo $infocdl2['anno_accademico_offerta']; ?>
    <br>
    <b>Facoltà:</b> <?php echo $infocdl2['facolta']; ?>
    <br>
    <b>Sede:</b> <?php echo $infocdl2['sede']; ?>
    <br>
    <b>Accesso:</b> <?php echo $infocdl2['accesso']; ?>
    <br>
    <b>Descrizione:</b> <?php echo $infocdl2['descrizione']; ?>
    <?php if ($role == 'Segreteria') { ?>

        <br>
        <b>Stato:</b> <?php if ($infocdl2['attivato'] != 'f') {echo 'Attivato';} else {echo 'Non attivato';} ?>

    <?php } ?>
    </p>
</div>

<h3 class="uk-card-title">Insegnamenti del corso di laurea</h3>
<table class="uk-table uk-table-divider">
<thead>
    <tr>
        <th>Id insegnamento</th>
        <th>Nome</th>
        <th>Descrizione testuale</th>
        <th>Docente</th>
        <th>Anno previsto</th>
        <th>Semestre</th>
        <th>Ore totali</th>
        <th>Lingua</th>
    </tr>
</thead>
<tbody>
<?php

if (!is_null($infocdl)) {
foreach($infocdl as $id=>$values){
?>
    <tr>
        <td><?php echo $values['id_insegnamento']; ?></td>
        <td><?php echo $values['nome']; ?></td>
        <td><?php echo $values['descrizione_testuale']; ?></td>
        <td><?php echo $values['docente']; ?></td>
        <td><?php echo $values['anno_previsto']; ?></td>
        <td><?php echo $values['semestre']; ?></td>
        <td><?php echo $values['ore_totali']; ?></td>
        <td><?php echo $values['lingua']; ?></td>
    </tr>
<?php
}
}
?>
</tbody>
</table>

<table class="uk-table uk-table-divider">
    <h3>Propedeuticità</h3>
<?php if (!is_null($props)) { ?>
        <thead>
            <tr>
                <th>Insegnamento 1</th>
                <th>Insegnamento 2</th>
            </tr>
        </thead>
        <tbody>
<?php
            foreach ($props as $id=>$values) {
?>
                <tr>
                    <td><?php echo $values[0]; ?></td>
                    <td><?php echo $values[1]; ?></td>
                </tr>
<?php
            }
?>
        </tbody>
<?php } ?>
</table>
<legend>Attenzione: L'insegnamento 1 è propedeutico all'insegnamento 2.</legend>
</div>
<?php
    close_pg_connection($db);
}
}
?>
