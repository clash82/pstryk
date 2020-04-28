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
                ->scalarNode('storage_raw_path')->end()
                ->scalarNode('storage_images_path')->end()
                ->scalarNode('storage_thumbs_path')->end()
                ->arrayNode('albums')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('title')->end()
                            ->integerNode('pagination_limit')->end()
                            ->integerNode('feed_limit')->end()
                            ->integerNode('image_horizontal_max_width')->end()
                            ->integerNode('image_vertical_max_height')->end()
                            ->integerNode('thumb_horizontal_max_width')->end()
                            ->integerNode('thumb_vertical_max_height')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
