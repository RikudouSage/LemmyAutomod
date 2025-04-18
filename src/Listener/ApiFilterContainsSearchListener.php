<?php

namespace App\Listener;

use Rikudou\JsonApiBundle\ApiEntityEvents;
use Rikudou\JsonApiBundle\Events\EntityApiOnFilterSearchEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ApiEntityEvents::ON_FILTER_SEARCH, method: 'onFilterSearch')]
final readonly class ApiFilterContainsSearchListener
{
    public function onFilterSearch(EntityApiOnFilterSearchEvent $event): void
    {
        $modified = false;
        foreach ($event->filterValues as $filterValue) {
            if (!str_starts_with($filterValue, '~')) {
                continue;
            }
            $modified = true;
            break;
        }
        if (!$modified) {
            return;
        }

        foreach ($event->filterValues as $filterValue) {
            $random = random_int(0, PHP_INT_MAX);
            if (!str_starts_with($filterValue, '~')) {
                $event->queryBuilder->andWhere("entity.{$event->filterName} LIKE :ApiFilterContainsSearchListener_Val{$random}")
                    ->setParameter("ApiFilterContainsSearchListener_Val{$random}", $filterValue);
            } else {
                $filterValue = substr($filterValue, 1);
                $event->queryBuilder->andWhere("entity.{$event->filterName} LIKE :ApiFilterContainsSearchListener_Val{$random}")
                    ->setParameter("ApiFilterContainsSearchListener_Val{$random}", "%{$filterValue}%");
            }
        }

        $event->handled = true;
    }
}
