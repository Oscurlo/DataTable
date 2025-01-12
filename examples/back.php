<?php

declare(strict_types=1);

use Oscurlo\DataTable\DataTable;
use Oscurlo\DataTable\Response;

include dirname(__DIR__) . "/vendor/autoload.php";

$response = new Response();

$inProduction = false;

try {
    $datatable = new DataTable([
        "dsn" => [
            "mysql" => [
                "host" => "localhost",
                "dbname" => "test_datatable",
                "charset" => "utf8mb4",
            ],
        ],
        "username" => "root",
        "password" => "",
    ]);

    switch ($_GET["action"] ?? null) {
        case 'users':

            $columns = [
                [
                    "db" => "name",
                ],
                [
                    "db" => "email",
                ],
                [
                    "db" => "id",
                ],
            ];

            $response::code(200)::json(
                $datatable
                    ->setRequest($_GET)
                    ->setTables("users")
                    ->setColumns($columns)
                    ->fetchRecords()
            );
            break;

        case 'orders':

            $date = fn(string $format, string $datetime = "now") => date($format, strtotime($datetime));

            $tables = [
                [
                    "db" => "users",
                    "as" => "u",
                ],
                [
                    "db" => "orders",
                    "as" => "o",
                    "inner_join" => "u.id = o.user_id",
                ],
            ];

            $columns = [
                [
                    "db" => "u.name",
                ],
                [
                    "db" => "o.product_name",
                ],
                [
                    "db" => "o.order_date",
                    "formatter" => fn($d) => <<<HTML
                    <p>
                        <time datetime="{$date('Y-m-d H:i', $d)}">
                        {$date('d/m/Y', $d)}</time>
                    </p>
                    HTML
                ],
                [
                    "db" => "o.order_id",
                ],
            ];

            $response::code(200)::json(
                $datatable
                    ->setRequest($_GET)
                    ->setTables($tables)
                    ->setColumns($columns)
                    ->fetchRecords()
            );
            break;

        default:
            $response::code(401)::json([
                "error" => "Action is undefined",
            ]);
            break;
    }
} catch (InvalidArgumentException $th) {
    $response::code(500)::json([
        "error" => $th->getMessage(),
    ]);
} catch (Throwable $th) {
    $response::code(500)::json([
        "error" => $inProduction ? "Internal error server" : $th->getMessage(),
    ]);
}
