<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Migration\Logging\Log;

interface LogEntryInterface
{
    final public const LOG_LEVEL_INFO = 'info';
    final public const LOG_LEVEL_WARNING = 'warning';
    final public const LOG_LEVEL_ERROR = 'error';
    final public const LOG_LEVEL_DEBUG = 'debug';

    public function getLevel(): string;

    public function getCode(): string;

    public function getTitle(): string;

    public function getParameters(): array;

    public function getDescription(): string;

    public function getTitleSnippet(): string;

    public function getDescriptionSnippet(): string;

    public function getEntity(): ?string;

    public function getSourceId(): ?string;

    public function getRunId(): ?string;
}
