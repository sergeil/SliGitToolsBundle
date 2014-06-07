<?php

namespace Sli\VendorGitToolsBundle\Git\Status;

use Symfony\Component\Process\Process;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class GitStatusCommand extends Process
{
    // override
    public function __construct($cwd)
    {
        parent::__construct('git status', $cwd);
    }

    /**
     * @return ExecutionResult
     */
    public function runAndGetExecutionResult()
    {
        $this->run();

        return new ExecutionResult($this->getOutput());
    }
} 