<legend class="uk-legend">Iscrizioni confermate</legend>
<table class="uk-table uk-table-divider">
<thead>
    <tr>
        <th>Codice insegnamento</th>
        <th>Nome</th>
        <th>Data</th>
        <th>Orario</th>
        <th>Tipo</th>
        <th>Luogo</th>
    </tr>
</thead>
<tbody>
<?php

$db = open_pg_connection();

$result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

$sql = "SELECT matricola FROM studente_corrente WHERE username = $1";

$params = array();
$params[] = $logged;

$result = pg_prepare($db, "info_query1", $sql);
$result = pg_execute($db, "info_query1", $params);

$matr = null;

if ($row = pg_fetch_assoc($result)) {
    $matr = $row['matricola'];
}

$sql = "SELECT i1.insegnamento, i1.data, i2.nome, e.orario, e.tipo, e.luogo FROM iscrizione i1 INNER JOIN insegnamento i2 ON i1.insegnamento = i2.id INNER JOIN esame e ON e.insegnamento = i1.insegnamento AND e.data = i1.data WHERE matricola = $1 ORDER BY i1.data";

$params = array();
$params[] = $matr;

$result = pg_prepare($db, "info_query2", $sql);
$result = pg_execute($db, "info_query2", $params);

$iscr = array();

while ($row = pg_fetch_row($result)) {

    $iscr[$row[0] . $row[1]] = $row;

}

close_pg_connection($db);

foreach($iscr as $id=>$values){
?>
    <tr>
        <td><?php echo $values[0]; ?></td>
        <td><?php echo $values[2]; ?></td>
        <td><?php echo $values[1]; ?></td>
        <td><?php echo $values[3]; ?></td>
        <td><?php echo $values[4]; ?></td>
        <td><?php echo $values[5]; ?></td>
    </tr>
<?php
}
?>
</tbody>
</table>