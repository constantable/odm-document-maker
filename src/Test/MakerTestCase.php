<?php

/*
 * Copyright (c) 2004-2020 Fabien Potencier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Constantable\OdmDocumentMaker\Test;

use Composer\Semver\Semver;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\MakerBundle\MakerInterface;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

abstract class MakerTestCase extends TestCase
{
    private ?KernelInterface $kernel = null;

    /**
     * @dataProvider getTestDetails
     *
     * @return void
     */
    public function testExecute(MakerTestDetails $makerTestDetails)
    {
        $this->executeMakerCommand($makerTestDetails);
    }

    abstract public function getTestDetails();

    abstract protected function getMakerClass(): string;

    protected function createMakerTest(): MakerTestDetails
    {
        return new MakerTestDetails($this->getMakerInstance($this->getMakerClass()));
    }

    /**
     * @return void
     */
    protected function executeMakerCommand(MakerTestDetails $testDetails)
    {
        if (!class_exists(Process::class)) {
            throw new \LogicException('The MakerTestCase cannot be run as the Process component is not installed. Try running "compose require --dev symfony/process".');
        }

        if (!$testDetails->isSupportedByCurrentPhpVersion()) {
            $this->markTestSkipped();
        }

        $testEnv = MakerTestEnvironment::create($testDetails);

        if ('7.0.x-dev' === $testEnv->getTargetSkeletonVersion() && $testDetails->getSkipOnSymfony7()) {
            $this->markTestSkipped('This test is skipped on Symfony 7');
        }

        // prepare environment to test
        $testEnv->prepareDirectory();

        if (!$this->hasRequiredDependencyVersions($testDetails, $testEnv)) {
            $this->markTestSkipped('Some dependencies versions are too low');
        }

        $makerRunner = new MakerTestRunner($testEnv);
        foreach ($testDetails->getPreRunCallbacks() as $preRunCallback) {
            $preRunCallback($makerRunner);
        }

        $callback = $testDetails->getRunCallback();
        $callback($makerRunner);

        // run tests
        $files = $testEnv->getGeneratedFilesFromOutputText();

        foreach ($files as $file) {
            $this->assertTrue($testEnv->fileExists($file), sprintf('The file "%s" does not exist after generation', $file));

            if (str_ends_with($file, '.twig')) {
                $csProcess = $testEnv->runTwigCSLint($file);

                $this->assertTrue($csProcess->isSuccessful(), sprintf('File "%s" has a twig-cs problem: %s', $file, $csProcess->getErrorOutput()."\n".$csProcess->getOutput()));
            }
        }
    }

    /**
     * @return void
     */
    protected function assertContainsCount(string $needle, string $haystack, int $count)
    {
        $this->assertEquals(1, substr_count($haystack, $needle), sprintf('Found more than %d occurrences of "%s" in "%s"', $count, $needle, $haystack));
    }

    private function getMakerInstance(string $makerClass): MakerInterface
    {
        if (null === $this->kernel) {
            $this->kernel = $this->createKernel();
            $this->kernel->boot();
        }

        // a cheap way to guess the service id
       // $serviceId ??= sprintf('maker.maker.odm_document_maker.command.%s', Str::asSnakeCase((new \ReflectionClass($makerClass))->getShortName()));
$serviceId = 'odm_document_maker.command.make_document_command';
        return $this->kernel->getContainer()->get($serviceId);
    }

    protected function createKernel(): KernelInterface
    {
        return new MakerTestKernel('dev', true);
    }

    private function hasRequiredDependencyVersions(MakerTestDetails $testDetails, MakerTestEnvironment $testEnv): bool
    {
        if (empty($testDetails->getRequiredPackageVersions())) {
            return true;
        }

        $installedPackages = json_decode($testEnv->readFile('vendor/composer/installed.json'), true, 512, \JSON_THROW_ON_ERROR);
        $packageVersions = [];

        foreach ($installedPackages['packages'] ?? $installedPackages as $installedPackage) {
            $packageVersions[$installedPackage['name']] = $installedPackage['version_normalized'];
        }

        foreach ($testDetails->getRequiredPackageVersions() as $requiredPackageData) {
            $name = $requiredPackageData['name'];
            $versionConstraint = $requiredPackageData['version_constraint'];

            if (!isset($packageVersions[$name])) {
                throw new \Exception(sprintf('Package "%s" is required in the test project at version "%s" but it is not installed?', $name, $versionConstraint));
            }

            if (!Semver::satisfies($packageVersions[$name], $versionConstraint)) {
                return false;
            }
        }

        return true;
    }
}
