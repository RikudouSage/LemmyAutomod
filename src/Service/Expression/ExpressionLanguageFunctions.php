<?php

namespace App\Service\Expression;

use App\Message\RunExpressionAsyncMessage;
use Closure;
use LogicException;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

final readonly class ExpressionLanguageFunctions implements ExpressionFunctionProviderInterface
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private ExpressionLanguage $expressionLanguage,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'async',
                $this->uncompilableFunction(),
                $this->asyncFunction(...),
            ),
            new ExpressionFunction(
                'all',
                $this->uncompilableFunction(),
                $this->allFunction(...),
            ),
            new ExpressionFunction(
                'any',
                $this->uncompilableFunction(),
                $this->anyFunction(...),
            ),
            new ExpressionFunction(
                '_and_',
                $this->uncompilableFunction(),
                $this->andFunction(...),
            ),
            new ExpressionFunction(
                '_or_',
                $this->uncompilableFunction(),
                $this->orFunction(...),
            ),
            new ExpressionFunction(
                'catchError',
                $this->uncompilableFunction(),
                $this->catchErrorFunction(...),
            ),
        ];
    }

    private function uncompilableFunction(): Closure
    {
        return fn () => throw new LogicException('This function cannot be compiled');
    }

    private function asyncFunction(array $context, string $expression): bool
    {
        $this->messageBus->dispatch(new RunExpressionAsyncMessage($context, $expression));
        return true;
    }

    private function allFunction(array $context, string ...$expressions): bool
    {
        foreach ($expressions as $expression) {
            $result = $this->expressionLanguage->evaluate($expression, $context);
            if (!$result) {
                return false;
            }
        }

        return true;
    }

    private function anyFunction(array $context, string ...$expressions): bool
    {
        foreach ($expressions as $expression) {
            $result = $this->expressionLanguage->evaluate($expression, $context);
            if ($result) {
                return true;
            }
        }

        return false;
    }

    private function andFunction(array $context, string ...$expressions): bool
    {
        $result = true;
        foreach ($expressions as $expression) {
            $result = $result && $this->expressionLanguage->evaluate($expression, $context);
        }

        return $result;
    }

    private function orFunction(array $context, string ...$expressions): bool
    {
        if (!count($expressions)) {
            return true;
        }

        $result = false;
        foreach ($expressions as $expression) {
            $result = $result || $this->expressionLanguage->evaluate($expression, $context);
        }

        return $result;
    }

    private function catchErrorFunction(array $context, string $expression, string $onErrorExpression): bool
    {
        try {
            return $this->expressionLanguage->evaluate($expression, $context);
        } catch (Throwable $exception) {
            $context['exception'] = $exception;
            return $this->expressionLanguage->evaluate($onErrorExpression, $context);
        }
    }
}
