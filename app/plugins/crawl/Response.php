<?php

namespace Crawl;

class Response {
    public string $body;
    public int $status;
    public float $startTime;
    public float $endTime;

    function __construct(string $body, int $status, float $startTime, float $endTime) {
        $this->body = $body;
        $this->status = $status;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    public function getElapsedTime() : float {
        return $this->endTime - $this->startTime;
    }
}

?>