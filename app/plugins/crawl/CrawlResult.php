<?php

namespace Crawl;

use \DOMDocument;

class CrawlResult {
    public string $path;
    public Response $response;
    public DOMDocument $dom;
    public array $childResults = [];

    function __construct(string $path, Response $response) {
        $this->path = $path;
        $this->response = $response;

        $this->dom = new DOMDocument;

        $strippedHtml = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $response->body);

        $this->dom->loadHTML($strippedHtml);
        $this->dom->preserveWhiteSpace = false;
    }

    public function addChildResult(CrawlResult $result) {
        array_push($this->childResults, $result);
    }

    public function getAllNestedResults(bool $includeThis = true) {
        $childLinks = array_map(function ($child) {
            return $child->getAllNestedResults();
        }, $this->childResults);

        if (!$includeThis) {
            return $childLinks;
        }

        return array_merge(array($this), ...$childLinks);
    }

    public function getPageTitle() {
        $elements = $this->dom->getElementsByTagName('title');

        if (empty($elements)) {
            return '';
        }

        return $elements->item(0)->textContent;
    }

    public function getElements(string $tag) {
        return iterator_to_array($this->dom->getElementsByTagName($tag));
    }

    public function getElementsNested(string $tag) {
        $childLinks = array_map(function ($child) use ($tag) {
            return $child->getElementsNested($tag);
        }, $this->childResults);

        return array_merge($this->getElements($tag), ...$childLinks);
    }

    public function getUniqueElements(array $elements, string $uniqueBy) {
        $assoc = [];

        foreach ($elements as $element) {
            $assoc[$element->getAttribute($uniqueBy)] = $element;
        }

        return array_values($assoc);
    }

    public function getImages(bool $nested = false, bool $unique = false) {
        $images = $nested
            ? $this->getElementsNested('img')
            : $this->getElements('img');

        if (!$unique) {
            return $images;
        }

        return $this->getUniqueElements($images, 'src');
    }

    public function getExternalLinks(bool $nested = false, bool $unique = false) {
        $links = $nested
            ? $this->getElementsNested('a')
            : $this->getElements('a');

        $links = array_filter($links, function ($link) {
            $href = $link->getAttribute('href');

            return $this->isLinkExternal($href);
        });

        if (!$unique) {
            return $links;
        }

        return $this->getUniqueElements($links, 'href');
    }

    public function getInternalLinks(bool $nested = false, bool $unique = false) {
        $links = $nested
            ? $this->getElementsNested('a')
            : $this->getElements('a');

        $links = array_filter($links, function ($link) {
            $href = $link->getAttribute('href');

            return !$this->isLinkExternal($href);
        });

        if (!$unique) {
            return $links;
        }

        return $this->getUniqueElements($links, 'href');
    }

    private function isLinkExternal(string $href) {
        return substr($href, 0, 4) == 'http';
    }

    public function getElapsedTime() : float {
        return $this->response->getElapsedTime();
    }

    public function calculateAverageElapsedTime() : float {
        if (empty($this->childResults)) {
            return $this->getElapsedTime();
        }

        $elapsedTimes = array_map(function ($result) {
            return $result->calculateAverageElapsedTime();
        }, $this->childResults);

        $sum = array_sum($elapsedTimes) + $this->getElapsedTime();

        return $sum / (count($elapsedTimes) + 1);
    }

    public function getHtmlText() {
        return strip_tags(str_replace('<', ' <', $this->dom->saveHTML()));
    }

    public function getWordCount() {
        return str_word_count($this->getHtmlText());
    }

    public function calculateAverageTitleLengthCharacters() {
        $length = strlen($this->getPageTitle());

        $titleLengths = array_map(function ($result) {
            return strlen($result->getPageTitle());
        }, $this->childResults);

        $sum = array_sum($titleLengths) + $length;

        return $sum / (count($titleLengths) + 1);
    }

    public function calculateAverageTitleLengthWords() {
        $length = str_word_count($this->getPageTitle());

        $titleLengths = array_map(function ($result) {
            return str_word_count($result->getPageTitle());
        }, $this->childResults);

        $sum = array_sum($titleLengths) + $length;

        return $sum / (count($titleLengths) + 1);
    }

    public function calculateAverageWordCount() {
        $wordCount = $this->getWordCount();

        $wordCounts = array_map(function ($result) {
            return $result->getWordCount();
        }, $this->childResults);

        $sum = array_sum($wordCounts) + $wordCount;

        return $sum / (count($wordCounts) + 1);
    }
}

?>