<?php
declare(strict_types=1);

namespace LesCoderTest\Interpreter\Parser\Specification\Typescript;

use A;
use RuntimeException;
use LesCoder\Stream\Lexical\LexicalStream;
use LesCoder\Token\Object\Visibility;
use LesCoder\Token\AttributeCodeToken;
use LesCoder\Stream\String\DirectStringStream;
use LesCoder\Token\Object\ClassCodeToken;
use LesCoder\Token\Hint\BuiltInCodeToken;
use LesCoder\Token\Hint\ReferenceCodeToken;
use LesCoder\Token\Object\ClassMethodCodeToken;
use LesCoder\Token\Object\ClassPropertyCodeToken;
use LesCoder\Token\Hint\GenericParameterCodeToken;
use LesCoder\Interpreter\Lexer\TypescriptCodeLexer;
use LesCoder\Interpreter\Lexer\Lexical\LabelLexical;
use LesCoder\Token\Object\ClassGetPropertyCodeToken;
use LesCoder\Token\Object\ClassSetPropertyCodeToken;
use LesCoder\Interpreter\Lexer\Lexical\Character\AtSignLexical;
use LesCoder\Interpreter\Parser\Specification\ParseSpecification;
use LesCoder\Interpreter\Parser\Specification\Typescript\ClassParseSpecification;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesCoder\Interpreter\Parser\Specification\Typescript\ClassParseSpecification
 */
class ClassParseSpecificationTest extends TestCase
{
    public function testParse(): void
    {
        $typescript = <<<'TS'
@Xyz
abstract class Foo<E extends Goo> extends Fiz implements Bar, Biz {
    private abc: string;
    private cba?: boolean;
    
    protected get rts(): string {
        return 'rts';
    }
    
    public set foo (val: string) {};
    
    protected xyz(): void {
    }
}
TS;

        $lexer = new TypescriptCodeLexer();
        $lexicals = $lexer->tokenize(new DirectStringStream($typescript));

        $referenceParseSpecification = $this->createMock(ParseSpecification::class);
        $referenceParseSpecification
            ->expects(self::exactly(3))
            ->method('parse')
            ->willReturnCallback(
                function (LexicalStream $stream) {
                    $current = $stream->current();
                    $stream->next();

                    if (!$current instanceof LabelLexical) {
                        throw new RuntimeException();
                    }

                    return new ReferenceCodeToken((string)$current);
                },
            );

        $expressionParseSpecification = $this->createMock(ParseSpecification::class);

        $attributeParseSpecification = $this->createMock(ParseSpecification::class);
        $attributeParseSpecification
            ->method('isSatisfiedBy')
            ->willReturnCallback(
                function (LexicalStream $stream) {
                    return $stream->current()->getType() === AtSignLexical::TYPE;
                },
            );

        $attributeToken = new AttributeCodeToken(new ReferenceCodeToken('Xyz'), []);

        $attributeParseSpecification
            ->expects(self::once())
            ->method('parse')
            ->willReturnCallback(
                function (LexicalStream $stream) use ($attributeToken) {
                    if ($stream->current()->getType() !== AtSignLexical::TYPE) {
                        throw new RuntimeException();
                    }

                    $stream->next();

                    if ($stream->current()->getType() !== LabelLexical::TYPE) {
                        throw new RuntimeException();
                    }

                    $stream->next();

                    return $attributeToken;
                },
            );

        $hintParseSpecification = $this->createMock(ParseSpecification::class);
        $hintParseSpecification
            ->expects(self::exactly(6))
            ->method('parse')
            ->willReturnCallback(
                function (LexicalStream $stream) {
                    if ($stream->current()->getType() === LabelLexical::TYPE) {
                        $hint = match ((string)$stream->current()) {
                            'string' => BuiltInCodeToken::String,
                            'boolean' => BuiltInCodeToken::Boolean,
                            'void' => BuiltInCodeToken::Void,
                            'Goo' => new ReferenceCodeToken('Goo'),
                            default => throw new RuntimeException("Unexpected '{$stream->current()}"),
                        };

                        $stream->next();

                        return $hint;
                    }

                    throw new RuntimeException();
                },
            );

        $classParser = new ClassParseSpecification($attributeParseSpecification, $expressionParseSpecification, $referenceParseSpecification, $hintParseSpecification);

        $class = $classParser->parse($lexicals);

        self::assertInstanceOf(ClassCodeToken::class, $class);

        self::assertEquals(
            new ClassCodeToken(
                'Foo',
                new ReferenceCodeToken('Fiz'),
                [
                    new ReferenceCodeToken('Bar'),
                    new ReferenceCodeToken('Biz'),
                ],
                attributes: [
                    $attributeToken,
                ],
                properties: [
                    new ClassPropertyCodeToken(
                        Visibility::Private,
                        'abc',
                        BuiltInCodeToken::String,
                    ),
                    new ClassPropertyCodeToken(
                        Visibility::Private,
                        'cba',
                        BuiltInCodeToken::Boolean,
                        flags: ClassPropertyCodeToken::FLAG_OPTIONAL,
                    ),
                    new ClassGetPropertyCodeToken(
                        Visibility::Protected,
                        'rts',
                        BuiltInCodeToken::String,
                    ),
                    new ClassSetPropertyCodeToken(
                        Visibility::Public,
                        'foo',
                        BuiltInCodeToken::String,
                    ),
                ],
                methods: [
                    new ClassMethodCodeToken(
                        Visibility::Protected,
                        'xyz',
                        returns: BuiltInCodeToken::Void,
                    ),
                ],
                flags: ClassCodeToken::FLAG_ABSTRACT,
                generics: [
                    new GenericParameterCodeToken(
                        new ReferenceCodeToken('E'),
                        new ReferenceCodeToken('Goo'),
                    ),
                ],
            ),
            $class,
        );

        self::assertTrue($lexicals->isEnd());
    }
}
