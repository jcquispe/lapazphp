<?php

$streamingUrl = 'http://icecasthd.net:45019/live';
$interval = 19200;
getMetadata($streamingUrl, $interval);
 
function getMetadata($streamingUrl, $interval, $offset = 0, $headers = true){
  
$jsondata = array();
  
  $needle = 'StreamTitle=';
  $ua = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.110 Safari/537.36';
  $opts = [
    'http' => [
      'method' => 'GET',
      'header' => 'Icy-MetaData: 1',
      'user_agent' => $ua
    ]
  ];
  if (($headers = get_headers($streamingUrl)))
    foreach ($headers as $h)
      if (strpos(strtolower($h), 'icy-metaint') !== false && ($interval = explode(':', $h)[1]))
        break;
  $context = stream_context_create($opts);
  if ($stream = fopen($streamingUrl, 'r', false, $context))
  {
    $buffer = stream_get_contents($stream, $interval, $offset);
    fclose($stream);
    if (strpos($buffer, $needle) !== false)
    {
      $title = explode($needle, $buffer)[1];
      $jsondata['success'] = true;
      $jsondata['message'] = substr($title, 1, strpos($title, ';') - 2);
    }
    else
      return getMetadata($streamingUrl, $interval, $offset + $interval, false);
  }
  else{
      $jsondata['success'] = false;
      $jsondata['message'] = "Error con el servidor";
  }
  
  header('Content-type: application/json; charset=utf-8');
  echo json_encode($jsondata);
  exit();
}
?>