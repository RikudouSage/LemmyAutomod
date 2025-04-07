<?php

namespace App\Automod\ModAction;

use App\Automod\Enum\ComplexRuleType;
use App\Context\Context;
use App\Dto\Model\EnrichedInstanceData;
use App\Dto\Model\LocalUser;
use App\Enum\FurtherAction;
use App\Enum\RunConfiguration;
use App\Repository\ComplexRuleRepository;
use App\Service\Expression\ExpressionLanguage;
use App\Service\Expression\ExpressionLanguageNotifier;
use Psr\Log\LoggerInterface;
use Rikudou\LemmyApi\Response\Model\Community;
use Rikudou\LemmyApi\Response\Model\Person;
use Rikudou\LemmyApi\Response\View\CommentReportView;
use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\PostReportView;
use Rikudou\LemmyApi\Response\View\PostView;
use Rikudou\LemmyApi\Response\View\PrivateMessageReportView;
use Rikudou\LemmyApi\Response\View\RegistrationApplicationView;
use Symfony\Component\ExpressionLanguage\SyntaxError;

/**
 * @implements ModAction<PostView|CommentView|Person|CommentReportView|PostReportView|PrivateMessageReportView|RegistrationApplicationView|LocalUser|EnrichedInstanceData|Community>
 */
final readonly class ComplexRuleAction implements ModAction
{
    public function __construct(
        private ComplexRuleRepository $ruleRepository,
        private ExpressionLanguage $expressionLanguage,
        private ExpressionLanguageNotifier $notifier,
        private LoggerInterface $logger,
    ) {
    }

    public function shouldRun(object $object): bool
    {
        $type = ComplexRuleType::fromClass(get_class($object));
        $rules = $this->ruleRepository->findBy([
            'enabled' => true,
            'type' => $type,
        ]);

        if (!count($rules)) {
            return false;
        }

        foreach ($rules as $rule) {
            $expression = $rule->getRule();
            if ($this->expressionLanguage->evaluate($expression, ['object' => $object])) {
                return true;
            }
        }

        return false;
    }

    public function takeAction(object $object, Context $context = new Context()): FurtherAction
    {
        $this->notifier->currentContext = $context;
        $type = ComplexRuleType::fromClass(get_class($object));

        $canContinue = true;
        $rules = $this->ruleRepository->findBy(['enabled' => true, 'type' => $type]);
        foreach ($rules as $rule) {
            if (!$canContinue && $rule->getRunConfiguration() !== RunConfiguration::Always) {
                continue;
            }
            $ruleExpression = $rule->getRule();
            if (!$this->expressionLanguage->evaluate($ruleExpression, ['object' => $object])) {
                continue;
            }
            $this->logger->debug("Running complex rule with id '{$rule->getId()}'");
            $expression = $rule->getActions();
            try {
                $result = (bool) $this->expressionLanguage->evaluate($expression, [
                    'object' => $object,
                ]);
            } catch (SyntaxError $e) {
                $context->addMessage("There's a syntax error in complex rule with id '{$rule->getId()}': {$e->getMessage()}");
                continue;
            }
            if (!$result) {
                $canContinue = false;
            }
        }

        return $canContinue ? FurtherAction::CanContinue : FurtherAction::ShouldAbort;
    }

    public function getRunConfiguration(): RunConfiguration
    {
        return RunConfiguration::Always;
    }
}
