<?php
    
    $db = open_pg_connection();
    
    $info = array();

    if ($role == 'Studente') {

        if (!$is_in_storico) {

            $sql = "SELECT matricola FROM esami_universita.studente_corrente WHERE username = $1";

            $params = array(
                $logged
            );

            $result = pg_prepare($db, "retrieve_matr_sc", $sql);
            $result = pg_execute($db, "retrieve_matr_sc", $params);

        } else {

            $sql = "SELECT matricola FROM esami_universita.storico_studenti WHERE username = $1";

            $params = array(
                $logged
            );

            $result = pg_prepare($db, "retrieve_matr_ss", $sql);
            $result = pg_execute($db, "retrieve_matr_ss", $params);

        }

        if($row = pg_fetch_assoc($result)){
            $info = $row;
        }
        
        $sql = "SELECT id, nome, data, voto FROM esami_universita.carriera_completa WHERE matricola = $1 ORDER BY data";

        $result = pg_prepare($db, "retrieve_matr_ex", $sql);
        $result = pg_execute($db, "retrieve_matr_ex", array($info['matricola']));

        $info = array();

        while ($row = pg_fetch_assoc($result)) {

            $id = $row['id'];
            $nome = $row['nome'];
            $data = $row['data'];
            $voto = $row['voto'];

            $info[$id . $data] = array($row['id'], $row['nome'], $row['data'], $row['voto']);
        }



?>
<h3 class="uk-card-title">Carriera completa</h3>
<p>Contiene tutti gli esami sostenuti</p>
<table class="uk-table uk-table-divider">
<thead>
	<tr>
		<th>Id insegnamento</th>
		<th>Nome insegnamento</th>
		<th>Data esame</th>
        <th>Voto</th>
	</tr>
</thead>
<tbody>
<?php

foreach($info as $key=>$value){
?>
    <tr>
        <td><?php echo $value[0]; ?></td>
        <td><?php echo $value[1]; ?></td>
        <td><?php echo $value[2]; ?></td>
        <td><?php echo $value[3]; ?></td>
    </tr>
<?php
}
?>
</tbody>
</table>

<?php } else { ?>

<?php

    $where = "";
    if(isset($_POST) && !empty($_POST['matricola'])){
        $where = " WHERE matricola = $1";
    }
    
    $sql = "SELECT * FROM esami_universita.carriera_completa";
    $sql .= $where;
    $sql .= ' ORDER BY matricola, data';

    $result = pg_prepare($db, "query_carrieracompleta", $sql);
    if(!empty($where)){
        $result = pg_execute($db, "query_carrieracompleta", array($_POST['matricola']));
    }else{
        $result = pg_execute($db, "query_carrieracompleta", array());
    }

    $carriere = array();

    while($row = pg_fetch_assoc($result)){

        $matricola = $row['matricola'];
        $id = $row['id'];
        $nome = $row['nome'];
        $data = $row['data'];
        $voto = $row['voto'];

        $carriere[$matricola . $id . $nome . $data . $voto] = array($matricola, $id, $nome, $data, $voto);
    }

    $pagelink = $_SERVER['PHP_SELF'] . '?mod=completa';


?>
<form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
    <legend class="uk-legend">Filtra le carriere per matricola</legend>

    <div class="uk-margin">     
        <div class="uk-form-controls">
            <input class="uk-input" type="text" placeholder="Inserisci la matricola" name="matricola">
        </div>
    </div>
    
    <button class="uk-button uk-button-default">Cerca</button>
</form>
<?php
$matricola_value = "";
if(isset($_POST) && !empty($_POST['matricola'])){
        $matricola_value = " per la matricola ". $_POST['matricola'];
    ?>
<h3 class="uk-card-title">Carriera in archivio <?php echo $matricola_value; ?></h3>
<?php
    } else {
?>
<h3 class="uk-card-title">Carriere in archivio</h3>
<?php } 
?>
<table class="uk-table uk-table-divider">
<thead>
    <tr>
        <th>Matricola</th>
        <th>Id insegnamento</th>
        <th>Nome Insegnamento</th>
        <th>Data esame</th>
        <th>Voto</th>
    </tr>
</thead>
<tbody>
<?php
foreach($carriere as $id=>$values){
?>
    <tr>
        <td><?php echo $values[0]; ?></td>
        <td><?php echo $values[1]; ?></td>
        <td><?php echo $values[2]; ?></td>
        <td><?php echo $values[3]; ?></td>
        <td><?php echo $values[4]; ?></td>
    </tr>
<?php
}
?>
</tbody>
</table>

<?php
    }
    close_pg_connection($db);
?>