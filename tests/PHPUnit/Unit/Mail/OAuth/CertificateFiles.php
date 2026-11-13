<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Unit\Mail\OAuth;

/**
 * Writes a freshly generated self-signed certificate and its private key to temporary files.
 */
class CertificateFiles
{
    public string $certificatePath;
    public string $privateKeyPath;
    public string $certificatePem;

    /**
     * @var string[]
     */
    private array $files = [];

    public function __construct(string $keyPassword = '')
    {
        $privateKey = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
        $request = openssl_csr_new(['commonName' => 'matomo-smtp-test'], $privateKey, ['digest_alg' => 'sha256']);
        $certificate = openssl_csr_sign($request, null, $privateKey, 1, ['digest_alg' => 'sha256']);

        openssl_x509_export($certificate, $certificatePem);
        openssl_pkey_export($privateKey, $privateKeyPem, $keyPassword !== '' ? $keyPassword : null);

        $this->certificatePem = $certificatePem;

        $this->certificatePath = $this->write($this->certificatePem);
        $this->privateKeyPath = $this->write($privateKeyPem);
    }

    public function writeCombinedFile(): string
    {
        return $this->write($this->certificatePem . file_get_contents($this->privateKeyPath));
    }

    public function write(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'matomo-smtp-oauth');
        file_put_contents($path, $contents);
        $this->files[] = $path;

        return $path;
    }

    public function delete(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }
    }
}
