<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem\Exception;

use SwagPublisher\Common\PublisherException;

class NotFoundException extends \InvalidArgumentException implements PublisherException
{
}
