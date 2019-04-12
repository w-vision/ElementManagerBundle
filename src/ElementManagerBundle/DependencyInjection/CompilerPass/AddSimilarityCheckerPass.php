<?php
/**
 * Element Manager
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2016-2018 w-vision AG (https://www.w-vision.ch)
 * @license    https://github.com/w-vision/ImportDefinitions/blob/master/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

namespace ElementManagerBundle\DependencyInjection\CompilerPass;

use ElementManagerBundle\DuplicateIndex\DataTransformer\ContainerDataTransformerFactory;
use ElementManagerBundle\DuplicateIndex\Similarity\ContainerSimilarityCheckerFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddSimilarityCheckerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(ContainerSimilarityCheckerFactory::class)) {
            return;
        }

        $dataTransformers = [];

        foreach ($container->findTaggedServiceIds('element_manager.similarity_checker', true) as $id => $attributes) {
            $definition = $container->getDefinition($id);

            if (!isset($attributes[0]['type'])) {
                $type = $definition->getClass();
            }
            else {
                $type = $attributes[0]['type'];
            }

            $dataTransformers[$type] = new Reference($id);
        }

        $container
            ->getDefinition(ContainerSimilarityCheckerFactory::class)
            ->replaceArgument(0, ServiceLocatorTagPass::register($container, $dataTransformers))
        ;
    }
}