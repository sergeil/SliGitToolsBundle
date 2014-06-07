<?php

namespace Sli\VendorGitToolsBundle\Git\Status;

/**
 * Analyses output of "git status" command.
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class ExecutionResult
{
    private $textOutput;
    private $analyzedResult = array(
        'modified' => array(),
        'untracked' => array(),
        'new' => array(),
        'deleted' => array(),
        'renamed' => array(),
        'branch' => null
    );

    /**
     * @param string $textOutput
     */
    public function __construct($textOutput)
    {
        $this->textOutput = $textOutput;

        $this->analyze();
    }

    /**
     * @param array $output
     * @param string $token
     *
     * @return string[]
     */
    private function extractFileNames(array $output, $token)
    {
        $result = array();

        foreach ($output as $line) {
            if (substr($line, 0, strlen($token)) == $token) {
                $filename = trim(substr($line, strlen($token)));

                $result[] = $filename;
            }
        }

        return $result;
    }

    private function analyze()
    {
        $output = array();

        foreach (explode("\n", $this->textOutput) as $line) {
            $output[] = trim(substr($line, 1));
        }

        if (isset($output[0])) {
            $token = 'On branch ';
            if (substr($output[0], 0, strlen($token)) == $token) {
                $this->analyzedResult['branch'] = trim(substr($output[0], strlen($token)));
            }
        }

        $this->analyzedResult['modified'] = $this->extractFileNames($output, 'modified:');
        $this->analyzedResult['new'] = $this->extractFileNames($output, 'new file:');
        $this->analyzedResult['deleted'] = $this->extractFileNames($output, 'deleted:');

        foreach ($this->extractFileNames($output, 'renamed:') as $line) {
            $line = explode('->', $line);

            if (count($line) == 2) {
                $this->analyzedResult['renamed'][trim($line[0])] = trim($line[1]);
            }
        }

        $isSegmentFound = false;
        $isEmptyLineFound = false;
        foreach ($output as $line) {
            $line = trim($line);

            if ($line == 'Untracked files:') {
                $isSegmentFound = true;
            }
            if ($isSegmentFound && '' == $line) {
                $isEmptyLineFound = true;
            }

            if ($isSegmentFound && $isEmptyLineFound && '' != $line) {
                $this->analyzedResult['untracked'][] = $line;
            }
        }
    }

    /**
     * @return string[]
     */
    public function getModifiedFiles()
    {
        return $this->analyzedResult['modified'];
    }

    /**
     * @return string[]
     */
    public function getUntrackedFiles()
    {
        return $this->analyzedResult['untracked'];
    }

    /**
     * @return string[]
     */
    public function getNewFiles()
    {
        return $this->analyzedResult['new'];
    }

    /**
     * @return string[]
     */
    public function getDeletedFiles()
    {
        return $this->analyzedResult['deleted'];
    }

    /**
     * @return string[]
     */
    public function getRenamedFiles()
    {
        return $this->analyzedResult['renamed'];
    }

    /**
     * @return bool
     */
    public function hasChanges()
    {
        return count(array_merge(
            $this->getModifiedFiles(),
            $this->getUntrackedFiles(),
            $this->getNewFiles(),
            $this->getDeletedFiles(),
            $this->getRenamedFiles()
        )) > 0;
    }

    /**
     * @return string
     */
    public function getBranchName()
    {
        return $this->analyzedResult['branch'];
    }
}