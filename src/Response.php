<?php

declare(strict_types=1);

namespace Oscurlo\DataTable;

final class Response
{
    public static string $charset = "utf-8";
    public static function code(int $response_code = 200): string
    {
        http_response_code($response_code);
        return self::class;
    }

    public static function html(string $content): void
    {
        header("Content-Type: text/html; charset=" . self::$charset);
        echo $content;
    }

    public static function json(array $content): void
    {
        header("Content-Type: application/json; charset=" . self::$charset);
        echo json_encode($content, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
    }
}
