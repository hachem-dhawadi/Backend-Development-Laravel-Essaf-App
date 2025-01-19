<?php

namespace App\Models;
use App\Models\User;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;
    protected $table = 'jobs';
    protected $fillable = [
        'room_id',
        'sender_id',
        'reciver_id', 
        'description',
        'file',
        'active',
        'gnot',
        'anot',
    ];


    protected $with = ['user','reciver','room'];
    public function user()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }
    
    public function reciver()
    {
        return $this->belongsTo(User::class, 'reciver_id', 'id');
    }
    
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }
    
    

}
