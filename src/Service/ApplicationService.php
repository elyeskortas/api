<?php

namespace App\Service;

use App\Repository\ApplicationRepository;
use App\Repository\MenuRepository;
use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ApplicationService extends AbstractController
{

    private $kernel;
    private $applicationRepository;
    private $menuRepository;
    private $pageRepository;


    public function __construct(
        KernelInterface $kernel,
        ApplicationRepository $applicationRepository,
        MenuRepository $menuRepository,
        PageRepository $pageRepository
    ) {
        $this->kernel = $kernel;
        $this->applicationRepository = $applicationRepository;
        $this->menuRepository = $menuRepository;
        $this->pageRepository = $pageRepository;
    }


    public function createApplication($application)
    {
        $filesystem = new Filesystem();

        $projectDir = $this->kernel->getProjectDir();
        $parentDir = dirname($projectDir) . '/generate-app';
        $directory = dirname($projectDir) . '/' . $application->getName();

        $filesystem->mirror($parentDir, $directory);

        $filePath = $directory . '/src/assets/data/data.json';
        $content = '{"application" :' .
            json_encode($this->applicationRepository->getApplication($application)) . ",\n" .
            ' "menu": ' . json_encode($this->menuRepository->getMenuByApplication($application)) .  ",\n" .
            ' "page": ' . json_encode($this->pageRepository->getPageByApplication($application)) .  "\n}";

        file_put_contents($filePath, $content);


        $application = $this->applicationRepository->find($application);
        $cssPath = $directory . '/src/assets/scss/_variables.scss';

        $file = file_get_contents($cssPath);
        // Close the file
        $contentCss = '$primary:' . $application->getPrimaryColor() . ';' .  "\n" . '$secondary:' . $application->getSecondaryColor() . ';' .  "\n"
        . '$light:'.   $application->getPrimaryColor() . '05' .';';

        file_put_contents($cssPath, $contentCss . $file);

        // // Create a new process
        // $process = new Process(['ng', 'build']);
        // $process->setWorkingDirectory($directory);

        // // Run the process
        // $process->run();

        // // Check if the process was successful
        // if (!$process->isSuccessful()) {
        //     throw new ProcessFailedException($process);
        // }

        // // Output the result of the command
        // echo $process->getOutput();


        // $finder->in($directory)->depth(0)->notName('docs')->notPath('docs/*');
        // foreach ($finder as $file) {
        //     $filesystem->remove($file->getRealPath());
        // }
        // $filesystem->mirror($directory . '/docs', $directory);
        // $filesystem->remove($directory . '/docs');

    }
}
