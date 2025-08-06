<?php

namespace dynamikasolucoesweb\responsive;

use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Html;
use Yii;

class Menu extends \yii\base\Widget
{
    const NIVEL_ROOT = 'r';
    const NIVEL_SUB = 's';
    const NIVEL_SUB_THIRD = 'l';

    public array $items = [];
    public int $maxItems = 5;

    private $_asset;
    private string $_key_cache;

    /**
     * Renders the menu.
     */
    public function run()
    {
        /** config widget */
        $this->_asset = MenuAsset::register($this->view);
        $this->_key_cache = $this->generateHash();

        /** retrieve content from cache */
        if (Yii::$app->cache->exists($this->_key_cache)) {
            echo Yii::$app->cache->get($this->_key_cache);
            return;
        }

        /** render widget */
        $content = $this->renderFile('wrapper', [
            'items' => $this->renderItems($this->normalizeItems(), self::NIVEL_ROOT),
            'id' => $this->getId(),
        ]);

        /** storage content in cache and print */
        Yii::$app->cache->set($this->_key_cache, $content);
        echo $content;
    }

    public function normalizeItems(): array
    {
        $newRoots = [];

        if (empty($this->items)) {
            return [];
        }

        // generate new roots
        foreach ($this->items as $oldRoot)
        {
            $oldRoot['items'] = ArrayHelper::getValue($oldRoot, 'items', []);
            $label = ArrayHelper::getValue($oldRoot, 'encode', true)? Html::encode($oldRoot['label']): $oldRoot['label'];
            $slug = Inflector::slug(strip_tags($oldRoot['label']));
            $target = ArrayHelper::getValue($oldRoot, 'target', '_self');
            $url = ArrayHelper::getValue($oldRoot, 'url', 'javascript:;');
            $newSubs = [];

            // automatic content root has a new sub menu
            if (!empty($oldRoot['content'])) {
                $url = 'javascript:;';
                $newSubs[] = [
                    'label' => $label,
                    'url' => $url,
                    'target' => $target,
                    'slug' => '_auto',
                    'content' => $oldRoot['content'],
                    'items' => []
                ];
            }

            // create sub menus
            foreach ($oldRoot['items'] as $oldSub) {
                // sub menu is a final link?
                if(empty($oldSub['items']) && empty($oldSub['content'])){
                    // last menu is no automatic or bigger
                    if((empty($newSubs) || (isset($newSubs[array_key_last($newSubs)]['items']) && count($newSubs[array_key_last($newSubs)]['items']) >= $this->maxItems)
                        || current($newSubs)['slug'] !== '_auto')) {
                        $newSubs[] = ['slug' => '_auto', 'url' => 'javascript:;'];
                    }
                    // add menu as link
                    $newSubs[array_key_last($newSubs)]['items'][] = [
                        'url' => ArrayHelper::getValue($oldSub, 'url', 'javascript:;'),
                        'target' => ArrayHelper::getValue($oldSub, 'target', '_self'),
                        'label' => ArrayHelper::getValue($oldSub, 'label', '???'),
                    ];
                    continue;
                }

                // add sub menu normal
                $newSubs[] = [
                    'slug' => Inflector::slug(ArrayHelper::getValue($oldSub, 'label', '_none')),
                    'target' => ArrayHelper::getValue($oldSub, 'target', '_self'),
                    'url' => ArrayHelper::getValue($oldSub, 'url', 'javascript:;'),
                    'content' => ArrayHelper::getValue($oldSub, 'content', null),
                    'label' => ArrayHelper::getValue($oldSub, 'label', null),
                    'items' => []
                ];

                // add links
                foreach($oldSub['items'] as $item) {
                    // balance quantity of links per sub menu
                    if (count($newSubs[array_key_last($newSubs)]['items']) >= $this->maxItems) {
                        $newSubs[] = ['slug' => '_auto', 'url' => 'javascript:;'];
                    }

                    // add link to sub menu
                    $newSubs[array_key_last($newSubs)]['items'][] = [
                        'url' => ArrayHelper::getValue($item, 'url', 'javascript:;'),
                        'target' => ArrayHelper::getValue($item, 'target', '_self'),
                        'label' => ArrayHelper::getValue($item, 'label', '???')
                    ];
                }
            }

            $newRoots[] = [
                'label' => $label,
                'slug' => $slug,
                'target' => $target,
                'url' => $url,
                'items' => $newSubs
            ];
        }

        return $newRoots;
    }
    
    protected function renderItems(array $items, string $nivel): string
    {
        switch ($nivel)
        {
            case self::NIVEL_ROOT: {
                return implode("\n", array_map(fn($item) => $this->renderFile('first', [
                    'items' => empty($item['items'])? null: Html::tag('ul', $this->renderItems($item['items'], self::NIVEL_SUB), ['class' => 'dl-submenu']),
                    'target' => $item['target'],
                    'label' => $item['label'],
                    'slug' => $item['slug'],
                    'url' => $item['url']
                ]),
                    $items
                ));
            }
            case self::NIVEL_SUB: {
                return implode("\n", array_map(fn($item) => $this->renderFile('second', [
                    'items' => empty($item['items'])? null: Html::tag('ul', $this->renderItems($item['items'], self::NIVEL_SUB_THIRD), ['class' => 'dl-submenu']),
                    'target' => $item['target'],
                    'label' => $item['label'],
                    'slug' => $item['slug'],
                    'url' => $item['url']
                ]),
                    $items
                ));
            }
            case self::NIVEL_SUB_THIRD: {
                return implode("\n", array_map(fn($item) => $this->renderFile('third', [
                    'target' => $item['target'],
                    'label' => $item['label'],
                    'url' => $item['url']
                ]),
                    $items
                ));
            }
        }
    }

    public function renderFile($view, $params = []): string
    {
        $placeholders = [];

        foreach ($params as $name => $value) {
            $placeholders['{' . $name . '}'] = $value;
        }

        return strtr(
            $this->view->renderFile($this->_asset->getFile($view)),
            $placeholders
        );
    }

    public function generateHash(): string
    {
        return strtr('menu-{hash_items}-{hash_assets}-{lastmod}-{id}', [
            '{hash_assets}' => md5($this->_asset->touchTimes),
            '{hash_items}' => md5(json_encode($this->items)),
            '{lastmod}' => getlastmod(),
            '{id}' => $this->getId()
        ]);
    }
}
