<?php
    $error_msg = '';
    $success_msg = '';
    $generated_data = null;

    if(isset($_POST) && isset($_POST['segr'])) {

        $generated_data = gen_data('segr', '', '');

        $db = open_pg_connection();

        $result = pg_query($db, 'SET SEARCH_PATH TO esami_universita');

        $sql = "INSERT INTO profilo_utente VALUES ($1, md5($2), $3)";

        $params = array();
        $params[] = $generated_data['username'];
        $params[] = $generated_data['password'];
        $params[] = 'Segreteria';

        $result = pg_prepare($db, "ins_query1", $sql);
        $result = pg_execute($db, "ins_query1", $params);

        if ($result)
            $success_msg = "Utente segreteria creato correttamente.";
        else
            $error_msg = pg_last_error($db);
    }

    $pagelink = $_SERVER['PHP_SELF'] . '?mod=crea';
            
?>
<?php
if (!empty($success_msg)) {
?>
<div class="uk-alert-success" uk-alert>
    <a class="uk-alert-close" uk-close></a>
    <p><b><?php echo $success_msg; ?></b></p>
    <h5 class="uk-margin-remove-top"><b>Informazioni sul nuovo utente segreteria:</b>
    <ul class="uk-margin-remove-top uk-margin-remove-bottom uk-text-default">
        <li><u>Username</u>: <?php echo $generated_data['username']; ?></li>
        <li><u>Password</u>: <?php echo $generated_data['password']; ?></li>
    </ul>
    </h5>
</div>
<?php
}
?>
<form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
    <legend class="uk-legend">Crea un nuovo utente segreteria</legend>
    <br>
    <input type="hidden" name="segr">
    <button class="uk-button uk-button-primary uk-align-right uk-margin-remove-bottom">Crea</button>
</form>