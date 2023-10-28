<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Message;

use Swag\CustomizedProducts\Template\TemplateDecisionTreeGeneratorInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * @deprecated tag:v6.6.0 - reason:class-hierarchy-change - Won't implement MessageSubscriberInterface anymore
 */
#[AsMessageHandler]
class GenerateDecisionTreeHandler implements MessageSubscriberInterface
{
    public function __construct(private readonly TemplateDecisionTreeGeneratorInterface $treeGenerator)
    {
    }

    /**
     * @param GenerateDecisionTreeMessage|mixed $message
     */
    public function __invoke($message): void
    {
        if (!($message instanceof GenerateDecisionTreeMessage)) {
            return;
        }

        $this->treeGenerator->generate($message->getTemplateId(), $message->readContext());
    }

    /**
     * @deprecated tag:v6.6.0 - reason:class-hierarchy-change - method will be removed
     *
     * @return iterable<int|string>
     */
    public static function getHandledMessages(): iterable
    {
        return [
            GenerateDecisionTreeMessage::class,
        ];
    }
}
