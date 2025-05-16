<?php

namespace App\Controller;

use App\Attribute\WebhookConfig;
use App\Dto\Model\BasicInstanceData;
use App\Dto\Model\LocalUser;
use App\Dto\Model\PrivateMessage;
use App\Dto\Request\InstanceFederatedRequest;
use App\Dto\Request\TriggerIdRequest;
use App\Message\AnalyzeCommentMessage;
use App\Message\AnalyzeCommentReportMessage;
use App\Message\AnalyzeCommunityMessage;
use App\Message\AnalyzeInstanceMessage;
use App\Message\AnalyzeLocalUserMessage;
use App\Message\AnalyzePostMessage;
use App\Message\AnalyzePostReportMessage;
use App\Message\AnalyzePrivateMessageMessage;
use App\Message\AnalyzeRegistrationApplicationMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/triggers')]
final class TriggerController extends AbstractController
{
    #[WebhookConfig(bodyExpression: '{id: data.data.id}', filterExpression: null, objectType: 'post', operation: 'INSERT', enhancedFilter: null)]
    #[WebhookConfig(bodyExpression: '{id: data.data.id}', filterExpression: 'data.data.body !== data.previous.body or data.data.url !== data.previous.url or data.data.name !== data.previous.name', objectType: 'post', operation: 'UPDATE', enhancedFilter: null, uniqueNameSuffix: 'update')]
    #[Route('/post', name: 'app.triggers.post', methods: [Request::METHOD_POST])]
    public function post(
        #[MapRequestPayload] TriggerIdRequest $request,
        MessageBusInterface $messageBus,
    ): JsonResponse {
        $messageBus->dispatch(new AnalyzePostMessage(postId: $request->id));
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }

    #[WebhookConfig(bodyExpression: '{id: data.data.id}', filterExpression: null, objectType: 'comment', operation: 'INSERT', enhancedFilter: null)]
    #[WebhookConfig(bodyExpression: '{id: data.data.id}', filterExpression: 'data.data.content !== data.previous.content', objectType: 'comment', operation: 'UPDATE', enhancedFilter: null, uniqueNameSuffix: 'update')]
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
        $messageBus->dispatch(new AnalyzeRegistrationApplicationMessage($request->id), [
            new DelayStamp(15_000),
        ]);
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }

    #[WebhookConfig(
        bodyExpression: '{instance: data.data.domain}',
        filterExpression: null,
        objectType: 'instance',
        operation: 'INSERT',
        enhancedFilter: null,
    )]
    #[Route('/instance/federated', name: 'app.triggers.instance.federated', methods: [Request::METHOD_POST])]
    public function newInstanceFederated(
        #[MapRequestPayload] InstanceFederatedRequest $request,
        MessageBusInterface $messageBus,
    ): JsonResponse {
        $messageBus->dispatch(new AnalyzeInstanceMessage(
            instance: $request->instance,
        ));
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }

    #[WebhookConfig(bodyExpression: '{id: data.data.id}', filterExpression: null, objectType: 'community', operation: 'INSERT', enhancedFilter: null)]
    #[WebhookConfig(bodyExpression: '{id: data.data.id}', filterExpression: null, objectType: 'community', operation: 'UPDATE', enhancedFilter: null, uniqueNameSuffix: 'update')]
    #[Route('/community', name: 'app.triggers.community', methods: [Request::METHOD_POST])]
    public function community(
        #[MapRequestPayload] TriggerIdRequest $request,
        MessageBusInterface $messageBus,
    ): JsonResponse {
        $messageBus->dispatch(new AnalyzeCommunityMessage(
            communityId: $request->id,
        ));

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }

    #[WebhookConfig(bodyExpression: 'private_message_with_content(data.data.id)', filterExpression: null, objectType: 'private_message', operation: 'INSERT', enhancedFilter: null)]
    #[Route('/private-message', name: 'app.triggers.private_message', methods: [Request::METHOD_POST])]
    public function privateMessage(
        #[MapRequestPayload] PrivateMessage $request,
        MessageBusInterface $messageBus,
    ): JsonResponse {
        $messageBus->dispatch(new AnalyzePrivateMessageMessage($request));

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
