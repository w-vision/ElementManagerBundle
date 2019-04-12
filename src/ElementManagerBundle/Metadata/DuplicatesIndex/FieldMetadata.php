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

namespace ElementManagerBundle\Metadata\DuplicatesIndex;

class FieldMetadata implements FieldMetadataInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $config;

    /**
     * @param string $name
     * @param array  $config
     */
    public function __construct(string $name, array $config)
    {
        $this->name = $name;
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function hasConfig(string $name): bool
    {
        return array_key_exists($name, $this->config);
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig(string $name)
    {
        if (!$this->hasConfig($name)) {
            throw new \InvalidArgumentException(sprintf('Key %s not found in config', $name));
        }

        return $this->config[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function getSimilarityIdentifier(): ?string
    {
        return $this->hasConfig('similarity') ? $this->getConfig('similarity') : null;
    }

   /**
     * {@inheritDoc}
     */
    public function getTransformerIdentifier(): ?string
    {
        return $this->hasConfig('transformer') ? $this->getConfig('transformer') : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetaphone(): bool
    {
        return $this->hasConfig('metaphone') ? $this->getConfig('metaphone') : null;
    }

        /**
     * {@inheritDoc}
     */
    public function getSoundex(): bool
    {
        return $this->hasConfig('soundex') ? $this->getConfig('soundex') : null;
    }
}