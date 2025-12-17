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
        
        $sql = "SELECT id, nome, data, voto FROM esami_universita.carriera_valida WHERE matricola = $1 ORDER BY data";

        $result = pg_prepare($db, "retrieve_matr_ex", $sql);
        $result = pg_execute($db, "retrieve_matr_ex", array($info['matricola']));
    
        $info = null;

        $media = 0.0;

        while($row = pg_fetch_assoc($result)){

            $id = $row['id'];
            $nome = $row['nome'];
            $data = $row['data'];
            $voto = $row['voto'];

            $info[$id . $data] = array($row['id'], $row['nome'], $row['data'], $row['voto']);

            $media += $row['voto'];

        }

        $esami = count($info);

        if ($esami != 0) {
            $media /= $esami;
        }


?>
<h3 class="uk-card-title">Carriera valida</h3>
<p>Contiene i voti e le date più recenti di tutti gli esami superati</p>
<p class="uk-align-right"><b>Media dei voti:</b> <?php echo $media; ?></p>
<p class="uk-align-right"><b>Esami registrati:</b> <?php echo $esami; ?></p>
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
    
    $sql = "SELECT * FROM esami_universita.carriera_valida";
    $sql .= $where;
    $sql .= ' ORDER BY matricola, data';

    $result = pg_prepare($db, "query_carrieravalida", $sql);
    if(!empty($where)){
        $result = pg_execute($db, "query_carrieravalida", array($_POST['matricola']));
    }else{
        $result = pg_execute($db, "query_carrieravalida", array());
    }

    $carriere = array();

    $media = 0.0;

    while($row = pg_fetch_assoc($result)){

        $matricola = $row['matricola'];
        $id = $row['id'];
        $nome = $row['nome'];
        $data = $row['data'];
        $voto = $row['voto'];

        $carriere[] = array($matricola, $id, $nome, $data, $voto);

        if(isset($_POST) && !empty($_POST['matricola'])){
            $media += $voto;
        }
    }

    $esami = count($carriere);

    if(isset($_POST) && !empty($_POST['matricola']) && $esami != 0){
        $media /= $esami;
    }

    $pagelink = $_SERVER['PHP_SELF'] . '?mod=valida';
    

?>
<form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
    <legend class="uk-legend">Filtra le carriere valide per matricola</legend>

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
<h3 class="uk-card-title">Carriera valida in archivio <?php echo $matricola_value; ?></h3>
<?php if ($esami != 0) {?>
<p class="uk-align-right"><b>Media dei voti:</b> <?php echo $media; ?></p>
<p class="uk-align-right"><b>Esami registrati:</b> <?php echo $esami; ?></p>
<?php } ?>
<?php
    } else {
?>
<h3 class="uk-card-title">Carriere valide in archivio</h3>
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