<?php declare(strict_types=1);

namespace Shopware\Commercial\TextTranslator\Entity\Review;

use Shopware\Core\Content\Product\Aggregate\ProductReview\ProductReviewEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\Language\LanguageEntity;

#[Package('inventory')]
class ProductReviewTranslationEntity extends Entity
{
    use EntityIdTrait;

    protected string $reviewId;

    protected string $languageId;

    protected ?string $title;

    protected ?string $content;

    protected ?string $comment;

    protected ?ProductReviewEntity $review = null;

    protected ?LanguageEntity $language = null;

    public function getReviewId(): string
    {
        return $this->reviewId;
    }

    public function setReviewId(string $reviewId): void
    {
        $this->reviewId = $reviewId;
    }

    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    public function setLanguageId(string $languageId): void
    {
        $this->languageId = $languageId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getReview(): ?ProductReviewEntity
    {
        return $this->review;
    }

    public function setReview(?ProductReviewEntity $review): void
    {
        $this->review = $review;
    }

    public function getLanguage(): ?LanguageEntity
    {
        return $this->language;
    }

    public function setLanguage(?LanguageEntity $language): void
    {
        $this->language = $language;
    }
}
