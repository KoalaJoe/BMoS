<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{

	protected $primaryKey = 'teamID';

	public function seasons(){
		return $this->belongsTo('App\Season', 'seasonID');
	}

	public function games(){
		return $this->belongsToMany('App\Game', 'game_team', 'teamID', 'gameID')
			->withPivot('seasonID', 'baseScore', 'bonusScore', 'totalScore', 'outcome');
	}


}
