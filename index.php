<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>php web scraper</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<?php
class Logger
{
    private static $logs;

    public static function log(bool $success, string $message)
    {
        self::$logs .= '<p class="' . ($success ? 'success' : 'error') . "\">$message</p>";
    }

    public static function getLogs()
    {
        return self::$logs;
    }
}

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

function scrape($url, $recursive = false)
{
    global $tags;

    $file = file_get_contents($url);

    if ($file) {
        if (is_writeable(__DIR__)) {
            $host = parse_url($url, PHP_URL_HOST);
            $path = parse_url($url, PHP_URL_PATH);
            
            if ($recursive) {
                $url = [parse_url($url, PHP_URL_SCHEME), $host];
                $url = implode('://', $url);

                $document = new DOMDocument();
                @$document->loadHTML($file);

                foreach ($tags as $tag) {
                    $nodes = $document->getElementsByTagName($tag['name']);
                    foreach ($nodes as $node) {
                        $location = $node->getAttribute($tag['attribute']);
                        if ($location and !parse_url($location, PHP_URL_HOST)) {
                            $location = ltrim($location, '/');
                            $node->setAttribute($tag['attribute'], $location);
                            scrape("$url/$location");
                        }
                    }
                }

                $file = $document->saveHTML();
            }


            // $url = urldecode($url); nazwa pliku wychodzi encoded

            
            

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
            Logger::log($saved, $saved ? "Saved: <a href=\"$filename\">$basename</a>" : "Błąd zapisu $basename");
        } else {
            Logger::log(false, 'Directory is not writable');
        }
    } else {
        Logger::log(false, 'Error downloading file');
    }
}

if (isset($_GET['url'])) {
    $url = $_GET['url'];
    !empty($url) ? scrape($url, true) : Logger::log(false, 'Filename must not be empty');
}
?>

<body>
    <header title="no cURL no bullshit">
        <h1 class="php">php</h1>
        <h2>web scraper</h2>
    </header>
    <form action="index.php" autocomplete="off">
        <input type="text" name="url" placeholder="https://example.com">
        <button type="submit">Download</button>
    </form>
    <div class="logs">
        <?php if ($logs = Logger::getLogs()) { ?>
            <pre><?= $logs ?></pre>
        <?php } else { ?>
            <ul></ul>
            <script>
                const urls = [
                    'http://edu.gplweb.pl',
                    'http://zsegw.pl',
                    'https://google.com',
                    'https://zsel.edupage.org/timetable/view.php?class=-75&fullscreen=1'
                ];
                const list = document.querySelector('ul');
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
            </script>
        <?php } ?>
    </div>
</body>

</html>