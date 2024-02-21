<?php

namespace App\Command;

use App\Entity\AutoApprovalRegex;
use App\Entity\BannedEmail;
use App\Entity\BannedUsername;
use App\Entity\InstanceBanRegex;
use App\Entity\ReportRegex;
use App\Service\RemovalLogValidityFactory;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Rikudou\LemmyApi\DefaultLemmyApi;
use Rikudou\LemmyApi\Enum\AuthMode;
use Rikudou\LemmyApi\Enum\LemmyApiVersion;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

#[AsCommand('app:validate')]
final class ValidateCommand extends Command
{
    public function __construct(
        #[Autowire('%env(LEMMY_USER)%')]
        private readonly string $lemmyUser,
        #[Autowire('%env(LEMMY_PASSWORD)%')]
        private readonly string $lemmyPassword,
        #[Autowire('%env(LEMMY_INSTANCE)%')]
        private readonly string $lemmyInstance,
        #[Autowire('%env(LEMMY_AUTH_MODE)%')]
        private readonly int $authMode,
        #[Autowire('%env(IMAGE_CHECK_REGEX)%')]
        private readonly string $imageCheckRegex,
        #[Autowire('%env(REMOVAL_LOG_VALIDITY)%')]
        private readonly string $logValidity,
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly EntityManagerInterface $entityManager,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $pass = '<fg=green>pass</>';
        $ignored = '<fg=yellow>ignored</>';
        $failure = '<fg=red>fail</>';

        /** @noinspection PhpArrayIndexImmediatelyRewrittenInspection */
        $tests = [
            'Auth mode' => $ignored,
            'Credentials provided' => $ignored,
            'Credentials valid' => $ignored,
            'Image check regex valid' => $ignored,
            'Removal log validity valid' => $ignored,
            'All regexes valid' => $ignored,
        ];

        $tests['Auth mode'] = $this->isAuthModeValid() ? $pass : $failure;
        $tests['Credentials provided'] = $this->lemmyUser && $this->lemmyPassword && $this->lemmyInstance ? $pass : $failure;
        if ($tests['Auth mode'] === $pass && $tests['Credentials provided'] === $pass) {
            $tests['Credentials valid'] = $this->areCredentialsValid() ? $pass : $failure;
        }
        $tests['Image check regex valid'] = $this->isImageCheckRegexValid() ? $pass : $failure;
        $tests['Removal log validity valid'] = $this->isLogValidityValid() ? $pass : $failure;
        $tests['All regexes valid'] = $this->validateAllRegexes($pass);

        $rows = [];
        foreach ($tests as $testName => $result) {
            $rows[] = [$testName, $result];
        }

        $io->createTable()
            ->setHeaders(['Test', 'Result'])
            ->setRows($rows)
            ->setStyle('box-double')
            ->render();

        return count(array_filter($tests, fn (string $value) => $value !== $pass)) === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    private function getApi(): LemmyApi
    {
        return new DefaultLemmyApi(
            instanceUrl: $this->lemmyInstance,
            version: LemmyApiVersion::Version3,
            httpClient: $this->httpClient,
            requestFactory: $this->requestFactory,
            authMode: $this->authMode,
        );
    }

    private function areCredentialsValid(): bool
    {
        $api = $this->getApi();
        try {
            $response = $api->login($this->lemmyUser, $this->lemmyPassword);
            return $response->jwt !== null;
        } catch (Throwable) {
            return false;
        }
    }

    private function isAuthModeValid(): bool
    {
        return $this->authMode & AuthMode::Body || $this->authMode & AuthMode::Header;
    }

    private function isImageCheckRegexValid(): bool
    {
        $regex = '@' . str_replace('@', '\\@', $this->imageCheckRegex) . '@';
        return $this->isRegexValid($regex);
    }

    private function isRegexValid(string $regex): bool
    {
        return @preg_match($regex, '') !== false;
    }

    private function isLogValidityValid(): bool
    {
        try {
            RemovalLogValidityFactory::createLogValidity($this->logValidity);
            return true;
        } catch (LogicException) {
            return false;
        }
    }

    private function validateAllRegexes(string $valueOnSuccess): string
    {
        $entityMapping = [
            AutoApprovalRegex::class => 'getRegex',
            BannedEmail::class => 'getRegex',
            BannedUsername::class => 'getUsername',
            InstanceBanRegex::class => 'getRegex',
            ReportRegex::class => 'getRegex',
        ];

        foreach ($entityMapping as $entityClass => $method) {
            $repository = $this->entityManager->getRepository($entityClass);
            $entities = $repository->findAll();
            foreach ($entities as $entity) {
                $regex = $entity->{$method}();
                $regex = '@' . str_replace('@', '\\@', $regex) . '@';
                if (!$this->isRegexValid($regex)) {
                    $meta = $this->entityManager->getClassMetadata($entityClass);
                    return "<fg=red>Invalid regex for row with id <fg=red;options=bold>{$entity->getId()}</> in table <fg=red;options=bold>{$meta->table['name']}</>: {$entity->{$method}()}</>";
                }
            }
        }

        return $valueOnSuccess;
    }
}
