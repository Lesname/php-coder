<?php
declare(strict_types=1);

namespace LesCoder\Interpreter\Parser\Specification;

use Override;
use LesCoder\Token\CodeToken;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Interpreter\Parser\Specification\Exception\NoParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Exception\ExpectedParseSpecification;

final class GroupParseSpecification implements ParseSpecification
{
    /** @var array<ParseSpecification> */
    private readonly array $specifications;

    /**
     * @param non-empty-array<class-string<ParseSpecification>|ParseSpecification|callable(ParseSpecification): ParseSpecification> $specifications
     *
     * @throws ExpectedParseSpecification
     */
    public function __construct(iterable $specifications)
    {
        $initializedSpecifications = [];

        foreach ($specifications as $specification) {
            if (is_callable($specification)) {
                $specification = $specification($this);
            }

            if (!$specification instanceof ParseSpecification) {
                throw new ExpectedParseSpecification($specification);
            }

            $initializedSpecifications[] = $specification;
        }

        $this->specifications = $initializedSpecifications;
    }


    #[Override]
    public function isSatisfiedBy(LexicalStream $stream): bool
    {
        return array_any($this->specifications, fn($specification) => $specification->isSatisfiedBy($stream));
    }

    /**
     * @throws NoParseSpecification
     */
    #[Override]
    public function parse(LexicalStream $stream, ?string $file = null): CodeToken
    {
        foreach ($this->specifications as $specification) {
            if ($specification->isSatisfiedBy($stream)) {
                return $specification->parse($stream, $file);
            }
        }

        throw new NoParseSpecification();
    }
}
