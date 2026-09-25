<?php

$config = new PhpCsFixer\Config();
return $config
    ->registerCustomFixers(new PhpCsFixerCustomFixers\Fixers())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        '@auto' => true,
        '@auto:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'attribute_empty_parentheses' => true,
        'class_keyword' => true,
        'date_time_create_from_format_call' => true,
        'date_time_immutable' => true,
        'final_public_method_for_abstract_class' => true,
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
        'mb_str_functions' => true,
        'multiline_string_to_heredoc' => true,
        'multiline_promoted_properties' => ['minimum_number_of_parameters' => 1],
        'ordered_interfaces' => true,
        'phpdoc_array_type' => true,
        'phpdoc_line_span' => true,
        'phpdoc_param_order' => true,
        'phpdoc_separation' => ['skip_unlisted_annotations' => true],
        'phpdoc_types' => false,
        'regular_callable_call' => true,
        'return_to_yield_from' => true,
        'simplified_if_return' => true,
        'simplified_null_return' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arguments', 'arrays', 'match', 'parameters']],
        'no_unreachable_default_argument_value' => false,
        'declare_strict_types' => true,
        \PhpCsFixerCustomFixers\Fixer\CommentSurroundedBySpacesFixer::name() => true,
        \PhpCsFixerCustomFixers\Fixer\ConstructorEmptyBracesFixer::name() => true,
        \PhpCsFixerCustomFixers\Fixer\MultilineCommentOpeningClosingAloneFixer::name() => true,
        \PhpCsFixerCustomFixers\Fixer\NoDoctrineMigrationsGeneratedCommentFixer::name() => true,
        \PhpCsFixerCustomFixers\Fixer\NoUselessDoctrineRepositoryCommentFixer::name() => true,
        \PhpCsFixerCustomFixers\Fixer\NoUselessCommentFixer::name() => true,
        \PhpCsFixerCustomFixers\Fixer\NoUselessDirnameCallFixer::name() => true,
        \PhpCsFixerCustomFixers\Fixer\EmptyFunctionBodyFixer::name() => true,
        \PhpCsFixerCustomFixers\Fixer\NoDuplicatedArrayKeyFixer::name() => true,
        \PhpCsFixerCustomFixers\Fixer\NoDuplicatedImportsFixer::name() => true,
        \PhpCsFixerCustomFixers\Fixer\NoTrailingCommaInSinglelineFixer::name() => true,
        \PhpCsFixerCustomFixers\Fixer\NoUselessStrlenFixer::name() => true,
        \PhpCsFixerCustomFixers\Fixer\PhpdocTypesTrimFixer::name() => true,
    ])
    ->setFinder(PhpCsFixer\Finder::create()
        ->in(__DIR__.'/src')
        ->in(__DIR__.'/tests')
    );
