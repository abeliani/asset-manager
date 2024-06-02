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
use Abeliani\AssetManager\Bundle\BundleInterface;
use Abeliani\AssetManager\Tag\Css;
use Abeliani\AssetManager\Tag\TagConfigInterface;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;

class AssetManagerCacheTest extends Unit
{
    protected \UnitTester $tester;

    private readonly BundleInterface|MockObject $bundle;

    protected function _after(): void
    {
        $this->tester->cleanDir(codecept_output_dir());
    }

    public function testHardCache(): void
    {
        $manager = new AssetManager(
            'https://localhost',
            codecept_output_dir('runtime'),
            codecept_output_dir('assets'),
            true
        );

        $bundle = $this->createMock(BundleInterface::class);
        $bundle->method('getTags')
            ->willReturnCallback(static fn(): TagConfigInterface => new Css('/css/style1.css'));
        $bundle->method('getPath')
            ->willReturn(codecept_data_dir('concrete'));

        $manager->addBundle($bundle);

        $bundle->expects($this->atLeast(1))->method('getTags');
        $bundle->expects($this->atLeast(1))->method('getPath');
        $manager->process();

        $bundle->expects($this->never())->method('getTags');
        $bundle->expects($this->never())->method('getPath');
        $manager->process();
    }

    public function testTagProcessedOnce(): void
    {
        $manager = new AssetManager(
            'https://localhost',
            codecept_output_dir('runtime'),
            codecept_output_dir('assets'),
            false
        );

        $bundle = $this->createMock(BundleInterface::class);
        $bundle->method('getTags')
            ->willReturnCallback(static fn(): TagConfigInterface => (new Css('/css/style1.css')));
        $bundle->method('getPath')
            ->willReturn(codecept_data_dir('concrete'));

        $manager->addBundle($bundle);

        $bundle->expects($this->atLeast(1))->method('getPath');
        $bundle->expects($this->atLeast(1))->method('getTags');
        $manager->process();

        $bundle->expects($this->exactly(1))->method('getPath');
        $bundle->expects($this->atLeast(1))->method('getTags');
        $manager->process();
    }
}
