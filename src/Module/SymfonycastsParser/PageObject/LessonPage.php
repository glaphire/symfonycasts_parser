<?php

namespace App\Module\SymfonycastsParser\PageObject;

class LessonPage extends BasePageObject
{
    public const VIDEO_URL = '.dropdown-menu a[data-download-type=video]';
    public const SCRIPT_URL = '.dropdown-menu a[data-download-type=code]';
    public const CODE_ARCHIVE_URL = '.dropdown-menu a[data-download-type=code]';

    public const DOWNLOAD_MENU_BUTTON = '#downloadDropdown';
    public const DOWNLOAD_MENU_LIST = '.dropdown-menu.show';

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
            $this->clickDropdownOptionAndDownload(self::CODE_ARCHIVE_URL);
        }

        $this->webdriver->waitFilesToDownload();
    }

    protected function clickDropdownOptionAndDownload(string $cssSelector)
    {
        $this->webdriver->waitAndClick(self::DOWNLOAD_MENU_BUTTON);
        $this->webdriver->waitToBeClickable(self::DOWNLOAD_MENU_LIST);
        $this->webdriver->click($cssSelector);
    }
}