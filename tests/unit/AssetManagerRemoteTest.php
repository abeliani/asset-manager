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
use Abeliani\AssetManager\Tag\TagConfigInterface;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;

class AssetManagerRemoteTest extends Unit
{
    protected \UnitTester $tester;

    private readonly AssetManager $manager;
    private readonly BundleInterface|MockObject $bundle;

    protected function setUp(): void
    {
        $this->bundle = $this->createMock(BundleInterface::class);

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

    public function testCssTag(): void
    {
        $tag = (new Css('https://best.cdn.server/css/style.css'))->remote()->addAttr('media', 'print');

        $this->bundle->method('getTags')
            ->willReturnCallback(static fn(): TagConfigInterface => $tag);

        $this->manager->addBundle($this->bundle);

        $this->assertStringContainsString(
            '<link href="https://best.cdn.server/css/style.css" rel="stylesheet" media="print">',
            $this->manager->process()
        );
    }

    public function testJsTag(): void
    {
        $tag = (new Js('https://best.cdn.server/js/app.js'))->remote()->addAttr('async');

        $this->bundle->method('getTags')
            ->willReturnCallback(static fn(): TagConfigInterface => $tag);

        $this->manager->addBundle($this->bundle);

        $this->assertStringContainsString(
            '<script src="https://best.cdn.server/js/app.js" async></script>',
            $this->manager->process()
        );
    }
}
