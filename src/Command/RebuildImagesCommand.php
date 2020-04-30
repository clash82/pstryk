<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\AlbumNotFoundException;
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

    /** @var ImageConverter */
    private $imageConverter;

    /** @var ItemProvider */
    private $itemProvider;

    /** @var AlbumProvider */
    private $albumProvider;

    public function __construct(
        ImageConverter $imageConverter,
        ItemProvider $itemProvider,
        AlbumProvider $albumProvider
    ) {
        $this->imageConverter = $imageConverter;
        $this->itemProvider = $itemProvider;
        $this->albumProvider = $albumProvider;

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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var string $slug */
        $slug = $input->getArgument('album');

        $output->writeln(sprintf('Selected album: <comment>%s</comment>', $slug));
        $output->writeln('');

        try {
            $this->imageConverter->setAlbum($this->albumProvider->getBySlug($slug));
        } catch (AlbumNotFoundException $e) {
            $output->writeln(sprintf('<error>`%s` album was not found</error>', $slug));

            return 1;
        }

        $this->imageConverter->setApplyUnsharpMask('false' === $input->getOption('unsharp') ? false : true);

        $items = $this->itemProvider->getAllByAlbum($slug);
        $processedCounter = 0;
        foreach ($items as $item) {
            $files = $item->getFiles();

            foreach ($files as $file) {
                $output->writeln(sprintf('Converting (<comment>%d</comment>): <info>%s</info>', ++$processedCounter, $file));
                /* @noinspection PhpUnhandledExceptionInspection */
                $this->imageConverter->convert($file);
            }
        }

        $output->writeln('');
        $output->writeln(sprintf('Done, <comment>%d</comment> files processed.', $processedCounter));

        return 0;
    }
}
