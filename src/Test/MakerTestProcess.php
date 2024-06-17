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

use Symfony\Component\Process\Process;

/**
 * @author Sadicov Vladimir <sadikoff@gmail.com>
 *
 * @internal
 */
final class MakerTestProcess
{
    private Process $process;

    private function __construct($commandLine, $cwd, array $envVars, $timeout)
    {
        $this->process = \is_string($commandLine)
            ? Process::fromShellCommandline($commandLine, $cwd, null, null, $timeout)
            : new Process($commandLine, $cwd, null, null, $timeout);

        $this->process->setEnv($envVars);
    }

    public static function create($commandLine, $cwd, array $envVars = [], $timeout = null): self
    {
        return new self($commandLine, $cwd, $envVars, $timeout);
    }

    public function setInput($input): self
    {
        $this->process->setInput($input);

        return $this;
    }

    public function run($allowToFail = false, array $envVars = []): self
    {
        $this->process->run(null, $envVars);

        if (!$allowToFail && !$this->process->isSuccessful()) {
            throw new \Exception(sprintf('Error running command: "%s". Output: "%s". Error: "%s"', $this->process->getCommandLine(), $this->process->getOutput(), $this->process->getErrorOutput()));
        }

        return $this;
    }

    public function isSuccessful(): bool
    {
        return $this->process->isSuccessful();
    }

    public function getOutput(): string
    {
        return $this->process->getOutput();
    }

    public function getErrorOutput(): string
    {
        return $this->process->getErrorOutput();
    }
}
