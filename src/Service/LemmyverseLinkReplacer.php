<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class LemmyverseLinkReplacer
{
    public function __construct(
        #[Autowire('%app.lemmy.instance%')]
        private string $instance,
    ) {
    }

    public function replace(string $message): string
    {
        $instance = preg_quote($this->instance);

        $message = preg_replace("@https://{$instance}/([uc])/@", 'https://lemmyverse.link/$1/', $message);
        $message = preg_replace("@https://{$instance}/(comment|post)@", "https://lemmyverse.link/{$this->instance}/$1", $message);

        return $message;
    }
}
