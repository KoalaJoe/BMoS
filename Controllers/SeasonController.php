<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Season;
use App\Team;
use App\Score;
use App\Game;
use App\Helper;

class SeasonController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Function For Route "manage.seasons.index"
    |--------------------------------------------------------------------------
    |
    | The following function uses the Season model, and will query all the
    | seasons in the database, then order them by year in descending. It
    | returns the manage.seasons.index view with the seasons collection
    |
    */

    public function index(){

        $seasons = Season::orderBy('year', 'desc')->get();
        return view('manage.seasons.index')->with('seasons', $seasons);

    }

    /*
    |--------------------------------------------------------------------------
    | Function For Route "manage.seasons.create"
    |--------------------------------------------------------------------------
    |
    | The following function returns the manage.seasons.create view
    |
    */

    public function create(){
        return view('manage.seasons.create');
    }

    /*
    |--------------------------------------------------------------------------
    | Function For Route "manage.seasons.store"
    |--------------------------------------------------------------------------
    |
    | The following function validates the form fields
    |
    */

    public function store(Request $request){
        
        $this->validate($request, [
            'seasonName' => 'required|max:25',
            'seasonYear' => 'required|digits:4'
        ]);


        $values = array(
            'season' => $request->seasonName,
            'year' => $request->seasonYear,
            'description' => $request->seasonDesc,
            'status' => 1);

        Season::insert($values);

        $formMsg = "Season Successfully Created!";
        $request->session()->flash('formMsg', $formMsg);
        return redirect()->back();

    }

    /*
    |--------------------------------------------------------------------------
    | Function For Route "manage.seasons.show"
    |--------------------------------------------------------------------------
    |
    | The following function shows the standings for the given seasonID
    | passed into it.
    |
    */

    public function show($id){
        
        //find the season
        $season = Season::find($id);
        
        //create an array to store the calculate fields
        $seasonStandings = array();

        //iterate through each team in the season
        foreach($season->teams as $team){

            //games played
            $gp = $team->games()->count();

            //wins                             
            $w = $team->games()->where('outcome', 'win')->count();

            //losses        
            $l = $team->games()->where('outcome', 'lose')->count();

            //draws      
            $d = $team->games()->where('outcome', 'draw')->count();

            //forfeits     
            $ff = $team->games()->where('outcome', 'forfeit')->count();

            //points for  
            $for = $team->games()->sum('totalScore');

            //points against           
            $against = $team->games()->sum('margin');

            //poinst per game               
            $ppg = $gp > 0 ? number_format(($for/$gp), 1) : '0.0';

            //winning percentage   
            $pct = $gp > 0 ? number_format(($w + ($d/2))/$gp, 3) : '0.000';

            //points
            $pts = (($w*3) + ($d*2) + $l);                                  

            //store the above calculations into the a team array
            $seasonTeam = [ 

                'name' => $team->teamName,
                'played' => $team->games()->count(),
                'wins' => $w,
                'losses' => $l,
                'draws' => $d,
                'forfeits' => $ff,
                'for' => $for,
                'against' => $against, 
                'difference' => $for - $against,
                'ppg' => $ppg,
                'percent' => $pct,
                'points' => $pts

            ];

            //push the above array into the standings array
            array_push($seasonStandings, $seasonTeam);
        }

        //sort the standings array. SEE sortStandings IN App/Helpers
        usort($seasonStandings, 'Helper::sortStandings');
    
        return view('manage.seasons.show')->with('standings', $seasonStandings);
    
    }

    /*
    |--------------------------------------------------------------------------
    | Function For Route "manage.seasons.update"
    |--------------------------------------------------------------------------
    |
    | The following function shows the edit view with the passed in seasonID
    |
    */

    public function edit($id){

        $season = Season::find($id);
        return view('manage.seasons.edit')->with('season', $season);

    }

    /*
    |--------------------------------------------------------------------------
    | Function For Route "manage.seasons.update"
    |--------------------------------------------------------------------------
    |
    | The following function validates the form fields and updates the Season
    | model with the passed form fields. Redirects back to the index page
    |
    */

    public function update(Request $request, $id){

        $this->validate($request, [
            'seasonStatus' => 'required|in:0, 1',
            'seasonName' => 'required|max:25',
            'seasonYear' => 'required|digits:4'
        ]);

        $season = Season::find($id);
        $season->season = $request->seasonName;
        $season->year = $request->seasonYear;
        $season->description = $request->seasonDesc;
        $season->status = $request->seasonStatus;
        $season->save();

        return redirect()->route('manage.seasons.index');

    }

    /*
    |--------------------------------------------------------------------------
    | Function For Route "'manage/seasons/delete/{id}" AKA deleteSeason
    |--------------------------------------------------------------------------
    |
    | The following function uses the Season and Team models. The function
    | will delete a season IF and only IF there are no teams associated
    | with the season.
    |
    */

    public function deleteSeason($id){

        //query the Team model by the seasonID and grab the first instance.
        $seasonTeams = Team::where('seasonID', $id)->first();

        //check if any teams have been returned.
        if(!(empty($seasonTeams))){

            //if there are any teams, Redirect back to manage.seasons.index with error messages 
            return redirect()
                ->back()
                ->with('errorDelete', 'You cannot delete this season as it has teams and games assigned to it. Delete them first before deleting the season.');
        }

        //otherwise continue deleting the record from the database
        $season = Season::find($id)->delete();
        return redirect()->back();
    }
}