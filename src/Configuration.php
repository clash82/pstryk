<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('app');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('storage_raw_path')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('storage_images_path')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('storage_thumbs_path')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('albums')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('title')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('description')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('feed_url')
                                ->defaultValue('')
                            ->end()
                            ->integerNode('pagination_limit')
                                ->min(1)
                                ->defaultValue(5)
                            ->end()
                            ->integerNode('feed_limit')
                                ->min(1)
                                ->defaultValue(10)
                            ->end()
                            ->booleanNode('sitemap')
                                ->defaultFalse()
                            ->end()
                            ->arrayNode('domains')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->prototype('scalar')
                                ->end()
                            ->end()
                            ->integerNode('image_horizontal_max_width')
                                ->defaultValue(0)
                            ->end()
                            ->integerNode('image_vertical_max_height')
                                ->defaultValue(0)
                            ->end()
                            ->integerNode('thumb_horizontal_max_width')
                                ->defaultValue(0)
                            ->end()
                            ->integerNode('thumb_vertical_max_height')
                                ->defaultValue(0)
                            ->end()
                            ->arrayNode('tags')
                                ->isRequired()
                                ->children()
                                    ->scalarNode('author')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('copyright')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('description')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('watermark')
                                ->children()
                                    ->booleanNode('enabled')
                                        ->defaultFalse()
                                    ->end()
                                    ->scalarNode('file')
                                        ->cannotBeEmpty()
                                        ->defaultValue('')
                                    ->end()
                                    ->integerNode('width')
                                        ->defaultValue(0)
                                    ->end()
                                    ->integerNode('height')
                                        ->defaultValue(0)
                                    ->end()
                                    ->integerNode('transparency')
                                        ->min(1)
                                        ->max(100)
                                        ->defaultValue(100)
                                    ->end()
                                    ->enumNode('position')
                                        ->values(['top-left', 'top-right', 'bottom-left', 'bottom-right'])
                                        ->defaultValue('bottom-right')
                                    ->end()
                                    ->integerNode('horizontal_margin')
                                        ->defaultValue(0)
                                    ->end()
                                    ->integerNode('vertical_margin')
                                        ->defaultValue(0)
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
