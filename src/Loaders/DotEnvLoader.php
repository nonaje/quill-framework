<?php

declare(strict_types=1);

namespace Quill\Loaders;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Quill\Contracts\Configuration\ConfigurationInterface;
use Quill\Contracts\Container\ContainerInterface;
use Quill\Contracts\Loader\FilesLoader;
use Quill\Contracts\Support\PathResolverInterface;
use Quill\Support\Path;

/**
 * Loads environment variables from .env files into application configuration
 */
final readonly class DotEnvLoader implements FilesLoader
{
    /**
     * Expected file extension for environment files
     */
    private const string ENV_EXTENSION = '.env';

    /**
     * @param ContainerInterface $container The dependency injection container
     */
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * Load environment variables from .env file
     *
     * @param string ...$filenames Optional path to .env file. If omitted, default .env will be used
     * @throws InvalidArgumentException|\Exception If more than one file is provided or if file doesn't have .env extension
     * @return void
     */
    public function load(string ...$filenames): void
    {
        // Check if too many files were provided
        if (count($filenames) > 1) {
            throw new InvalidArgumentException('Only one dotenv file can be loaded.');
        }

        // Use provided filename or default
        $filename = $filenames[0] ?? $this->container->get(PathResolverInterface::class)->toFile(self::ENV_EXTENSION);

        // Validate file extension
        if (!str_ends_with($filename, self::ENV_EXTENSION)) {
            throw new InvalidArgumentException("File: {$filename} must be a .env file");
        }

        // Skip if file doesn't exist
        if (!is_file($filename) || !is_readable($filename)) {
            return;
        }

        $env = $this->parseEnvFile($filename);
        $this->storeEnvironmentVariables($env);
    }

    /**
     * Parse .env file and return contents as array
     *
     * @param string $filename Path to .env file
     * @return array<string, string> Parsed environment variables
     */
    private function parseEnvFile(string $filename): array
    {
        $env = parse_ini_file($filename, false, INI_SCANNER_RAW) ?: [];

        // Process environment variable values for special syntax
        foreach ($env as $key => $value) {
            // Process string values only
            if (is_string($value)) {
                // Remove quotes if present
                if (preg_match('/^([\'"])(.*)\1$/', $value, $matches)) {
                    $env[$key] = $matches[2];
                }
            }
        }

        return $env;
    }

    /**
     * Store environment variables in the configuration repository
     *
     * @param array<string, string|bool|int|float|null> $variables Environment variables
     */
    private function storeEnvironmentVariables(array $variables): void
    {
        $normalizedVariables = array_combine(
            array_map('strtolower', array_keys($variables)),
            array_map(fn($value) => is_string($value) ? trim($value) : $value, array_values($variables))
        ) ?: [];


        $normalizedVariables = array_filter($normalizedVariables, fn($value, $key) => $key !== '' && $value !== '', ARRAY_FILTER_USE_BOTH);

        array_walk($normalizedVariables, function (&$value) {
            $lowerValue = strtolower($value);
            if ($lowerValue === 'true') {
                $value = true;
            } elseif ($lowerValue === 'false') {
                $value = false;
            } elseif ($lowerValue === 'null') {
                $value = null;
            }
        });

        /** @var ConfigurationInterface $config */
        $config = $this->container->get(ConfigurationInterface::class);
        $config->put('env', $normalizedVariables);
    }
}
