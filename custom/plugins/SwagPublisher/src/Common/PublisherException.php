<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\Common;

interface PublisherException extends \Throwable
{
    public const EXCEPTION_CODE_PREFIX = 'SWAG_PUBLISHER_';
}
