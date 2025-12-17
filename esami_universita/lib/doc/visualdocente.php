<legend class="uk-legend">Docenti</legend>
<table class="uk-table uk-table-divider">
<thead>
    <tr>
        <th>Username</th>
        <th>Nome</th>
        <th>Cognome</th>
        <th>Sesso</th>
        <th>Data di nascita</th>
        <th>Istruzione</th>
        <th>Sede ufficio</th>
    </tr>
</thead>
<tbody>
<?php

$db = open_pg_connection();

$result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

$sql = "SELECT * FROM docente ORDER BY username";

$result = pg_prepare($db, "info_query1", $sql);
$result = pg_execute($db, "info_query1", array());

$docs = array();

while ($row = pg_fetch_row($result)) {

    $docs[$row[0]] = $row;

}

close_pg_connection($db);

foreach($docs as $id=>$values){
?>
    <tr>
        <td><?php echo $values[0]; ?></td>
        <td><?php echo $values[1]; ?></td>
        <td><?php echo $values[2]; ?></td>
        <td><?php echo $values[3]; ?></td>
        <td><?php echo $values[4]; ?></td>
        <td><?php echo $values[5]; ?></td>
        <td><?php echo $values[6]; ?></td>
    </tr>
<?php
}
?>
</tbody>
</table>