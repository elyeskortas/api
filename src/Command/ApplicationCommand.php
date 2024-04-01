<?php

namespace App\Command;

use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Service\ApplicationService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\DBAL\Connection;

class ApplicationCommand extends Command
{
    protected static $defaultName = 'ApplicationCommand';
    protected static $defaultDescription = 'Add a short description for your command';

    private $sessionRepository;
    private $userRepository;
    private $entityManager;
    private $connection;
    private $filesystem;
    private $applicationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        Connection $connection,
        Filesystem $filesystem,
        ApplicationService $applicationService
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->connection = $connection;
        $this->filesystem = $filesystem;
        $this->applicationService = $applicationService;
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);


        $this->applicationService->createApplication();
      
       
        $output->writeln('Old sessions removed successfully.');
        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
