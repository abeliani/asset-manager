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

final class TagExtractor implements TagExtractorInterface
{
    private ?ProtoTag $protoTag;
    private ?array $src;
    private ?bool $remote;
    private ?string $tagClass;
    private ?bool $minimize;
    private ?bool $relative;
    private ?bool $withTimestamp;

    public function setProtoTag(ProtoTag $protoTag): self
    {
        $this->protoTag = $protoTag;

        return $this;
    }

    public function setRemote(bool $remote): self
    {
        $this->remote = $remote;

        return $this;
    }

    public function setSrc(array $src): self
    {
        $this->src = $src;

        return $this;
    }

    public function setTagClass(string $tagClass): self
    {
        $this->tagClass = $tagClass;

        return $this;
    }

    public function setMinimize(bool $minimize): self
    {
        $this->minimize = $minimize;

        return $this;
    }

    public function setRelative(bool $relative): self
    {
        $this->relative = $relative;

        return $this;
    }

    public function setWithTimestamp(bool $withTimestamp): self
    {
        $this->withTimestamp = $withTimestamp;

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

    public function getTagClass(): string
    {
        return $this->tagClass;
    }

    public function isRemote(): bool
    {
        return $this->remote;
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

    public function __clone(): void
    {
        $this->protoTag = null;
        $this->src = null;
        $this->tagClass = null;
        $this->minimize = null;
        $this->relative = null;
        $this->withTimestamp = null;
    }
}
