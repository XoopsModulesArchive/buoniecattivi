<?php

/**************************************************************************\
 * Programma scritto da Guido Volpi <tamerlo@gattonerong.it>              *
 * Quest software è rilascaito senza alcuna categoria e sottoposto alla   *
 * licenza GPL v 2.0                                                      *
\**************************************************************************/
# introduzione
require dirname(__DIR__, 3) . '/include/cp_header.php';
if (file_exists('../language/' . $xoopsConfig['language'] . '/main.php')) {
    include '../language/' . $xoopsConfig['language'] . '/main.php';
} else {
    include '../language/italian/main.php';
}

include '../include/functions.php';
require_once XOOPS_ROOT_PATH . '/class/module.errorhandler.php';

# header
xoops_cp_header();

# raccolta informazioni generali
$utable = $xoopsDB->prefix('users');
$vtable = $xoopsDB->prefix('buoniecattivi_voti');

if (isset($_GET['del']) || isset($_POST['ok'])) {
    if (isset($_GET['ok']) || isset($_POST['ok'])) {
        $delok = $_GET['ok'] ?: $_POST['ok'];
    }

    if ($delok) {
        $del = $_GET['del'] ?: $_POST['del'];

        $query = "delete from $vtable where idvoto=$del";

        $res = $xoopsDB->query($query);

        echo '<div class="odd">';

        if ($res) {
            echo '<p>Rimozione voto riuscita</p>';

            echo "<p><a href=\"index.php\">Ritorna all'amministrazione del modulo</a>";
        } else {
            echo '<p>Rimozione voto non riuscita</p>';

            echo "<p> query: $query<br>Errore: " . $xoopsDB->error() . '</p>';
        }

        echo '</div>';
    } else {
        echo '<h4>Cancellazione commento</h4>';

        xoops_confirm(['del' => $_GET['del'], 'ok' => 1 ], 'index.php', 'Vuoi procedere alla rimozione del commento');
    }
} else {
    # amministrazione vera e propria
    echo "<h3>Amministrazione Buoni e Cattivi</h3>\n"; ?>
<div class="odd" width="100%">
<h4>Lista dei voti</h4>
<table border="1" width="100%">
<thead>
<tr><th>Data</th><th>Personaggio</th><th>Come</th><th>Motivo</th><th>&nbsp;</th></tr>
</thead>
<tbody>
<?php
    # raccoglie l'elenco dei voti
    $from = 0;

    if (isset($_GET['from'])) {
        $from = (int)$_GET['from'];
    }

    $result = $xoopsDB->query("select idvoto,data,uname,come,motivo from $vtable,$utable where $vtable.uid=$utable.uid order by $vtable.idvoto desc limit $from,15");

    while (($userdata = $xoopsDB->fetchArray($result))) {
        if (1 == $userdata['come']) {
            $userdata['come'] = _MI_GOOD;
        } else {
            if (2 == $userdata['come']) {
                $userdata['come'] = _MI_BAD;
            }
        }

        echo '<tr>';

        echo '<td>' . $userdata['data'] . '</td>';

        echo '<td>' . $userdata['uname'] . '</td>';

        echo '<td>' . $userdata['come'] . '</td>';

        echo '<td>' . stripslashes($userdata['motivo']) . '</td>';

        echo '<td><a href="?del=' . $userdata['idvoto'] . '">Cancella</a></td>';
    } ?>
</tbody>
</table>
<p align="center">
<?php
    $result = $xoopsDB->query("select count(*) from $vtable");

    $ndata = $xoopsDB->fetchRow($result);

    if ($from > 0) {
        $offmin = $from > 14 ? $from - 15 : 0;

        echo '<a href="?from=' . $offmin . '">&lt;&lt;</a>';
    }

    if ($ndata[0] > $from + 14) {
        echo '<a href="?from=' . ($from + 15) . '">&gt;&gt;</a>';
    }
}
?>
</p>
</div>
<?php

# fine pagina
xoops_cp_footer();
?>
