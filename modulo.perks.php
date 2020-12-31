<?php
/* ¿Requiere php-luasandbox? */
require_once 'funciones.php';

$refrescar = isset($_SERVER['argv'][1]) && ($_SERVER['argv'][1] === 'refrescar');

$lenguajes = [
  'en',
  'es',
];

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

/* Recortamos el contenido original (en inglés) para ser traducido */
if (preg_match('/^(.*)(perks = {)(.*)(\n}\n)(.*)$/s', $datos['contenido']['en'], $trozos) === false) {
  echo "no hemos encontrado el patrón para realizar la traducción", PHP_EOL;
  exit(1);
}

/* Almacenaremos el resultado parcial para luego mostrarlo o recuperarlo */
$resultado = '';
if (preg_match_all('/^((--)?\t)({.*name ?= ?)("[^"]+")( *)(, ?baseLevel.*)$/mi', $trozos[3], $salida)) {
  foreach($salida[4] as $clave => $valor) {
    $nombre = json_decode($valor);
    $nombre = str_replace(
      [
        'Barbecue & Chilli',
        'Hex: Blood Favour',
        "Repressed Alliance",
      ],
      [
        'Barbecue & Chili',
        'Hex: Blood Favor',
        "Repressed Alliance ",
      ],
      $nombre
    );
    /* Buscamos el término para traducirlo */
    $encontrado = array_search(mb_strtolower($nombre) , $datos['normalizado']['en']);
    $resultado .= implode(
      [
        ($encontrado !== false) ? '--' : '',
        $salida[1][$clave],
        $salida[2][$clave],
        $salida[3][$clave],
        $salida[4][$clave],
        $salida[5][$clave],
        $salida[6][$clave],
        PHP_EOL,
      ],
      ''
    );
    /* Si hemos encontrado la traducción montamos la cadena con el campo traducido */
    if ($encontrado !== false) {
      /* Primer carácter de cada palabra en mayúsculas */
      $traduccion = ucwords($datos['traduccion']['es']->{$encontrado});
      /* Exceptuando determinantes y otras excepciones */
      $traduccion = str_replace(
        [
          ' A ',
          ' Y ',
          ' De ',
          ' La ',
          ' Lo ',
          ' El ',
          ' Las ',
          ' Los ',
          ' Que ',
          ' Para ',
        ],
        [
          ' a ',
          ' y ',
          ' de ',
          ' la ',
          ' lo ',
          ' el ',
          ' las ',
          ' los ',
          ' que ',
          ' para ',
        ],
        $traduccion
      );
      /* Convertimos la cadena en cadena LUA (compatible con JSON) */
      $traduccion = json_encode($traduccion, JSON_UNESCAPED_UNICODE);
      /* Calculamos el relleno de espacios en blanco necesario */
      $relleno = str_repeat(" ", max(0, 31 - mb_strlen($traduccion)));
      $resultado .= implode(
        [
          $salida[1][$clave],
          $salida[2][$clave],
          $salida[3][$clave],
          $traduccion,
          $relleno,
          $salida[6][$clave],
          PHP_EOL,
        ],
        ''
      );
      }
  }
}

/* Mostramos el resultado */
echo $resultado;
