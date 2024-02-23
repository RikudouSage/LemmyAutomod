<?php

namespace App\Service\InstanceDataEnricher;

use Override;

final readonly class PixelfedInstanceDataEnricher extends MastodonV1LikeEnricher
{
    #[Override]
    public function supports(string $software, string $version): bool
    {
        return $software === 'pixelfed';
    }
}
