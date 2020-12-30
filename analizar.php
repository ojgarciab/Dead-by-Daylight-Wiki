<?php
require_once 'funciones.php';

$refrescar = isset($_SERVER['argv'][1]) && ($_SERVER['argv'][1] === 'refrescar');

$lenguajes = [
  'en',
  'es',
];

foreach($lenguajes as $lenguaje) {
  $datos[$lenguaje]['Template:Icons']['contenido'] = json_decode(cache_url(
    obtener_url($lenguaje, 'Template:Icons'),
    $lenguaje . '.Template-Icons',
    $refrescar
  ))->query->pages[0]->revisions[0]->content;
}

/* Eliminamos el bloque explícito */
$datos['en']['Template:Icons']['contenido'] = preg_replace('#<!--French Translation -.*(}})#ms', '$1', $datos['en']['Template:Icons']['contenido']);

/* Búsqueda de bloques */
foreach($lenguajes as $lenguaje) {
  if (preg_match_all('#<!--[ ]*([^\-]+)( -|-->)#U', $datos[$lenguaje]['Template:Icons']['contenido'], $salida)) {
    $datos[$lenguaje]['Template:Icons']['bloques'] = $salida[1];
  } else {
    echo 'No he encontrado bloques en el lenguaje "', $lenguaje, '"', PHP_EOL;
    exit;
  }
}

/* Si existe algún error debemos parar el análisis */
$salir = false;

echo 'Analizando diferencias de bloques:', PHP_EOL;
foreach(array_diff($datos['en']['Template:Icons']['bloques'], $datos['es']['Template:Icons']['bloques']) as $valor) {
  echo 'Falta en "es": ', $valor, PHP_EOL;
  $salir = true;
}
foreach(array_diff($datos['es']['Template:Icons']['bloques'], $datos['en']['Template:Icons']['bloques']) as $valor) {
  echo 'Falta en "en": ', $valor, PHP_EOL;
  $salir = true;
}

if ($salir === true) {
  echo 'No se puede continuar el análisis sin paridad de bloques', PHP_EOL;
  exit;
}

echo 'Analizando contenido de bloques:', PHP_EOL;
/* Comprobamos los bloques existentes: */
foreach($datos['en']['Template:Icons']['bloques'] as $bloque) {
  echo 'Analizando contenido de bloque "', $bloque, '":', PHP_EOL;
  foreach($lenguajes as $lenguaje) {
    $datos[$lenguaje]['Template:Icons']['bloque'][$bloque] = [];
    if (
      preg_match('#<!--[ ]*' . $bloque . '(?: -|--)[^>]*>(.*)<!--#Ums', $datos[$lenguaje]['Template:Icons']['contenido'], $salida)
      || preg_match('#<!--[ ]*' . $bloque . ' -[^>]*>(.*)}}#Ums', $datos[$lenguaje]['Template:Icons']['contenido'], $salida)
    ) {
      //var_export($salida);
      if (preg_match_all('#^[ ]*\|([^=]*)=(.*)$#Um', $salida[1], $salida)) {
        foreach($salida[1] as $indice => $valor) {
          $datos[$lenguaje]['Template:Icons']['bloque'][$bloque][trim($valor)] = trim($salida[2][$indice]);
          $datos[$lenguaje]['Template:Icons']['duplas'][$bloque][] = [ trim($valor), trim($salida[2][$indice])];
        }
      }
    }
  }
  foreach(array_diff($datos['en']['Template:Icons']['bloque'][$bloque], $datos['es']['Template:Icons']['bloque'][$bloque]) as $valor) {
    echo 'Falta en bloque "', $bloque, '" ("es"): ', $valor, PHP_EOL;
    $salir = true;
  }
}

if ($salir === true) {
  echo 'No se puede continuar el análisis sin definir en inglés todos los términos', PHP_EOL;
  exit;
}

foreach($datos['en']['Template:Icons']['bloques'] as $bloque) {
  echo 'Analizando detalladamente contenido del bloque "', $bloque, '":', PHP_EOL;
  reset($datos['es']['Template:Icons']['duplas'][$bloque]);
  reset($datos['en']['Template:Icons']['duplas'][$bloque]);
  $siguiente = [
    'en' => each($datos['en']['Template:Icons']['duplas'][$bloque]),
    'es' => each($datos['es']['Template:Icons']['duplas'][$bloque]),
  ];
  while($siguiente['en'] !== false) {
    //var_export([$siguiente['en'], $siguiente['es']]);
    error_log("Primer paso: {$siguiente['en']['value'][0]}/{$siguiente['en']['value'][1]} => {$siguiente['es']['value'][0]}/{$siguiente['es']['value'][1]}");
    /* Si hemos llegado al final del castellano o no coincide el término */
    if ($siguiente['es'] === false || $siguiente['en']['value'][0] !== $siguiente['es']['value'][0]) {
      echo 'Falta el término "', $siguiente['en']['value'][1], '"', PHP_EOL;
      $siguiente['en'] = each($datos['en']['Template:Icons']['duplas'][$bloque]);
      /* ¿Volvemos atrás? */
    } else {
      $anterior = $siguiente;
      $siguiente = [
        'en' => each($datos['en']['Template:Icons']['duplas'][$bloque]),
        'es' => each($datos['es']['Template:Icons']['duplas'][$bloque]),
      ];
      //var_export([$siguiente['en'], $siguiente['es']]);
      error_log("- Segundo paso: {$siguiente['en']['value'][0]}/{$siguiente['en']['value'][1]} => {$siguiente['es']['value'][0]}/{$siguiente['es']['value'][1]}");
      /* Leemos la traducción y comprobamos que no sea el siguiente término */
      $falta = false;
      if ($siguiente['es'] === false) { /* Falta traducción si no existe el registro */
        error_log('Falta por siguiente === false');
        $falta = true;
      } elseif ($anterior['en']['value'][1] !== $siguiente['es']['value'][1]) { /* O si los iconos son diferentes */
        error_log('Falta por "O si los iconos son diferentes"' . "'{$anterior['en']['value'][1]}' -> '{$siguiente['es']['value'][1]}'");
        $falta = true;
      //} elseif ($siguiente['en']['key'] === $siguiente['es']['key']) { /* O si la clave es la misma */
      //  error_log('Falta por "O si la clave es la misma"');
      //  $falta = true;
      }
      if ($falta === true) {
        echo 'Falta traducción del término "', $anterior['en']['value'][1], '"', PHP_EOL;
        //prev($datos['es']['Template:Icons']['bloque'][$bloque]);
      } else {
        $siguiente['es'] = each($datos['es']['Template:Icons']['duplas'][$bloque]);
        error_log("OK");
      }
    }
  }
}

