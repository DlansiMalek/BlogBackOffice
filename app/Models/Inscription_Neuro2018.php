<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Inscription_Neuro2018 extends Authenticatable
{
    use Notifiable;
    protected $table = 'Inscription_Neuro2018';
    protected $primaryKey = 'id_inscription';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'prenom', 'nom', 'statut', 'email', 'adresse', 'tel', 'ville', 'pays', 'transport', 'repas', 'diner', 'hebergement',
        'chambre', 'conjoint', 'date_arrivee', 'date_depart', 'date', 'qr_code'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
}
