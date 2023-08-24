<?php

namespace App\Command;

use App\Repository\RecettesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PublishRecetteCommand extends Command
{
    protected static $defaultName = 'app:publish-recette';
    protected static $defaultDescription = 'Add a short description for your command';

    private $recettesRepository;
    private $manager;

    public function __construct(RecettesRepository $recettesRepository, EntityManagerInterface $manager)
    {
        $this->recettesRepository = $recettesRepository;
        $this->manager = $manager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $nombreDeRecords = 0;

        // Rcherche des recettes avec l'état : Brouillon
        $recettes = $this->recettesRepository->findBy(
            [
                'etat' => 'Brouillon'
            ]
        );

        // Parcours de l ensemble des recettes et changement de l'état : Publie
        foreach ($recettes as $recette) {
            $recette->setEtat('Publie');
        }

        // Mémorisation du nombre de recettes modifiées
        $nombreDeRecords = count($recettes);

        // Mise à jour de la bdd
        $this->manager->flush();

        // Affiche d'un message au niveau de la log
        $io->success((string)$nombreDeRecords . ' article(s) publié(s)');

        return 0;
    }
}
