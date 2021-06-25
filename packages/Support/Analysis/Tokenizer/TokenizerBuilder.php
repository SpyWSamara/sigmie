<?php

declare(strict_types=1);

namespace Sigmie\Support\Analysis\Tokenizer;

use Exception;
use Sigmie\Base\Analysis\Tokenizers\Pattern;
use Sigmie\Base\Analysis\Tokenizers\Whitespace;
use Sigmie\Base\Analysis\Tokenizers\WordBoundaries;
use Sigmie\Base\Contracts\Analysis;
use Sigmie\Base\Contracts\Analyzer;
use Sigmie\Base\Contracts\Tokenizer;
use Sigmie\Support\Collection as SupportCollection;
use Sigmie\Support\Contracts\Collection;

use function Sigmie\Helpers\random_letters;

trait TokenizerBuilder
{
    private Tokenizer $tokenizer;

    protected function tokenizer(): Tokenizer
    {
        return $this->tokenizer;
    }

    protected function tokenizeOnWhiteSpaces(): void
    {
        $this->setTokenizer(new Whitespace);
    }

    protected function tokenizeOnPattern(string $pattern, string|null $name = null): void
    {
        $name = $name ?? $this->createTokenizerName('pattern_tokenizer');

        $this->setTokenizer(new Pattern($name, $pattern));
    }

    protected function tokenizeOnWordBoundaries(string $name): void
    {
        $name = $name ?? $this->createTokenizerName('standard');

        $this->setTokenizer(new WordBoundaries($name));
    }

    private function ensureTokenizerNameIsAvailable(string $name)
    {
        if ($this->analysis()->hasTokenizer($name)) {
            throw new Exception('Tokenizer already exists.');
        }
    }

    private function setTokenizer(Tokenizer $tokenizer)
    {
        $this->ensureTokenizerNameIsAvailable($tokenizer->name());

        $this->analysis()->updateTokenizers([$tokenizer->name() => $tokenizer]);

        $this->tokenizer = $tokenizer;
    }

    abstract public function analysis(): Analysis;

    private function createTokenizerName(string $name): string
    {
        $suffixed = $name . '_' . random_letters();

        while ($this->analysis()->hasTokenizer($suffixed)) {
            $suffixed = $name . '_' . random_letters();
        }

        return $suffixed;
    }
}
