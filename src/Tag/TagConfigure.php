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

abstract class TagConfigure implements TagConfigInterface
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
     * @inheritDoc
     */
    public function extractor(TagExtractor $extractor): void
    {
        $extractor
            ->setSrc($this->src)
            ->setProtoTag($this->protoTag)
            ->setMinimize($this->minimize)
            ->setRelative($this->relative)
            ->setTagClass(static::class)
            ->setWithTimestamp($this->withTimestamp);
    }

    abstract protected function initProto(): void;
}
