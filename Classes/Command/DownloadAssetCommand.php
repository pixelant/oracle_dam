<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Command;

use Oracle\Typo3Dam\Service\AssetService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Core\Bootstrap;

class DownloadAssetCommand extends Command
{
    /**
     * @var AssetService
     */
    protected $assetService;

    /**
     * @param AssetService $assetService
     */
    public function __construct(AssetService $assetService)
    {
        parent::__construct();

        $this->assetService = $assetService;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        Bootstrap::initializeBackendAuthentication();
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Download an asset from Oracle DAM.')
            ->addArgument(
                'assetId',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'One or more IDs of assets to download from Oracle DAM.',
                []
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($input->getArgument('assetId') as $assetId) {
            $file = $this->assetService->createLocalAssetCopy($assetId);

            if ($file === null) {
                $output->writeln('<error>Failed downloading asset with ID ' . $assetId . '</error>');

                continue;
            }

            $output->writeln(
                '<info>Downloaded asset with ID ' . $assetId . ' as ' . $file->getIdentifier() . '</info>'
            );
        }

        return 0;
    }
}
