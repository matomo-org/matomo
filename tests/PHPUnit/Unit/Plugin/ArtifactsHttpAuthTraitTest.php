<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Core\Plugin;

use PHPUnit\Framework\TestCase;
use Piwik\Plugin\ArtifactsHttpAuthTrait;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @group Core
 */
class ArtifactsHttpAuthTraitTest extends TestCase
{
    public function testPasswordOptionIsUsedWhenStdinIsNotRequested()
    {
        $command = $this->makeCommand(['--http-password' => 'from-option']);

        $this->assertSame('from-option', $command->resolvePassword());
    }

    public function testNoPasswordIsResolvedWhenNoOptionIsGiven()
    {
        $command = $this->makeCommand([]);

        $this->assertNull($command->resolvePassword());
    }

    /**
     * @dataProvider getConflictingPasswords
     */
    public function testPasswordOptionAndStdinCannotBeCombined(string $password)
    {
        $command = $this->makeCommand(['--http-password' => $password, '--http-password-stdin' => true]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Pass either --http-password or --http-password-stdin, not both.');

        $command->resolvePassword();
    }

    public function getConflictingPasswords(): array
    {
        return [
            'a password'        => ['from-option'],
            'a falsy password'  => ['0'],
        ];
    }

    /**
     * @dataProvider getPipedPasswords
     */
    public function testPasswordIsReadFromTheStream(string $piped, string $expected)
    {
        $command = $this->makeCommand([]);

        $this->assertSame($expected, $command::readStream($this->makeStream($piped)));
    }

    public function getPipedPasswords(): array
    {
        return [
            'password only'          => ['s3cret', 's3cret'],
            'trailing newline'       => ["s3cret\n", 's3cret'],
            'trailing CRLF'          => ["s3cret\r\n", 's3cret'],
            'spaces are kept'        => ["a b c\n", 'a b c'],
            'only the first line'    => ["first\nsecond\n", 'first'],
        ];
    }

    /**
     * @dataProvider getEmptyStreams
     */
    public function testEmptyStreamIsRejected(string $piped)
    {
        $command = $this->makeCommand([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('no password was read from STDIN');

        $command::readStream($this->makeStream($piped));
    }

    public function getEmptyStreams(): array
    {
        return [
            'nothing piped' => [''],
            'blank line'    => ["\n"],
        ];
    }

    /**
     * @return resource
     */
    private function makeStream(string $contents)
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $contents);
        rewind($stream);

        return $stream;
    }

    private function makeCommand(array $parameters)
    {
        $definition = new InputDefinition([
            new InputOption('http-password', '', InputOption::VALUE_OPTIONAL),
            new InputOption('http-password-stdin', '', InputOption::VALUE_NONE),
        ]);

        // the trait belongs to a ConsoleCommand, so the fixture has to be one for its option
        // definitions to resolve
        return new class (new ArrayInput($parameters, $definition)) extends ConsoleCommand {
            use ArtifactsHttpAuthTrait;

            private $testInput;

            public function __construct(InputInterface $input)
            {
                parent::__construct('test:artifacts-http-auth');

                $this->testInput = $input;
            }

            protected function getInput(): InputInterface
            {
                return $this->testInput;
            }

            public function resolvePassword(): ?string
            {
                return $this->getArtifactsHttpPassword();
            }

            /**
             * @param resource $stream
             */
            public static function readStream($stream): string
            {
                return self::readPasswordFromStream($stream);
            }
        };
    }
}
