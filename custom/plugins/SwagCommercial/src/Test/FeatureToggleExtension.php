<?php declare(strict_types=1);

namespace Shopware\Commercial\Test;

use Doctrine\Common\Annotations\AnnotationReader;
use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token\Signature;
use Lcobucci\JWT\UnencryptedToken;
use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeTestHook;
use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Test\Annotation\ActiveFeatureToggles;
use Symfony\Component\Config\Util\XmlUtils;

/**
 * @internal
 */
class FeatureToggleExtension implements BeforeTestHook, AfterTestHook
{
    private readonly AnnotationReader $annotationReader;

    /**
     * @var \ReflectionClass<License>
     */
    private readonly \ReflectionClass $licenseReflection;

    /**
     * @var UnencryptedToken|false|mixed|null
     */
    private $beforeLicense;

    public function __construct()
    {
        $this->annotationReader = new AnnotationReader();
        $this->licenseReflection = new \ReflectionClass(License::class);
    }

    public function executeBeforeTest(string $test): void
    {
        $this->beforeLicense = $this->licenseReflection->getStaticPropertyValue('license');

        $toggles = $this->getToggles($test);

        if ($toggles === []) {
            return;
        }

        $this->licenseReflection->setStaticPropertyValue('license', new Plain(new DataSet([], ''), new DataSet(['license-toggles' => $toggles], ''), new Signature('test', 'test')));
        $this->licenseReflection->setStaticPropertyValue('toggles', $toggles);
    }

    public function executeAfterTest(string $test, float $time): void
    {
        $this->licenseReflection->setStaticPropertyValue('license', $this->beforeLicense);
    }

    /**
     * @return array<string, mixed>
     */
    private function getToggles(string $test): array
    {
        preg_match('/([^:]+)::([^$ ]+)($| )/', $test, $matches);

        if (!isset($matches[1], $matches[2])) {
            return [];
        }

        /** @var class-string $class */
        $class = $matches[1];
        $method = $matches[2];

        if (!class_exists($class)) {
            return [];
        }
        $class = new \ReflectionClass($class);

        $method = $class->getMethod($method);

        // Method annotations have presence over class annotations
        $annotations = \array_merge(
            $this->annotationReader->getClassAnnotations($class),
            $this->annotationReader->getMethodAnnotations($method),
        );

        $toggles = [];
        foreach ($annotations as $annotation) {
            if ($annotation instanceof ActiveFeatureToggles) {
                /** @var array<string, string|int> $toggles */
                $toggles = [...$toggles, ...$annotation->toggles];
            }
        }

        /**
         * @var string $name
         * @var string|int $toggle
         */
        foreach ($toggles as $name => $toggle) {
            $toggles[$name] = XmlUtils::phpize((string) $toggle);
        }

        return $toggles;
    }
}
