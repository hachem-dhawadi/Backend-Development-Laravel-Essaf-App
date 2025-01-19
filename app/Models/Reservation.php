<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;
    protected $table = 'reservations';
    protected $fillable = [
        'category_id',
        'service_id',
        'room_id',
        'worker_id',
        'user_id',
        'time',
        'date',
        'Newrepetition',
        'timeDiff',
        'notification',
    ];

    protected $with = ['room','client'];
    public function room()
    {
        return $this->belongsTo(Room::class,'room_id','id');
    }
    public function client()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

}
