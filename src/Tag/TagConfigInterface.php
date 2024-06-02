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

interface TagConfigInterface
{
    /**
     * If called optimization filters will be applied
     *
     * @return TagConfigInterface
     */
    public function minimize(): TagConfigInterface;

    /**
     * If is relative a host will not be added
     * An example:
     *
     *      /asset/qwer1/style.css
     *      //host.local/asset/qwer1/style.css
     *
     * @return TagConfigInterface
     */
    public function relative(): TagConfigInterface;

    /**
     * Add query part to a tag with timestamp
     * An example:
     *
     *      /asset/qwer1/style.css?ts=12345678
     *
     * @return TagConfigInterface
     */
    public function withTimeStamp(): TagConfigInterface;

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
     * @return TagConfigInterface
     */
    public function addAttr(string $name, mixed $value = null): TagConfigInterface;

    /**
     * Extract tag data to render
     *
     * @param TagExtractor $extractor
     * @return void
     */
    public function extractor(TagExtractor $extractor): void;
}
