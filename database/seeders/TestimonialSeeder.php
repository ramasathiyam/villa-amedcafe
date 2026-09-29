<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Deliberately empty. The current frontend shows 3 identical placeholder quotes per
 * dining venue ("Placeholder — real guest testimonial not yet available", "Guest 1/2/3")
 * — per explicit instruction, placeholder copy is not treated as real testimonial data
 * and is not seeded. The `testimonials` table/model/relations exist and are ready for
 * admin CRUD once real guest quotes are available.
 */
class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        // Intentionally no-op — see class docblock.
    }
}
