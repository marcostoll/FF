<?php
/**
 * Definition of ConfigParser
 *
 * @author Marco Stoll <marco@fast-forward-encoding.de>
 * @copyright 2019-forever Marco Stoll
 * @filesource
 */
declare(strict_types=1);

namespace FF\Services\Runtime;

use FF\Services\AbstractService;
use FF\Services\Exceptions\ResourceInvalidException;
use FF\Utils\ArrayUtils;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfigParser
 *
 * @package FF\Services\Runtime
 */
class ConfigParser extends AbstractService
{
    /**
     * Loads the contents of a config file
     *
     * Replaces any occurrence of '<<$key>>', where $key is a key within $replacements.
     *
     * @param string $file
     * @param array $replacements
     * @return string|null
     */
    public function load(string $file, array $replacements = []): ?string
    {
        if (!is_file($file) || !is_readable($file)) {
            return null;
        }

        $contents = file_get_contents($file);
        foreach ($replacements as $token => $value) {
            $contents = str_replace('<<' . $token . '>>', $value, $contents);
        }

        return $contents;
    }

    /**
     * Parses the contents of a config file
     *
     * @param string $ymlContents
     * @return array
     * @throws ResourceInvalidException not valid yml
     */
    public function parse(string $ymlContents): array
    {
        try {
            return Yaml::parse($ymlContents);
        } catch (ParseException $exception) {
            throw new ResourceInvalidException('not valid yml', 0, $exception);
        }
    }

    /**
     * Recursively merges two config arrays
     *
     * @param array $config1
     * @param array $config2
     * @return array
     */
    public function merge(array $config1, array $config2)
    {
        foreach (array_keys($config2) as $key) {
            // test if $key is new to $config1
            if (!array_key_exists($key, $config1)) {
                $config1[$key] = $config2[$key]; // copy key and value to $config1
                continue;
            }

            // test if either value of $config1 or $config2 is non-array
            if (!is_array($config1[$key]) || !is_array($config2[$key])) {
                $config1[$key] = $config2[$key]; // replace value in $config1
                continue;
            }

            // both values are arrays
            $isAssoc1 = ArrayUtils::isAssoc($config1[$key]);
            $isAssoc2 = ArrayUtils::isAssoc($config2[$key]);
            switch (true) {
                case $isAssoc1 != $isAssoc2 :
                    // array types differ -> replace value in first array
                    $config1[$key] = $config2[$key];
                    break;
                case !$isAssoc1 && !$isAssoc2 :
                    // both numeric arrays -> append second to first
                    $config1[$key] = array_merge($config1[$key], $config2[$key]);
                    break;
                case $isAssoc1 && $isAssoc2 :
                    // both associative arrays -> start recursion
                    $config1[$key] = $this->merge($config1[$key], $config2[$key]);
                    break;
                default :
                    break;
            }
        }

        return $config1;
    }
}
