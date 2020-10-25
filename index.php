<?php

/**************************************************************************\
 * Programma scritto da Guido Volpi <tamerlo@gattonerong.it>              *
 * Quest software è rilasciato senza alcuna garanzia e sottoposto alla    *
 * licenza GPL v 2.0                                                      *
\**************************************************************************/
require __DIR__ . '/header.php';
require XOOPS_ROOT_PATH . '/header.php';

$myts = MyTextSanitizer::getInstance();

$commento = '';

# funzione per la ripulitura del testo ad evitare
# l'iniezione di codice e per l'aggiunta di emoticons
function PulisciMotivo($text)
{
    global $myts;

    $text = $myts->stripSlashesGPC($text);

    $text = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5);

    $text = $myts->smiley($text);

    return $text;
}

# raccolta lista degli utenti da processare
$table = $xoopsDB->prefix('users');
$myusers = [];
$result = $xoopsDB->query("select uid,uname from $table where level > 0 order by uname");
while (($userdata = $xoopsDB->fetchArray($result))) {
    $myusers[] = $userdata;
}
$xoopsDB->freeRecordSet($result);

# racoglie informazione sll'operazione da compiere
$op = $_GET['op'] ?? '';

# controlla se deve registrare un voto
if ('vote' == $op) {
    # controllo per l'utente registrato

    if ($xoopsUser) {
        # raccola dei dati

        $ctrl = 0;

        $chi = $_POST['personaggio'];

        # controlla l'esistenza del votato

        foreach ($myusers as $tmpu) {
            if ($tmpu['uid'] == $chi) {
                $come = $_POST['come'];

                if ($_POST['motivo']) {
                    $motivo = $xoopsDB->quoteString($_POST['motivo']);

                    $oggi = date('Y/m/d');

                    $table = $xoopsDB->prefix('buoniecattivi_voti');

                    $res = $xoopsDB->query("insert into $table(uid,come,motivo,data,ipvoto) values ($chi,$come,$motivo,'$oggi',\"" . $HTTP_SERVER_VARS['REMOTE_ADDR'] . '")');

                    if (1 == $res) {
                        $commento = 'Voto a ' . $tmpu['uname'] . " registrato $oggi";

                        $ctrl = 1;
                    }

                    break;
                }

                $commento = 'Manca un motivo valido';

                $ctrl = 1;
            }
        }

        if (0 == $ctrl) {
            $commento = 'Registrazione del voto fallita, ricontrolla i dati inseriti';
        }
    } else {
        $commento = 'Voto non accettato, per votare devi essere registrato';
    }
}

$buoni = [];
$cattivi = [];
$voti = [];
# controlla il tipo di pagina da visualizzare
if ('log' == $op) {
    $GLOBALS['xoopsOption']['template_main'] = 'bc_log.html';

    $utable = $xoopsDB->prefix('users');

    $vtable = $xoopsDB->prefix('buoniecattivi_voti');

    # raccoglie l'offset ed il numero totale dei voti

    if (isset($_GET['from'])) {
        $from = (int)$_GET['from'];
    } else {
        $from = 0;
    }

    $result = $xoopsDB->query("select data,uname,come,motivo from $vtable,$utable where $vtable.uid=$utable.uid order by $vtable.idvoto desc limit $from,30");

    while (($userdata = $xoopsDB->fetchArray($result))) {
        if (1 == $userdata['come']) {
            $userdata['come'] = _MI_GOOD;
        } else {
            if (2 == $userdata['come']) {
                $userdata['come'] = _MI_BAD;
            } else {
                continue;
            }
        }

        $userdata['motivo'] = PulisciMotivo($userdata['motivo']);

        $voti[] = $userdata;
    }

    $xoopsDB->freeRecordSet($result);

    # raccoglie il numero totale degli inserimenti

    $result = $xoopsDB->query("select count(*) from $vtable");

    $userdata = $xoopsDB->fetchRow($result);

    $totvoti = $userdata[0];

    if ($from > 0) {
        $offmin = $from > 29 ? $from - 30 : 0;

        $previous = '<a href="?op=log&from=' . $offmin . '" title="' . _MI_LOG_PREV . '">&lt;&lt;</a>';
    } else {
        $previous = '&lt;&lt;';
    }

    if ($totvoti >= $from + 30) {
        $next = '<a href="?op=log&from=' . ($from + 30) . '" title="' . _MI_LOG_NEXT . '">&gt;&gt;</a>';
    } else {
        $next = '&gt;&gt;';
    }
} else {
    # template di base

    $GLOBALS['xoopsOption']['template_main'] = 'bc_index.html';

    #raccolta dei buoni e cattivi

    $utable = $xoopsDB->prefix('users');

    $vtable = $xoopsDB->prefix('buoniecattivi_voti');

    # raccoglie gli ultimi 4

    $ultimi = [];

    $result = $xoopsDB->query('select uid,max(idvoto) as mid from xoops_buoniecattivi_voti group by uid order by mid desc limit 4');

    while (($userdata = $xoopsDB->fetchArray($result))) {
        $ultimi[] = $userdata;
    }

    foreach ($myusers as $tmpu) {
        # don't show votes older than...

        $cval = $xoopsModuleConfig['oldest'];

        $timelimit = date('Y/m/d', strtotime("-$cval days"));

        $result = $xoopsDB->query("select $utable.uid,uname,come,motivo from $vtable,$utable where $utable.uid=$vtable.uid and $utable.uid=" . $tmpu['uid'] . " and data > \"$timelimit\" order by idvoto desc limit 1");

        if ($result) {
            $userdata = $xoopsDB->fetchArray($result);

            # sostituzioni di simboli nel testo

            $userdata['motivo'] = PulisciMotivo($userdata['motivo']);

            # controlla se fa parte degli ultimi

            for ($i = 0, $iMax = count($ultimi); $i < $iMax; $i++) {
                if ($userdata['uid'] == $ultimi[$i]['uid']) {
                    $userdata['last'] = 1;
                }
            }

            if (1 == $userdata['come']) {
                $buoni[] = $userdata;
            } else {
                if (2 == $userdata['come']) {
                    $cattivi[] = $userdata;
                }
            }
        }

        $xoopsDB->freeRecordSet($result);
    }
}

# cosi passo variabili a smart che riprendo con <{$va}>
# $xoopsTpl->assign('uname', $uname);
if ('log' == $op) {
    $xoopsTpl->assign('estremi', ['from' => $from + 1, 'to' => $from + count($voti) ]);

    $xoopsTpl->assign('voti', $voti);

    $xoopsTpl->assign('previous', $previous);

    $xoopsTpl->assign('next', $next);
} else {
    $xoopsTpl->assign('users', $myusers);

    $xoopsTpl->assign('buoni', $buoni);

    $xoopsTpl->assign('cattivi', $cattivi);

    $xoopsTpl->assign('comment', $commento);

    $xoopsTpl->assign('vislog', _MI_VIS_LOG);
}

require_once XOOPS_ROOT_PATH . '/footer.php';
