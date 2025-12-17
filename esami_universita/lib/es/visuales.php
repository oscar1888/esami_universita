<?php
    $db = open_pg_connection();

    $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

    if (isset($_POST) && isset($_POST['setstatoesame'])) {

        $sql = "UPDATE esame SET iscrizioni_aperte = $1 WHERE insegnamento = $2 AND data = $3";

        $params = array();
        $params[] = $_POST['setstatoesame'];
        $params[] = $_POST['esameins'];
        $params[] = $_POST['esamedata'];

        $result = pg_prepare($db, "change_examstatus" . $_POST['esameins'], $sql);
        $result = pg_execute($db, "change_examstatus" . $_POST['esameins'], $params);
    }

    $where = "";
    if(isset($_POST) && !empty($_POST['ins'])){
        $where = " AND e.insegnamento = $2";
    }
    
    $sql = "SELECT e.*, i.nome FROM esame e INNER JOIN insegnamento i ON e.insegnamento = i.id WHERE i.docente = $1";
    $sql .= $where;
    $sql .= 'ORDER BY e.data DESC';

    $result = pg_prepare($db, "info_sql1", $sql);
    if(!empty($where)){
        $result = pg_execute($db, "info_sql1", array($logged, $_POST['ins']));
    }else{
        $result = pg_execute($db, "info_sql1", array($logged));
    }

    $es = array();

    while($row = pg_fetch_row($result)){

        $es[$row[0] . $row[1]] = $row;
    
    }

    $pagelink = $_SERVER['PHP_SELF'] . '?mod=visual';

?>
<form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
    <h3 class="uk-margin-remove-bottom">Appelli d'esame</h3>
    <legend class="uk-legend uk-text-small">Filtra gli appelli d'esame creati per codice insegnamento</legend>

    <div class="uk-margin">     
        <div class="uk-form-controls">
            <input class="uk-input" type="text" placeholder="Inserisci il codice dell'insegnamento" name="ins">
        </div>
    </div>
    
    <button class="uk-button uk-button-default">Cerca</button>
</form>
<?php
$ins = "";
if(isset($_POST) && !empty($_POST['ins'])){
        $ins = " per l'insegnamento ". $_POST['ins'];
    }
?>
<h3 class="uk-card-title">Appelli d'esame creati <?php echo $ins; ?></h3>
<table class="uk-table uk-table-divider">
<thead>
    <tr>
        <th>Insegnamento</th>
        <th>Nome</th>
        <th>Data</th>
        <th>Orario</th>
        <th>Tipo</th>
        <th>Luogo</th>
        <th>Iscrizioni</th>
    </tr>
</thead>
<tbody>
<?php
foreach($es as $id=>$values){
?>
    <tr>
        <td><?php echo $values[0]; ?></td>
        <td><?php echo $values[6]; ?></td>
        <td><?php echo $values[1]; ?></td>
        <td><?php echo $values[2]; ?></td>
        <td><?php echo $values[3]; ?></td>
        <td><?php echo $values[4]; ?></td>
        <td>
            <form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
                <input type="hidden" name="esameins" value="<?php echo $values[0]; ?>">
                <input type="hidden" name="esamedata" value="<?php echo $values[1]; ?>">
                <?php if ($values[5] == "f") { ?>
                    <input type="hidden" name="setstatoesame" value="<?php echo "t"; ?>">
                    <button class="uk-button uk-button-primary">Apri</button>
                <?php } else { ?>
                    <input type="hidden" name="setstatoesame" value="<?php echo "f"; ?>">
                    <button class="uk-button uk-button-danger">Chiudi</button>
                <?php } ?>
            </form>
        </td>
    </tr>
<?php
}
?>
</tbody>
</table>
<?php
    close_pg_connection($db);
?>  