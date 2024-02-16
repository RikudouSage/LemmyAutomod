<?php

namespace App\Controller;

use App\Attribute\WebhookConfig;
use App\Dto\Model\LocalUser;
use App\Dto\Request\TriggerIdRequest;
use App\Message\AnalyzeCommentMessage;
use App\Message\AnalyzeCommentReportMessage;
use App\Message\AnalyzeLocalUserMessage;
use App\Message\AnalyzePostMessage;
use App\Message\AnalyzePostReportMessage;
use App\Message\AnalyzeRegistrationApplicationMessage;
use App\Message\AnalyzeUserMessage;
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
    #[WebhookConfig(bodyExpression: '{id: data.data.id}', filterExpression: null, objectType: 'post', operation: 'UPDATE', enhancedFilter: null, uniqueNameSuffix: 'update')]
    #[Route('/post', name: 'app.triggers.post', methods: [Request::METHOD_POST])]
    public function post(
        #[MapRequestPayload] TriggerIdRequest $request,
        MessageBusInterface $messageBus,
    ): JsonResponse {
        $messageBus->dispatch(new AnalyzePostMessage(postId: $request->id));
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }

    #[WebhookConfig(bodyExpression: '{id: data.data.id}', filterExpression: null, objectType: 'comment', operation: 'INSERT', enhancedFilter: null)]
    #[WebhookConfig(bodyExpression: '{id: data.data.id}', filterExpression: null, objectType: 'comment', operation: 'UPDATE', enhancedFilter: null, uniqueNameSuffix: 'update')]
    #[Route('/comment', name: 'app.triggers.comment', methods: [Request::METHOD_POST])]
    public function comment(
        #[MapRequestPayload] TriggerIdRequest $request,
        MessageBusInterface $messageBus,
    ): JsonResponse
    {
        $messageBus->dispatch(new AnalyzeCommentMessage(commentId: $request->id));
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }

    #[WebhookConfig(bodyExpression: 'data.data', filterExpression: null, objectType: 'local_user', operation: 'INSERT', enhancedFilter: null)]
    #[Route('/new-user', name: 'app.triggers.user.new', methods: [Request::METHOD_POST])]
    public function newLocalUser(
        #[MapRequestPayload] LocalUser $request,
        MessageBusInterface $messageBus,
    ): JsonResponse {
        $messageBus->dispatch(new AnalyzeLocalUserMessage($request));
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }

    #[WebhookConfig(bodyExpression: '{id: data.data.commentId}', filterExpression: null, objectType: 'comment_report', operation: 'INSERT', enhancedFilter: null)]
    #[Route('/report/comment', name: 'app.triggers.report.comment', methods: [Request::METHOD_POST])]
    public function newCommentReportCreated(
        #[MapRequestPayload] TriggerIdRequest $request,
        MessageBusInterface $messageBus,
    ): JsonResponse {
        $messageBus->dispatch(new AnalyzeCommentReportMessage($request->id));
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }

    #[WebhookConfig(bodyExpression: '{id: data.data.postId}', filterExpression: null, objectType: 'post_report', operation: 'INSERT', enhancedFilter: null)]
    #[Route('/report/post', name: 'app.triggers.report.post', methods: [Request::METHOD_POST])]
    public function newPostReportCreated(
        #[MapRequestPayload] TriggerIdRequest $request,
        MessageBusInterface $messageBus,
    ): JsonResponse {
        $messageBus->dispatch(new AnalyzePostReportMessage($request->id));
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }

    #[WebhookConfig(bodyExpression: '{id: data.data.id}', filterExpression: null, objectType: 'registration_application', operation: 'INSERT', enhancedFilter: null)]
    #[Route('/registration-application', name: 'app.triggers.registration_application', methods: [Request::METHOD_POST])]
    public function registrationApplicationCreated(
        #[MapRequestPayload] TriggerIdRequest $request,
        MessageBusInterface $messageBus,
    ): JsonResponse {
        $messageBus->dispatch(new AnalyzeRegistrationApplicationMessage($request->id));
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
