<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Service;

class Payement extends Model
{
    use HasFactory;
    protected $table = 'payement';
    protected $fillable = [
        'user_id',
        'adresse',
        'date', 
        'code', 
        'payement_id', 
        'payement_mode', 
        'tracking_no',
        'money', 
        'nb_services',
        'active_plus', 
        'file', 
    ];


    protected $with = ['user'];
    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
