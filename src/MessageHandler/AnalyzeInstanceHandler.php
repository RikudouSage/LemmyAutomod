<?php

namespace App\MessageHandler;

use App\Automod\Automod;
use App\Dto\Model\EnrichedInstanceData;
use App\Message\AnalyzeInstanceMessage;
use App\Service\BasicInfoInstanceDataFetcher;
use App\Service\InstanceDataEnricher\InstanceDataEnricher;
use LogicException;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class AnalyzeInstanceHandler
{
    /**
     * @param iterable<InstanceDataEnricher> $enrichers
     */
    public function __construct(
        #[TaggedIterator('app.instance_enricher')]
        private iterable $enrichers,
        private BasicInfoInstanceDataFetcher $basicInfoInstanceDataFetcher,
        private Automod $automod,
    ) {
    }

    public function __invoke(AnalyzeInstanceMessage $message): void
    {
        $data = $this->getEnriched($message);
        $this->automod->analyze($data);
    }

    private function getEnriched(AnalyzeInstanceMessage $message): EnrichedInstanceData
    {
        $basic = $this->basicInfoInstanceDataFetcher->fetch($message->instance)
            ?? throw new LogicException('Could not fetch basic instance info');
        $default = new EnrichedInstanceData(
            instance: $basic->instance,
            software: $basic->software,
            version: $basic->version,
            openRegistrations: null,
            captcha: null,
            emailVerification: null,
            applications: null,
        );

        if ($basic->software === null || $basic->version === null) {
            return $default;
        }

        foreach ($this->enrichers as $enricher) {
            if ($enricher->supports($basic->software, $basic->version)) {
                return $enricher->getEnriched($basic);
            }
        }

        return $default;
    }
}
