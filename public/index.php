<?php
    // require_once('./andrewm/autoload.php');

    error_reporting(E_ERROR | E_PARSE);

    use Phalcon\Loader;

    define('BASE_PATH', dirname(__DIR__));
    define('APP_PATH', BASE_PATH . '/app');
    // ...

    $loader = new Loader();

    $loader->registerDirs(
        [
            APP_PATH . '/controllers/',
            APP_PATH . '/models/',
        ]
    );

    $loader->register();

    // use AndrewM\Crawler;

    // $crawler = new Crawler('https://bidsquid.com');
    // $crawlResult = $crawler->start();
?>
<html>
    <head>
        <style>
            body {
                font-family: sans-serif;
            }

            .content {
                display: flex;
                flex-direction: row-reverse;
            }

            .table,
            .table th,
            .table td {
                flex: 1;
                font-size: 16px;
                border: 1px solid #777;
            }
            .table {
                border-collapse: collapse;
            }
            .table th {
                padding: 10px 6px;
            }
            .table td {
                padding: 10px;
            }
            .table thead {
                background: #eee;
            }

            .total-results-wrapper {
                display: flex;
                flex-direction: column;
                justify-content: center;
                padding: 20px;
            }

            .total-results {
                border: 1px solid #eee;
                border-radius: 10px;
                background-color: #eee;
                display: inline-block;
                padding: 12px;
            }

            .status-code--good {
                font-weight: bold;
                color: green;
            }

            .status-code--redirect {
                font-weight: bold;
                color: gold;
            }

            .status-code--bad {
                font-weight: bold;
                color: red;
            }

            @media only screen and (max-width: 1200px) {
                .content {
                    flex-direction: column-reverse;
                }
            }
        </style>
    </head>
    <body>
        <div class="content">
            <table class="table">
                <thead>
                    <tr>
                        <th>Path</th>
                        <th>Status</th>
                        <th>Title</th>
                        <th>Internal links</th>
                        <th>External links</th>
                        <th>Images (unique)</th>
                        <th>Images (non-unique)</th>
                        <th>Words on page</th>
                        <th>Load time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        function getStatusClass($statusCode) {
                            $statusClass = 'status-code--';
                            if ($statusCode >= 200 && $statusCode < 300) {
                                $statusClass .= 'good';
                            } elseif ($statusCode >= 300 && $statusCode < 400) {
                                $statusClass .= 'redirect';
                            } else {
                                $statusClass .= 'bad';
                            }
                            return $statusClass;
                        }

                        foreach ($crawlResult->getAllNestedResults() as $result) {
                            $path = $result->path;
                            $statusCode = $result->response->status;
                            $statusClass = getStatusClass($statusCode);
                            $title = $result->getPageTitle();
                            $internalLinks = $result->getInternalLinks(false, true);
                            $internalLinksArray = json_encode(
                                array_values(
                                    array_map(function ($link) {
                                        return $link->getAttribute('href');
                                    }, $internalLinks)
                                )
                            );
                            $internalLinksCount = count($internalLinks);
                            $externalLinks = $result->getExternalLinks(false, true);
                            $externalLinksArray = json_encode(
                                array_values(
                                    array_map(function ($link) {
                                        return $link->getAttribute('href');
                                    }, $externalLinks)
                                )
                            );
                            $externalLinksCount = count($externalLinks);
                            $uniqueImages = count($result->getImages(false, true));
                            $nonUniqueImages = count($result->getImages(false, false));
                            $wordsOnPage = $result->getWordCount();
                            $loadTimeSeconds = round($result->getElapsedTime(), 3) . 's';

                            echo "
                                <tr>
                                    <td>$path</td>
                                    <td class='$statusClass'>$statusCode</td>
                                    <td>$title</td>
                                    <td>$internalLinksCount <a class='showLinks' href='#' data-links='$internalLinksArray'>(Show)</a></td>
                                    <td>$externalLinksCount <a class='showLinks' href='#' data-links='$externalLinksArray'>(Show)</a></td>
                                    <td>$uniqueImages</td>
                                    <td>$nonUniqueImages</td>
                                    <td>$wordsOnPage</td>
                                    <td>$loadTimeSeconds</td>
                                </tr>
                            ";
                        }
                    ?>
                </tbody>
            </table>

            <div class="total-results-wrapper">
                <div class='total-results'>
                    <div>
                        <strong>Total pages crawled:</strong>
                        <?php echo(count($crawlResult->getAllNestedResults())); ?>
                    </div>
                    <div>
                        <strong>Total unique images:</strong>
                        <?php echo(count($crawlResult->getImages(true, true))); ?>
                    </div>
                    <div>
                        <strong>Total unique internal links:</strong>
                        <?php echo(count($crawlResult->getInternalLinks(true, true))); ?>
                    </div>
                    <div>
                        <strong>Total unique external links:</strong>
                        <?php echo(count($crawlResult->getExternalLinks(true, true))); ?>
                    </div>
                    <hr/>
                    <div>
                        <strong>Avg time per page:</strong>
                        <?php echo(round($crawlResult->calculateAverageElapsedTime(), 3) . 's'); ?>
                    </div>
                    <div>
                        <strong>Avg title length (characters):</strong>
                        <?php echo($crawlResult->calculateAverageTitleLengthCharacters() . ' characters'); ?>
                    </div>
                    <div>
                        <strong>Avg title length (words):</strong>
                        <?php echo($crawlResult->calculateAverageTitleLengthWords() . ' words'); ?>
                    </div>
                    <div>
                        <strong>Avg words on page:</strong>
                        <?php echo($crawlResult->calculateAverageWordCount() . ' words'); ?>
                    </div>
                </div>
            </div>
        </div>

        <script type='text/javascript'>
            function onLoad() {
                Array.prototype.forEach.call(document.getElementsByClassName('showLinks'), (link) => {
                    link.addEventListener('click', (event) => {
                        event.preventDefault()

                        const links = JSON.parse(link.dataset.links)

                        const closestTd = link.closest('td')

                        closestTd.innerHTML = '<div>' + links.join('<br>') + '</div'

                    })
                })
            }

            // document.addEventListener('load', function () {
            //     onLoad()
            // })
            onLoad()
        </script>
    </body>
</html>