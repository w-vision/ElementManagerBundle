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

namespace Wvision\Bundle\ElementManagerBundle\SaveManager;

use Pimcore\Model\DataObject\Concrete;

class ObjectSaveManager implements ObjectSaveManagerInterface
{
    /**
     * @var ObjectSaveHandlerInterface[]
     */
    protected $saveHandlers = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * {@inheritdoc}
     */
    public function preAdd(Concrete $object): void
    {
        if ($object->getPublished()) {
            $this->validateOnSave($object);
        }

        $this->applySaveHandlers($object, 'preAdd');

        // TODO: Should be a save handler
        /*if ($this->pimcoreContextResolver->getPimcoreContext() === PimcoreContextResolver::CONTEXT_ADMIN) {
            $this->applyNamingScheme($address);
        }*/
    }

    /**
     * {@inheritdoc}
     */
    public function postAdd(Concrete $object): void
    {
        $this->applySaveHandlers($object, 'postAdd');
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(Concrete $object): void
    {
        $this->applySaveHandlers($object, 'preUpdate');
        $this->validateOnSave($object);
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate(Concrete $object): void
    {
        $this->applySaveHandlers($object, 'postUpdate');
    }

    /**
     * {@inheritdoc}
     */
    public function preDelete(Concrete $object): void
    {
        $this->applySaveHandlers($object, 'preDelete');
    }

    /**
     * {@inheritdoc}
     */
    public function postDelete(Concrete $object): void
    {
        $this->applySaveHandlers($object, 'postDelete');
    }

    /**
     * {@inheritdoc}
     */
    public function validateOnSave(Concrete $object, bool $withDuplicatesCheck = true): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSaveHandlers(): array
    {
        return $this->saveHandlers;
    }

    /**
     * {@inheritdoc}
     */
    public function setSaveHandlers(array $saveHandlers): void
    {
        $this->saveHandlers = $saveHandlers;
    }

    /**
     * {@inheritdoc}
     */
    public function addSaveHandler(ObjectSaveHandlerInterface $saveHandler): void
    {
        $this->saveHandlers[] = $saveHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @param Concrete $concrete
     * @param          $saveHandlerMethod
     */
    private function applySaveHandlers(Concrete $concrete, $saveHandlerMethod): void
    {
        $saveHandlers = $this->getSaveHandlers();
        $postSaveMethod = 'post' . ucfirst($saveHandlerMethod);

        foreach ($saveHandlers as $handler) {
            if (method_exists($handler, $saveHandlerMethod)) {
                $handler->{$saveHandlerMethod}($concrete, $this->options);
            }

            if (in_array($saveHandlerMethod, ['preAdd', 'preUpdate'], true)) {
                $handler->preSave($concrete, $this->options);
            }

            if (in_array($saveHandlerMethod, ['postUpdate', 'postAdd'], true)) {
                $handler->postSave($concrete, $this->options);
            }
        }

        foreach ($saveHandlers as $handler) {
            if (!$handler instanceof PostObjectSaveHandlerInterface) {
                continue;
            }

            if (method_exists($handler, $postSaveMethod)) {
                $handler->{$postSaveMethod}($concrete, $this->options);
            }

            if (in_array($saveHandlerMethod, ['preAdd', 'preUpdate'], true)) {
                $handler->postPreSave($concrete, $this->options);
            }

            if (in_array($saveHandlerMethod, ['postUpdate', 'postAdd'], true)) {
                $handler->postPostSave($concrete, $this->options);
            }
        }
    }
}
