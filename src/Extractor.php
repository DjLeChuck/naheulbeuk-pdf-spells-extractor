<?php

namespace App;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Symfony\Component\String\Slugger\AsciiSlugger;

class Extractor
{
    private string $tmpDirectory;
    private Filesystem $filesystem;

    public function __construct()
    {
        $this->tmpDirectory = sys_get_temp_dir().'/'.sha1(random_bytes(10));
        $this->filesystem = new Filesystem();

        $this->filesystem->mkdir($this->tmpDirectory);
    }

    public function listPdfs(string $path): \Iterator
    {
        return Finder::create()
                     ->files()
                     ->name('*.pdf')
                     ->in($path)
                     ->sortByName(true)
                     ->getIterator()
        ;
    }

    public function splitPdf(\SplFileInfo $pdf): \Iterator
    {
        $slugger = new AsciiSlugger('fr');
        $name = $slugger->slug($pdf->getBasename('.'.$pdf->getExtension()));
        $process = new Process([
            'pdfseparate',
            $pdf->getPathname(),
            sprintf('%s/%s-page-%%d.pdf', $this->tmpDirectory, $name),
        ]);

        $process->mustRun();

        return Finder::create()
                     ->files()
                     ->name(sprintf('%s-page-*.pdf', $name))
                     ->in($this->tmpDirectory)
                     ->sortByName(true)
                     ->getIterator()
        ;
    }

    public function processPage(\SplFileInfo $page): string
    {
        $file = $this->tmpDirectory.'/'.$page->getBasename($page->getExtension()).'txt';
        $process = new Process(['pdftotext', '-nopgbrk', '-raw', $page->getPathname(), $file]);

        $process->mustRun();

        return file_get_contents($file);
    }

    public function __destruct()
    {
        $this->filesystem->remove($this->tmpDirectory);
    }
}
