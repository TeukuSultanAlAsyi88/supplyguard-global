<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_country_api_returns_paginated_data(): void
    {
        $this->getJson('/api/countries?per_page=5')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(5, 'data.data')
            ->assertJsonStructure(['data' => ['data' => [['id', 'name', 'code']]]]);
    }

    public function test_port_map_api_returns_coordinates(): void
    {
        $this->getJson('/api/ports/map?limit=10')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => [['id', 'name', 'latitude', 'longitude']]]);
    }

    public function test_sentiment_endpoint_uses_lexicon_words(): void
    {
        $this->postJson('/api/news/analyze', [
            'text' => 'Growth improve stable and profit',
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sentiment', 'Positif');
    }

    public function test_dashboard_chart_endpoint_is_available(): void
    {
        $this->getJson('/api/dashboard/charts')
            ->assertOk()
            ->assertJsonStructure(['data' => ['risk_levels', 'news_sentiment', 'ports_by_country']]);
    }
}
