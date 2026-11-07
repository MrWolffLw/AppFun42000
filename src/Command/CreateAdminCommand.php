<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-admin', description: 'Crée un compte administrateur (ou change son mot de passe)')]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly UserRepository $users,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Adresse e-mail de connexion');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = mb_strtolower(trim((string) $input->getArgument('email')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('Adresse e-mail invalide.');

            return Command::FAILURE;
        }
        $password = (string) $io->askHidden('Mot de passe (12 caractères minimum)');
        if (mb_strlen($password) < 12) {
            $io->error('Le mot de passe doit faire au moins 12 caractères.');

            return Command::FAILURE;
        }
        if ($password !== (string) $io->askHidden('Confirmez le mot de passe')) {
            $io->error('Les deux mots de passe ne correspondent pas.');

            return Command::FAILURE;
        }

        $user = $this->users->findOneBy(['email' => $email]) ?? new User();
        $user->setEmail($email)->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->hasher->hashPassword($user, $password));
        $this->em->persist($user);
        $this->em->flush();
        $io->success(sprintf('Administrateur « %s » enregistré. Connexion : /login', $email));

        return Command::SUCCESS;
    }
}
