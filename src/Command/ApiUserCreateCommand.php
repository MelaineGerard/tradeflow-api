<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;

#[AsCommand(
    name: 'api:user:create',
    description: 'Permet de créer un utilisateur',
)]
class ApiUserCreateCommand extends Command
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        // ask for username, name, password, role

        $io->title('Création d\'un utilisateur');
        $username = $io->ask('Quel est le nom d\'utilisateur ?');
        $name = $io->ask('Quel est le nom complet ?');
        $password = $io->askHidden('Quel est le mot de passe ?');
        $role = $io->ask('Quel est le rôle ?');

        $io->table(
            ['Nom d\'utilisateur', 'Nom complet', 'Mot de passe', 'Rôle'],
            [[$username, $name, $password, $role]]
        );

        $isExist = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

        if ($isExist) {
            $io->error('Cet utilisateur existe déjà');
            return Command::FAILURE;
        }

        $user = new User();
        $user->setUsername($username);
        $user->setName($name);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles([$role]);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Vous avez créé un utilisateur');
        

        return Command::SUCCESS;
    }
}
