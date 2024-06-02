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

use Abeliani\AssetManager\Tag;
use Abeliani\CssJsHtmlOptimizer\{Css, Js};

class TagHandler
{
    public function __construct(private readonly string $bundlePath)
    {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(TagExtractorInterface $tag): ?string
    {
        switch ($tag->getTagClass()) {
            case Tag\Js::class:
                $document = Js\Parser\Document::class;
                $optimizer = Js\Optimizer\Optimizer::class;
                break;
            case Tag\Css::class:
                $document = Css\Parser\Document::class;
                $optimizer = Css\Optimizer\Optimizer::class;
                break;
            default:
                throw new \LogicException(sprintf('Unknown tag type: %s', get_class($tag)));
        }

        $src = is_string($tag->getSrc()) ? [$tag->getSrc()] : $tag->getSrc();

        for ($i = 0, $sourcesData = []; $i < count($src); $i++) {
            $path = sprintf('%s/%s', rtrim($this->bundlePath, '/'), ltrim($src[$i], '/'));

            if (file_exists($path)) {
                $sourcesData[] = new $document(file_get_contents($path));
            }
        }

        if (empty($sourcesData)) {
            return null;
        }

        return (new $optimizer($sourcesData, $tag->isOptimize() ? null : []))->do()->flush();
    }
}
