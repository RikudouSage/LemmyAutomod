<?php

namespace App\Service\InstanceDataEnricher;

use App\Dto\Model\BasicInstanceData;
use App\Dto\Model\EnrichedInstanceData;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: 'app.instance_enricher')]
interface InstanceDataEnricher
{
    public function supports(string $software, string $version): bool;
    public function getEnriched(BasicInstanceData $instanceData): EnrichedInstanceData;
}
