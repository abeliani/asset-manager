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

final class Css extends Tag
{
    public function initProto(): void
    {
        $this->protoTag = (new ProtoTag('link', 'href'))->addAttribute( 'rel', 'stylesheet');
    }
}
