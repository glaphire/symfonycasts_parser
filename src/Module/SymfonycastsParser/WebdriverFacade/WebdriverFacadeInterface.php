<?php

namespace App\Module\SymfonycastsParser\Service\WebdriverFacade;

interface WebdriverFacadeInterface
{
    public function click(string $cssSelector);

    public function waitToBeClickable(string $cssSelector);

    public function waitAndClick(string $cssSelector);

    public function openUrl(string $url);

    public function findOne(string $cssSelector);

    public function findAll(string $cssSelector);

    public function fillInput(string $cssSelector, string $text);

    public function waitFilesToDownload();

    public function close();
}