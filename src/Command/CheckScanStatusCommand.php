<?php

namespace App\Command;

use App\Service\ScanService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:check-scan-status',
    description: 'Gets the scan status of files using ciUploadId queue.',
)]
class CheckScanStatusCommand extends Command
{
    protected static $defaultName = 'app:check-scan-status';
    private $scanService;

    /**
     * Constructor to initialize the ScanService service
     * 
     * @param ScanService $scanService ScanService service
     * 
     * @return null
     */
    public function __construct(ScanService $scanService)
    {
        parent::__construct();
        $this->scanService = $scanService;
    }

    /**
     * Configures the command
     * 
     * @return null
     */
    protected function configure()
    {
        $this->setDescription('Check scan status in background');
    }

    /**
     * Executes the command
     * 
     * @param InputInterface $input InputInterface object
     * @param OutputInterface $output OutputInterface object
     * 
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ciUploadId = $input->getArgument('ciUploadId');

        $status = $this->scanService->checkScanStatus($ciUploadId);

        return Command::SUCCESS;
    }
}

