<?php
    $error_msg = '';
    $success_msg = '';
    $info = null;
    $date = null;
    $dateiscritte = null;

    if (isset($_POST) && isset($_POST['iscr'])) {
        $info = $_POST['iscr'];

        if (isset($info['matr'])) {

            $db = open_pg_connection();

            $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

            $sql = "INSERT INTO iscrizione VALUES ($1, $2, $3)";

            $params = array();
            $params[] = $info['matr'];
            $params[] = $info['data'];
            $params[] = $info['id'];

            $result = pg_prepare($db, "iscr_query1", $sql);
            $result = pg_execute($db, "iscr_query1", $params);

            if ($result) {
                $success_msg = "Iscrizione effettuata correttamente.";
            }
            else {
                $error_msg = pg_last_error($db);
                if (strpos($error_msg, 'non si sono rispettate le propedeuticita')) {
                    $error_msg = "Non si può effettuare un'iscrizione ad un esame di un insegnamento per il quale non si sono rispettate le propedeuticità.";
                }
                if (strpos($error_msg, 'duplicate key')) {
                    $error_msg = "Sei già iscritto a questo esame.";
                }
            }

            close_pg_connection($db);

        } else {

            $date = array();

            $db = open_pg_connection();

            $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

            $sql = "SELECT matricola FROM studente_corrente WHERE username = $1";

            $params = array($logged);

            $result = pg_prepare($db, "tab_query4", $sql);
            $result = pg_execute($db, "tab_query4", $params);

            $matricola = null;

            if ($row = pg_fetch_assoc($result)) {
                $matricola = $row['matricola'];
            }

            $sql = "WITH esami_disponibili AS (
                    SELECT data, insegnamento AS id FROM esame WHERE insegnamento = $1 AND iscrizioni_aperte = TRUE EXCEPT SELECT data, id FROM carriera_completa WHERE matricola = $2
                    )
                    SELECT e2.data, e2.orario, e2.tipo, e2.luogo FROM esami_disponibili e1 INNER JOIN esame e2 ON e1.data = e2.data AND e1.id = e2.insegnamento ORDER BY e1.data";

            $params = array();
            $params[] = $info['id'];
            $params[] = $matricola;

            $result = pg_prepare($db, "info_query2", $sql);
            $result = pg_execute($db, "info_query2", $params);

            while ($row = pg_fetch_assoc($result)) {

                $date[$row['data']] = array($row['data'], $row['orario'], $row['tipo'], $row['luogo']);

            }

            $dateiscritte = array();

            $sql = "SELECT data FROM iscrizione WHERE matricola = $1 AND insegnamento = $2";

            $params = array();
            $params[] = $matricola;
            $params[] = $info['id'];

            $result = pg_prepare($db, "info_query4", $sql);
            $result = pg_execute($db, "info_query4", $params);

            while ($row = pg_fetch_assoc($result)) {

                $dateiscritte[$row['data']] = $row['data'];

            }

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
<?php if (isset($_POST) && isset($_POST['iscr']) && empty($success_msg) && !isset($_POST['iscr']['matr'])) { ?>
            <legend class="uk-legend">Iscriviti ad un esame</legend>
            <legend class="uk-text-small">Ricorda che puoi iscriverti solamente agli esami di insegnamenti per i quali hai rispettato le propedeuticità.</legend>
<!------------------------------------------------------------------------------------------------------>
            <?php if (count($date) == 0) {
                echo "<br>";
                echo "Non ci sono appelli disponibili in questo momento per questo insegnamento.";
            } else { ?>
            <h3><?php echo $info['nome']; ?></h3>
            <table class="uk-table uk-table-divider">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Orario</th>
                    <th>Tipo</th>
                    <th>Luogo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php

            $db = open_pg_connection();

            $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

            $sql = "SELECT matricola FROM studente_corrente WHERE username = $1";

            $params = array($logged);

            $result = pg_prepare($db, "tab_query2", $sql);
            $result = pg_execute($db, "tab_query2", $params);

            $matr = null;

            if ($row = pg_fetch_assoc($result)) {
                $matr = $row['matricola'];
            }

            foreach($date as $id=>$values){
            ?>
                <tr>
                    <td><?php echo $values[0]; ?></td>
                    <td><?php echo $values[1]; ?></td>
                    <td><?php echo $values[2]; ?></td>
                    <td><?php echo $values[3]; ?></td>
                    <td><form action="<?php echo $pagelink; ?>" method="POST">
                        <input type="hidden" name="iscr[id]" value="<?php echo $info['id']; ?>">
                        <input type="hidden" name="iscr[data]" value="<?php echo $values[0]; ?>">
                        <input type="hidden" name="iscr[matr]" value="<?php echo $matr; ?>">
                        <button class="uk-button uk-button-default" <?php if ($dateiscritte[$id]) {echo 'disabled';} ?>>Iscriviti</button>
                    </form></td>
                </tr>
            <?php
            }
            ?>
            </tbody>
            </table>
            <?php } ?>
<!------------------------------------------------------------------------------------------------------>
        <form action="<?php echo $pagelink; ?>" method="POST">
            <br>
            <button type="submit" class="uk-button uk-button-primary uk-align-left">Indietro</button>
        </form>
<?php } else { ?>
        <form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
            <legend class="uk-legend">Iscriviti ad un esame</legend>
            <legend class="uk-text-small">Ricorda che puoi iscriverti solamente agli esami di insegnamenti per i quali hai rispettato le propedeuticità.</legend>
            <table class="uk-table uk-table-divider">
            <thead>
                <tr>
                    <th>Codice</th>
                    <th>Nome insegnamento</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php

            $db = open_pg_connection();

            $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

            $sql = "SELECT matricola, corso_di_laurea FROM studente_corrente WHERE username = $1";

            $params = array($logged);

            $result = pg_prepare($db, "tab_query1", $sql);
            $result = pg_execute($db, "tab_query1", $params);

            $cdlstud = null;
            $matricola = null;

            if ($row = pg_fetch_assoc($result)) {
                $cdlstud = $row['corso_di_laurea'];
                $matricola = $row['matricola'];
            }

            $sql = "SELECT id, nome FROM insegnamento WHERE corso_di_laurea = $1";

            $params = array($cdlstud);

            $result = pg_prepare($db, "tab_query2", $sql);
            $result = pg_execute($db, "tab_query2", $params);

            $insstud = array();

            while ($row = pg_fetch_assoc($result)) {
                $insstud[$row['id']] = $row['nome'];
            }

            //Query per trovare gli insegnamenti per i quali non si sono rispettate le propedeuticita
            $sql = "WITH requisiti AS (
                    SELECT p1.insegnamento_1
                    FROM propedeuticita p1 INNER JOIN insegnamento i ON p1.insegnamento_1 = i.id
                    WHERE i.corso_di_laurea = $1
                    ), ins_superati AS (
                    SELECT id
                    FROM carriera_valida
                    WHERE matricola = $2
                    ), req_non_compiuti AS (
                    SELECT * 
                    FROM requisiti
                    EXCEPT
                    SELECT *
                    FROM ins_superati
                    )
                    SELECT DISTINCT p.insegnamento_2 AS ins
                    FROM req_non_compiuti AS r INNER JOIN propedeuticita p ON r.insegnamento_1 = p.insegnamento_1";

            $params = array();
            $params[] = $cdlstud;
            $params[] = $matricola;

            $result = pg_prepare($db, "tab_query3", $sql);
            $result = pg_execute($db, "tab_query3", $params);

            $insnonacc = array();

            while ($row = pg_fetch_assoc($result)) {
                $insnonacc[$row['ins']] = $row['ins'];
            }

            close_pg_connection($db);

            foreach($insstud as $id=>$value){
                $link = $_SERVER['PHP_SELF'] . '?mod=crea&id=' . $value;
            ?>
                <tr>
                    <td><?php echo $id; ?></td>
                    <td><?php echo $value; ?></td>
                    <td><form action="<?php echo $link; ?>" method="POST">
                        <input type="hidden" name="iscr[id]" value="<?php echo $id; ?>">
                        <input type="hidden" name="iscr[nome]" value="<?php echo $value; ?>">
                        <button class="uk-button uk-button-primary uk-button-small" <?php if ($insnonacc[$id]) {echo 'disabled';} ?>>Iscrizione</button>
                    </form></td>
                </tr>
            <?php
            }
            ?>
            </tbody>
            </table>
        </form>
<?php } ?>