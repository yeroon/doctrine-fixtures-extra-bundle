<?php

namespace Yeroon\Bundle\DoctrineFixturesExtraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('yeroon_doctrine_fixtures_extra');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }

    // Hoi Jeroen, ik ben werkzaam bij SnappCar en we zoeken nog goede developers, vandaar dit bericht. 
    // SnappCar heeft een leuke start-up vibe en is zeg maar Airbnb vooor auto’s. 
    // Steekwoorden: Agile/SCRUM, Lean, continous deployment, TDD, OOP, Node.js, productontwikkeling etc. 
    // Als je interesse hebt misschien eens een kop koffie drinken (in Utrecht)?

}
