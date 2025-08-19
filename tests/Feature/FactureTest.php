<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Facture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class FactureTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user and authenticate
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);

        // Create a test client
        $this->client = Client::create([
            'nom' => 'Test Client',
            'email' => 'test@example.com',
            'siret' => '12345678901234',
            'date_creation' => now()
        ]);
    }

    public function test_can_create_facture()
    {
        $factureData = [
            'client_id' => $this->client->id,
            'date' => now()->format('Y-m-d'),
            'lignes' => [
                [
                    'description' => 'Test Product',
                    'quantite' => 2,
                    'prix_unitaire_ht' => 100,
                    'taux_tva' => 20
                ]
            ]
        ];

        $response = $this->postJson('/api/factures', $factureData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'facture' => [
                    'id',
                    'client_id',
                    'date',
                    'total_ht',
                    'total_tva',
                    'total_ttc',
                    'factureslines' => [
                        '*' => [
                            'id',
                            'facture_id',
                            'description',
                            'quantite',
                            'prix_unitaire_ht',
                            'taux_tva',
                            'montant_ht',
                            'montant_tva',
                            'montant_ttc'
                        ]
                    ]
                ]
            ]);

        // Verify calculations
        $facture = $response->json('facture');
        $this->assertEquals(200, $facture['total_ht']); // 2 * 100
        $this->assertEquals(40, $facture['total_tva']); // 20% of 200
        $this->assertEquals(240, $facture['total_ttc']); // 200 + 40
    }

    public function test_facture_requires_at_least_one_line()
    {
        $factureData = [
            'client_id' => $this->client->id,
            'date' => now()->format('Y-m-d'),
            'lignes' => []
        ];

        $response = $this->postJson('/api/factures', $factureData);
        $response->assertStatus(422);
    }

    public function test_can_export_facture_as_json()
    {
        // Create a facture first
        $facture = Facture::create([
            'client_id' => $this->client->id,
            'date' => now(),
            'total_ht' => 200,
            'total_tva' => 40,
            'total_ttc' => 240
        ]);

        // Create a facture line
        $facture->factureslines()->create([
            'description' => 'Test Product',
            'quantite' => 2,
            'prix_unitaire_ht' => 100,
            'taux_tva' => 20,
            'montant_ht' => 200,
            'montant_tva' => 40,
            'montant_ttc' => 240
        ]);

        $response = $this->getJson("/api/factures/{$facture->id}/export");

        $response->assertOk()
            ->assertJsonStructure([
                'facture_id',
                'date',
                'client' => [
                    'nom',
                    'email',
                    'siret'
                ],
                'lignes' => [
                    '*' => [
                        'description',
                        'quantite',
                        'prix_unitaire_ht',
                        'taux_tva',
                        'montant_ht',
                        'montant_tva',
                        'montant_ttc'
                    ]
                ],
                'totaux' => [
                    'total_ht',
                    'total_tva',
                    'total_ttc'
                ]
            ]);
    }

    public function test_can_search_factures()
    {
        // Create two factures with different dates
        $facture1 = Facture::create([
            'client_id' => $this->client->id,
            'date' => now()->subDay(),
            'total_ht' => 200,
            'total_tva' => 40,
            'total_ttc' => 240
        ]);

        $facture2 = Facture::create([
            'client_id' => $this->client->id,
            'date' => now(),
            'total_ht' => 300,
            'total_tva' => 60,
            'total_ttc' => 360
        ]);

        // Search by date range
        $response = $this->getJson('/api/factures/search?' . http_build_query([
            'date_debut' => now()->subDay()->format('Y-m-d'),
            'date_fin' => now()->format('Y-m-d')
        ]));

        $response->assertOk()
            ->assertJsonCount(2, 'factures');

        // Search by client
        $response = $this->getJson('/api/factures/search?client_id=' . $this->client->id);
        $response->assertOk()
            ->assertJsonCount(2, 'factures');
    }
}
