<?php

namespace Tests\Unit\Traits;

use Tests\TestCase;

class HasCommonScopesTest extends TestCase
{
    /** @test */
    public function trait_exists()
    {
        $this->assertTrue(trait_exists(\App\Traits\HasCommonScopes::class));
    }

    /** @test */
    public function trait_adds_scopes_methods()
    {
        // تحقق من وجود methods
        $this->assertTrue(method_exists(\App\Models\Entity::class, 'scopeActive'));
        $this->assertTrue(method_exists(\App\Models\Entity::class, 'scopeRecent'));
        $this->assertTrue(method_exists(\App\Models\Entity::class, 'scopePopular'));
    }
}
