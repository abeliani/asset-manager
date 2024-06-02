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

abstract class Tag implements TagInterface
{
    protected ProtoTag $protoTag;

    private array $src;
    private bool $minimize = false;
    private bool $relative = false;
    private bool $withTimestamp = false;

    public function __construct(string ...$src)
    {
        $this->initProto();
        $this->src = $src;
    }

    /**
     * @inheritDoc
     */
    public function addAttr(string $name, mixed $value = null): self
    {
        $this->protoTag->addAttribute($name, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function render(string $src): string
    {
        return $this->protoTag->render($src);
    }

    /**
     * @inheritDoc
     */
    public function relative(): self
    {
        $this->relative = true;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function minimize(): self
    {
        $this->minimize = true;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withTimeStamp(): self
    {
        $this->withTimestamp = true;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function handle(TagHandler $processor): ?string
    {
        return $processor($this);
    }

    public function getSrc(): string|array
    {
        return $this->src;
    }

    public function isOptimize(): bool
    {
        return $this->minimize;
    }

    public function isRelative(): bool
    {
        return $this->relative;
    }

    public function isWithTimestamp(): bool
    {
        return $this->withTimestamp;
    }

    abstract protected function initProto(): void;
}
