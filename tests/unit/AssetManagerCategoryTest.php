<?php

/**
 * This file is part of the AssetManager Project.
 *
 * @package     AssetManager
 * @author      Anatolii Belianin <belianianatoli@gmail.com>
 * @license     See LICENSE.md for license information
 * @link        https://github.com/abeliani/asset-manager
 */

namespace Abeliani\AssetManager\Tests\unit;

use Abeliani\AssetManager\AssetManager;
use Abeliani\AssetManager\AssetManagerInterface;
use Abeliani\AssetManager\Bundle\BundleInterface;
use Abeliani\AssetManager\Tag\Css;
use Abeliani\AssetManager\Tag\Js;
use Abeliani\AssetManager\Tag\TagConfigInterface;
use Abeliani\AssetManager\Tag\TagExtractorInterface;
use Codeception\Test\Unit;

class AssetManagerCategoryTest extends Unit
{
    protected \UnitTester $tester;

    private readonly AssetManager $manager;

    protected function _before(): void
    {
        $this->manager = new AssetManager(
            'https://localhost',
            codecept_output_dir('runtime'),
            codecept_output_dir('assets'),
        );
    }

    protected function _after(): void
    {
        $this->tester->cleanDir(codecept_output_dir());
    }

    public function testGetTagsByCategory(): void
    {
        $tagCommon = new Css('/css/style1.css');
        $bundle = $this->createMock(BundleInterface::class);
        $bundle->method('getPath')->willReturn(codecept_data_dir('concrete'));
        $bundle->method('getTags')->willReturnCallback(static fn(): TagConfigInterface => $tagCommon);

        $this->manager->addBundle($bundle);

        $tagTop = new Css('/css/style2.css');
        $bundle = $this->createMock(BundleInterface::class);
        $bundle->method('getPath')->willReturn(codecept_data_dir('concrete'));
        $bundle->method('getTags')->willReturnCallback(static fn(): TagConfigInterface => $tagTop);

        $this->manager->addBundle($bundle, AssetManagerInterface::CATEGORY_TOP);

        $tagBottom = new Js('/js/script1.js');
        $bundle = $this->createMock(BundleInterface::class);
        $bundle->method('getPath')->willReturn(codecept_data_dir('concrete'));
        $bundle->method('getTags')->willReturnCallback(static fn(): TagConfigInterface => $tagBottom);

        $this->manager->addBundle($bundle, AssetManagerInterface::CATEGORY_BOTTOM);

        $this->assertStringContainsString(
            '/css/style1.css',
            $this->manager->process()
        );

        $this->assertStringContainsString(
            '/css/style2.css',
            $this->manager->process(AssetManagerInterface::CATEGORY_TOP)
        );

        $this->assertStringContainsString(
            '/js/script1.js',
            $this->manager->process(AssetManagerInterface::CATEGORY_BOTTOM)
        );
    }
}
