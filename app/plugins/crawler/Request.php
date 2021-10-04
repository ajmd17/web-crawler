<?php

namespace Crawler;

class Request {
    private $curl;
    private string $url;

    function __construct(string $url) {
        $this->url = $url;
    }

    public function fetch() : Response {
        $this->curl = curl_init();

        curl_setopt($this->curl, CURLOPT_URL, $this->url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);

        $startTime = microtime(true);

        $responseBody = curl_exec($this->curl);

        $endTime = microtime(true);

        $httpStatus = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        curl_close($this->curl);

        return new Response($responseBody, $httpStatus, $startTime, $endTime);
    }
}

?>