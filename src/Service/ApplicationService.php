<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

class ApplicationService extends AbstractController
{

    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }


    public function createApplication()
    {
        $filesystem = new Filesystem();
        $finder = new Finder();

        $projectDir = $this->kernel->getProjectDir();
        $parentDir = dirname($projectDir) . '/generate-app';
        $filesystem->mirror($parentDir, $projectDir);
        // $finder->directories()->in($projectDir)->name('original_folder_name');


        var_dump($parentDir);
    }
}
