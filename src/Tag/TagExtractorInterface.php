<?php

/**
 * This file is part of the AssetManager Project.
 *
 * @package     AssetManager
 * @author      Anatolii Belianin <belianianatoli@gmail.com>
 * @license     See LICENSE.md for license information
 * @link        https://github.com/abeliani/asset-manager
 */

namespace Abeliani\AssetManager\Tag;

interface TagExtractorInterface
{
    /**
     * Render a tag
     * An example:
     *
     *      <script src="script.js" async></script>
     *
     * @param string $src
     * @return string
     */
    public function render(string $src): string;

    /**
     * @return string[]|string
     */
    public function getSrc(): array|string;

    /**
     * @return string
     */
    public function getTagClass(): string;

    /**
     * @return bool
     */
    public function isRelative(): bool;

    /**
     * @return bool
     */
    public function isOptimize(): bool;

    /**
     * @return bool
     */
    public function isWithTimestamp(): bool;

    /**
     * @return bool
     */
    public function isREmote(): bool;

    /**
     * @param TagHandler $processor
     * @return string|null
     */
    public function handle(TagHandler $processor): ?string;
}
