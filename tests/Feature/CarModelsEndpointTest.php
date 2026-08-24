<?php

namespace Tests\Feature;

use App\Models\Make;
use Tests\TestCase;

/**
 * Regression test for a production incident (Aug 2026): CarModel::active()
 * did `where('is_active', true)` unqualified. That's fine on its own, but
 * once GET /api/v1/models?year= joins in variants (which also has an
 * is_active column), Postgres rejects the query as an ambiguous column
 * reference and every request 500s — silently, since APP_DEBUG is off in
 * production. This broke model selection for every make/year combination
 * on the live valuation form, not just newly added models.
 */
class CarModelsEndpointTest extends TestCase
{
    public function test_models_endpoint_with_year_does_not_500(): void
    {
        $make = Make::query()->first();

        if (!$make) {
            $this->markTestSkipped('No makes in database to test against.');
        }

        $response = $this->getJson("/api/v1/models?make_id={$make->id}&year=" . date('Y'));

        $response->assertOk();
        $this->assertTrue($response->json('success'));
    }
}
