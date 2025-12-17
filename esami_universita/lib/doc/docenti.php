<?php include ('../checklogin.php'); ?>
<!DOCTYPE html>
<html>
    <head>
        <?php include_once ('../header.php'); ?>
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
        include('../navigation.php');
    ?>
<!--INIZIO-->
            <h3 class="uk-card-title">Gestione docenti</h3>
<!-------------------------------------------------------------------------------------------------------->
<div uk-grid>
    <div class="uk-width-1-3">
        <div class="uk-card uk-card-secondary uk-card-body uk-padding-small uk-text-left">
            <nav>
                <ul class="uk-nav uk-nav-default">
                <?php
                if (isset($_GET['mod']))
                    $active = $_GET['mod'];
                else
                    $active = 'crea';
            
                $menu = get_menu_entries_gest('doc');
                foreach ($menu as $key => $value) {
                    $active_option = '';
                    if ($key == $active)
                        $active_option = 'class="uk-active"';
                ?>
                    <li <?php echo $active_option; ?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?mod=<?php echo $key; ?>"><?php echo $value; ?></a></li>
                <?php
                }
                $active_option = '';
                if (isset($_GET['mod']) && $_GET['mod'] == 'visual') {
                        $active_option = 'class="uk-active"';
                }
                ?>
                <li <?php echo $active_option; ?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?mod=visual"> Visualizzazione</a></li>
                </ul>
            </nav>
        </div>
    </div>
    <div class="uk-width-2-3">
        <div class="uk-card uk-card-default uk-card-body uk-padding-small uk-text-left">
         <?php
            if (isset($_GET) && isset($_GET['mod'])) {
                switch ($_GET['mod']) {
                case 'visual':
                    include_once('visualdocente.php');   
                    break;
                case 'modifica':
                    include_once('modificadocente.php');   
                    break;
                case 'elimina':
                    include_once('eliminadocente.php');   
                    break;
                case 'crea':
                default:
                    include_once('creadocente.php'); 
                    break;
                }
            } else {
                include_once('creadocente.php');     
            }
        ?>       
        </div>
    </div>
</div>
<!-------------------------------------------------------------------------------------------------------->
        </div>
    <!--FINE-->
    <?php
    }
    ?>
    </div>
    </body>
</html>