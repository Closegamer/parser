<html>
  <head>
    <title>Тестовое задание от Григорьева Александра</title>
    <link rel="stylesheet" href="styles.css">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  </head>
  <body>
    <div class="mainContainer">
      <?php 
        require 'parser.php';

        // определение хоста для парсинга новостей
        $urlParam = 'https://lenta.ru/parts/news/';

        // получение ссылок на страницы новостей
        $allLinks = Parser::getLinks($urlParam);

        // получение контента страниц новостей
        $allPages = Parser::getPages($allLinks);

        // вывод результата
        Parser::printResult($allPages);
      ?>
    </div>
  </body>
</html>

