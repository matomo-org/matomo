<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugin;

/**
 * Shared HTTP auth options for the console commands that download build artifacts.
 *
 * Artifacts of premium plugins are protected, so those commands need credentials for the artifacts
 * server. Reading the password from STDIN keeps it out of the process list and the shell history,
 * which lets it be resolved from an OS credential store at the time the command runs.
 *
 * Can only be used in a {@see ConsoleCommand}.
 *
 * @internal
 */
trait ArtifactsHttpAuthTrait
{
    protected function addArtifactsHttpAuthOptions(): void
    {
        $this->addOptionalValueOption('http-user', '', 'the HTTP AUTH username (for premium plugins where artifacts are protected)');
        $this->addOptionalValueOption('http-password', '', 'the HTTP AUTH password (for premium plugins where artifacts are protected)');
        $this->addNoValueOption('http-password-stdin', '', 'read the HTTP AUTH password from STDIN instead of passing it as an option value');
    }

    protected function getArtifactsHttpPassword(): ?string
    {
        $input    = $this->getInput();
        $password = $input->getOption('http-password');

        if (!$input->getOption('http-password-stdin')) {
            return $password;
        }

        // a password of "0" is falsy, but it was still passed
        if (null !== $password && '' !== $password) {
            throw new \InvalidArgumentException('Pass either --http-password or --http-password-stdin, not both.');
        }

        return self::readPasswordFromStream(STDIN);
    }

    /**
     * @param resource $stream
     */
    protected static function readPasswordFromStream($stream): string
    {
        // reading from a terminal would block until something is typed, which looks like a hang
        if (stream_isatty($stream)) {
            throw new \InvalidArgumentException(
                '--http-password-stdin expects the password to be piped in, eg `printf \'%s\' "$password" | ./console ...`.'
            );
        }

        $password = rtrim((string) fgets($stream), "\r\n");

        if ('' === $password) {
            throw new \InvalidArgumentException('--http-password-stdin was given, but no password was read from STDIN.');
        }

        return $password;
    }
}
