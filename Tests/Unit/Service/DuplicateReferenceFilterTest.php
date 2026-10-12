<?php

declare(strict_types=1);

namespace TGM\TgmCopyright\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TGM\TgmCopyright\Service\DuplicateReferenceFilter;

/**
 * Tests which references the filter keeps for each value of the plugin setting.
 */
#[CoversClass(DuplicateReferenceFilter::class)]
final class DuplicateReferenceFilterTest extends TestCase
{
    /**
     * Provides the raw rows as the repository fetches them, ordered by the reference uid.
     * File 7 is used by the references 10, 11 and 12, file 8 by 13.
     *
     * @return list<array{uid: int, uid_local: int}> The raw rows
     */
    private function preResults(): array
    {
        return [
            ['uid' => 10, 'uid_local' => 7],
            ['uid' => 11, 'uid_local' => 7],
            ['uid' => 12, 'uid_local' => 7],
            ['uid' => 13, 'uid_local' => 8],
        ];
    }

    /**
     * Provides the setting values and usable reference uids together with the expected result.
     * The cases span duplicates off (first, skipped first, only later, order), the string value of
     * a checkbox, duplicates on, a missing setting and an empty list.
     *
     * @return iterable<string, array{displayDuplicateImages: int|string|null, referenceUids: list<int>, expected: list<int>}>
     */
    public static function settingsAndReferencesProvider(): iterable
    {
        yield 'duplicates off keeps the first reference of each file' => [
            'displayDuplicateImages' => 0,
            'referenceUids' => [10, 11, 12, 13],
            'expected' => [10, 13],
        ];

        yield 'duplicates off with the value of a FlexForm checkbox as string' => [
            'displayDuplicateImages' => '0',
            'referenceUids' => [10, 11, 12, 13],
            'expected' => [10, 13],
        ];

        yield 'duplicates off skips a first reference that is not usable' => [
            'displayDuplicateImages' => 0,
            'referenceUids' => [11, 12, 13],
            'expected' => [11, 13],
        ];

        yield 'duplicates off keeps a file whose only usable reference comes last' => [
            'displayDuplicateImages' => 0,
            'referenceUids' => [12],
            'expected' => [12],
        ];

        yield 'duplicates off keeps the order of the references' => [
            'displayDuplicateImages' => 0,
            'referenceUids' => [13, 10, 11],
            'expected' => [13, 10],
        ];

        yield 'duplicates on lists every reference' => [
            'displayDuplicateImages' => 1,
            'referenceUids' => [10, 11, 12, 13],
            'expected' => [10, 11, 12, 13],
        ];

        yield 'missing setting lists every reference like the FlexForm default' => [
            'displayDuplicateImages' => null,
            'referenceUids' => [10, 11, 12, 13],
            'expected' => [10, 11, 12, 13],
        ];

        yield 'no usable reference stays empty with duplicates off' => [
            'displayDuplicateImages' => 0,
            'referenceUids' => [],
            'expected' => [],
        ];
    }

    /**
     * Checks that the filter keeps only the first usable reference per file when duplicates are off
     * and every reference otherwise.
     *
     * @param int|string|null $displayDuplicateImages Value of the plugin setting
     * @param list<int> $referenceUids The usable reference uids
     * @param list<int> $expected The reference uids that must stay
     */
    #[Test]
    #[DataProvider('settingsAndReferencesProvider')]
    public function filterReturnsTheReferencesTheSettingAsks(
        int|string|null $displayDuplicateImages,
        array $referenceUids,
        array $expected,
    ): void {
        $subject = new DuplicateReferenceFilter();

        self::assertSame($expected, $subject->filter($displayDuplicateImages, $this->preResults(), $referenceUids));
    }
}
