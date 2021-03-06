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

namespace Wvision\Bundle\ElementManagerBundle\Repository;

use CoreShop\Component\Resource\Repository\RepositoryInterface;
use Doctrine\ORM\NonUniqueResultException;
use Wvision\Bundle\ElementManagerBundle\Model\DuplicateInterface;
use Pimcore\Model\DataObject\Concrete;

interface DuplicateRepositoryInterface extends RepositoryInterface
{
    /**
     * @param Concrete $concrete
     *
     * @return DuplicateInterface[]
     */
    public function findForObject(Concrete $concrete): array;

    /**
     * @param string $className
     * @param string $algorithm
     *
     * @return DuplicateInterface[]
     */
    public function findExactByAlgorithm(string $className, string $algorithm): array;

    /**
     * @param string $className
     * @param string $md5
     * @param int    $crc
     *
     * @return DuplicateInterface|null
     *
     * @throws NonUniqueResultException
     */
    public function findForMd5AndCrc(string $className, string $md5, int $crc): ?DuplicateInterface;

    /**
     * @param Concrete $concrete
     */
    public function deleteForObject(Concrete $concrete);
}
