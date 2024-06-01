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

interface TagInterface
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
     * If called optimization filters will be applied
     *
     * @return TagInterface
     */
    public function minimize(): self;

    /**
     * If is relative a host will not be added
     * An example:
     *
     *      /asset/qwer1/style.css
     *      //host.local/asset/qwer1/style.css
     *
     * @return TagInterface
     */
    public function relative(): self;

    /**
     * Add attribute to tag
     * An example:
     *
     *      addAttribute('media', 'print');
     *      // ...media="print">
     *
     *      addAttribute('my_attr', '');
     *      // ...my_attr="">
     *
     *      addAttribute('async');
     *      // ...async>
     * @param string $name
     * @param mixed|null $value
     * @return self
     */
    public function addAttr(string $name, mixed $value = null): self;

    /**
     * @return string[]|string
     */
    public function getSrc(): array|string;

    /**
     * @return bool
     */
    public function isRelative(): bool;

    /**
     * @return bool
     */
    public function isOptimize(): bool;

    /**
     * @param TagHandler $processor
     * @return string
     */
    public function handle(TagHandler $processor): ?string;
}
