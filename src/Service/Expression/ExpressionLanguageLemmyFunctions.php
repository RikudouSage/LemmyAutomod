<?php

namespace App\Service\Expression;

use Rikudou\LemmyApi\LemmyApi;
use Rikudou\LemmyApi\Response\Model\Comment;
use Rikudou\LemmyApi\Response\View\CommentView;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

final readonly class ExpressionLanguageLemmyFunctions extends AbstractExpressionLanguageFunctionProvider
{
    public function __construct(
        private LemmyApi $api,
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
}
