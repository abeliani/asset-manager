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

namespace Abeliani\AssetManager;

use Abeliani\AssetManager\Bundle\BundleInterface;
use Abeliani\AssetManager\Tag\TagHandler;
use Abeliani\AssetManager\Tag\TagInterface;
use FilesystemIterator;

final class AssetManager implements AssetManagerInterface
{
    private string $host;
    private int $buildTime;
    private string $runtimePath;
    private string $buildFilePath;

    private array $bundles = [];
    private ?string $assetPath = null;

    public function __construct(
        string $host,
        string $runtimePath,
        private readonly string $publicPath,
        private readonly bool $lockBuild = true,
        private readonly bool $symlink = true,
        private readonly int $buildDirMode = 0755,
        string $buildFile = '.asset_build',
        string $runtimeDir = 'asset_manager',
    ) {
        $this->runtimePath = sprintf('%s/%s', $runtimePath, $runtimeDir);
        $this->buildFilePath = sprintf('%s/%s', rtrim($publicPath, '/'), trim($buildFile, '/'));
        $this->host = preg_replace('~https?://~', '', rtrim($host, '/'));

        if (!is_dir($this->runtimePath) && !mkdir($this->runtimePath, $buildDirMode, true)) {
            throw new \RuntimeException('Failed to create runtime directory');
        }

        if (!is_dir($publicPath) && !mkdir($publicPath, $buildDirMode, true)) {
            throw new \RuntimeException('Failed to create asset build directory');
        }

        if (!file_exists($this->buildFilePath) && !touch($this->buildFilePath) && !chmod($this->buildFilePath, 0644)) {
            throw new \RuntimeException('Failed to create asset build file');
        }

        $this->buildTime = filemtime($this->buildFilePath);
    }

    public function addBundle(BundleInterface $bundle, string $category = self::CATEGORY_COMMON): void
    {
        if (!preg_match( '~^[\w.-]+$~', $category)) {
            throw new \LogicException('Category name can contains only words, number and symbols: _.-');
        }

        $this->bundles[$category][] = $bundle;
    }

    /**
     * @throws \Exception
     */
    public function process(string $category = self::CATEGORY_COMMON): string
    {
        $processed = $distExcludedPaths = [];

        if (!file_exists($this->getAssertsPath()) && !mkdir($this->getAssertsPath(), $this->buildDirMode, true)) {
            throw new \RuntimeException('Filed to create public bundle directory');
        }

        if (empty($this->bundles)) {
            return '';
        }

        if (!array_key_exists($category, $this->bundles)) {
            throw new \LogicException(sprintf('Category bundle not found: %s', $category));
        }

        /** @var $bundles BundleInterface[] */
        $bundles = $this->bundles[$category];
        $buildFile = sprintf('%s/%s', $this->runtimePath, $category);

        if ($this->lockBuild && file_exists($buildFile)) {
            return file_get_contents($buildFile);
        }

        ob_start();
        foreach ($bundles as $bundle) {
            $distPaths = $bundle->getDistPaths();
            $bundlePath = $bundle->name() ? ($bundle->getPath() . "/{$bundle->name()}") : $bundle->getPath();
            array_walk($distPaths, fn (&$src) => $src = sprintf('%s/%s', $bundlePath, $src));

            foreach ($this->processBundle($bundle, $processed) as $bundleFilePath => $tag) {
                $publicUrl = str_replace(dirname($this->buildFilePath, 2), '', $bundleFilePath);
                $itemPath = $tag->isRelative() ? $publicUrl : sprintf('//%s%s', $this->host, $publicUrl);

                echo $tag->render($itemPath), PHP_EOL;
            }

            foreach ($distPaths as $dist) {
                if (!file_exists($dist)) {
                    throw new \LogicException(sprintf('Bundle `%s` path not found: `%s`', get_class($bundle), $dist));
                }

                /** @var \SplFileInfo[] $paths */
                $paths = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($dist, FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::SELF_FIRST
                );
                foreach ($paths as $path) {
                    $distRelative = str_replace($bundle->getPath(), '', $path->getRealPath());
                    $publicDistPath = sprintf('%s/%s', $this->assetPath, ltrim($distRelative, '/'));

                    if (in_array($distRelative, $distExcludedPaths)) {
                        continue;
                    }

                    if (!file_exists($publicDistPath)) {
                        if ($path->isDir()) {
                            mkdir($publicDistPath, $this->buildDirMode, true);
                            continue;
                        } else {
                            $dir = dirname($publicDistPath);
                            !file_exists($dir) and mkdir($dir, $this->buildDirMode, true);
                        }

                        if ($this->symlink) {
                            symlink($path->getRealPath(), $publicDistPath);
                            continue;
                        }

                        copy($path->getRealPath(), $publicDistPath);
                    }
                }
            }
        }

        if (($build = ob_get_clean()) && (file_put_contents($buildFile, $build) === false)) {
            throw new \Exception('Failed to save asset build');
        }

        if (!touch($this->buildFilePath, $this->buildTime)) {
            throw new \Exception('Failed to fix asset build time');
        }

        return $build;
    }

    public function getAssertsPath(): string
    {
        if ($this->assetPath === null) {
            $this->assetPath = sprintf('%s/%s', $this->publicPath, $this->getAssertDirectorySalt());
        }

        return $this->assetPath;
    }

    /**
     * @param BundleInterface $bundle
     * @param array $processed
     * @return \Generator<string, TagInterface>
     */
    private function processBundle(BundleInterface $bundle, array &$processed): \Generator
    {
        $bundleClass = get_class($bundle);

        if (in_array($bundleClass, $processed)) {
            return;
        }

        foreach ($bundle->getDependencies() as $class) {
            if (!in_array($class, $processed)) {
                foreach ($this->bundles as $bundles) {
                    foreach ($bundles as $bundle) {
                        $this->processBundle($bundle, $processed);
                    }
                }
            }
        }

        $processed[] = $bundleClass;

        if (!is_iterable($bundle->getTags())) {
            $tags = new \SplFixedArray(1);
            $tags[0] = $bundle->getTags();
        } else {
            $tags = $bundle->getTags();
        }

        foreach ($tags as $tag) {
            $src = is_string($tag->getSrc()) ? $tag->getSrc() : $tag->getSrc()[0];
            $pi = pathinfo($src);

            $bundleDir = $this->getAssertsPath()
                . ($bundle->name() ? "/{$bundle->name()}/" : '/')
                . ltrim($pi['dirname'], '/');

            if (!is_dir($bundleDir) && !mkdir($bundleDir, $this->buildDirMode, true)) {
                throw new \RuntimeException('Filed to create public bundle directory');
            }

            $bundleFilePath = sprintf('%s/%s', $bundleDir, $pi['basename']);

            if (!file_exists($bundleFilePath) || ($this->buildTime > filemtime($bundleFilePath))) {
                $bundlePath = $bundle->name() ? ($bundle->getPath() . "/{$bundle->name()}/") : $bundle->getPath();

                if (!$bundlePath) {
                    throw new \RuntimeException(sprintf('Filed to determine path of %s', get_class($bundle)));
                }

                if (!$handled = $tag->handle(new TagHandler($bundlePath))) {
                    continue;
                }

                if (!file_put_contents($bundleFilePath, $handled)) {
                    throw new \RuntimeException('Filed to create public bundle file');
                }
            }

            yield $bundleFilePath => $tag;
        }
    }

    private function getAssertDirectorySalt(): string
    {
        return substr(md5((string) $this->buildTime), 0, 10);
    }
}
