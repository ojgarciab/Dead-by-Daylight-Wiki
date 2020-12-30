<?php
/*
curl "https://deadbydaylight.gamepedia.com/index.php?title=Template:Perk_icon&action=edit" > Perk_icon.en.html
curl "https://deadbydaylight-es.gamepedia.com/index.php?title=Plantilla:Perk_icon&action=edit" > Perk_icon.es.html

curl "https://deadbydaylight.gamepedia.com/index.php?title=Template:Perk_desc&action=edit" > Perk_desc.en.html
curl "https://deadbydaylight-es.gamepedia.com/index.php?title=Plantilla:Perk_desc&action=edit" > Perk_desc.es.html

*/

$raiz = 'en';
$urls = [
  'en' => 'Perk_icon.en.html',
  'es' => 'Perk_icon.es.html',
];

/* Cargamos los archivos */
$doms = [];
foreach($urls as $indice => $url) {
  $doms[$indice] = new DOMDocument();
  @$doms[$indice]->loadHTMLFile($url);
}

/* Analizamos contenido */
$contenidos = [];
foreach($doms as $indice => $dom) {
  $contenidos[$indice] = [];
  $contenidos[$indice]['html'] = $dom->getElementById('wpTextbox1')->textContent;
  if (preg_match_all('#\|([^={]+)=(.+)$#mi', $contenidos[$indice]['html'], $salida)) {
    $contenidos[$indice]['iconos'] = [];
    foreach($salida[2] as $clave => $valor) {
      $valor = trim($valor);
      $contenidos[$indice]['iconos'][$valor] = trim($salida[1][$clave]);
    }
  }
  unset($contenidos[$indice]['html']);
}

echo 'Analizando Iconos que faltan:', PHP_EOL;
foreach($contenidos as $lenguaje => $contenido) {
  if ($lenguaje == $raiz) continue;
  foreach($contenidos[$raiz]['iconos'] as $icono => $nombre) {
    //echo $lenguaje, $icono, PHP_EOL;
    if (!isset($contenido['iconos'][$icono])) {
      echo 'Falta (', $indice, '): ', $nombre, PHP_EOL;
    } else if ($contenido['iconos'][$icono] == $nombre) {
      echo 'Sin traducir (', $indice, '): ', $nombre, PHP_EOL;
    }
  }
}

$urls = [
  'en' => 'Perk_desc.en.html',
  'es' => 'Perk_desc.es.html',
];

/* Cargamos los archivos */
$doms = [];
foreach($urls as $indice => $url) {
  $doms[$indice] = new DOMDocument();
  @$doms[$indice]->loadHTMLFile($url);
}

/* Analizamos contenido */
foreach($doms as $indice => $dom) {
  $contenidos[$indice]['html'] = $dom->getElementById('wpTextbox1')->textContent;
  if (preg_match_all('#^[ \t]*\|([^={]+)=#mi', $contenidos[$indice]['html'], $salida)) {
    $contenidos[$indice]['descripciones'] = [];
    foreach($salida[1] as $clave => $valor) {
      $valor = trim($valor);
      $contenidos[$indice]['descripciones'][$valor] = true;
    }
  }
  unset($contenidos[$indice]['html']);
}

echo 'Analizando Descripciones que faltan:', PHP_EOL;
foreach($contenidos as $lenguaje => $contenido) {
  foreach($contenido['iconos'] as $icono => $nombre) {
    if (!isset($contenido['descripciones'][$nombre])) {
      echo 'Falta (', $indice, '): ', $nombre, ' (', $contenidos[$raiz]['iconos'][$icono] ,')', PHP_EOL;
    }
  }
}

//var_export($contenidos);

