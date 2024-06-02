<?php

/**
 * This file is part of the AssetManager Project.
 *
 * @package     AssetManager
 * @author      Anatolii Belianin <belianianatoli@gmail.com>
 * @license     See LICENSE.md for license information
 * @link        https://github.com/abeliani/asset-manager
 */

namespace Abeliani\AssetManager;

use Abeliani\AssetManager\Bundle\BundleInterface;

interface AssetManagerInterface
{
    public const CATEGORY_TOP = 'top';
    public const CATEGORY_BOTTOM = 'bottom';
    public const CATEGORY_COMMON = 'common';

    public function addBundle(BundleInterface $bundle, string $category): void;

    public function process(string $category): string;

    public function getAssertsPath(string $bundleClass): string;
}
