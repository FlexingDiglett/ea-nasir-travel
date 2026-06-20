<?php

class Translator {
    private $lang = 'en';
    private $translations = [];

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_GET['lang'])) {
            $allowed_langs = ['en', 'it', 'de', 'es'];
            if (in_array($_GET['lang'], $allowed_langs)) {
                $_SESSION['lang'] = $_GET['lang'];
            }
        }

        $this->lang = $_SESSION['lang'] ?? 'en';

        $langFile = __DIR__ . '/../lang/' . $this->lang . '.php';

        if (file_exists($langFile)) {
            $this->translations = require $langFile;
        } else {
            $fallbackFile = __DIR__ . '/../lang/en.php';
            $this->translations = file_exists($fallbackFile) ? require $fallbackFile : [];
        }
    }
        
    public function get($key) {
        return $this->translations[$key] ?? $key;
    }

    public function getCurrentLang() {
        return $this->lang;
    }
}