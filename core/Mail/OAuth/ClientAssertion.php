<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Mail\OAuth;

/**
 * Builds the signed JWT a client sends instead of a client secret when it authenticates with a certificate.
 */
class ClientAssertion
{
    public const ASSERTION_TYPE = 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer';

    /**
     * @param string $privateKeyPath when empty, the private key is read from the certificate file
     */
    public static function create(
        string $clientId,
        string $tokenUrl,
        string $certificatePath,
        string $privateKeyPath,
        #[\SensitiveParameter]
        string $privateKeyPassword,
        int $now
    ): string {
        // drop errors left over from earlier openssl calls, so they don't end up in the detail
        self::getOpenSslErrors();

        $certificate = @openssl_x509_read('file://' . $certificatePath);
        if ($certificate === false) {
            self::fail('Could not read a PEM encoded certificate from ' . $certificatePath . '.');
        }

        $privateKey = @openssl_pkey_get_private('file://' . ($privateKeyPath ?: $certificatePath), $privateKeyPassword);
        if ($privateKey === false) {
            self::fail('Could not read the private key. Check that it is PEM encoded and that oauth_private_key_password is correct.');
        }

        if (!openssl_x509_check_private_key($certificate, $privateKey)) {
            self::fail('The private key does not belong to the certificate.');
        }

        $header = [
            'alg'      => 'RS256',
            'typ'      => 'JWT',
            'x5t#S256' => self::base64UrlEncode((string) openssl_x509_fingerprint($certificate, 'sha256', true)),
        ];
        $claims = [
            'aud' => $tokenUrl,
            'iss' => $clientId,
            'sub' => $clientId,
            'jti' => bin2hex(random_bytes(16)),
            'nbf' => $now,
            'iat' => $now,
            'exp' => $now + 300,
        ];

        $unsignedToken = self::base64UrlEncode((string) json_encode($header, JSON_UNESCAPED_SLASHES))
            . '.' . self::base64UrlEncode((string) json_encode($claims, JSON_UNESCAPED_SLASHES));

        if (!openssl_sign($unsignedToken, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            self::fail('Could not sign the client assertion.');
        }

        return $unsignedToken . '.' . self::base64UrlEncode($signature);
    }

    private static function fail(string $reason): never
    {
        $openSslErrors = self::getOpenSslErrors();

        // the message can be shown to visitors who are not logged in, so the reason only goes in the detail
        throw new TokenRequestFailedException(
            'The SMTP OAuth certificate or private key could not be used.',
            $reason . ($openSslErrors !== '' ? ' (' . $openSslErrors . ')' : '')
        );
    }

    private static function getOpenSslErrors(): string
    {
        $errors = [];
        while (($error = openssl_error_string()) !== false) {
            $errors[] = $error;
        }

        return implode('; ', $errors);
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
