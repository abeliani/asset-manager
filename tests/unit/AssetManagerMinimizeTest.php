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
use Abeliani\AssetManager\Tag\Js;
use Abeliani\AssetManager\Tag\TagInterface;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;

class AssetManagerMinimizeTest extends Unit
{
    protected \UnitTester $tester;

    private readonly AssetManager $manager;
    private readonly BundleInterface|MockObject $bundle;

    protected function setUp(): void
    {
        $this->bundle = $this->createMock(BundleInterface::class);
        $this->bundle->method('getPath')->willReturn(codecept_data_dir());

        parent::setUp();
    }

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

    public function testOptimizeCss(): void
    {
        $assetCssPath = "{$this->manager->getAssertsPath()}/concrete/css/style2.css";
        $tag = new Css('/concrete/css/style2.css');

        $this->bundle->method('getTags')->willReturnCallback(static fn(): TagInterface => $tag);
        $this->manager->addBundle($this->bundle);

        $this->assertStringContainsString('/concrete/css/style2.css', $this->manager->process());

        $this->tester->openFile($assetCssPath);
        $this->tester->seeFileContentsEqual('menu{margin:3px 4px 3px 4px}');
    }

    public function testMinimizeCss(): void
    {
        $assetCssPath = "{$this->manager->getAssertsPath()}/concrete/css/style2.css";
        $tagMinimized = (new Css('/concrete/css/style2.css'))->minimize();

        $this->bundle->method('getTags')->willReturnCallback(static fn(): TagInterface => $tagMinimized);
        $this->manager->addBundle($this->bundle);

        $this->assertStringContainsString('/concrete/css/style2.css', $this->manager->process());

        $this->tester->openFile($assetCssPath);
        $this->tester->seeFileContentsEqual('menu{margin:3px 4px}');
    }

    public function testOptimizeJs(): void
    {
        $assetCssPath = "{$this->manager->getAssertsPath()}/concrete/js/plugin.js";
        $tag = new Js('/concrete/js/plugin.js');

        $this->bundle->method('getTags')->willReturnCallback(static fn(): TagInterface => $tag);
        $this->manager->addBundle($this->bundle);

        $this->assertStringContainsString('/concrete/js/plugin.js', $this->manager->process());

        $this->tester->openFile($assetCssPath);
        $this->tester->seeFileContentsEqual("function test(){console.log('Minimized')}test()");
    }

    public function testMinimizeJs(): void
    {
        $assetCssPath = "{$this->manager->getAssertsPath()}/concrete/js/plugin.js";
        $tagMinimized = (new Js('/concrete/js/plugin.js'))->minimize();

        $this->bundle->method('getTags')->willReturnCallback(static fn(): TagInterface => $tagMinimized);
        $this->manager->addBundle($this->bundle);

        $this->assertStringContainsString('/concrete/js/plugin.js', $this->manager->process());

        $this->tester->openFile($assetCssPath);
        $this->tester->seeFileContentsEqual("function o_1(){console.log('Minimized')}o_1()");
    }
}