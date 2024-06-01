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

namespace Abeliani\AssetManager\Tag;

class ProtoTag
{
    private array $attributes = [];

    public function __construct(
        private readonly string $name,
        private readonly string $srcName,
        private readonly bool $closed = false,
    ) {
    }

    public function addAttribute(string $name, mixed $value = null): self
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    public function render(string $src): string
    {
        return sprintf(
            '<%s %s="%s"%s>%s',
            $this->name,
            $this->srcName,
            $src,
            $this->renderAttributes(),
            $this->renderClosed(),
        );
    }

    private function renderClosed(): string
    {
        return $this->closed ? sprintf('</%s>', $this->name) : '';
    }

    private function renderAttributes(): string
    {
        foreach ($this->attributes as $name => $value) {
            $result[] = $value === null ? $name : sprintf('%s="%s"', $name, $value);
        }

        return empty($result) ? '' : sprintf(' %s', implode(' ', $result));
    }
}
