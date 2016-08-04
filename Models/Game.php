<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
	protected $primaryKey = 'gameID';

	public $timestamps = false;

	public function season(){
		return $this->belongsTo('App\Season', 'seasonID');
	}

	public function teams(){
		return $this->belongsToMany('App\Team', 'game_team', 'gameID', 'teamID')
			->withPivot('seasonID', 'baseScore', 'bonusScore', 'totalScore', 'outcome');
	}


}
