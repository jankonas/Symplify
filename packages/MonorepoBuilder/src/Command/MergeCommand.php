<?php declare(strict_types=1);

namespace Symplify\MonorepoBuilder\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\MonorepoBuilder\DependenciesMerger;
use Symplify\MonorepoBuilder\Package\PackageComposerJsonMerger;
use Symplify\MonorepoBuilder\PackageComposerFinder;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class MergeCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var PackageComposerJsonMerger
     */
    private $packageComposerJsonMerger;

    /**
     * @var string[]
     */
    private $mergeSections = [];

    /**
     * @var PackageComposerFinder
     */
    private $packageComposerFinder;

    /**
     * @var DependenciesMerger
     */
    private $dependenciesMerger;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        PackageComposerJsonMerger $packageComposerJsonMerger,
        PackageComposerFinder $packageComposerFinder,
        DependenciesMerger $dependenciesMerger
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->packageComposerJsonMerger = $packageComposerJsonMerger;
        $this->packageComposerFinder = $packageComposerFinder;
        $this->dependenciesMerger = $dependenciesMerger;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Merge "composer.json" from all found packages to root one');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $composerPackageFiles = $this->packageComposerFinder->getPackageComposerFiles();
        if (! count($composerPackageFiles)) {
            $this->symfonyStyle->error('No "composer.json" were found in packages.');
            return 1;
        }

        $merged = $this->packageComposerJsonMerger->mergeFileInfos($composerPackageFiles, $this->mergeSections);
        $this->dependenciesMerger->mergeJsonToRootFilePath($merged, getcwd() . DIRECTORY_SEPARATOR . 'composer.json');

        $this->symfonyStyle->success('Main "composer.json" was updated.');

        // success
        return 0;
    }
}
