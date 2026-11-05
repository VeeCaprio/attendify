<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The root URL redirects to the Filament admin panel.
     */
    public function test_the_root_url_redirects_to_the_admin_panel(): void
    {
        $response = $this->followingRedirects()->get('/');

        $response->assertOk();
    }
}
