<?php

namespace Tests;

use LiquidWeb\HtaccessValidator\Exceptions\ValidationException;
use LiquidWeb\HtaccessValidator\Validator;
use PHPUnit\Framework\TestCase;

/**
 * @covers LiquidWeb\HtaccessValidator\Validator
 */
class ValidatorTest extends TestCase
{
    /**
     * @var string A temporary file to use for testing.
     */
    private static $file;

    /**
     * @beforeClass
     */
    public static function createTmpfile(): void
    {
        $tmpdir = __DIR__ . '/tmp';

        if (! file_exists($tmpdir)) {
            mkdir($tmpdir);
        }

        self::$file = tempnam($tmpdir, 'test-');
    }

    /**
     * @afterClass
     */
    public static function deleteTmpFile(): void
    {
        unlink(self::$file);
    }

    /**
     * @test
     */
    public function it_should_approve_valid_configurations(): void
    {
        $this->writeContents(<<<EOT
<IfModule mod_rewrite>
    Options +FollowSymLinks
    RewriteEngine on
    RewriteCond %{HTTP_HOST} ^www.example.com [NC]
    RewriteRule ^(.*)$ https://example.com/$1 [R=301,L]
</IfModule>
EOT);

        $validator = new Validator(self::$file);

        $this->assertNull($validator->validate());
    }

    /**
     * @test
     */
    public function it_should_throw_an_exception_with_invalid_configurations(): void
    {
        $this->writeContents(<<<EOT
<IfModule mod_rewrite>
    Options +FollowSymLinks!!
    RewriteEngine 👍
    RewriteCond %{HTTP_HOST ^www.example.com [NC]
    RewriteRules ^(.*)$ https://example.com/$1 [R=301,L]
EOT);

        $validator = new Validator(self::$file);

        $this->expectException(ValidationException::class);

        $validator->validate();
    }

    /**
     * @test
     * @testdox isValid() should return TRUE if no exceptions were encountered
     */
    public function isValid_should_return_true_if_no_exceptions_were_encountered(): void
    {
        $validator = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->setMethods(['validate'])
            ->getMock();
        $validator->expects($this->once())
            ->method('validate')
            ->willReturn(null);

        $this->assertTrue($validator->isValid());
    }

    /**
     * @test
     * @testdox isValid() should catch and return false when exceptions are encountered
     */
    public function isValid_should_catch_exceptions(): void
    {
        $validator = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->setMethods(['validate'])
            ->getMock();
        $validator->expects($this->once())
            ->method('validate')
            ->will($this->throwException(new ValidationException('Invalid')));

        $this->assertFalse($validator->isValid());
    }

    /**
     * @test
     * @testdox createFromString() will allow contents to be passed directly
     */
    public function createFromString_will_allow_contents_to_be_passed_directly()
    {
        $contents = <<<EOT
Options +FollowSymLinks
RewriteEngine on
RewriteCond %{HTTP_HOST} ^www.example.com [NC]
RewriteRule ^(.*)$ https://example.com/$1 [R=301,L]
EOT;

        $validator = Validator::createFromString($contents);
        $tmpfile   = $validator->getFilePath();

        $this->assertTrue(file_exists($tmpfile));
        $this->assertSame($contents, file_get_contents($tmpfile));
    }

    /**
     * @test
     * @testdox Temporary files should be deleted when the validator is destroyed
     */
    public function temp_files_should_be_deleted_when_the_validator_is_destroyed()
    {
        $validator = Validator::createFromString('Options +FollowSymLinks');
        $tmpfile   = $validator->getFilePath();

        $this->assertTrue(file_exists($tmpfile));
        unset($validator);
        $this->assertFalse(file_exists($tmpfile));
    }

    /**
     * @test
     * @testdox Real (e.g. non-temporary) files should not be deleted when the validator is destroyed.
     */
    public function real_files_should_not_be_deleted_when_the_validator_is_destroyed()
    {
        $validator = new Validator(self::$file);
        $realfile  = $validator->getFilePath();

        unset($validator);
        $this->assertTrue(file_exists($realfile));
    }

    /**
     * Overwrite the contents of self::$file.
     */
    protected function writeContents(string $contents): void
    {
        file_put_contents(self::$file, $contents);
    }
}
