<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Profile\Writer;

use Swag\CustomizedProducts\Profile\Shopware\DataSelection\DataSet\TemplateDataSet;
use SwagMigrationAssistant\Migration\Writer\AbstractWriter;

class TemplateWriter extends AbstractWriter
{
    public function supports(): string
    {
        return TemplateDataSet::getEntity();
    }
}
