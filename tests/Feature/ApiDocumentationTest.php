<?php

it('serves swagger ui', function () {
    $this->get('/api/documentation')
        ->assertSuccessful()
        ->assertSee('swagger-ui', false);
});

it('serves the openapi specification', function () {
    $response = $this->get('/api/docs/openapi.yaml');

    $response
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/yaml');

    expect($response->getContent())
        ->toContain('openapi: 3.0.3')
        ->toContain('/api/missions');
});
