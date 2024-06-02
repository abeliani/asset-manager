<?php

/**
 * This file is part of the AssetManager Project.
 *
 * @package     AssetManager
 * @author      Anatolii Belianin <belianianatoli@gmail.com>
 * @license     See LICENSE.md for license information
 * @link        https://github.com/abeliani/asset-manager
 */

namespace Abeliani\AssetManager\Bundle;

use Abeliani\AssetManager\Tag\TagConfigInterface;

interface BundleInterface
{
    /**
     * @return TagConfigInterface|\SplFixedArray<TagConfigInterface>|TagConfigInterface[]
     */
    public function getTags(): TagConfigInterface|\SplFixedArray|array;

    public function name(): string;

    public function getDistPaths(): array;

    public function getPath(): string;

    public function getDependencies(): array;
}
