<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_route_shows_custom_not_found_page(): void
    {
        $this->get('/ruta-que-no-existe')
            ->assertNotFound()
            ->assertSee('No encontrado')
            ->assertSee('La pagina o recurso solicitado no existe.');
    }
}
