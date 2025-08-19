<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactureLine extends Model
{
    use HasFactory;

    protected $table = 'facture_lines'; // Assurez-vous que le nom de table correspond

    protected $fillable = [
        'facture_id',
        'description',
        'quantite',
        'prix_unitaire_ht',
        'taux_tva',
        'montant_ht',
        'montant_tva',
        'montant_ttc'
    ];

    protected $casts = [
        'quantite' => 'decimal:4',
        'prix_unitaire_ht' => 'decimal:2',
        'taux_tva' => 'decimal:2',
        'montant_ht' => 'decimal:2',
        'montant_tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    /**
     * Calculer automatiquement les totaux HT, TVA et TTC
     */
    public function calculateTotals()
    {
        $this->montant_ht = round($this->quantite * $this->prix_unitaire_ht, 2);
        $this->montant_tva = round($this->montant_ht * ($this->taux_tva / 100), 2);
        $this->montant_ttc = $this->montant_ht + $this->montant_tva;
    }

    protected static function booted()
    {
        static::saving(function ($line) {
            $line->calculateTotals();
        });
    }
}
