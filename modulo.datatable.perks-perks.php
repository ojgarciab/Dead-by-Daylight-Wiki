<?php
/* ¿Requiere php-luasandbox? */
require_once 'funciones.php';

$refrescar = isset($_SERVER['argv'][1]) && ($_SERVER['argv'][1] === 'refrescar');

$lenguajes = [
  'en',
  'es',
];

require_once 'modulo.comun.php';
$datos = cargar_datos('Module:Datatable/Perks', $lenguajes, $refrescar);
/* Agregamos datos que faltan pero se mantienen en la Wiki */
$datos['normalizado']['en']['lastStanding'] = 'last standing';
$datos['traduccion']['es']->lastStanding = 'El último en pie';

/* Recortamos el contenido original (en inglés) para ser traducido */
if (
    0 === preg_match('/^(.*)(perks = {)(.*)(\n}\n)(.*)$/s', $datos['contenido']['en'], $trozos)
    || 6 !== count($trozos)
) {
  echo "ERROR: No hemos encontrado el patrón para realizar la traducción", PHP_EOL;
  exit(1);
}

/* Almacenaremos el resultado parcial para luego mostrarlo o recuperarlo */
$resultado = '';
$vacio = str_repeat(" ", 48);
if (preg_match_all('/^((--)?(?:\t|    ))({.*name ?= ?)("[^"]+")([ \t]*)(, ?baseLevel.*)$/mi', $trozos[3], $salida)) {
  foreach($salida[4] as $clave => $valor) {
    $nombre = json_decode($valor);
    echo $nombre, PHP_EOL;
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
    if ($encontrado === false) {
      $resultado .= implode(
        [
          ($encontrado !== false) ? '--' : '',
          $salida[1][$clave],
          $salida[2][$clave],
          $salida[3][$clave],
          $salida[4][$clave],
          $salida[5][$clave],
          $vacio,
          $salida[6][$clave],
          PHP_EOL,
        ],
        ''
      );
    } else {
      /* Primer carácter de cada palabra en mayúsculas */
      $traduccion = primera_letra($datos['traduccion']['es']->{$encontrado});
      /* Convertimos la cadena en cadena LUA (compatible con JSON) */
      $traduccion = json_encode($traduccion, JSON_UNESCAPED_UNICODE);
      /* Calculamos el relleno de espacios en blanco necesario */
      $relleno = str_repeat(" ", max(0, 40 - mb_strlen($traduccion)));
      $resultado .= implode(
        [
          $salida[1][$clave],
          $salida[2][$clave],
          $salida[3][$clave],
          $salida[4][$clave],
          $salida[5][$clave],
          ', tName=',
          $traduccion,
          $relleno,
          $salida[6][$clave],
          PHP_EOL,
        ],
        ''
      );
      }
  }
} else {
    echo "No hay coincidencia", PHP_EOL;
}

/* Mostramos el resultado */
echo $resultado;
