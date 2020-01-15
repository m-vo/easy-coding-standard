<?php

namespace Contao\EasyCodingStandard\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class NoLineBreakBetweenMethodArgumentsFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinition
    {
        return new FixerDefinition(
            'Method declarations must be done in a single line.',
            [
                new CodeSample(
                    '<?php

class Foo
{
    public function bar(FooService $fooService, BarService $barService, array $options = [], Logger $logger = null): void
    {
    }
}
'
                ),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_FUNCTION);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
            if (!$tokens[$index]->isGivenKind(T_FUNCTION)) {
                continue;
            }

            $nextMeaningful = $tokens->getNextMeaningfulToken($index);

            if ($tokens[$nextMeaningful]->isGivenKind(CT::T_RETURN_REF)) {
                $nextMeaningful = $tokens->getNextMeaningfulToken($nextMeaningful);
            }

            if ($tokens[$nextMeaningful]->isGivenKind(T_STRING)) {
                $nextMeaningful = $tokens->getNextMeaningfulToken($nextMeaningful);
            }

            if (!$end = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $nextMeaningful)) {
                continue;
            }

            for ($i = $nextMeaningful; $i < $end; ++$i) {
                if (!$tokens[$i]->isGivenKind(T_WHITESPACE)) {
                    continue;
                }

                if ($tokens[$i - 1]->equals('(') || $tokens[$i + 1]->equals(')')) {
                    $tokens->clearAt($i);
                } else {
                    $tokens->offsetSet($i, new Token([T_WHITESPACE, ' ']));
                }
            }

            $index = $end + 1;
        }
    }
}