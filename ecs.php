<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Alias\MbStrFunctionsFixer;
use PhpCsFixer\Fixer\Alias\RandomApiMigrationFixer;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\Basic\PsrAutoloadingFixer;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\ClassNotation\FinalPublicMethodForAbstractClassFixer;
use PhpCsFixer\Fixer\ConstantNotation\NativeConstantInvocationFixer;
use PhpCsFixer\Fixer\FunctionNotation\CombineNestedDirnameFixer;
use PhpCsFixer\Fixer\FunctionNotation\FunctionTypehintSpaceFixer;
use PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer;
use PhpCsFixer\Fixer\FunctionNotation\NoUnreachableDefaultArgumentValueFixer;
use PhpCsFixer\Fixer\FunctionNotation\NoUselessSprintfFixer;
use PhpCsFixer\Fixer\FunctionNotation\SingleLineThrowFixer;
use PhpCsFixer\Fixer\FunctionNotation\StaticLambdaFixer;
use PhpCsFixer\Fixer\FunctionNotation\UseArrowFunctionsFixer;
use PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer;
use PhpCsFixer\Fixer\LanguageConstruct\DirConstantFixer;
use PhpCsFixer\Fixer\LanguageConstruct\IsNullFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Operator\LogicalOperatorsFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Operator\TernaryToElvisOperatorFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocToCommentFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitInternalClassFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestClassRequiresCoversFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->import(SetList::SPACES);
    $ecsConfig->import(SetList::STRICT);
    $ecsConfig->import(SetList::CONTROL_STRUCTURES);
    $ecsConfig->import(SetList::ARRAY);
    $ecsConfig->import(SetList::DOCBLOCK);
    $ecsConfig->import(SetList::COMMON);
    $ecsConfig->import(SetList::PHPUNIT);
    $ecsConfig->import(SetList::PSR_12);
    $ecsConfig->import(SetList::SYMPLIFY);
    $ecsConfig->import(SetList::CLEAN_CODE);
    $ecsConfig->import(SetList::DOCTRINE_ANNOTATIONS);

    $ecsConfig->paths([__DIR__ . '/src', __DIR__ . '/tests']);
    $ecsConfig->skip(
        [
            GeneralPhpdocAnnotationRemoveFixer::class,
            NotOperatorWithSuccessorSpaceFixer::class,
            SingleLineThrowFixer::class,
            PhpUnitTestClassRequiresCoversFixer::class,
            PhpUnitInternalClassFixer::class,
            PhpdocToCommentFixer::class,
            FunctionTypehintSpaceFixer::class,
        ]
    );

    $ecsConfig->rules([
                          FinalPublicMethodForAbstractClassFixer::class,
                          CombineNestedDirnameFixer::class,
                          NoUselessSprintfFixer::class,
                          NoUnreachableDefaultArgumentValueFixer::class,
                          StaticLambdaFixer::class,
                          UseArrowFunctionsFixer::class,
                          DirConstantFixer::class,
                          IsNullFixer::class,
                          LogicalOperatorsFixer::class,
                          TernaryToElvisOperatorFixer::class,
                          DeclareStrictTypesFixer::class,
                          StrictParamFixer::class,
                          MbStrFunctionsFixer::class,
                      ]);

    $ecsConfig->rulesWithConfiguration(
        [
            ArraySyntaxFixer::class => [
                'syntax' => 'short',
            ],
            ConcatSpaceFixer::class => [
                'spacing' => 'one',
            ],
            NoSuperfluousPhpdocTagsFixer::class => [
                'allow_mixed' => true,
            ],
            CastSpacesFixer::class => [
                'space' => 'single',
            ],
            LineLengthFixer::class => [
                'line_length' => 120,
                'break_long_lines' => true,
                'inline_short_lines' => false,
            ],
            GlobalNamespaceImportFixer::class => [
                'import_classes' => false,
                'import_constants' => false,
                'import_functions' => false,
            ],
            NativeFunctionInvocationFixer::class => [],
            NativeConstantInvocationFixer::class => [],
            RandomApiMigrationFixer::class => [],
            PsrAutoloadingFixer::class => [
                'dir' => './src',
            ],
        ]
    );
};
