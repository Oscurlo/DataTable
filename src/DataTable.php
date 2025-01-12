<?php

declare(strict_types=1);

namespace Oscurlo\DataTable;

use Throwable;

class DataTable extends Database
{
    private array $request;
    private string $primaryKey = "id";
    private string $table;
    private string $order;
    private string $limit;
    private string $filter;
    private string $condition = "";
    private array $columnsArray;
    private string $columns;
    private array $preparedParams;


    /**
     * Datos enviados por serverside (Opcional)
     *
     * @param null|array $data
     *
     */
    public function setRequest(?array $data = null): static
    {
        $this->request = $data ?: $_REQUEST;
        return $this;
    }

    /**
     * establece la tabla padre
     */
    public function setTables(array|string $name): static
    {
        if (is_string($name)) {
            $name = [["db" => $name]];
        }

        $joins = ["INNER JOIN", "LEFT JOIN", "RIGHT JOIN"];

        $this->table = implode(" ", array_unique(array_map(
            function ($data) use ($joins) {
                $data["as"] ??= "";

                foreach ($joins as $join) {
                    $key = str_replace(" ", "_", strtolower($join));

                    if (array_key_exists($key, $data)) {
                        return str_replace(
                            "  ",
                            " ",
                            "{$join} {$data["db"]} {$data["as"]} ON {$data[$key]}"
                        );
                    }
                }

                return trim($data["db"] . " " . $data["as"]);
            },
            $name
        )));

        return $this;
    }

    /**
     * Nombre de la llave primaria
     */
    public function setPrimaryKey(string $name)
    {
        $this->primaryKey = $name;

        return $this;
    }

    /**
     * Columnas
     */
    public function setColumns(array $columns, ?string $use = null): static
    {
        $use ??= implode(", ", array_unique(array_map(
            function ($column) {
                return trim($column["db"] . " " . ($column["as"] ?? ""));
            },
            $columns
        )));

        $this->columnsArray = $columns;
        $this->columns = $use;

        return $this->applyFilter()->applyOrder()->applyLimit();
    }

    private function applyFilter()
    {
        $search = $this->request["search"]["value"] ?? null;

        $this->filter = implode(" OR ", array_map(
            function ($data) use ($search) {
                $key = str_replace("=", "", base64_encode((string) rand()));
                $this->preparedParams[":{$key}"] = "%{$search}%";
                return "{$data["db"]} LIKE :{$key}";
            },
            $this->columnsArray
        ));

        return $this;
    }

    private function applyOrder(): static
    {
        $columnId = $this->request["order"][0]["column"] ?? null;
        $orderDirection = $this->request["order"][0]["dir"] ?? null;

        if (!isset($columnId) || !isset($orderDirection)) {
            return $this;
        }

        $column = $this->columnsArray[$columnId]["db"] ?? $this->primaryKey;
        $this->order = "ORDER BY {$column} {$orderDirection}";

        return $this;
    }

    private function applyLimit()
    {
        $start = $this->request["start"] ?? null;
        $length = $this->request["length"] ?? null;

        $this->limit = "";

        if (!$start || !$length) {
            return $this;
        }

        $this->limit = match ($this->gestor) {
            "mysql" => "LIMIT {$start}, {$length}",
            "sqlsrv" => "OFFSET {$start} ROWS FETCH NEXT {$length} ROWS ONLY",
        };

        return $this;
    }

    public function addCondition(string $condition)
    {
        $this->condition = " AND {$condition}";
        return $this;
    }

    public function formatValues(array $result)
    {
        $response = [];

        foreach ($result as $i => $data) {
            foreach ($this->columnsArray as $key => $column) {
                $dbField = $column["db"];
                $alias = $column["as"] ?? null;

                $formatter = $column["formatter"] ?? null;
                $fallback = $column["fallback"] ?? null;

                $db = explode(".", $dbField);
                $keyField = preg_replace("/\[(.*?)\]/", "$1", $db[2] ?? $db[1] ?? $db[0]);
                $value = $alias ? $data[$alias] : $data[$keyField] ?? null;

                $response[$i][] = (string) empty($value) && !is_numeric($value) && $fallback !== null
                    ? (is_callable($fallback) ? $fallback() : $fallback)
                    : (is_callable($formatter) ? $formatter($value, $data, $key) : $value);
            }
        }

        return $response;
    }

    public function fetchRecords(): array
    {
        $response = [];

        try {
            $buildExtra = function (array $array): string {
                return implode(" ", array_filter($array));
            };

            $query = "SELECT {$this->columns} FROM {$this->table} WHERE {$this->filter} {$buildExtra([$this->condition, $this->order, $this->limit])}";

            $data = $this->fetchAll("SELECT {$this->columns} FROM {$this->table} WHERE {$this->filter} {$buildExtra([$this->condition, $this->order, $this->limit])}", $this->preparedParams);
            $recordsTotal = $this->fetchColumn("SELECT count(*) FROM {$this->table} WHERE 1 = 1 {$buildExtra([$this->condition])}");
            $recordsFiltered = $this->fetchColumn("SELECT count(*) FROM {$this->table} WHERE {$this->filter} {$buildExtra([$this->condition])}", $this->preparedParams);

            $response = [
                "draw" => $this->request["draw"],
                "recordsTotal" => $recordsTotal,
                "recordsFiltered" => $recordsFiltered,
                "data" => $this->formatValues($data),
            ];
        } catch (Throwable $th) {
            $response["error"] = $th->getMessage();
        } finally {
            return $response;
        }
    }
}
