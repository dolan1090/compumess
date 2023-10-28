<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Exception;

use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Core\Framework\HttpException;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[Package('checkout')]
class EmployeeManagementException extends HttpException
{
    public const BUSINESS_PARTNER_NOT_LOGGED_IN_CODE = 'B2B__BUSINESS_PARTNER_NOT_LOGGED_IN';

    public const EMPLOYEE_MISSING_PERMISSIONS = 'B2B__EMPLOYEE_MISSING_PERMISSIONS';

    public const EMPLOYEE_NOT_FOUND_CODE = 'B2B__EMPLOYEE_NOT_FOUND';

    public const ROLE_NOT_FOUND_CODE = 'B2B__ROLE_NOT_FOUND';

    public const INVALID_REQUEST_ARGUMENT_CODE = 'B2B__INVALID_ARGUMENT';

    public const CUSTOMER_NOT_FOUND_BY_HASH_CODE = 'B2B__CUSTOMER_NOT_FOUND_BY_HASH_CODE';

    public const CUSTOMER_NOT_FOUND_BY_EMAIL_CODE = 'B2B__CUSTOMER_NOT_FOUND_BY_EMAIL';

    public const HASH_EXPIRED_CODE = 'B2B__HASH_EXPIRED_CODE';

    public const UNEXPECTED_TYPE_CODE = 'B2B__UNEXPECTED_TYPE';

    public const ALREADY_EXISTING_PERMISSION_CODE = 'B2B__ALREADY_EXISTING_PERMISSION';

    public const DECORATION_PATTERN_CODE = 'B2B__DECORATION_PATTERN';

    public const BUSINESS_PARTNER_NOT_FOUND = 'B2B__BUSINESS_PARTNER_NOT_FOUND';

    public const EMPLOYEE_ID_PARAMETER_MISSING = 'B2B__EMPLOYEE_ID_PARAMETER_MISSING';

    public const BAD_CREDENTIALS = 'B2B__BAD_CREDENTIALS';

    public const EMPLOYEE_MAIL_NOT_UNIQUE = 'B2B__EMPLOYEE_MAIL_NOT_UNIQUE';

    public static function businessPartnerNotLoggedIn(): self
    {
        return new self(
            Response::HTTP_FORBIDDEN,
            self::BUSINESS_PARTNER_NOT_LOGGED_IN_CODE,
            'Business partner is not logged in.'
        );
    }

    /**
     * @param string[] $permissions
     */
    public static function employeeMissingPermissions(array $permissions): self
    {
        return new self(
            Response::HTTP_FORBIDDEN,
            self::EMPLOYEE_MISSING_PERMISSIONS,
            sprintf('Employee is missing permissions: %s', implode(', ', $permissions))
        );
    }

    public static function employeeNotFound(): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::EMPLOYEE_NOT_FOUND_CODE,
            'No matching employee was found.'
        );
    }

    public static function roleNotFound(): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::ROLE_NOT_FOUND_CODE,
            'No matching role was found.'
        );
    }

    public static function invalidRequestArgument(string $message): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::INVALID_REQUEST_ARGUMENT_CODE,
            $message
        );
    }

    public static function customerNotFoundByEmail(string $email): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::CUSTOMER_NOT_FOUND_BY_EMAIL_CODE,
            sprintf('No matching customer for the email "%s" was found.', $email),
        );
    }

    public static function hashExpired(string $hash): self
    {
        return new self(
            Response::HTTP_GONE,
            self::HASH_EXPIRED_CODE,
            sprintf('The hash "%s" is expired.', $hash),
        );
    }

    public static function alreadyExistingPermission(?string $permission): self
    {
        $message = 'The permission already exists.';

        if (\is_string($permission)) {
            $message = sprintf('The permission "%s" already exists.', $permission);
        }

        return new self(
            Response::HTTP_CONFLICT,
            self::ALREADY_EXISTING_PERMISSION_CODE,
            $message,
        );
    }

    public static function unexpectedType(mixed $currentType, string $expectedType): self
    {
        return new self(
            Response::HTTP_BAD_GATEWAY,
            self::UNEXPECTED_TYPE_CODE,
            sprintf('Expected argument of type "%s", "%s" given', $expectedType, get_debug_type($currentType)),
        );
    }

    public static function decorationPattern(string $class): self
    {
        return new self(
            Response::HTTP_NOT_IMPLEMENTED,
            self::DECORATION_PATTERN_CODE,
            sprintf('The getDecorated() function of core class %s cannot be used. This class is the base class.', $class),
        );
    }

    public static function businessPartnerNotFound(string $id): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::BUSINESS_PARTNER_NOT_FOUND,
            sprintf('No matching business partner for the id "%s" was found.', $id),
        );
    }

    public static function employeeIdParameterMissing(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::EMPLOYEE_ID_PARAMETER_MISSING,
            'The employee id parameter is missing.',
        );
    }

    public static function badCredentials(): self
    {
        return new self(
            Response::HTTP_UNAUTHORIZED,
            self::BAD_CREDENTIALS,
            'Invalid credentials.',
        );
    }

    public static function employeeMailNotUnique(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::EMPLOYEE_MAIL_NOT_UNIQUE,
            'The email address is already in use.',
        );
    }

    public static function licenseExpired(): LicenseExpiredException
    {
        return new LicenseExpiredException();
    }
}
