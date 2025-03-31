<?php

namespace App\Service\Expression;

use App\Message\BanUserMessage;
use Rikudou\LemmyApi\LemmyApi;
use Rikudou\LemmyApi\Response\Model\Comment;
use Rikudou\LemmyApi\Response\Model\Person;
use Rikudou\LemmyApi\Response\View\CommentView;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

final readonly class ExpressionLanguageLemmyFunctions extends AbstractExpressionLanguageFunctionProvider
{
    public function __construct(
        private LemmyApi $api,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'remove_comment',
                $this->uncompilableFunction(),
                $this->removeComment(...),
            ),
            new ExpressionFunction(
                'ban_user',
                $this->uncompilableFunction(),
                $this->banUser(...),
            ),
            new ExpressionFunction(
                'person',
                $this->uncompilableFunction(),
                $this->person(...),
            ),
        ];
    }

    public function removeComment(array $context, int|CommentView|Comment $comment, ?string $reason = null): bool
    {
        $id = match (true) {
            $comment instanceof CommentView => $comment->comment->id,
            $comment instanceof Comment => $comment->id,
            is_int($comment) => $comment,
        };

        return $this->api->moderator()->removeComment(comment: $id, reason: $reason);
    }

    private function banUser(array $context, int|Person $person, ?string $reason = null, bool $removeAll = true): bool
    {
        if (is_int($person)) {
            $person = $this->person($context, $person);
        }

        $this->messageBus->dispatch(new BanUserMessage(
            user: $person,
            reason: $reason ?? '',
            removePosts: $removeAll,
            removeComments: $removeAll,
        ), [
            new DispatchAfterCurrentBusStamp(),
        ]);

        return true;
    }

    private function person(array $context, int|string $idOrUsername): Person
    {
        static $cache = [];
        if (isset($cache[$idOrUsername])) {
            return $cache[$idOrUsername];
        }

        $person = $this->api->user()->get($idOrUsername);
        $cache[$idOrUsername] = $person;

        return $person;
    }
}
