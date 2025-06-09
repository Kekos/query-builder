<?php
$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS' => true,
        '@PHP70Migration' => true,
        '@PHP71Migration' => true,
        '@PHP70Migration:risky' => true,
        '@PHP71Migration:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
