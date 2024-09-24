<?php

declare(strict_types=1);

namespace Okvpn\Bundle\DatadogBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('okvpn_datadog');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode->children()
            ->arrayNode('handle_exceptions')
                ->children()
                    ->arrayNode('skip_instanceof')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('skip_capture')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('skip_hash')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('skip_wildcard')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('skip_command')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
            ->enumNode('exception')
                ->values(['all', 'uncaught', 'none'])
                ->defaultValue('all')
            ->end()
            ->scalarNode('host')
                ->defaultValue('127.0.0.1')
            ->end()
            ->integerNode('port')
                ->defaultValue(8125)
            ->end()
            ->scalarNode('namespace')
                ->defaultNull()
            ->end()
            ->arrayNode('tags')
                ->prototype('scalar')->end()
            ->end()
            ->booleanNode('doctrine')
                ->defaultTrue()
            ->end()
            ->scalarNode('dedup_path')
                ->defaultNull()
            ->end()
            ->scalarNode('artifacts_path')
                ->defaultNull()
            ->end()
            ->integerNode('dedup_keep_time')
                ->defaultValue(7 * 86400)
            ->end()
            ->booleanNode('profiling')
                ->defaultFalse()
            ->end();

        $this->addClientsSection($rootNode);

        return $treeBuilder;
    }

    private function addClientsSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode->children()
            ->arrayNode('clients')
            ->useAttributeAsKey('alias', false)
            ->beforeNormalization()
                ->always()
                ->then(static function ($v) {
                    if (is_iterable($v)) {
                        foreach ($v as $name => $client) {
                            if (is_string($client)) {
                                $client = ['dsn' => $client];
                            }
                            $client['alias'] = $name;
                            $v[$name] = $client;
                        }
                    }
                    return $v;
                })
            ->end()
            ->arrayPrototype()
                ->children()
                    ->scalarNode('alias')->isRequired()->end()
                    ->scalarNode('dsn')->isRequired()->end()
                ->end()
            ->end()
            ->end();
    }
}
