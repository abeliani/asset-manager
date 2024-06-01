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
use Abeliani\AssetManager\Tag\TagInterface;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;

class AssetManagerDistTest extends Unit
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

    public function testCopyDist(): void
    {
        $tag = new Css('/concrete/css/style2.css');
        $this->bundle->method('getTags')->willReturnCallback(static fn(): TagInterface => $tag);
        $this->bundle->method('getPath')->willReturn(codecept_data_dir());
        $this->bundle->method('getDistPaths')->willReturn(['concrete/css/dist']);
        $this->manager->addBundle($this->bundle);

        $this->assertStringContainsString('/concrete/css/style2.css', $this->manager->process());
        $this->tester->seeFileFound("{$this->manager->getAssertsPath()}/concrete/css/dist/README.txt");
    }

    public function testCopyDistNotOptimized(): void
    {
        $tag = new Css('/concrete/css/style2.css');
        $this->bundle->method('getTags')->willReturnCallback(static fn(): TagInterface => $tag);
        $this->bundle->method('getPath')->willReturn(codecept_data_dir());
        $this->bundle->method('getDistPaths')->willReturn(['concrete/css']);
        $this->manager->addBundle($this->bundle);

        $this->assertStringContainsString('/concrete/css/style2.css', $this->manager->process());

        $this->tester->seeFileFound($optimized = "{$this->manager->getAssertsPath()}/concrete/css/style2.css");
        $this->tester->seeFileFound($distCopied = "{$this->manager->getAssertsPath()}/concrete/css/style1.css");
        $this->tester->seeFileFound('*', "{$this->manager->getAssertsPath()}/concrete/css/dist");

        $this->tester->openFile($optimized);
        $this->assertNotTrue(is_link($optimized));
        $this->tester->seeFileContentsEqual('menu{margin:3px 4px 3px 4px}');

        $this->tester->openFile($distCopied);
        $this->assertTrue(is_link($distCopied));
        $this->tester->seeInThisFile('This file is part of the AssetManager Project.');
    }
}
