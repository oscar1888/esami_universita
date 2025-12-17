<?php
    $error_msg = '';
    $success_msg = '';

    if (isset($_POST) && isset($_POST['iscr'])) {
        
        $iscr = $_POST['iscr'];

        $db = open_pg_connection();

        $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

        $sql = "DELETE FROM iscrizione WHERE matricola = $1 AND data = $2 AND insegnamento = $3";

        $params = array();
        $params[] = $iscr['matr'];
        $params[] = $iscr['data'];
        $params[] = $iscr['id'];

        $result = pg_prepare($db, "del_query3", $sql);
        $result = pg_execute($db, "del_query3", $params);

        if ($result) {
            $success_msg = "Iscrizione eliminata correttamente.";
        }
        else {
            $error_msg = pg_last_error($db);
        }

        close_pg_connection($db);

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
<h3>Elimina un iscrizione</h3>
<table class="uk-table uk-table-divider">
<thead>
    <tr>
        <th>Codice</th>
        <th>Nome insegnamento</th>
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

$result = pg_prepare($db, "tab_query1", $sql);
$result = pg_execute($db, "tab_query1", $params);

$matr = null;

if ($row = pg_fetch_assoc($result)) {
    $matr = $row['matricola'];
}

$sql = "SELECT i.insegnamento, c.nome, i.data, e.orario, e.tipo, e.luogo FROM iscrizione i INNER JOIN insegnamento c ON i.insegnamento = c.id INNER JOIN esame e ON e.insegnamento = i.insegnamento AND e.data = i.data WHERE matricola = $1 ORDER BY i.data";

$params = array($matr);

$result = pg_prepare($db, "tab_query2", $sql);
$result = pg_execute($db, "tab_query2", $params);

$iscrizioni = array();

while ($row = pg_fetch_assoc($result)) {
    $iscrizioni[] = array($row['nome'], $row['data'], $row['insegnamento'], $row['orario'], $row['tipo'], $row['luogo']);
}

foreach($iscrizioni as $id=>$values){
?>
    <tr>
        <td><?php echo $values[2]; ?></td>
        <td><?php echo $values[0]; ?></td>
        <td><?php echo $values[1]; ?></td>
        <td><?php echo $values[3]; ?></td>
        <td><?php echo $values[4]; ?></td>
        <td><?php echo $values[5]; ?></td>
        <td><form action="<?php echo $pagelink; ?>" method="POST">
            <input type="hidden" name="iscr[id]" value="<?php echo $values[2]; ?>">
            <input type="hidden" name="iscr[data]" value="<?php echo $values[1]; ?>">
            <input type="hidden" name="iscr[matr]" value="<?php echo $matr; ?>">
            <button class="uk-button uk-button-danger">Elimina</button>
        </form></td>
    </tr>
<?php
}
?>
</tbody>
</table>
<?php close_pg_connection($db); ?>