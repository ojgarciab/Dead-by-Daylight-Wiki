<?php
function cache_url($url, $archivo, $refrescar = false) {
  $archivo = __DIR__ . '/cache/' . implode([ $archivo, substr(md5($url), 0, 5) , 'txt' ], '.');
  if ($refrescar === false && is_file($archivo)) {
    return file_get_contents($archivo);
  } else {
    $datos = file_get_contents($url);
    file_put_contents($archivo, $datos);
    return $datos;
  }
}

function obtener_url($lang, $pagina) {
  $url = "https://deadbydaylight.fandom.com/";
  if ($lang !== 'en') {
    $url .= $lang .'/';
  }
  return $url .'api.php?'. http_build_query([
    'action' => 'query',
    'prop' => 'revisions',
    'rvprop' => 'content',
    'rvslots' => 'main',
    'format' => 'json',
    'formatversion' => 2,
    'titles' => $pagina,
  ]);
}

function primera_letra($cadena) {
  /* Primer carácter de cada palabra en mayúsculas */
  $cadena = mb_convert_case($cadena, MB_CASE_TITLE);
  /* Exceptuando determinantes y otras excepciones */
  $cadena = str_replace(
    [
      ' A ',
      ' Y ',
      ' De ',
      ' Del ',
      ' La ',
      ' Lo ',
      ' El ',
      ' Las ',
      ' Los ',
      ' Que ',
      ' Para ',
      ' Un ',
      ' Una ',
    ],
    [
      ' a ',
      ' y ',
      ' de ',
      ' del ',
      ' la ',
      ' lo ',
      ' el ',
      ' las ',
      ' los ',
      ' que ',
      ' para ',
      ' un ',
      ' una ',
    ],
    $cadena
  );
  return $cadena;
}
