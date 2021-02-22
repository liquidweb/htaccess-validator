<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the validate-htaccess shell script.
 */
class ValidateHtaccessTest extends TestCase
{
	/**
     * @test
     */
    public function it_should_approve_valid_Htaccess_files(): void
    {
        $fh = tmpfile();
        fwrite($fh, <<<EOT
<IfModule mod_rewrite>
    Options +FollowSymLinks
    RewriteEngine on
    RewriteCond %{HTTP_HOST} ^www.example.com [NC]
    RewriteRule ^(.*)$ https://example.com/$1 [R=301,L]
</IfModule>
EOT );

        $this->assertSame(0, $this->validateHtaccess(stream_get_meta_data($fh)['uri'])->exitCode);
    }

    /**
     * @test
     */
    public function it_should_catch_invalid_Htaccess_files(): void
    {
        $fh = tmpfile();
        fwrite($fh, <<<EOT
<IfModule mod_rewrite>
    Options +FollowSymLinks!!
    RewriteEngine 👍
    RewriteCond %{HTTP_HOST ^www.example.com [NC]
    RewriteRules ^(.*)$ https://example.com/$1 [R=301,L]
EOT );

        $response = $this->validateHtaccess(stream_get_meta_data($fh)['uri']);
        $this->assertSame(1, $response->exitCode);
        $this->assertStringContainsString('Expected </IfModule> before end of configuration', $response->output);
    }

    /**
     * Invoke the shell script.
     *
     * @throws \RuntimeException if the shell script cannot be run.
     *
     * @param string $file The file to check.
     *
     * @return object {
     *   An object describing the run.
     *
     *   @type int    $exitCode The script's exit code.
     *   @type string $output   The script's output.
     */
    protected function validateHtaccess(string $file): object
    {
        $script = dirname(__DIR__) . '/bin/validate-htaccess';

        if (! is_executable($script)) {
            throw new \RuntimeException(sprintf('%s is not executable, aborting.', $script));
        }

        $run = exec(escapeshellcmd($script . ' ' . escapeshellarg($file) ) . ' 2>&1', $output, $exitCode);

        if (false === $run) {
            throw new \RuntimeException('Unable to run the validate-htaccess script.');
        }

        return (object) [
            'exitCode' => $exitCode,
            'output'   => implode(PHP_EOL, $output),
        ];
    }
}
