<?php

class Translator {
    private $cacheDir;
    private $translations = []; 
    
    public function __construct() {
        $this->cacheDir = __DIR__ . '/translation_cache';
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    public function translate($text) {
        if (empty($text)) {
            return $text;
        }
        
        $originalText = trim($text);
        $cacheKey = md5($originalText);
        
        if (isset($this->translations[$cacheKey])) {
            return $this->translations[$cacheKey];
        }
        
        $cacheFile = $this->cacheDir . '/' . $cacheKey . '.txt';
        if (file_exists($cacheFile)) {
            $translated = file_get_contents($cacheFile);
            $this->translations[$cacheKey] = $translated;
            return $translated;
        }
        
        $translatedText = $this->translateWithGoogle($originalText);
        
        if ($translatedText && $translatedText !== $originalText) {
            file_put_contents($cacheFile, $translatedText);
            $this->translations[$cacheKey] = $translatedText;
            return $translatedText;
        }
        
        return $originalText;
    }
    
    private function translateWithGoogle($text) {
        $text = urlencode($text);
        $url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=es&tl=en&dt=t&q=" . $text;
        
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ],
                'timeout' => 10
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response) {
            $result = json_decode($response, true);
            if (isset($result[0][0][0])) {
                return $result[0][0][0];
            }
        }
        
        return false;
    }
}

$GLOBALS['translator'] = null;

function tr($text) {
    if ($GLOBALS['translator'] === null) {
        $GLOBALS['translator'] = new Translator();
    }
    
    return $GLOBALS['translator']->translate($text);
}

function translate_array($array, $fields) {
    $result = $array;
    foreach ($fields as $field) {
        if (isset($result[$field])) {
            $result[$field] = tr($result[$field]);
        }
    }
    return $result;
}

?>