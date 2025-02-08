<?php

namespace App\Automod\Enum;

use App\Dto\Model\EnrichedInstanceData;
use App\Dto\Model\LocalUser;
use InvalidArgumentException;
use Rikudou\LemmyApi\Response\Model\Person;
use Rikudou\LemmyApi\Response\View\CommentReportView;
use Rikudou\LemmyApi\Response\View\CommentView;
use Rikudou\LemmyApi\Response\View\CommunityView;
use Rikudou\LemmyApi\Response\View\PostReportView;
use Rikudou\LemmyApi\Response\View\PostView;
use Rikudou\LemmyApi\Response\View\PrivateMessageReportView;
use Rikudou\LemmyApi\Response\View\RegistrationApplicationView;

enum ComplexRuleType: string
{
    case Post = 'post';
    case Comment = 'comment';
    case Person = 'person';
    case CommentReport = 'comment_report';
    case PostReport = 'post_report';
    case PrivateMessageReport = 'private_message_report';
    case RegistrationApplication = 'registration_application';
    case LocalUser = 'local_user';
    case Instance = 'instance';
    case Community = 'community';

    public static function fromClass(string $class): self
    {
        return match ($class) {
            PostView::class => self::Post,
            CommentView::class => self::Comment,
            Person::class => self::Person,
            CommentReportView::class => self::CommentReport,
            PostReportView::class => self::PostReport,
            PrivateMessageReportView::class => self::PrivateMessageReport,
            RegistrationApplicationView::class => self::RegistrationApplication,
            LocalUser::class => self::LocalUser,
            EnrichedInstanceData::class => self::Instance,
            CommunityView::class => self::Community,
            default => throw new InvalidArgumentException("Unsupported class: {$class}"),
        };
    }
}
