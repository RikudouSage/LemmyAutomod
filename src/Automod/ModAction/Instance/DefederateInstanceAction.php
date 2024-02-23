<?php

namespace App\Automod\ModAction\Instance;

use App\Automod\ModAction\AbstractModAction;
use App\Context\Context;
use App\Dto\Model\EnrichedInstanceData;
use App\Entity\InstanceDefederationRule;
use App\Enum\FurtherAction;
use App\Repository\InstanceDefederationRuleRepository;
use Override;
use Rikudou\LemmyApi\Response\Model\Instance;

/**
 * @extends AbstractModAction<EnrichedInstanceData>
 */
final readonly class DefederateInstanceAction extends AbstractModAction
{
    public function __construct(
        private InstanceDefederationRuleRepository $ruleRepository,
    ) {
    }

    #[Override]
    public function shouldRun(object $object): bool
    {
        if (!$object instanceof EnrichedInstanceData) {
            return false;
        }

        if (!count($this->ruleRepository->findForSoftwareOrNull($object->software))) {
            return false;
        }

        return true;
    }

    #[Override]
    public function takeAction(object $object, Context $context = new Context()): FurtherAction
    {
        $rules = $this->ruleRepository->findForSoftwareOrNull($object->software);
        foreach ($rules as $rule) {
            if (!$this->matchesOpenRegistrationRequirements($object, $rule, $context)) {
                $this->defederate($object);
                return FurtherAction::ShouldAbort;
            }
            if (!$this->matchesMinimumVersion($object, $rule, $context)) {
                $this->defederate($object, $rule, $context);
                return FurtherAction::ShouldAbort;
            }
        }

        return FurtherAction::CanContinue;
    }

    private function matchesOpenRegistrationRequirements(
        EnrichedInstanceData $object,
        InstanceDefederationRule $rule,
        Context $context,
    ): bool {
        $default = $rule->getTreatMissingDataAs();

        $instanceAllowsOpenRegistrations = $object->openRegistrations ?? $default;
        $instanceHasCaptcha = $object->captcha ?? $default;
        $instanceHasRegistrationApplications = $object->applications ?? $default;
        $instanceHasEmailVerification = $object->emailVerification ?? $default;

        if ($instanceAllowsOpenRegistrations === false) {
            return true;
        }
        if ($rule->allowsOpenRegistrations() !== false) {
            return true;
        }
        if ($rule->allowsOpenRegistrationsWithCaptcha() && $instanceHasCaptcha) {
            return true;
        }
        if ($rule->allowsOpenRegistrationsWithApplication() && $instanceHasRegistrationApplications) {
            return true;
        }
        if ($rule->allowsOpenRegistrationsWithEmailVerification() && $instanceHasEmailVerification) {
            return true;
        }

        $stringify = fn (?bool $value) => $value === null ? 'unknown' : ($value ? 'yes' : 'no');

        $message = "The instance has been defederated from based on the following rule:\n\n";

        $message .= "Defederate if the instance has open registrations";
        if ($rule->allowsOpenRegistrationsWithCaptcha() || $rule->allowsOpenRegistrationsWithApplication() || $rule->allowsOpenRegistrationsWithEmailVerification()) {
            $message .= ", unless any of the following is true:\n\n";
        } else {
            $message .= ".";
        }
        $message .= "\n\n";

        if ($rule->allowsOpenRegistrationsWithCaptcha()) {
            $message .= "- instance does have captcha enabled";
        }
        if ($rule->allowsOpenRegistrationsWithApplication()) {
            $message .= "- instance does have registration applications enabled";
        }
        if ($rule->allowsOpenRegistrationsWithEmailVerification()) {
            $message .= "- instance does have email verification";
        }
        $message .= "\n\n";

        $message .= "The instance has the following attributes:\n\n";
        $message .= "- Software: " . ($object->software ?: $stringify($object->software)) . "\n";
        $message .= "- Version: " . ($object->version ?: $stringify($object->version)) . "\n";
        $message .= "- Allows open registrations: {$stringify($instanceAllowsOpenRegistrations)}\n";
        $message .= "- Has captcha enabled: {$stringify($instanceHasCaptcha)}\n";
        $message .= "- Has email verification enabled: {$stringify($instanceHasEmailVerification)}\n";
        $message .= "- Has registration applications: {$stringify($instanceHasRegistrationApplications)}\n";

        $context->addMessage($message);

        return false;
    }

    private function defederate(EnrichedInstanceData $object): void
    {
        $instances = array_map(
            fn (Instance $instance) => $instance->domain,
            $this->api->site()->getFederatedInstances()->blocked,
        );
        if (in_array($object->instance, $instances, true)) {
            return;
        }
        $instances[] = $object->instance;

        $this->api->site()->update(blockedInstances: $instances);
    }

    private function matchesMinimumVersion(
        EnrichedInstanceData $object,
        InstanceDefederationRule $rule,
        Context $context,
    ): bool {
        if (!$rule->getSoftware()) {
            return true;
        }
        if ($rule->getMinimumVersion() === null) {
            return true;
        }

        $default = $rule->getTreatMissingDataAs();

        if ($default === false) {
            $default = '0.0.0';
        } elseif ($default === true) {
            $default = $rule->getMinimumVersion();
        }

        $instanceVersion = $object->version ?? $default;
        if ($instanceVersion === null) {
            $context->addMessage("The instance has **NOT** been defederated from because the version cannot be found and there's no setting for how to treat unknown data.");
            return true;
        }

        if (version_compare($instanceVersion, $rule->getMinimumVersion()) > -1) {
            return true;
        }

        $context->addMessage("The instance has been defederated from, because the instance version ({$instanceVersion}) does not match your configured minimum ({$rule->getMinimumVersion()})");
        return false;
    }
}
