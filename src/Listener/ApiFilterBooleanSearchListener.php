<?php

namespace App\Listener;

use LogicException;
use Rikudou\JsonApiBundle\ApiEntityEvents;
use Rikudou\JsonApiBundle\Events\EntityApiOnFilterSearchEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ApiEntityEvents::ON_FILTER_SEARCH, method: 'onFilterSearch')]
final readonly class ApiFilterBooleanSearchListener
{
    public function onFilterSearch(EntityApiOnFilterSearchEvent $event): void
    {
        $values = [];
        $changed = false;
        foreach ($event->filterValues as $filterValue) {
            $transformedValue = match ($filterValue) {
                'true' => true,
                'false' => false,
                default => $filterValue,
            };
            $values[] = $transformedValue;
            if ($transformedValue === $filterValue) {
                continue;
            }
            $changed = true;
        }

        if (!$changed) {
            return;
        }

        if (count($values) !== 1) {
            throw new LogicException('Only one boolean value is allowed in a filter');
        }

        $random = random_int(0, PHP_INT_MAX);
        $event->queryBuilder->andWhere("entity.{$event->filterName} = :ApiFilterBooleanSearchListener_Val{$random}")
            ->setParameter("ApiFilterBooleanSearchListener_Val{$random}", $values[0]);
        $event->handled = true;
    }
}
