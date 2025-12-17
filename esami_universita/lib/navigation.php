<?php 
    if ($role == 'Studente') {
        if (!$is_in_storico) {
?>
            <nav class="uk-navbar-container" uk-navbar>
                <div class="uk-navbar-left">

                    <ul class="uk-navbar-nav">
                        <li><a href="/esami_universita/index.php">Home</a></li>
                        <li>
                            <a href="#">Account</a>
                            <div class="uk-navbar-dropdown">
                                <ul class="uk-nav uk-navbar-dropdown-nav">
                                    <li><a href="/esami_universita/lib/changepassword.php">Cambio password</a></li>
                                </ul>
                            </div>
                        </li>
                        <li>
                            <a href="/esami_universita/lib/iscr/iscrizioneesami.php">Iscrizione esami</a>
                        </li>
                        <li>
                            <a href="/esami_universita/lib/carr/carriera.php">Carriera</a>
                        </li>
                        <li>
                            <a href="/esami_universita/lib/cdl/infocdl.php">Informazioni corsi di laurea</a>
                        </li>
                    </ul>
                </div>
            </nav>
<?php 
        } else {
?>
            <nav class="uk-navbar-container" uk-navbar>
                <div class="uk-navbar-left">

                    <ul class="uk-navbar-nav">
                        <li><a href="/esami_universita/index.php">Home</a></li>
                        <li>
                            <a href="#">Account</a>
                            <div class="uk-navbar-dropdown">
                                <ul class="uk-nav uk-navbar-dropdown-nav">
                                    <li><a href="/esami_universita/lib/changepassword.php">Cambio password</a></li>
                                </ul>
                            </div>
                        </li>
                        <li>
                            <a href="/esami_universita/lib/carr/carriera.php">Carriera</a>
                        </li>
                        <li>
                            <a href="/esami_universita/lib/cdl/infocdl.php">Informazioni corsi di laurea</a>
                        </li>
                    </ul>
                </div>
            </nav>
<?php
        }
?>
<?php
    } elseif ($role == 'Docente') {
?>
        <nav class="uk-navbar-container" uk-navbar>
            <div class="uk-navbar-left">

                <ul class="uk-navbar-nav">
                    <li><a href="/esami_universita/index.php">Home</a></li>
                    <li>
                        <a href="#">Account</a>
                        <div class="uk-navbar-dropdown">
                            <ul class="uk-nav uk-navbar-dropdown-nav">
                                <li><a href="/esami_universita/lib/changepassword.php">Cambio password</a></li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <a href="/esami_universita/lib/es/gestioneesami.php">Calendario degli esami</a>
                    </li>
                    <li>
                        <a href="/esami_universita/lib/verbalizzazione.php">Registrazione esiti</a>
                    </li>
                </ul>
            </div>
        </nav>
<?php
    } else {
?>
        <nav class="uk-navbar-container" uk-navbar>
            <div class="uk-navbar-left">
                <ul class="uk-navbar-nav">
                    <li><a href="/esami_universita/index.php">Home</a></li>
                    <li>
                        <a href="#">Account</a>
                        <div class="uk-navbar-dropdown">
                            <ul class="uk-nav uk-navbar-dropdown-nav">
                                <li><a href="/esami_universita/lib/changepassword.php">Cambio password</a></li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <a href="#">Gestione utenti</a>
                        <div class="uk-navbar-dropdown">
                            <ul class="uk-nav uk-navbar-dropdown-nav">
                                <li><a href="/esami_universita/lib/stud/studenti.php">Studenti</a></li>
                                <li><a href="/esami_universita/lib/doc/docenti.php">Docenti</a></li>
                                <li><a href="/esami_universita/lib/segr/segreteria.php">Segreteria</a></li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <a href="/esami_universita/lib/cdl/gestionecdl.php">Corsi di laurea</a>
                    </li>
                    <li>
                        <a href="/esami_universita/lib/ins/gestioneinsegnamenti.php">Insegnamenti</a>
                    </li>
                    <li>
                        <a href="/esami_universita/lib/prop/gestioneprop.php">Propedeuticità</a>
                    </li>
                    <li>
                        <a href="/esami_universita/lib/carr/carriera.php">Carriere</a>
                    </li>
                    <li>
                        <a href="/esami_universita/lib/cdl/infocdl.php">Informazioni corsi di laurea</a>
                    </li>
                </ul>
            </div>
        </nav>
<?php
    }
?>