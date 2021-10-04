<?php

use Phalcon\Di\Injectable;
use Crawler\Response;
use Crawler\CrawlResult;
use Crawler\Request;

class Crawler extends Injectable {
    private static $LINK_DEPTH = 4;
    private static $MAX_LINK_RECURSION_LEVEL = 1;

    private int $linkCounter = 0;

    public function crawl(string $baseUrl) {
        return $this->crawlUrl($baseUrl, $baseUrl, self::$MAX_LINK_RECURSION_LEVEL);
    }

    private function crawlUrl(string $url, string $baseUrl, int $maxRecursionLevel, int $currentRecursionLevel = 0) {
        $baseRequest = new Request($url);
        $response = $baseRequest->fetch();
        
        $crawlResult = new CrawlResult($url, $response);

        if ($currentRecursionLevel >= $maxRecursionLevel) {
            return $crawlResult;
        }

        $links = $crawlResult->getInternalLinks();

        foreach ($links as $link) {
            if ($this->linkCounter >= self::$LINK_DEPTH) {
                break;
            }

            $href = $link->getAttribute('href');

            if ($this->shouldSkipLink($href)) {
                continue;
            }

            $nestedCrawlResult = $this->crawlUrl(
                $this->getAbsoluteUrl($baseUrl, $href),
                $baseUrl,
                $maxRecursionLevel,
                $currentRecursionLevel + 1
            );

            $crawlResult->addChildResult($nestedCrawlResult);

            $this->linkCounter += 1;
        }

        return $crawlResult;
    }

    private function getAbsoluteUrl(string $baseUrl, string $href) {
        $pathTrimmed = trim($href, '/');

        return "$baseUrl/$pathTrimmed";
    }

    private function shouldSkipLink(string $href) {
        return empty($href) || $href[0] == '#';
    }
}
?>