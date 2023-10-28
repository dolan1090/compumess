<?php declare(strict_types=1);

namespace Shopware\Commercial\Captcha\Storefront\Framework\Captcha;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Client\ClientExceptionInterface;
use Shopware\Commercial\Captcha\Exception\FriendlyCaptchaException;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Log\Package;
use Shopware\Storefront\Framework\Captcha\AbstractCaptcha;
use Symfony\Component\HttpFoundation\Request;

#[Package('checkout')]
class FriendlyCaptcha extends AbstractCaptcha
{
    final public const CAPTCHA_NAME = 'friendlyCaptcha';

    final public const CAPTCHA_REQUEST_PARAMETER = 'frc-captcha-solution';

    final public const FRC_CAPTCHA_PUZZLE_ENDPOINT = 'https://api.friendlycaptcha.com/api/v1/puzzle';

    private const FRC_CAPTCHA_VERIFY_ENDPOINT = 'https://api.friendlycaptcha.com/api/v1/siteverify';

    private const FEATURE_TOGGLE_FOR_SERVICE = 'CAPTCHA-8765432';

    /**
     * @internal
     */
    public function __construct(
        private readonly ClientInterface $client
    ) {
    }

    /**
     * @param array<string, array<string, string>|string|bool> $captchaConfig
     *
     * @throws GuzzleException
     */
    public function isValid(Request $request, array $captchaConfig = []): bool
    {
        if (!License::get(self::FEATURE_TOGGLE_FOR_SERVICE)) {
            throw FriendlyCaptchaException::licenseExpired();
        }

        if (!$request->get(self::CAPTCHA_REQUEST_PARAMETER)) {
            return false;
        }

        $secretKey = !empty($captchaConfig['config']['secretKey']) ? $captchaConfig['config']['secretKey'] : null;
        $siteKey = !empty($captchaConfig['config']['siteKey']) ? $captchaConfig['config']['siteKey'] : null;

        if (!\is_string($secretKey) || !\is_string($siteKey)) {
            return false;
        }

        try {
            $response = $this->client->request('POST', self::FRC_CAPTCHA_VERIFY_ENDPOINT, [
                'form_params' => [
                    'secret' => $secretKey,
                    'solution' => $request->get(self::CAPTCHA_REQUEST_PARAMETER),
                    'sitekey' => $siteKey,
                ],
            ]);

            /** @var array<string, mixed> $response */
            $response = \json_decode($response->getBody()->getContents(), true) ?? [];

            return \array_key_exists('success', $response) && $response['success'];
        } catch (ClientExceptionInterface) {
            return false;
        }
    }

    public function supports(Request $request, array $captchaConfig): bool
    {
        if (!License::get(self::FEATURE_TOGGLE_FOR_SERVICE)) {
            return false;
        }

        return parent::supports($request, $captchaConfig);
    }

    public function getName(): string
    {
        return self::CAPTCHA_NAME;
    }
}
