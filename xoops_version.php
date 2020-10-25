<?php

//
// Guido Volpi <tamerlo@gattonerong.it>, per gattonerong.it
// Rilasciato stto Licenza GPL V 2.0
//

$modversion['name'] = _MI_BC_NAME;
$modversion['version'] = 0.3;
$modversion['description'] = _MI_BC_DESC;
$modversion['credits'] = 'Guido Volpi <tamerlo@gattonerong.it>';
$modversion['author'] = 'Guido Volpi';
$modversion['help'] = '';
$modversion['license'] = 'GPL see LICENSE';
$modversion['official'] = 0;
$modversion['image'] = 'images/buoniecattivi-logo.gif';
$modversion['dirname'] = 'buoniecattivi';

# sql, li ho fatti a mano ma non li caga, why?
$modversion['sqlfile']['mysql'] = 'sql/mysql.sql';
$modversion['tables'][0] = 'buoniecattivi_voti';

//Admin things
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = 'admin/index.php';
$modversion['adminmenu'] = 'admin/menu.php';

// Menu
$modversion['hasMain'] = 1;
$modversion['sub'][1]['name'] = 'Log';
$modversion['sub'][1]['url'] = '?op=log';

// Templates
$modversion['templates'][0]['file'] = 'bc_index.html';
$modversion['templates'][0]['description'] = 'Lavagna';
$modversion['templates'][1]['file'] = 'bc_log.html';
$modversion['templates'][1]['description'] = 'Log dei voti';

// variabile configurabili
$modversion['config'][1]['name'] = 'oldest';
$modversion['config'][1]['title'] = '_MI_BC_OLDEST';
$modversion['config'][1]['description'] = '_MI_BC_OLDESTDSC';
$modversion['config'][1]['formtype'] = 'textbox';
$modversion['config'][1]['valuetype'] = 'int';
$modversion['config'][1]['default'] = 30;
