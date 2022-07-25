#!/usr/bin/env php
<?php
require __DIR__.'/vendor/autoload.php';

use App\Extractor;
use App\SpellFormatter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

(new SingleCommandApplication())
    ->setName('Extraction des sorts du JdR Naheulbeuk')
    ->setDescription('Extraction des sorts des PDFs du JdR Naheulbeuk.')
    ->setVersion('1.0.0')
    ->addArgument('pdfs', InputArgument::REQUIRED, 'Répertoire où sont stockés les fichiers PDF.')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);

        $io->title(sprintf('Extraction des sorts contenus dans les PDFs.'));

        $directory = $input->getFirstArgument();
        $filesystem = new Filesystem();

        if (!$filesystem->exists($directory)) {
            $io->error(sprintf('Répertoire %s inexistant.', $directory));

            return 1;
        }

        $extractor = new Extractor();

        /** @var SplFileInfo $pdf */
        foreach ($extractor->listPdfs($directory) as $pdf) {
            $io->info(sprintf('Traitement du fichier %s', $pdf->getFilename()));

            /** @var SplFileInfo $page */
            foreach ($extractor->splitPdf($pdf) as $page) {
                $io->writeln(sprintf('<info>Traitement de la page %s</info>', $page->getFilename()));

                $content = $extractor->processPage($page);

                if (!str_starts_with($content, 'Niveau ')) {
                    $io->error(sprintf('Le contenu ne commence pas par "Niveau", on ignore.'));

                    continue;
                }

                $formatter = new SpellFormatter($content, 'test');

                var_dump($formatter->format()->name);
            }
        }

        return 0;
    })
    ->run()
;
