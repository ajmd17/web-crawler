<?php

require_once 'crawl/Request.php';
require_once 'crawl/Response.php';
require_once 'crawl/CrawlResult.php';

use Phalcon\Di\Injectable;
use Crawl\Response;
use Crawl\CrawlResult;
use Crawl\Request;

class Crawler extends Injectable {
    private static $LINK_DEPTH = 4;
    private static $MAX_LINK_RECURSION_LEVEL = 1;

    private int $linkCounter;
    private array $crawledUrls;
    private string $baseUrl;

    public function crawl(string $baseUrl) {
        $this->linkCounter = 0;
        $this->crawledUrls = [];
        $this->baseUrl = $baseUrl;

        return $this->crawlUrl($baseUrl, self::$MAX_LINK_RECURSION_LEVEL);
    }

    private function crawlUrl(string $url, int $maxRecursionLevel, int $currentRecursionLevel = 0) {   
        $baseRequest = new Request($url);
        $response = $baseRequest->fetch();

        array_push($this->crawledUrls, rtrim($url, '/'));

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

            $sublink = $this->getAbsoluteUrl(rtrim($this->baseUrl, '/'), $href);

            if (in_array($sublink, $this->crawledUrls)) {
                // do not scan a link again
                continue;
            }

            $nestedCrawlResult = $this->crawlUrl(
                $sublink,
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

        if (empty($pathTrimmed)) {
            return $baseUrl;
        }

        return "$baseUrl/$pathTrimmed";
    }

    private function shouldSkipLink(string $href) {
        return empty($href) || $href[0] == '#';
    }
}
?>