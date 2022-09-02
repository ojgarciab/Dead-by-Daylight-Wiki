<?php
/* ¿Requiere php-luasandbox? */
require_once 'funciones.php';

$refrescar = isset($_SERVER['argv'][1]) && ($_SERVER['argv'][1] === 'refrescar');

$lenguajes = [
  'en',
  'es',
];

require_once 'modulo.comun.php';
$datos = cargar_datos('Module:Datatable', $lenguajes, $refrescar);
/* Agregamos datos que faltan pero se mantienen en la Wiki */
//$datos['normalizado']['en']['lastStanding'] = 'last standing';
//$datos['traduccion']['es']->lastStanding = 'El último en pie';

/* Recortamos el contenido original (en inglés) para ser traducido */
if (
    0 === preg_match('/^(.*)(killers = {\n)(.*)(\n}\n)(.*)$/s', $datos['contenido']['en'], $trozos)
    || 6 !== count($trozos)
) {
  echo "ERROR: No hemos encontrado el patrón para realizar la traducción", PHP_EOL;
  exit(1);
}

/* Almacenaremos el resultado parcial para luego mostrarlo o recuperarlo */
$resultado = '';
$vacio = str_repeat(" ", 48);

echo $trozos[2];
$restante = 5;
foreach(explode("\n", $trozos[3]) as $linea) {
    $detalles = [];
    $resultado = [];
    $comienzo = 0;
    //echo "++", $linea, PHP_EOL;
    while (preg_match(
        '/(?:,\s*|{)[^=]* = [^=]+\s*(?:,\s*[^=]* = |},?$)/',
        $linea,
        $salida,
        PREG_OFFSET_CAPTURE,
        $comienzo
    )) {
        preg_match('/(?:,\s*|{)((?<clave>[^=]*) = (?<valor>[^=]+))\s*(?<cola>,\s*[^=]* = |},?$)/', $salida[0][0], $salida2);
        //var_export($salida2);
        //echo "--", $salida2[1], "--", PHP_EOL;
        $detalles[] = [ $salida2["clave"], $salida2["valor"] ];
        $comienzo += strlen($salida[0][0]) - strlen($salida2['cola']);
    }
    foreach($detalles as $detalle) {
        $resultado[] = $detalle[0] ." = ". $detalle[1];
    }
    echo "--\t{", implode(", ", $resultado), "},--", PHP_EOL;
}

