<?php declare(strict_types=1);

namespace Symplify\CodingStandard\Tests\Sniffs\Naming\TraitName;

use Iterator;
use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;

/**
 * @see \Symplify\CodingStandard\Sniffs\Naming\TraitNameSniff
 */
final class TraitNameSniffTest extends AbstractCheckerTestCase
{
    /**
     * @dataProvider provideWrongCases()
     */
    public function testWrongToFixed(string $file): void
    {
        $this->doTestWrongFile($file);
    }

    public function provideWrongCases(): Iterator
    {
        yield [__DIR__ . '/wrong/wrong.php.inc'];
        yield [__DIR__ . '/wrong/wrong2.php.inc'];
        yield [__DIR__ . '/wrong/wrong3.php.inc'];
    }

    /**
     * @dataProvider provideCorrectCases()
     */
    public function testCorrect(string $file): void
    {
        $this->doTestCorrectFile($file);
    }

    public function provideCorrectCases(): Iterator
    {
        yield [__DIR__ . '/correct/correct.php.inc'];
        yield [__DIR__ . '/correct/correct2.php.inc'];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
