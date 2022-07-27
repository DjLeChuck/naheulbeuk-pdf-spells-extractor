#!/usr/bin/env php
<?php
require __DIR__.'/vendor/autoload.php';

use App\Extractor;
use App\SpellFormatter;
use App\SpellSerializer;
use App\SpellTypeGuesser;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

(new SingleCommandApplication())
    ->setName('Extraction des sorts du JdR Naheulbeuk')
    ->setDescription('Extraction des sorts des PDFs du JdR Naheulbeuk.')
    ->setVersion('1.0.0')
    ->addArgument('pdfs', InputArgument::REQUIRED, 'Répertoire où sont stockés les fichiers PDF.')
    ->addArgument('packs', InputArgument::REQUIRED, 'Répertoire où seront stockés les packs générés.')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);

        $io->title(sprintf('Extraction des sorts contenus dans les PDFs.'));

        $filesystem = new Filesystem();

        $pdfsDirectory = $input->getArgument('pdfs');
        if (!$filesystem->exists($pdfsDirectory)) {
            $io->error(sprintf('Répertoire %s inexistant.', $pdfsDirectory));

            return 1;
        }

        $packsDirectory = $input->getArgument('packs');
        if (!$filesystem->exists($packsDirectory)) {
            $io->error(sprintf('Répertoire %s inexistant.', $packsDirectory));

            return 1;
        }

        $extractor = new Extractor();
        $serializer = new SpellSerializer(new Serializer([new ObjectNormalizer()], [new JsonEncoder()]));
        $typeGuesser = new SpellTypeGuesser();

        /** @var SplFileInfo $pdf */
        foreach ($extractor->listPdfs($pdfsDirectory) as $pdf) {
            $io->info(sprintf('Traitement du fichier %s', $pdf->getFilename()));

            $spellType = $typeGuesser->guess($pdf->getFilename());
            $pack = '';

            /** @var SplFileInfo $page */
            foreach ($extractor->splitPdf($pdf) as $page) {
                $io->writeln(sprintf('<info>Traitement de la page %s</info>', $page->getFilename()));

                $content = $extractor->processPage($page);

                if (!str_starts_with($content, 'Niveau ')) {
                    $io->warning(sprintf('Le contenu ne commence pas par "Niveau", on ignore.'));

                    continue;
                }

                try {
                    $formatter = new SpellFormatter($content, $spellType);
                    $spell = $formatter->format();
                } catch (\Throwable $e) {
                    $io->error($e->getMessage());

                    continue;
                }

                $pack .= $serializer->serialize($spell)."\n";
            }

            $io->info('Génération du fichier pack');
            file_put_contents(sprintf('%ssorts-%s.db', $packsDirectory, $spellType), $pack);
        }

        $io->title('Traitement terminé !');

        return 0;
    })
    ->run()
;
