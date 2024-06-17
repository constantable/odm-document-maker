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

use PHPUnit\Framework\ExpectationFailedException;
use Constantable\OdmDocumentMaker\Util\ClassSourceManipulator;
use Symfony\Bundle\MakerBundle\Util\YamlSourceManipulator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class MakerTestRunner
{
    private Filesystem $filesystem;
    private ?MakerTestProcess $executedMakerProcess = null;

    public function __construct(
        private MakerTestEnvironment $environment,
    ) {
        $this->filesystem = new Filesystem();
    }

    public function runMaker(array $inputs, string $argumentsString = '', bool $allowedToFail = false, array $envVars = []): string
    {
        $this->executedMakerProcess = $this->environment->runMaker($inputs, $argumentsString, $allowedToFail, $envVars);

        return $this->executedMakerProcess->getOutput();
    }

    /**
     * @return void
     */
    public function copy(string $source, string $destination)
    {
        $path = __DIR__.'/../../tests/fixtures/'.$source;

        if (!file_exists($path)) {
            throw new \Exception(sprintf('Cannot find file "%s"', $path));
        }

        if (is_file($path)) {
            $this->filesystem->copy($path, $this->getPath($destination), true);

            return;
        }

        // handle a directory copy
        $finder = new Finder();
        $finder->in($path)->files();

        foreach ($finder as $file) {
            $this->filesystem->copy($file->getPathname(), $this->getPath($file->getRelativePathname()), true);
        }
    }

    public function renderTemplateFile(string $source, string $destination, array $variables): void
    {
        $twig = new Environment(
            new FilesystemLoader(__DIR__.'/../../tests/fixtures')
        );

        $rendered = $twig->render($source, $variables);

        $this->filesystem->mkdir(\dirname($this->getPath($destination)));
        file_put_contents($this->getPath($destination), $rendered);
    }

    public function getPath(string $filename): string
    {
        return $this->environment->getPath().'/'.$filename;
    }

    public function readYaml(string $filename): array
    {
        return Yaml::parse(file_get_contents($this->getPath($filename)));
    }

    public function getExecutedMakerProcess(): MakerTestProcess
    {
        if (!$this->executedMakerProcess) {
            throw new \Exception('Maker process has not been executed yet.');
        }

        return $this->executedMakerProcess;
    }

    /**
     * @return void
     */
    public function modifyYamlFile(string $filename, \Closure $callback)
    {
        $path = $this->getPath($filename);
        $manipulator = new YamlSourceManipulator(file_get_contents($path));

        $newData = $callback($manipulator->getData());
        if (!\is_array($newData)) {
            throw new \Exception('The modifyYamlFile() callback must return the final array of data');
        }
        $manipulator->setData($newData);

        file_put_contents($path, $manipulator->getContents());
    }

    /**
     * @return void
     */
    public function runConsole(string $command, array $inputs, string $arguments = '')
    {
        $process = $this->environment->createInteractiveCommandProcess(
            $command,
            $inputs,
            $arguments
        );

        $process->run();
    }

    public function runProcess(string $command): void
    {
        MakerTestProcess::create($command, $this->environment->getPath())->run();
    }

    public function replaceInFile(string $filename, string $find, string $replace, bool $allowNotFound = false): void
    {
        $this->environment->processReplacement(
            $this->environment->getPath(),
            $filename,
            $find,
            $replace,
            $allowNotFound
        );
    }

    public function removeFromFile(string $filename, string $find, bool $allowNotFound = false): void
    {
        $this->environment->processReplacement(
            $this->environment->getPath(),
            $filename,
            $find,
            '',
            $allowNotFound
        );
    }

    public function configureMongoDatabase(bool $createSchema = true): void
    {
        $this->replaceInFile(
            '.env',
            'mongodb://localhost:27017',
            getenv('TEST_MONGODB_URL')
        );

        $this->replaceInFile(
            '.env',
            'MONGODB_DB=symfony',
            'MONGODB_DB='.getenv('TEST_MONGODB_DB')
        );

        $this->runConsole('doctrine:mongodb:schema:drop', [], '--env=test');

        if ($createSchema) {
            $this->runConsole('doctrine:mongodb:schema:create', [], '--env=test');
        }
    }

    public function runTests(): void
    {
        $internalTestProcess = MakerTestProcess::create(
            sprintf('php %s', $this->getPath('/bin/phpunit')),
            $this->environment->getPath())
            ->run(true)
        ;

        if ($internalTestProcess->isSuccessful()) {
            return;
        }

        throw new ExpectationFailedException(sprintf("Error while running the PHPUnit tests *in* the project: \n\n %s \n\n Command Output: %s", $internalTestProcess->getErrorOutput()."\n".$internalTestProcess->getOutput(), $this->getExecutedMakerProcess()->getErrorOutput()."\n".$this->getExecutedMakerProcess()->getOutput()));
    }

    public function writeFile(string $filename, string $contents): void
    {
        $this->filesystem->mkdir(\dirname($this->getPath($filename)));
        file_put_contents($this->getPath($filename), $contents);
    }

    /**
     * @return void
     */
    public function addToAutoloader(string $namespace, string $path)
    {
        $composerJson = json_decode(
            json: file_get_contents($this->getPath('composer.json')),
            associative: true,
            flags: \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES
        );

        $composerJson['autoload-dev']['psr-4'][$namespace] = $path;

        $this->filesystem->dumpFile(
            $this->getPath('composer.json'),
            json_encode($composerJson, \JSON_UNESCAPED_SLASHES | \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR)
        );

        $this->environment->runCommand('composer dump-autoload');
    }

    public function deleteFile(string $filename): void
    {
        $this->filesystem->remove($this->getPath($filename));
    }

    public function manipulateClass(string $filename, \Closure $callback): void
    {
        $contents = file_get_contents($this->getPath($filename));
        $manipulator = new ClassSourceManipulator(
            sourceCode: $contents,
            overwrite: true,
        );
        $callback($manipulator);

        file_put_contents($this->getPath($filename), $manipulator->getSourceCode());
    }

    public function getSymfonyVersion(): int
    {
        return $this->environment->getSymfonyVersionInApp();
    }

    public function doesClassExist(string $class): bool
    {
        return $this->environment->doesClassExistInApp($class);
    }
}
