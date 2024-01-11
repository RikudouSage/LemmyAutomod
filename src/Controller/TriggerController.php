<?php

namespace App\Controller;

use App\Automod\Automod;
use App\Dto\Request\TriggerPostRequest;
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
        #[MapRequestPayload] TriggerPostRequest $request,
        MessageBusInterface $messageBus,
    ): JsonResponse {
        $messageBus->dispatch(new AnalyzePostMessage(postId: $request->id));
        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
