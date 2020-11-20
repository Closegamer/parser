<?php

require('phpQuery/phpQuery.php');

/*********************************************************************************************************************************
 *	Класс для парсинга новостей с применением curl и phpQuery
 *	-------------------------------------------------------------------------------
 *	Александр Григорьев (Closegamer), 2020
 *********************************************************************************************************************************/

class Parser{
  public static function getLinks($url){
    $hostUrl = 'https://lenta.ru';
    $output = curl_init($url);
    curl_setopt($output, CURLOPT_URL, $url);
    curl_setopt($output, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($output, CURLOPT_HEADER, 0);
    $fullPage .= curl_exec($output);
    curl_close($output);

    $allNews = phpQuery::newDocument($fullPage)->find('#more')->find('.item')->find('.titles')->find('a');

    $text = [];
    $href = [];
    $count = 0;

    foreach ($allNews as $value) {
      if($count <= 15){
        $pqLink = pq($value);
        $text[] = $pqLink->html();
        $href[] = $hostUrl.$pqLink->attr('href');
        $count++;
      }
    }

    $selectedLinks = [];

    foreach ($href as $value) {
      if(strlen($value) < 100){
        $selectedLinks[] = $value;
       }
    }

    return $selectedLinks;
  }

  public static function getPages($allLinks){
    $allPages = [];
    $dir = 'newsFolder';
    // self::cleanDir($dir);
    foreach ($allLinks as $key => $link) {
      $output = curl_init($link);
      curl_setopt($output, CURLOPT_URL, $link);
      curl_setopt($output, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($output, CURLOPT_HEADER, 0);
      $fullPage = curl_exec($output);
      curl_close($output);
      
      $page = phpQuery::newDocument($fullPage)->find('.b-topic__content');
      
      $title = $page->find('h1')->text();
      $image = $page->find('.b-topic__title-image')->html();
      $text = $page->find('p')->text();
      $guid = self::generate_uuid();
      $filename = $dir.'/'.$guid.'.html';
      $backLink = '<a href="/">Назад</a>';
     
      if(empty($text) || empty($title)){
        continue;
      }

      $result = [
        'title'     => $title,
        'image'     => $image,
        'text'      => $text,
        'link'      => $filename,
        'backLink'  => $backLink
      ];

      $stringsArray = [
        '<html>',
        '<head>',
        '<title>Страница одной новости</title>',
        '<link rel="stylesheet" href="../styles.css">',
        '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">',
        '</head>',
        '<body>',
        '<div class="mainContainer">',
        '<div><h1>'.$result['title'].'</h1></div>',
        '<div class="image">'.$result['image'].'</div>',
        '<div class="text">'.$result['text'].'</div>',
        '<div>'.$result['backLink'].'</div>',
        '</div>',
        '</body>',
        '</html>'
      ];

      $fileToFill = file_get_contents($filename);

      foreach ($stringsArray as $string) {
        $fileToFill .= $string;
      }

      file_put_contents($filename, $fileToFill);
      
      $allPages[$key] = $result;
    }

    return $allPages;
  }

  public static function printResult($allPages){
    foreach ($allPages as $pageContent) {
      mb_internal_encoding("UTF-8");
      $shortText = mb_substr($pageContent['text'], 0, 200);
      echo '<div class="card">';
      echo '<div><h1>'.$pageContent['title'].'</h1></div>';
      echo '<div class="text">'.$shortText.'...</div>';
      echo '<div><a href="'.$pageContent['link'].'">Читать целиком</a></div>';
      echo '</div>';
    }
  }

  public static function generate_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
  }

  public static function cleanDir($dir) {
    $files = glob($dir."/*");
    $c = count($files);
    if (count($files) > 0) {
        foreach ($files as $file) {
            if (file_exists($file)) {
            unlink($file);
            }
        }
    }
  }
}
?>