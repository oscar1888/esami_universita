<?php include ('checklogin.php'); ?>
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
<!--INIZIO-->
        <br>
        <div class="uk-card uk-card-default uk-card-body uk-background-muted">
            <h3 class="uk-card-title">Cambio password</h3>
<!------------------------------------------------------------------------------->
<?php
    $error_msg = '';
    $success_msg = '';

    if(isset($_POST) && isset($_POST['pw'])) {
        $pw = $_POST['pw'];
        
        $oldpw = null;
        if(!empty($pw['oldpw']))
            $oldpw = $pw['oldpw'];
        else
            $error_msg = "Errore. E' necessario inserire la vecchia password.";
            
        $newpw = null;
        if(!empty($pw['newpw']))
            $newpw = $pw['newpw'];
        else
            $error_msg = "Errore. E' necessario inserire la nuova password.";
            
        $newpwrp = null;
        if(!empty($pw['newpwrp'])) {
            $newpwrp = $pw['newpwrp'];
            if ($newpwrp <> $newpw) {
                $error_msg = "Errore. La nuova password non coincide con la ripetizione.";
            }
        }
        else {
            $error_msg = "Errore. E' necessario ripetere la nuova password.";
        }

        if (empty($error_msg)) {
            $db = open_pg_connection();
            
            $sql = "SELECT username FROM esami_universita.profilo_utente WHERE username = $1 AND password = $2";

            $params = array(
                $logged,
                md5($oldpw)
            );

            $result = pg_prepare($db, "check_old_pw", $sql);
            $result = pg_execute($db, "check_old_pw", $params);

            $temp = null;

            if($row = pg_fetch_assoc($result)){
                $temp = $row['username'];
            }

            if (!isset($temp)) {
                $error_msg = 'La vecchia password non coincide.';
            } else {

                $result = pg_prepare($db, "set_path", 'SET SEARCH_PATH TO esami_universita');
                $result = pg_execute($db, "set_path", array());
    
                $sql = "UPDATE profilo_utente SET password = $1 WHERE username = $2";
                
                $params = Array();
                $params[] = md5($newpw);
                $params[] = $logged;

                $result = pg_prepare($db, "ins_query", $sql);
                $result = pg_execute($db, "ins_query", $params);
                
                if ($result)
                    $success_msg = "Password modificata correttamente.";
                else
                    $error_msg = pg_last_error($db);

            }
            
        }
    }

    $pagelink = $_SERVER['PHP_SELF'];
            
?>
<?php
if (!empty($success_msg)) {
?>
    <div class="uk-alert-success" uk-alert>
        <a class="uk-alert-close" uk-close></a>
        <p><?php echo $success_msg; ?></p>
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
<form class="uk-form-horizontal" action="<?php echo $pagelink; ?>" method="POST">
    <div class="uk-margin">
        <label class="uk-form-label">Vecchia password</label>
        <div class="uk-form-controls">
            <input class="uk-input" type="password" placeholder="Vecchia password" name="pw[oldpw]">
        </div>
    </div>
    <div class="uk-margin">     
        <label class="uk-form-label">Nuova Password</label>
        <div class="uk-form-controls">
            <input class="uk-input" type="password" placeholder="Nuova password" name="pw[newpw]">
        </div>
    </div>
    <div class="uk-margin">
        <label class="uk-form-label">Ripeti la nuova password</label>
        <div class="uk-form-controls">
            <input class="uk-input" type="password" placeholder="Nuova password" name="pw[newpwrp]">
        </div>
    </div>
    <button class="uk-button uk-button-danger uk-align-right uk-margin-remove-bottom">Modifica password</button>
</form>
<!------------------------------------------------------------------------------->
        </div>
    <!--FINE-->
    <?php
    }
    ?>
    </div>
    </body>
</html>