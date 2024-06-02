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
use PHPUnit\Framework\MockObject\MockObject;

class AssetManagerFolderTest extends Unit
{
    protected \UnitTester $tester;

    private readonly AssetManager $manager;

    protected function _before(): void
    {
        $this->manager = new AssetManager(
            'https://localhost',
            codecept_output_dir('runtime'),
            codecept_output_dir('assets'),
            false,
        );
    }

    protected function _after(): void
    {
        $this->tester->cleanDir(codecept_output_dir());
    }

    public function testGetTagsByCategory(): void
    {
        $tagCommon = new Css('/css/style1.css');
        $bundle = $this->getUniqMock();
        $bundle->method('getPath')->willReturn(codecept_data_dir('concrete'));
        $bundle->method('getTags')->willReturnCallback(static fn(): TagInterface => $tagCommon);

        $this->manager->addBundle($bundle);

        $tagTop = new Css('/css/style2.css');
        $bundle1 = $this->getUniqMock();
        $bundle1->method('getPath')->willReturn(codecept_data_dir('concrete'));
        $bundle1->method('getTags')->willReturnCallback(static fn(): TagInterface => $tagTop);

        $this->manager->addBundle($bundle1);

        $tagBottom = new Js('/js/script1.js');
        $bundle2 = $this->getUniqMock();
        $bundle2->method('getPath')->willReturn(codecept_data_dir('concrete'));
        $bundle2->method('getTags')->willReturnCallback(static fn(): TagInterface => $tagBottom);

        $this->manager->addBundle($bundle2, AssetManagerInterface::CATEGORY_BOTTOM);

        $this->assertStringContainsString(
            '/css/style1.css',
            $this->manager->process()
        );

        $assetPatPath1 = $this->manager->getAssertsPath(get_class($bundle));

        $this->assertStringContainsString(
            '/css/style2.css',
            $this->manager->process()
        );

        $assetPatPath2 = $this->manager->getAssertsPath(get_class($bundle1));

        $this->assertStringContainsString(
            '/js/script1.js',
            $this->manager->process(AssetManagerInterface::CATEGORY_BOTTOM)
        );

        $assetPatPath3 = $this->manager->getAssertsPath(get_class($bundle2));

        $this->assertNotEquals($assetPatPath1, $assetPatPath2);
        $this->assertNotEquals($assetPatPath2, $assetPatPath3);

        $this->tester->assertDirectoryExists($assetPatPath1);
        $this->tester->assertDirectoryExists($assetPatPath2);
        $this->tester->assertDirectoryExists($assetPatPath3);
    }

    private function getUniqMock(): MockObject|BundleInterface
    {
        return $this->getMockBuilder(BundleInterface::class)
            ->setMockClassName(sprintf('Mock_BundleInterface_%s', uniqid()))
            ->getMock();
    }
}
