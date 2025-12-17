<br>
<?php
    if ($role == 'Studente') {
        if (!$is_in_storico) {
?>
            <div class="uk-card uk-card-secondary uk-card-body">
                <h3 class="uk-card-title">Informazioni utente</h3>
                <p>
                <?php
                    $out = array(
                        '<b>Username</b> ' . $logged,
                        '<b>Tipo account:</b> ' . $role 
                    );
                    $info = get_info($logged, $role, $is_in_storico);
                    foreach ($info as $key => $value) {
                        $out[] = '<b>' . str_replace('_', ' ', ucfirst($key)) . '</b>: ' . $value;
                    }

                    print (implode('<br>', $out));
                ?>
                </p>
            </div>
<?php 
        } else {
 ?>
            <div class="uk-card uk-card-secondary uk-card-body">
                <h3 class="uk-card-title">Informazioni utente</h3>
                <p>
                <?php
                    $out = array(
                        '<b>Username</b> ' . $logged
                    );
                    $info = get_info($logged, $role, $is_in_storico);
                    $out[] = '<b>Tipo account:</b> ' . 'Ex-' . $role . ' (<b>Motivo:</b> ' . $info['motivo'] . ', <b>Anno sospensione account:</b> ' . $info['anno_rimozione'] . ')';
                    unset($info['motivo']);
                    unset($info['anno_rimozione']);
                    foreach ($info as $key => $value) {
                        $out[] = '<b>' . str_replace('_', ' ', ucfirst($key)) . '</b>: ' . $value;
                    }

                    print (implode('<br>', $out));
                ?>
                </p>
            </div>
<?php 
        } 
    } elseif ($role == 'Docente') {
 ?>
        <div class="uk-card uk-card-secondary uk-card-body">
            <h3 class="uk-card-title">Informazioni utente</h3>
            <p>
            <?php
                $out = array(
                    '<b>Username</b> ' . $logged,
                    '<b>Tipo account:</b> ' . $role 
                );
                $info = get_info($logged, $role, $is_in_storico);
                foreach ($info as $key => $value) {
                    $out[] = '<b>' . str_replace('_', ' ', ucfirst($key)) . '</b>: ' . $value;
                }

                print (implode('<br>', $out));
            ?>
            </p>
        </div>
<?php 
    } else {
 ?>
        <div class="uk-card uk-card-secondary uk-card-body">
            <h3 class="uk-card-title">Informazioni utente</h3>
            <p>
            <?php
                $out = array(
                    '<b>Username</b> ' . $logged,
                    '<b>Tipo account:</b> ' . $role 
                );

                print (implode('<br>', $out));
            ?>
            </p>
        </div>
<?php 
    }
 ?>