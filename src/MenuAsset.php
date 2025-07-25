<?php

namespace dynamikasolucoesweb\responsive;

class MenuAsset extends \yii\web\AssetBundle
{
    public string $sourcePath = '@vendor/dynamikaweb/yii2-responsive-menu/assets';

    public array $files = [
        'forest' => 'menu-forest.html',
        'link' => 'menu-link.html',
        'root' => 'menu-root.html',
        'sub' => 'menu-sub.html'
    ];

    public array $css = [
        'css/style.css'
    ];

    public array $js = [
        'js/script.js',
        'js/dlmenu.js',
        'js/modernizr.js',
    ];

    public function getFile(string $view): string
    {
        return "{$this->sourcePath}/{$this->files[$view]}";
    }

    public function getTouchTimes(): string
    {
        return implode('-', array_map(function($file) {
            return filemtime($this->getFile($file));
        },
            array_keys($this->files)
        ));
    }
}