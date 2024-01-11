<?php

namespace App\Automod\ModAction;

use App\Enum\FurtherAction;
use App\Enum\RunConfiguration;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @template TObject of object
 */
#[AutoconfigureTag(name: 'app.mod_action')]
interface ModAction
{
    public function shouldRun(object $object): bool;

    /**
     * @param TObject $object
     * @param array<ModAction> $previousActions
     */
    public function takeAction(object $object, array $previousActions = []): FurtherAction;

    public function getRunConfiguration(): RunConfiguration;

    public function getDescription(): ?string;
}
