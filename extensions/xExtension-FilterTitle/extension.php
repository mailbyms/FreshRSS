<?php

class FilterTitleExtension extends Minz_Extension {
    public function init(): void {
        $this->registerTranslates();

        $this->registerHook('entry_before_insert', [$this, 'filterTitle']);
    }

    public function handleConfigureAction(): void {
        $this->registerTranslates();

        if (Minz_Request::isPost()) {
            $configuration = [
                'blacklist' => array_filter(preg_split('/\R/u', Minz_Request::paramString('blacklist', true)) ?: []),
                'mark_as_read' => Minz_Request::paramString('mark_as_read'),
                'whitelist' => array_filter(preg_split('/\R/u', Minz_Request::paramString('whitelist', true)) ?: []),
            ];
            $this->setSystemConfiguration($configuration);
        }
    }

    public function filterTitle($entry) {
        if (is_object($entry) === true) {
            //-- do check BLACKLIST ---------------------------
            $patterns = $this->getSystemConfigurationValue('blacklist') ?? $this->getSystemConfigurationValue('blacklist_title_keywords') ?? [];
            if (is_array($patterns)) {
                foreach ($patterns as $pattern) {
                    if ($this->isPatternFound($entry->title(), $pattern)) {
                        Minz_Log::info(_t('ext.filter_title.warning.not_allowed_keyword', $entry->title()) . ' (Matched: ' . $pattern . ')');
                        if ($this->getSystemConfigurationValue('mark_as_read') == '1') {
                            // add entry into database and mark as read
                            $entry->_isRead(true);
                            return $entry;
                        } else {
                            // add entry into database not allowed
                            Minz_Log::warning(_t('ext.filter_title.warning.not_allowed_keyword', $entry->title()));
                            return null;
                        }
                    }
                }
            }

            //-- do check WHITELIST ---------------------------
            $patterns = $this->getSystemConfigurationValue('whitelist') ?? [];
            if (is_array($patterns)) {
                foreach ($patterns as $pattern) {
                    if (!$this->isPatternFound($entry->title(), $pattern)) {
                        if ($this->getSystemConfigurationValue('mark_as_read') == '1') {
                            // add entry into database and mark as read
                            $entry->_isRead(true);
                            return $entry;
                        } else {
                            // add entry into database not allowed
                            Minz_Log::warning(_t('ext.filter_title.warning.not_allowed_keyword', $entry->title()));
                            return null;
                        }
                    }
                }
            }
        }

        return $entry;
    }

    private function isPatternFound(string $title, string $pattern): bool {
        if ($pattern === '') {
            return false;
        }

        // Check if the pattern is a valid regex (starts and ends with the same delimiter)
        if (preg_match('/^([^\w\s\\\\\/]).*?\1[a-z]*$/u', $pattern)) {
            if (@preg_match($pattern, $title) === 1) {
                return true;
            }
        }

        return mb_strpos($title, $pattern) !== false;
    }

    public function getBlacklistData() {
        $blacklist = $this->getSystemConfigurationValue('blacklist') ?? $this->getSystemConfigurationValue('blacklist_title_keywords') ?? [];
        return implode(PHP_EOL, $blacklist);
    }

    public function getWhitelistData() {
        $whitelist = $this->getSystemConfigurationValue('whitelist') ?? [];
        return implode(PHP_EOL, $whitelist);
    }
}
