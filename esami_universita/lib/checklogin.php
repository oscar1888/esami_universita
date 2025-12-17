<?php 
    ini_set ("display_errors", "On");
    ini_set("error_reporting", E_ERROR);
    include_once ('functions.php'); 

    $logged = null;
    $role = null;
    $is_in_storico = false;

    session_start();

    $error_msg = '';
    if(isset($_POST) && isset($_POST['usr']) && isset($_POST['psw'])){
        $arr = login($_POST['usr'], $_POST['psw']);
        $logged = $arr['username'];
        $role = $arr['ruolo'];
        $is_in_storico = $arr['storico'];
        if (is_null($logged)) {
            $error_msg = 'Credenziali errate. Ripetere il login';
        }
    }

    if(isset($_SESSION['user'])){
        $logged = $_SESSION['user'];
        $role = $_SESSION['role'];
        $is_in_storico = $_SESSION['storico'];
    }
    
    if(isset($logged)) {
        $_SESSION['user'] = $logged;
        $_SESSION['role'] = $role;
        $_SESSION['storico'] = $is_in_storico;
    }

    if(isset($_GET) && isset($_GET['log']) && $_GET['log'] == 'del'){
        unset($_SESSION['user']);
        $logged = null;
        unset($_SESSION['role']);
        $role = null;
        unset($_SESSION['is_in_storico']);
        $is_in_storico = null;
    }
?>