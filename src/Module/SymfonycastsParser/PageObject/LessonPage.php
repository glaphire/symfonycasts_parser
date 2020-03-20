<?php

namespace App\Module\SymfonycastsParser\PageObject;

class LessonPage extends BasePageObject
{
    public const VIDEO_URL = '.dropdown-menu a[data-download-type=video]';
    public const SCRIPT_URL = '.dropdown-menu a[data-download-type=code]';
    public const CODE_ARCHIVE_URL = '.dropdown-menu a[data-download-type=code]';

    public function parseLessonPage(
        $lessonPageUrl,
        $parseVideoFlag = true,
        $parseScriptFlag = false,
        $parseCodeArchiveFlag = false
    ) {
        $this->webdriver->openUrl($lessonPageUrl);

        if ($parseVideoFlag) {
            $this->clickDropdownOptionAndDownload(self::VIDEO_URL);
        }
        if ($parseScriptFlag) {
            $this->clickDropdownOptionAndDownload(self::SCRIPT_URL);
        }
        if ($parseCodeArchiveFlag) {
            $this->clickDropdownOptionAndDownload('.dropdown-menu a[data-download-type=code]');
        }

        $this->webdriver->waitFilesToDownload();
    }

    protected function clickDropdownOptionAndDownload(string $cssSelector)
    {
        $this->webdriver->waitToBeClickable('#downloadDropdown');
        $this->webdriver->click('#downloadDropdown');
        $this->webdriver->waitToBeClickable('.dropdown-menu.show');
        $this->webdriver->click($cssSelector);
    }
}