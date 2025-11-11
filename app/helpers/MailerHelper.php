<?php
namespace presupuestos\helpers;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class MailerHelper {

    private Mailer $mailer;
    private Environment $twig;
    private string $from;

    public function __construct() {
        $transportType = $_ENV['MAILER_TRANSPORT'];
        $user = $_ENV['MAILER_USER'];
        $pass = $_ENV['MAILER_PASS'];
        $host = $_ENV['MAILER_HOST'];
        $port = $_ENV['MAILER_PORT'];
        $this->from = $user;

        $dsn = sprintf(
            'smtps://%s:%s@smtp.gmail.com:465',
            urlencode($_ENV['MAILER_USER']),
            urlencode($_ENV['MAILER_PASS'])
        );

        $transport = Transport::fromDsn($dsn);
        $this->mailer = new Mailer($transport);


        $emailsPath = __DIR__ . '/../view/emails';
        if (!is_dir($emailsPath)) {
            mkdir($emailsPath, 0755, true);
        }

        $loader = new FilesystemLoader($emailsPath);
        $this->twig = new Environment($loader);
        $this->twig->addGlobal('app_url', $_ENV['APP_URL']);
    }

    public function sendRecoveryEmail(array $user, string $token) {
        try {
            $body = $this->twig->render('recovery_email.html.twig', [
                'name' => $user['name'],
                'token' => $token,
            ]);

            $email = (new Email())
                ->from($_ENV['MAILER_USER'])
                ->to($user['email'] ?? '')
                ->subject('Recuperación de contraseña')
                ->html($body);

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            error_log("Error enviando correo de recuperación: " . $e->getMessage());
            return $e->getMessage();
        }
    }

    public function sendVerificationEmail(array $user, string $token) {
        try {
            $body = $this->twig->render('verification_email.html.twig', [
                'name' => $user['name'],
                'lastName'=> $user['lastName'],
                'token' => $token,

            ]);

            $email = (new Email())
                ->from($_ENV['MAILER_USER'])
                ->to($user['email'])
                ->subject('Verifica tu cuenta')
                ->html($body);

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            error_log("Error enviando correo de verificación: " . $e->getMessage());
            return $e->getMessage();
        }
    }
    
}