<?php

declare(strict_types=1);

namespace TGM\TgmCopyright\Service;

/**
 * Applies the plugin setting "displayDuplicateImages" to the usable image references.
 *
 * The filter works on rows that are already checked, so a file stays in the list when
 * only some of its references are usable.
 */
final readonly class DuplicateReferenceFilter
{
    /**
     * Without the setting every reference is listed, like the FlexForm default says.
     * With the setting turned off only the reference with the lowest uid per file stays.
     *
     * Every uid in the list of usable references must come from the raw rows.
     *
     * @param int|string|null $displayDuplicateImages Value of the plugin setting, null when it is not set
     * @param list<array<string, int|string|null>> $preResults Raw rows with uid and uid_local, ordered by uid
     * @param list<int> $referenceUids Usable reference uids, in the order of the rows
     *
     * @return list<int> The reference uids that stay in the list
     */
    public function filter(int|string|null $displayDuplicateImages, array $preResults, array $referenceUids): array
    {
        if ((int)($displayDuplicateImages ?? 1) !== 0) {
            return $referenceUids;
        }

        $fileByReference = [];
        foreach ($preResults as $preResult) {
            $fileByReference[(int)$preResult['uid']] = (int)$preResult['uid_local'];
        }

        $seenFiles = [];
        $firstReferenceUids = [];
        foreach ($referenceUids as $referenceUid) {
            $fileUid = $fileByReference[$referenceUid];
            if (isset($seenFiles[$fileUid])) {
                continue;
            }

            $seenFiles[$fileUid] = true;
            $firstReferenceUids[] = $referenceUid;
        }

        return $firstReferenceUids;
    }
}
