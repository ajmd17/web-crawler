<?php

use Phalcon\Mvc\Controller;

class IndexController extends Controller
{
    public function indexAction()
    {
        $this->view->crawlResult = $this->crawler->crawl('https://bidsquid.com');
    }
}