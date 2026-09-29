<?php

namespace App\Support;

/**
 * Primary nav, ported verbatim from the Next.js reference's src/data/navigation.ts.
 * "Special Offers" has no page yet, so it stays in the nav for visual parity but renders
 * disabled instead of linking to a fabricated URL. "Dining" is a dropdown-only parent
 * (no page of its own — two separate venues instead).
 */
class SiteNav
{
    public static function items(): array
    {
        return [
            ['label' => 'Home', 'href' => '/'],
            ['label' => 'Room', 'href' => '/room'],
            ['label' => 'Activity', 'href' => '/activity'],
            [
                'label' => 'Dining',
                'children' => [
                    ['label' => 'Resto Amed Cafe', 'href' => '/dining/resto-amed-cafe'],
                    ['label' => 'Barak Rooftop and Bar', 'href' => '/dining/barak-rooftop-and-bar'],
                ],
            ],
            ['label' => 'Spa', 'href' => '/spa'],
            ['label' => 'Special Offers', 'href' => '/', 'disabled' => true],
            ['label' => 'Contact Us', 'href' => '/contact'],
        ];
    }

    /**
     * Footer's "About Us" column: Dining's two real pages flattened in place of the
     * dropdown-only parent; Home and Contact Us are excluded (matches the reference).
     */
    public static function footerLinks(): array
    {
        $links = [];

        foreach (self::items() as $item) {
            if (! empty($item['children'])) {
                array_push($links, ...$item['children']);
                continue;
            }

            if (($item['href'] ?? null) === '/' || $item['label'] === 'Contact Us') {
                continue;
            }

            $links[] = $item;
        }

        return $links;
    }
}
