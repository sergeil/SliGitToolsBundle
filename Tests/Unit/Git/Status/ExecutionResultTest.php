<?php

namespace Sli\VendorGitToolsBundle\Tests\Unit\Git\Status;

use Sli\VendorGitToolsBundle\Git\Status\ExecutionResult;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class ExecutionResultTest extends \PHPUnit_Framework_TestCase
{
    private $sample1;
    /* @var ExecutionResult $er */
    private $er;

    public function setUp()
    {
        $this->sample1 = <<<TEXT
# On branch master
# Changes to be committed:
#   (use "git reset HEAD <file>..." to unstage)
#
#	modified:   app/AppKernel.php
#	new file:   app/phpunit.xml
#	renamed:    index.html -> index.htm
#
# Changes not staged for commit:
#   (use "git add <file>..." to update what will be committed)
#   (use "git checkout -- <file>..." to discard changes in working directory)
#
#	deleted:    README.md
#	modified:   app/config/config_dev.yml
#	modified:   app/kernel.json
#	modified:   composer.lock
#
# Untracked files:
#   (use "git add <file>..." to include in what will be committed)
#
#	src/MFA/
#	src/Sli/
TEXT;

        $this->er = new ExecutionResult($this->sample1);
    }

    public function testModifiedFiles()
    {
        $files = $this->er->getModifiedFiles();

        $this->assertTrue(is_array($files));
        $this->assertEquals(4, count($files));
        $this->assertTrue(in_array('app/AppKernel.php', $files));
        $this->assertTrue(in_array('app/config/config_dev.yml', $files));
        $this->assertTrue(in_array('app/kernel.json', $files));
        $this->assertTrue(in_array('composer.lock', $files));
    }

    public function testGetUntrackedFiles()
    {
        $files = $this->er->getUntrackedFiles();

        $this->assertTrue(is_array($files));
        $this->assertEquals(2, count($files));
        $this->assertTrue(in_array('src/MFA/', $files));
        $this->assertTrue(in_array('src/Sli/', $files));
    }

    public function testGetNewFiles()
    {
        $files = $this->er->getNewFiles();

        $this->assertTrue(is_array($files));
        $this->assertEquals(1, count($files));
        $this->assertTrue(in_array('app/phpunit.xml', $files));
    }

    public function testGetBranchName()
    {
        $this->assertEquals('master', $this->er->getBranchName());
    }

    public function testGetDeletedFiles()
    {
        $files = $this->er->getDeletedFiles();

        $this->assertTrue(is_array($files));
        $this->assertEquals(1, count($files));
        $this->assertTrue(in_array('README.md', $files));
    }

    public function testGetRenamedFiles()
    {
        $files = $this->er->getRenamedFiles();

        $this->assertTrue(is_array($files));
        $this->assertEquals(1, count($files));
        $this->assertArrayHasKey('index.html', $files);
    }

    public function testHasChanges()
    {
        $this->assertTrue($this->er->hasChanges());

        $output = <<<OUTPUT
# On branch master
nothing to commit, working directory clean
OUTPUT;

        $er = new ExecutionResult($output);

        $this->assertFalse($er->hasChanges());
    }
} 