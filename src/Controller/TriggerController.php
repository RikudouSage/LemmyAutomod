<?php

namespace App\Controller;

use App\Automod\Automod;
use App\Dto\Request\TriggerIdRequest;
use App\Message\AnalyzeCommentMessage;
use App\Message\AnalyzePostMessage;
use Rikudou\LemmyApi\LemmyApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/triggers')]
final class TriggerController extends AbstractController
{
    #[Route('/post', name: 'app.triggers.post', methods: [Request::METHOD_POST])]
    public function post(
        #[MapRequestPayload] TriggerIdRequest $request,
        MessageBusInterface $messageBus,
    ): JsonResponse {
        $messageBus->dispatch(new AnalyzePostMessage(postId: $request->id));
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/comment', name: 'app.triggers.comment', methods: [Request::METHOD_POST])]
    public function comment(
        #[MapRequestPayload] TriggerIdRequest $request,
        MessageBusInterface $messageBus,
    ): JsonResponse
    {
        $messageBus->dispatch(new AnalyzeCommentMessage(commentId: $request->id));
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
