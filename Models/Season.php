<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    protected $primaryKey = 'seasonID';
    protected $fillable = array('season', 'year', 'description');
    public $timestamps = false;

    public function teams(){
    	return $this->hasMany('App\Team', 'seasonID');
    }

    public function games(){
    	return $this->hasMany('App\Game', 'seasonID');
    }

}
