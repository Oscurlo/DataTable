<?php

declare(strict_types=1);

use PhpCsFixer\{Config, Finder};

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        "@PSR12" => true,
        "array_syntax" => ["syntax" => "short"],
        "single_quote" => false,
        "braces" => ["position_after_functions_and_oop_constructs" => "next"],
        "no_unused_imports" => true,
        "trim_array_spaces" => true,
        "no_whitespace_in_blank_line" => true,
        "ordered_imports" => true,
        "return_type_declaration" => ["space_before" => "none"],
        "binary_operator_spaces" => ["default" => "single_space"],
        "blank_line_after_namespace" => true,
        "blank_line_after_opening_tag" => true,
        "single_import_per_statement" => true,
        "single_line_after_imports" => true,
        "trailing_comma_in_multiline" => ["elements" => ["arrays"]],
        "phpdoc_align" => ["align" => "vertical"],
        "closure_fn_spacing" => "none" // Esta regla elimina el espacio entre `fn` y `(`
    ])
    ->setFinder(
        Finder::create()
            ->in(__DIR__)
            ->exclude("vendor")
            ->name("*.php")
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
    );
