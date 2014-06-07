<?php

namespace Sli\GitToolsBundle\Command;

use Sli\GitToolsBundle\Git\Status\ExecutionResult;
use Sli\GitToolsBundle\Git\Status\GitStatusCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class GlobalStatusCommand extends ContainerAwareCommand
{
    // override
    protected function configure()
    {
        $this
            ->setName('sli:vendor-git-tools:global-status')
            ->addArgument('bundle-name-filter')
            ->setDescription('Shows git status for all registered bundles.')
        ;
    }

    // override
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filterArg = $input->getArgument('bundle-name-filter');

        /* @var KernelInterface $kernel */
        $kernel = $this->getContainer()->get('kernel');

        $modifiedBundles = array();
        foreach ($kernel->getBundles() as $bundle) {
            if (null !== $filterArg && substr($bundle->getName(), 0, strlen($filterArg)) != $filterArg) {
                continue;
            }

            $gitStatus = new GitStatusCommand($bundle->getPath());
            if (0 == $gitStatus->run()) {
                $result = $gitStatus->runAndGetExecutionResult();

                if ($result->hasChanges()) {
                    $modifiedBundles[] = array(
                        'bundle' => $bundle,
                        'result' => $result
                    );
                }
            }
        }

        if (count($modifiedBundles) == 0) {
            $output->writeln(' No bundles have modifications');
        } else {
            $output->writeln(' These bundles have modifications:');

            if ($input->getOption('verbose')) {
                $output->writeln('');

                foreach ($modifiedBundles as $entry) {
                    /* @var Bundle $bundle */
                    $bundle = $entry['bundle'];
                    /* @var ExecutionResult $result */
                    $result = $entry['result'];

                    $output->writeln(sprintf(' <bg=yellow;options=bold>%s</bg=yellow;options=bold>', $bundle->getName()));
                    $this->printFileNames($output, $result->getNewFiles(), 'green', 'New files');
                    if ($result->getRenamedFiles()) {
                        $output->writeln('   Renamed files:');
                        foreach ($result->getRenamedFiles() as $oldFilename=>$newFilename) {
                            $output->writeln("      <fg=red>$oldFilename</fg=red> -> <fg=green>$newFilename</fg=green>");
                        }
                    }
                    $this->printFileNames($output, $result->getModifiedFiles(), 'yellow', 'Modified files');
                    $this->printFileNames($output, $result->getDeletedFiles(), 'red', 'Deleted files');
                    $this->printFileNames($output, $result->getUntrackedFiles(), null, 'Untracked files');

                    $output->writeln("\n");
                }
            } else {
                foreach ($modifiedBundles as $entry) {
                    /* @var Bundle $bundle */
                    $bundle = $entry['bundle'];

                    $output->writeln( ' - ' . $bundle->getName());
                }
            }
        }
    }

    /**
     * @param OutputInterface $output
     * @param string[] $filenames
     * @param string $color
     */
    private function printFileNames(OutputInterface $output, array $filenames, $color, $label)
    {
        if ($filenames) {
            $output->writeln('   ' . $label . ':');
            foreach ($filenames as $filename) {
                if ($color) {
                    $output->writeln("     <fg=$color>$filename</fg=$color>");
                } else {
                    $output->writeln('     ' . $filename);
                }
            }
        }
    }
}