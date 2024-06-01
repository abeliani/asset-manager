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
use Abeliani\AssetManager\Tag\TagInterface;
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
        $tagCommon = new Css('/concrete/css/style1.css');
        $bundle = $this->createMock(BundleInterface::class);
        $bundle->method('getPath')->willReturn(codecept_data_dir());
        $bundle->method('getTags')->willReturnCallback(static fn(): TagInterface => $tagCommon);

        $this->manager->addBundle($bundle);

        $tagTop = new Css('/concrete/css/style2.css');
        $bundle = $this->createMock(BundleInterface::class);
        $bundle->method('getPath')->willReturn(codecept_data_dir());
        $bundle->method('getTags')->willReturnCallback(static fn(): TagInterface => $tagTop);

        $this->manager->addBundle($bundle, AssetManagerInterface::CATEGORY_TOP);

        $tagBottom = new Js('/concrete/js/script1.js');
        $bundle = $this->createMock(BundleInterface::class);
        $bundle->method('getPath')->willReturn(codecept_data_dir());
        $bundle->method('getTags')->willReturnCallback(static fn(): TagInterface => $tagBottom);

        $this->manager->addBundle($bundle, AssetManagerInterface::CATEGORY_BOTTOM);

        $this->assertStringContainsString(
            '/concrete/css/style1.css',
            $this->manager->process()
        );

        $this->assertStringContainsString(
            '/concrete/css/style2.css',
            $this->manager->process(AssetManagerInterface::CATEGORY_TOP)
        );

        $this->assertStringContainsString(
            '/concrete/js/script1.js',
            $this->manager->process(AssetManagerInterface::CATEGORY_BOTTOM)
        );
    }
}
