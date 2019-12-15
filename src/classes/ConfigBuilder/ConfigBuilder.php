<?php

namespace ConfigBuilder;

use Exception;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \ConfigBuilder
 */
final class ConfigBuilder
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $configFile = "";

    /**
     * @param array &$config
     * @throws \Exception
     *
     * Constructor.
     */
    public function __construct(array &$config)
    {
        if (!isset($config["target_file"])) {
            throw new Exception("\"target_file\" is needed in the config fragment");
        }

        if (!is_string($config["target_file"])) {
            throw new Exception("\"target_file\" must be a string");
        }

        if (!array_key_exists("const", $config)) {
            throw new Exception("\"const\" is needed in the config fragment");
        }

        if ((!is_null($config["const"])) && (!is_array($config["const"]))) {
            throw new Exception("\"const\" must be null or and array");
        }

        $this->config = &$config;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function build(): void
    {
        if (array_search("clean", $_SERVER["argv"]) !== false) {
            @unlink($this->config["target_file"]);
            return;
        }

        $this->configFile = "<?php".PHP_EOL;
        foreach ($this->config["const"] as $k => $v) {

            if (!is_string($k)) {
                throw new Exception("\"const\" item key must be a string");
            }

            $this->configFile .= "define(\"";
            $this->configFile .= self::strEscape($k);
            $this->configFile .= "\",";
            $this->configFile .= self::buildData($v);
            $this->configFile .= ");".PHP_EOL;
        }
        file_put_contents($this->config["target_file"], $this->configFile);
    }

    /**
     * @param string $str
     * @return string
     */
    public static function strEscape(string $str): string
    {
        return str_replace(["\$", "\\", "\""], ["\\\$", "\\\\", "\\\""], $str);
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public static function buildData($data): string
    {
        switch (gettype($data)) {
            case "string":
                return "\"".self::strEscape($data)."\"";
            break;
            case "array":
                $r = "[";
                $i = 0;
                foreach ($data as $k => $v) {
                    if ($k !== $i) {
                        $r .= self::buildData($k)."=>".self::buildData($v).",";
                    } else {
                        $r .= self::buildData($v).",";
                    }
                    $i++;
                }
                return rtrim($r, ",")."]";
            break;
            case "integer":
                return $data;
            break;
            default:
                return "null";
            break;
        }
    }
}
