<?php

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class CrawlerProvider implements ServiceProviderInterface {
    protected $providerName = 'crawler';

    public function register(DiInterface $di) : void {
        $di->setShared($this->providerName, Crawler::class);
    }
}

?>