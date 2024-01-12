<?php

namespace App\Controller;

use App\Attribute\WebhookConfig;
use App\Automod\Automod;
use App\Dto\Request\TriggerIdRequest;
use App\Message\AnalyzeCommentMessage;
use App\Message\AnalyzePostMessage;
use App\Message\AnalyzeUserMessage;
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
    #[WebhookConfig(bodyExpression: '{id: data.data.id}', filterExpression: null, objectType: 'post', operation: 'INSERT', enhancedFilter: null)]
    #[Route('/post', name: 'app.triggers.post', methods: [Request::METHOD_POST])]
    public function post(
        #[MapRequestPayload] TriggerIdRequest $request,
        MessageBusInterface $messageBus,
    ): JsonResponse {
        $messageBus->dispatch(new AnalyzePostMessage(postId: $request->id));
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }

    #[WebhookConfig(bodyExpression: '{id: data.data.id}', filterExpression: null, objectType: 'comment', operation: 'INSERT', enhancedFilter: null)]
    #[Route('/comment', name: 'app.triggers.comment', methods: [Request::METHOD_POST])]
    public function comment(
        #[MapRequestPayload] TriggerIdRequest $request,
        MessageBusInterface $messageBus,
    ): JsonResponse
    {
        $messageBus->dispatch(new AnalyzeCommentMessage(commentId: $request->id));
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }

    #[WebhookConfig(bodyExpression: '{id: data.data.id}', filterExpression: 'data.data.local', objectType: 'person', operation: 'INSERT', enhancedFilter: null)]
    #[Route('/new-user', name: 'app.triggers.user.new', methods: [Request::METHOD_POST])]
    public function newLocalUser(
        #[MapRequestPayload] TriggerIdRequest $request,
        MessageBusInterface $messageBus,
    ): JsonResponse {
        $messageBus->dispatch(new AnalyzeUserMessage($request->id));
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
