<?php

declare(strict_types=1);

namespace Oscurlo\DataTable;

use Exception;
use InvalidArgumentException;
use PDO;

class Database
{
    protected static ?PDO $conn = null;
    protected string $gestor;
    private array $options = [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY];

    public function __construct(array|PDO $sql_details)
    {
        if (self::$conn instanceof PDO) {
            $this->gestor = self::$conn->getAttribute(PDO::ATTR_DRIVER_NAME);
        } else {
            $this->connect($sql_details);
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Crea la conexion
     *
     * @throws InvalidArgumentException
     */
    private function connect($sql_details): PDO
    {
        $params = [];

        if (is_array($sql_details)) {
            if (!array_key_exists("dsn", $sql_details)) {
                throw new InvalidArgumentException("...");
            }

            if (is_array($sql_details["dsn"])) {
                $this->gestor = key($sql_details["dsn"]);

                $params["dsn"] = "{$this->gestor}:";
                $params["dsn"] .= implode(";", array_map(
                    function ($value, $key) {
                        return "{$key}={$value}";
                    },
                    $sql_details["dsn"][$this->gestor],
                    array_keys($sql_details["dsn"][$this->gestor])
                ));
            }

            self::$conn = new PDO(
                $params["dsn"],
                $sql_details["username"] ?? null,
                $sql_details["password"] ?? null,
                $sql_details["options"] ?? null,
            );
        } else {
            self::$conn = $sql_details;
        }

        return self::$conn;
    }

    /**
     * Cierra la conexion
     */
    private function disconnect()
    {
        if (self::$conn instanceof PDO) {
            self::$conn = null;
        }
    }

    protected function fetchAll(string $query, array|null $params = null, int $mode = PDO::FETCH_ASSOC)
    {
        return $this->fetch(__FUNCTION__, $query, $params, $mode);
    }

    protected function fetchColumn(string $query, array|null $params = null, int $column = 0)
    {
        return $this->fetch(__FUNCTION__, $query, $params, $column);
    }

    private function fetch(string $method, string $query, array|null $params = null, mixed $param)
    {
        $stmt = self::$conn->prepare($query, $this->options);
        $exec = $stmt->execute($params);

        if (!$exec) {
            throw new Exception("...");
        }

        return $stmt->{$method}(
            $param
        );
    }
}
