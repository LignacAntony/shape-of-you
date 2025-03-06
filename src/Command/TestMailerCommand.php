<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:test-mailer',
    description: 'Test l\'envoi d\'email avec le mailer configuré',
)]
class TestMailerCommand extends Command
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        parent::__construct();
        $this->mailer = $mailer;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $email = (new Email())
                ->from('onboarding@resend.dev')
                ->to('antony.lignac@eemi.com')
                ->subject('Test d\'envoi d\'email depuis Symfony')
                ->html('<p>Ceci est un test d\'envoi d\'email depuis Symfony.</p>
                       <p>Heure du test : ' . (new \DateTime())->format('Y-m-d H:i:s') . '</p>');

            $this->mailer->send($email);

            $io->success('Email envoyé avec succès !');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 