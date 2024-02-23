<?php

namespace App\Service\InstanceDataEnricher;

use App\Dto\Model\BasicInstanceData;
use App\Dto\Model\EnrichedInstanceData;
use LogicException;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class MastodonInstanceDataEnricher extends MastodonV1LikeEnricher
{
    #[Override]
    public function supports(string $software, string $version): bool
    {
        return $software === 'mastodon';
    }
}
