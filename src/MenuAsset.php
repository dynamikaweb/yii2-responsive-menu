<?php

namespace dynamikasolucoesweb\responsive;

class MenuAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/dynamikasolucoesweb/yii2-responsive-menu/assets';

    public $files = [
        'wrapper' => 'menu-wrapper.html',
        'first' => 'menu-first.html',
        'second' => 'menu-second.html',
        'forest' => 'menu-forest.html',
        'link' => 'menu-link.html',
        //'root' => 'menu-root.html',
        'sub' => 'menu-sub.html'
    ];

    public $css = [
        'css/style.css'
    ];

    public $js = [
        'js/modernizr.custom.js',
        'js/dlmenu.js',
        'js/script.js',
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