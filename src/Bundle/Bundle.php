<?php

/**
 * This file is part of the AssetManager Project.
 *
 * @package     AssetManager
 * @author      Anatolii Belianin <belianianatoli@gmail.com>
 * @license     See LICENSE.md for license information
 * @link        https://github.com/abeliani/asset-manager
 */

declare(strict_types=1);

namespace Abeliani\AssetManager\Bundle;

use Abeliani\AssetManager\Tag\TagConfigInterface;

abstract class Bundle implements BundleInterface
{
    private ?string $path = null;

    public function name(): string
    {
        return '';
    }

    public function getDistPaths(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [];
    }

    public function getPath(): string
    {
        if ($this->path === null) {
            $this->path = dirname((new \ReflectionClass(static::class))->getFileName());
        }

        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getTags(): TagConfigInterface|\SplFixedArray|array
    {
        return [];
    }
}
