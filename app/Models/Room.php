<?php

namespace App\Models;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    protected $table = 'rooms';
    protected $fillable = [
        'user_id',
        'service_id',
        'start',
        'end',
        'name',
        'description',
        'active',
        'image',
    ];

    protected $with = ['service'];
    public function service()
    {
        return $this->belongsTo(Service::class,'service_id','id');
    }
        

}
