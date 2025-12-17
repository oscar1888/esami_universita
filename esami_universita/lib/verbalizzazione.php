<?php include ('checklogin.php');?>
<!DOCTYPE html>
<html>
    <head>
        <?php include_once ('header.php'); ?>
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
        include('navigation.php');
    ?>
<br>
<!--INIZIO-------------------------------------------------------------------------------------------------->
<div class="uk-card uk-card-default uk-card-body">
<?php
    $error_msg = '';
    $success_msg = '';

    if(isset($_POST) && isset($_POST['es'])) {
        $es = $_POST['es'];

        if (isset($es['data']) && isset($es['matr'])) {

            $matr = null;
            if(!empty($es['matr'])) {
                if (is_numeric($es['matr'])) {
                    $matr = $es['matr'];
                } else {
                    $error_msg = 'Errore. La matricola deve essere un valore numerico.';
                }
            } else {
                $error_msg = "Errore. E' necessario inserire la matricola.";
            }
        
            $voto = null;
            if(!empty($es['voto'])){
                $voto = $es['voto'];
            } else {
                if (!isset($es['ass'])) {
                    $error_msg = "Errore. E' necessario inserire il voto.";
                }
            }

            if (empty($error_msg)) {

                $db = open_pg_connection();

                $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

                $sql = "SELECT * FROM verbalizzazione($1, $2, $3, $4, $5)";

                $params = array();
                $params[] = $es['matr'];
                $params[] = $es['data'];
                $params[] = $es['ins'];
                if (isset($es['ass'])) {
                    $params[] = 0;   
                    $params[] = 'true'; 
                } else {
                    $params[] = $es['voto'];
                    $params[] = 'false';
                }
                

                $result = pg_prepare($db, "upd_query1", $sql);
                $result = pg_execute($db, "upd_query1", $params);
                
                if ($result) {
                    if (isset($es['ass'])) {
                        $success_msg = "Esame concluso per la matricola " . $es['matr'] . ".";
                    } else {
                        $success_msg = "Voto verbalizzato correttamente.";
                    }
                }
                else {
                    $error_msg = pg_last_error($db);
                    if (strpos($error_msg, 'Non esiste alcun studente con la matricola inserita')) {
                        $error_msg = 'Non esiste alcun studente con la matricola inserita iscritto correntemente a questo esame.';
                    }
                }
            }
        }

    }

    $pagelink = $_SERVER['PHP_SELF'] . '?mod=verb';
            
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
    <h3>Registrazione esiti</h3>
    <div class="uk-margin">     
        <label class="uk-form-label" for="es-ins">Data</label>
        <div class="uk-form-controls">
            <?php 
                $db = open_pg_connection();

                $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

                $sql = "SELECT data FROM esame WHERE insegnamento = $1 ORDER BY data DESC";

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
    <h3>Registrazione esiti</h3>
    <legend class="uk-text-small">Se lo studente non si è presentato all'appello d'esame puoi lasciare vuoto il campo voto.</legend>
    <legend class="uk-text-small">Inoltre, ricorda che, la registrazione dell'esito comporta l'eliminazione dell'iscrizione dello studente all'esame in questione.</legend>
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
        <label class="uk-form-label" for="es-matr">Matricola</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="es-matr" type="text" placeholder="Inserisci la matricola" name="es[matr]">
        </div>
    </div>
    <div class="uk-margin">     
        <label class="uk-form-label" for="es-ass">Assente</label>
        <div class="uk-form-controls">
            <input class="uk-checkbox" id="es-ass" type="checkbox" name="es[ass]">
        </div>
    </div>
    <div class="uk-margin">     
        <label class="uk-form-label" for="es-voto">Voto</label>
        <div class="uk-form-controls">
            <input class="uk-input" id="es-voto" type="number" min="0" max="30" step="1" placeholder="Inserisci il voto" name="es[voto]">
        </div>
    </div>
    <input type="hidden" name="es[ins]" value="<?php echo $es['ins']; ?>">
    <input type="hidden" name="es[data]" value="<?php echo $_POST['es']['data']; ?>">
    <button class="uk-button uk-button-secondary uk-align-right uk-margin-remove-bottom">Fine</button>
</form>

<form action="<?php echo $pagelink; ?>" method="POST">
    <input type="hidden" name="es[ins]" value="<?php echo $es['ins']; ?>">
    <button type="submit" class="uk-button uk-button-primary uk-align-left">Indietro</button>
</form>

<?php } else { ?>
<form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
    <h3>Registrazione esiti</h3>
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
<?php } 
}
?>
</div>