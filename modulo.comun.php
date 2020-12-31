<?php
/* Cargamos los archivos de datos de cada lenguaje */
$datos = [];
foreach($lenguajes as $lenguaje) {
  $datos['traduccion'][$lenguaje] = json_decode(cache_url(
    "https://raw.githubusercontent.com/Masusder/DataTrackerDBD/master/Content/Localization/DeadByDaylight/{$lenguaje}/DeadByDaylight.json",
    '.DeadByDaylight.json.'. $lenguaje,
    $refrescar
  ))->{''};
  $datos['normalizado'][$lenguaje] = array_map('mb_strtolower', (array)$datos['traduccion'][$lenguaje]);
  $datos['contenido'][$lenguaje] = json_decode(cache_url(
    obtener_url($lenguaje, 'Module:Datatable/Perks'),
    'Module-Datatable-Perks.'. $lenguaje,
    $refrescar
  ))->query->pages[0]->revisions[0]->slots->main->content;
}
