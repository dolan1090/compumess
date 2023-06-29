<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Profile\Shopware6\Writer;

use SwagMigrationAssistant\Migration\DataSelection\DefaultEntities;
use SwagMigrationAssistant\Migration\Writer\AbstractWriter;

class SeoUrlTemplateWriter extends AbstractWriter
{
    public function supports(): string
    {
        return DefaultEntities::SEO_URL_TEMPLATE;
    }
}
