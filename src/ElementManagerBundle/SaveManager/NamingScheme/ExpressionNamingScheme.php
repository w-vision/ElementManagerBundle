<?php
/**
 * Element Manager.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2016-2020 w-vision AG (https://www.w-vision.ch)
 * @license    https://github.com/w-vision/ImportDefinitions/blob/master/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

namespace Wvision\Bundle\ElementManagerBundle\SaveManager\NamingScheme;

use Pimcore\Bundle\CoreBundle\EventListener\Traits\PimcoreContextAwareTrait;
use Pimcore\Http\Request\Resolver\PimcoreContextResolver;
use Pimcore\Http\RequestHelper;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Service;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpressionNamingScheme implements NamingSchemeInterface
{
    use PimcoreContextAwareTrait;

    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param ExpressionLanguage     $expressionLanguage
     * @param PimcoreContextResolver $contextResolver
     * @param RequestStack           $requestStack
     */
    public function __construct(
        ExpressionLanguage $expressionLanguage,
        PimcoreContextResolver $contextResolver,
        RequestStack $requestStack
    ) {
        $this->expressionLanguage = $expressionLanguage;
        $this->requestStack = $requestStack;

        $this->setPimcoreContextResolver($contextResolver);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Concrete $object, array $options): void
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefaults([
            'parent_path' => '/',
            'archive_path' => '/_temp',
            'scheme' => '',
            'auto_prefix_path' => true,
            'skip_path_for_variant' => false,
            'initial_key_mapping' => null,
        ]);
        $optionsResolver->setRequired([
            'parent_path', 'archive_path', 'scheme', 'auto_prefix_path'
        ]);

        $options = $optionsResolver->resolve($options);

        $autoPrefixPath = $options['auto_prefix_path'];
        $parentPath = $object->getPublished() ? $options['parent_path'] : $options['archive_path'];

        $namingScheme = $this->expressionLanguage->evaluate(
            $options['scheme'],
            array_merge($options, ['object' => $object, 'path' => $parentPath])
        );

        // Map initial key to an object field
        if ($options['initial_key_mapping']) {
            $request = $this->requestStack->getMasterRequest();

            if (null !== $request && $this->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_ADMIN) &&
                $object->getKey() && $object->getId() === 0
            ) {
                $setter = sprintf('set%s', ucfirst($options['initial_key_mapping']));

                if (method_exists($object, $setter)) {
                    $object->$setter($object->getKey());
                }
            }
        }

        if (is_array($namingScheme)) {
            $key = $namingScheme[count($namingScheme) - 1];
            unset($namingScheme[count($namingScheme) - 1]);

            if ($autoPrefixPath) {
                $parentPath .= '/' . implode('/', $namingScheme);
            }
            else {
                $parentPath = '/' . implode('/', $namingScheme);
            }
        } else {
            $key = $namingScheme;
        }

        $object->setKey($key);
        $parentPath = $this->correctPath($parentPath);

        if (!$options['skip_path_for_variant'] || $object->getType() !== Concrete::OBJECT_TYPE_VARIANT) {
            $object->setParent(Service::createFolderByPath($parentPath));
        }

        if (!$object->getKey()) {
            $className = strtolower(ltrim(preg_replace(
                '/[A-Z]([A-Z](?![a-z]))*/',
                '_$0',
                $object->getClassName()
            ), '_'));
            $object->setKey(uniqid(sprintf('%s_', $className), true));
        }

        $object->setKey(Service::getUniqueKey($object));
    }

    /**
     * @param $path
     *
     * @return string
     */
    private function correctPath($path): string
    {
        return str_replace('//', '/', $path);
    }
}
