<?php

namespace LiquidWeb\HtaccessValidator;

use LiquidWeb\HtaccessValidator\Exceptions\ValidationException;

class Validator
{
	/**
	 * @var string The file being validated.
	 */
	protected $file;

	/**
	 * @var bool Whether or not $this->file is a temp file.
	 */
	protected $isTempFile = false;

	/**
	 * Construct a new instance of the class.
	 *
	 * @param string $file The system path to the file being validated.
	 */
	public function __construct($file)
	{
		$this->file = (string) $file;
	}

    /**
     * Automatically remove temporary files as script execution ends.
     */
	public function __destruct()
	{
		if ($this->isTempFile) {
			unlink($this->file);
		}
	}

	/**
	 * Retrieve the underlying filepath.
	 *
	 * @return string The path to $this->file.
	 */
	public function getFilePath()
	{
		return $this->file;
	}

	/**
	 * Mark $this->file as being temporary, indicating it should be removed after validation.
	 *
	 * @param bool $isTempFile Whether or not the file should be considered temporary.
	 */
	public function setIsTempFile($isTempFile)
	{
		$this->isTempFile = (bool) $isTempFile;
	}

	/**
	 * Simply return whether or not the given file's syntax is valid.
	 *
	 * @return bool True if validation passes, false if an error is encountered.
	 */
	public function isValid()
	{
		try {
			$this->validate();
		} catch (ValidationException $e) {
			return false;
		}

		return true;
	}

	/**
	 * Validate the given file.
	 *
	 * Validation is handled by the underlying Apache instance, using a stripped-down configuration
	 * so the entire Apache configuration consists of a) the necessary bootstrapping and b) the
	 * contents of $this->file.
	 *
	 * @throws ValidationException
	 */
	public function validate()
	{
		$command     = sprintf(
			'%1$s %2$s',
			escapeshellarg(dirname(__DIR__) . '/bin/validate-htaccess'),
			escapeshellarg($this->file)
		);
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

		$process = proc_open($command, $descriptors, $pipes);

        if (! is_resource($process)) {
            throw new ValidationException('Unable to open validation process.');
        }

        $exitCode = proc_close($process);

		if (0 !== $exitCode) {
			throw new ValidationException(
				sprintf('Validation errors were encountered: %s', $pipes[2]),
				$exitCode
			);
		}
	}

	/**
	 * Create a new validator instance using a temporary file.
	 *
	 * @return self
	 */
	public static function createFromString($contents)
	{
		$file = tempnam(null, 'htaccess-');
		file_put_contents($file, $contents);

		$instance = new static($file);
		$instance->setIsTempFile(true);

		return $instance;
	}
}
