<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>php wget tool</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<?php
$tags = [
    [
        'name' => 'link',
        'attribute' => 'href'
    ],
    [
        'name' => 'img',
        'attribute' => 'src'
    ],
    [
        'name' => 'script',
        'attribute' => 'src'
    ]
];

function wget($url, $recursive = false)
{
    global $tags;

    $file = file_get_contents($url);

    if ($file) {
        if (is_writeable(__DIR__)) {

            if ($recursive) {
                $document = new DOMDocument();
                @$document->loadHTML($file);

                foreach ($tags as $tag) {
                    $nodes = $document->getElementsByTagName($tag['name']);
                    foreach ($nodes as $node) {
                        $location = $node->getAttribute($tag['attribute']);
                        if ($location and !parse_url($location, PHP_URL_HOST)) {
                            $location = ltrim($location, '/');
                            $node->setAttribute($tag['attribute'], $location);
                            wget("$url/$location");
                        }
                    }
                }

                $file = $document->saveHTML();
            }


            // $url = urldecode($url); nazwa pliku wychodzi encoded

            $host = parse_url($url, PHP_URL_HOST);
            $path = parse_url($url, PHP_URL_PATH);
            // $path = ltrim($path, '/') ?? '/index.html';
            if (!$path or $path === '/') {
                $path = '/index.html';
            }

            $filename = $host . $path;

            $filename = urldecode($filename);

            $dir = dirname($filename);
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            $saved = file_put_contents($filename, $file);
            $basename = basename($path);
            if ($saved) {
                echo "<p class=\"success\">Zapisano: <a href=\"$filename\">$basename</a> <span class=\"main\">&leftarrow;</span></p>";
            } else {
                echo "<p class=\"error\">Błąd zapisu pliku $basename</p>";
            }
        } else {
            echo '<p class="error">Brak uprawnień zapisu</p>';
        }
    } else {
        echo '<p class="error">Wystąpił błąd podczas pobierania pliku</p>';
    }
}

class Logger {
    private static $logs;

    public static function log(string $message, bool $success) {
        self::$logs .= '<p class="' . ($success ? 'success' : 'error') . "\">$message</p>";
    }

    public static function getLogs() {
        return '<pre class="logs">' . self::$logs . '</pre>';
    }
}

Logger::log('xd', false);
Logger::log('xd', true);
var_dump(Logger::getLogs());

if (isset($_GET['url'])) {
    $url = $_GET['url'];
    echo '<pre class="logs">';
    !empty($url) ? wget($url, true) : print('Nazwa pliku nie moze byc pusta');
    echo '</pre>';
}
?>

<body>
    <header>
        <h1 class="php">php</h1>
        <h2>wget tool</h2>
    </header>
    <form action="index.php" autocomplete="off">
        <input type="text" name="url" placeholder="url...">
        <button type="submit">Download</button>
    </form>
    <script>
        const list = document.createElement('ul');
        const urls = [
            'http://edu.gplweb.pl',
            'http://zsegw.pl',
            'https://google.com'
        ];
        urls.forEach(url => {
            const listItem = document.createElement('li');
            listItem.textContent = new URL(url).hostname;
            listItem.dataset.url = url;
            listItem.addEventListener('click', e => {
                const input = document.querySelector('input');
                input.value = e.target.dataset.url;
            });
            list.append(listItem);
        });
        document.body.append(list);
    </script>
</body>

</html>