<?php

namespace Tests\End2End\Api\Concerns;

trait AssertsEndpointGuards
{
    protected function assertRoutesRequireAuthentication(array $routes): void
    {
        foreach ($routes as $route) {
            $response = $this->json(
                $route['method'],
                $route['uri'],
                $route['payload'] ?? []
            );

            $response->assertStatus(401);
        }
    }

    protected function assertRoutesRequireAbility(array $routes, string $requiredAbility = 'user'): void
    {
        $this->actingAsUser(abilities: []);

        foreach ($routes as $route) {
            $response = $this->json(
                $route['method'],
                $route['uri'],
                $route['payload'] ?? []
            );

            $response->assertStatus(403);
        }
    }
}
