<?php declare(strict_types=1);

namespace App\Command;

use App\Exception\AlbumSettingsNotFoundException;
use App\Image\ImageConverter;
use App\Provider\AlbumProvider;
use App\Provider\ItemProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RebuildImagesCommand extends Command
{
    protected static $defaultName = 'app:rebuild-images';

    public function __construct(
        private readonly ImageConverter $imageConverter,
        private readonly ItemProvider $itemProvider,
        private readonly AlbumProvider $albumProvider
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Rebuilds images cache (normal images and thumbs) based on the raw files')
            ->setHelp('This command creates public images again based on the files stored in raw folder.')
            ->addArgument('album', InputArgument::REQUIRED, 'Select album to process')
            ->addOption(
                'unsharp',
                'u',
                InputOption::VALUE_OPTIONAL,
                'Apply unsharp mask to final image (slower, but better quality)',
                'true'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $slug */
        $slug = $input->getArgument('album');

        $output->writeln(sprintf('Selected album: <comment>%s</comment>', $slug));
        $output->writeln('');

        try {
            $this->imageConverter->setAlbum($this->albumProvider->getBySlug($slug));
        } catch (AlbumSettingsNotFoundException) {
            $output->writeln(sprintf('<error>`%s` album was not found</error>', $slug));

            return 1;
        }

        $this->imageConverter->setApplyUnsharpMask('false' !== $input->getOption('unsharp'));

        $items = $this->itemProvider->getAllByAlbum($slug);
        $processedCounter = 0;
        foreach ($items as $item) {
            $files = $item->getImages();

            foreach ($files as $file) {
                $output->writeln(sprintf('Converting (<comment>%d</comment>): <info>%s</info>', ++$processedCounter, $file));
                /* @noinspection PhpUnhandledExceptionInspection */
                $this->imageConverter->convert($file);
            }
        }

        $output->writeln('');
        $output->writeln(sprintf('Done, <comment>%d</comment> images processed.', $processedCounter));

        return 0;
    }
}
