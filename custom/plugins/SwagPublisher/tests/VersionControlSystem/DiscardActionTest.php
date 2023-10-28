<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\VersionControlSystem;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use SwagPublisher\VersionControlSystem\DiscardAction;
use SwagPublisher\VersionControlSystem\DraftAction;
use SwagPublisherTest\PublisherCmsFixtures;

class DiscardActionTest extends TestCase
{
    use ActionBehaviour;
    use IntegrationTestBehaviour;
    use PublisherCmsFixtures;

    public function testDiscardsRemoveLogs(): void
    {
        $pageId = $this->importPage();
        $context = Context::createDefaultContext();
        $versionId = $this->getContainer()->get(DraftAction::class)->draft($pageId, 'foo', $context);

        $action = $this->getContainer()->get(DiscardAction::class);

        $action->discard($pageId, $versionId, $context);

        $this->assertSingleLogExistsWith('isDiscarded', $context);
    }
}
