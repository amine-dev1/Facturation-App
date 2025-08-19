<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'date',
        'total_ht',
        'total_tva', 
        'total_ttc'
    ];

    protected $casts = [
        'date' => 'date',
        'total_ht' => 'decimal:2',
        'total_tva' => 'decimal:2',
        'total_ttc' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function lignes()
    {
        return $this->hasMany(FactureLine::class);
    }

    // Alias pour compatibilité avec votre contrôleur existant
    public function factureslines()
    {
        return $this->hasMany(FactureLine::class);
    }


    public function calculateTotals()
    {
        // Recharger les lignes depuis la base de données pour s'assurer d'avoir les dernières valeurs
        $this->load('lignes');
        $this->total_ht = $this->lignes->sum('montant_ht');
        $this->total_tva = $this->lignes->sum('montant_tva');
        $this->total_ttc = $this->lignes->sum('montant_ttc');
    }
}
?>