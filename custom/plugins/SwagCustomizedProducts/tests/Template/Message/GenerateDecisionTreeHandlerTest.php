<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Template\Message;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Template\Message\GenerateDecisionTreeHandler;
use Swag\CustomizedProducts\Template\Message\GenerateDecisionTreeMessage;
use Swag\CustomizedProducts\Template\TemplateDecisionTreeGenerator;
use Swag\CustomizedProducts\Template\TemplateDecisionTreeGeneratorInterface;

class GenerateDecisionTreeHandlerTest extends TestCase
{
    public function testHandlerDoesNotCallGenerateOnWrongMessage(): void
    {
        /** @var MockObject|TemplateDecisionTreeGenerator $generator */
        $generator = $this->createMock(TemplateDecisionTreeGeneratorInterface::class);
        $handler = new GenerateDecisionTreeHandler($generator);
        $generator->expects(static::never())->method('generate')->withAnyParameters();

        $handler->__invoke(new \stdClass());
    }

    public function testHandlerCallsGenerate(): void
    {
        /** @var MockObject|TemplateDecisionTreeGenerator $generator */
        $generator = $this->createMock(TemplateDecisionTreeGeneratorInterface::class);
        $handler = new GenerateDecisionTreeHandler($generator);
        $templateId = Uuid::randomHex();
        $context = Context::createDefaultContext();
        $msg = new GenerateDecisionTreeMessage($templateId);

        $generator->expects(static::once())->method('generate')->with($templateId, $context);
        $handler->__invoke($msg->withContext($context));
    }
}
