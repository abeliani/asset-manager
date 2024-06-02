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

class AssetManagerTest extends Unit
{
    protected \UnitTester $tester;

    private string $bundleClass;
    private AssetManager $manager;
    private BundleInterface|MockObject $bundle;

    protected function setUp(): void
    {
        $this->bundle = $this->createMock(BundleInterface::class);
        $this->bundle->method('getPath')->willReturn(codecept_data_dir('concrete'));
        $this->bundleClass = get_class($this->bundle);

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

    public function testSingleCss(): void
    {
        $cssPath = '/css/style1.css';
        $this->bundle->method('getTags')
            ->willReturnCallback(static fn(): TagInterface => new Css($cssPath));

        $this->manager->addBundle($this->bundle);

        $tagPattern = '~<link href="//localhost/assets/\w+/css/style1\.css" rel="stylesheet">~';
        $this->assertMatchesRegularExpression($tagPattern, $this->manager->process());

        $this->tester->openFile("{$this->manager->getAssertsPath($this->bundleClass)}/{$cssPath}");
        $this->tester->seeFileContentsEqual('body{margin:0}');
    }

    public function testMergeTwoFiles(): void
    {
        $firstCssPath = '/css/style1.css';
        $secondCssPath = '/css/style2.css';

        $this->bundle->method('getTags')
            ->willReturnCallback(static fn(): TagInterface => new Css($firstCssPath, $secondCssPath));

        $this->manager->addBundle($this->bundle);

        $this->assertStringContainsString('/css/style1.css', $this->manager->process());
        $this->assertStringNotContainsString('/css/style2.css', $this->manager->process());

        $this->tester->openFile("{$this->manager->getAssertsPath($this->bundleClass)}/{$firstCssPath}");
        $this->tester->seeFileContentsEqual('body{margin:0}menu{margin:3px 4px 3px 4px}');
    }

    public function testSetAttributeToTag(): void
    {
        $this->bundle->method('getTags')->willReturnCallback(
                static fn(): TagInterface => (new Css('/css/style1.css'))->addAttr('media', 'print')
        );
        $this->manager->addBundle($this->bundle);

        $resultTmpl = '/css/style1.css" rel="stylesheet" media="print">';
        $this->assertStringContainsString($resultTmpl, $this->manager->process());
    }

    public function testJsMerge(): void
    {
        $resultScriptPath = '/js/script1.js';
        $scripts = [$resultScriptPath, '/js/script2.js'];

        $this->bundle->method('getTags')
            ->willReturnCallback(static fn(): TagInterface => (new Js(...$scripts)));

        $this->manager->addBundle($this->bundle);

        $this->assertStringContainsString('/js/script1.js', $this->manager->process());
        $this->assertStringNotContainsString('/js/script2.js', $this->manager->process());

        $this->tester->openFile("{$this->manager->getAssertsPath($this->bundleClass)}{$resultScriptPath}");
        $this->tester->seeFileContentsEqual("alert('Hi!');console.log('am here')");
    }

    public function testJsSeparated(): void
    {
        $this->bundle->method('getTags')->willReturnCallback(
            static fn(): array => [
                new Js('/js/script1.js'),
                new Js('/js/script2.js')
            ]
        );

        $this->manager->addBundle($this->bundle);

        $this->assertStringContainsString('/js/script1.js', $this->manager->process());
        $this->assertStringContainsString('/js/script2.js', $this->manager->process());

        $this->tester->openFile("{$this->manager->getAssertsPath($this->bundleClass)}/js/script1.js");
        $this->tester->seeFileContentsEqual("alert('Hi!')");

        $this->tester->openFile("{$this->manager->getAssertsPath($this->bundleClass)}/js/script2.js");
        $this->tester->seeFileContentsEqual("console.log('am here')");
    }

    public function testRelativePath(): void
    {
        $this->bundle->method('getTags')->willReturnCallback(
            static fn(): TagInterface =>  (new Js('/js/script1.js'))->relative()
        );

        $this->manager->addBundle($this->bundle);

        $tagPattern = '~<script src="/assets/\w+/js/script1\.js"></script>~';
        $this->assertMatchesRegularExpression($tagPattern, $this->manager->process());
    }

    public function testBundleNameUsing(): void
    {
        $this->bundle = $this->createMock(BundleInterface::class);
        $this->bundle->method('getPath')->willReturn(codecept_data_dir());

        $this->bundle->method('name')
            ->willReturn('concrete');
        $this->bundle->method('getTags')
            ->willReturnCallback(static function(): \SplFixedArray {
            $scripts = new \SplFixedArray(2);
            $scripts[0] = new Js('/js/script1.js');
            $scripts[1] = new Js('/js/script2.js');

            return $scripts;
        });

        $this->manager->addBundle($this->bundle);

        $this->assertStringContainsString('/js/script1.js', $this->manager->process());
        $this->assertStringContainsString('/js/script2.js', $this->manager->process());
    }
}
