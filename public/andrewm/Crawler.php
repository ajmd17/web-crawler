<?php

namespace AndrewM;

use AndrewM\Fetch\Request;
use AndrewM\Fetch\CrawlResult;

class Crawler {
    private static $LINK_DEPTH = 4;
    private static $MAX_LINK_RECURSION_LEVEL = 1;

    private string $baseUrl;
    private int $linkCounter = 0;

    function __construct(string $baseUrl) {
        $this->baseUrl = $baseUrl;
    }

    public function start() {
        return $this->crawlUrl($this->baseUrl, self::$MAX_LINK_RECURSION_LEVEL);
    }

    private function crawlUrl(string $url, int $maxRecursionLevel, int $currentRecursionLevel = 0) {
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
                $this->getAbsoluteUrl($this->baseUrl, $href),
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