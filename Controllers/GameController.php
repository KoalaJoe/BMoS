<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Season;
use App\Team;
use App\Game;

class GameController extends Controller
{


    /* GAMEGETTEAMS
     * this function receives an AJAX request from "manage.games.create" and dynamically
     * returns the team list based on the seasonID that is passed to this function.
     * the return value is a html string in "<option></option>" format.
     */

    public function gameGetTeams(Request $request){

        //query and grab the teams from the TEAM model using the passed seasonID
        $teams = Team::where('seasonID', $request->seasonID)->orderBy('teamName', 'asc')->get();

        //the string to be returned back to the page
        $result = "";
        
        //check if the request was an ajax request
        if($request->ajax()){

            //check if the query returned any results
            if($teams->count() === 0){

                //set a default string value if there are no teams
                $result = '<option value="none"> -- No Teams In Season -- </option>';

            }else{

                //iterate through the results of the returned query
                foreach($teams as $team){

                    //append the returning string for all returned items
                    $result = $result . '<option value="'. $team->teamID . '">' . $team->teamName . '</option>';

                }
            }

            //return the string back to the page for use in ajax
            return $result;
        }
    }


    /*  MANAGE.GAMES.INDEX
     */

    public function index()
    {
        //some code here
    }


    /*  MANAGE.GAMES.CREATE
     *  this function will return the "manage.games.create" 
     *  view with the season details in SEASON model.
     */

    public function create()
    {
        //query and grab the seasons from the SEASON model and 
        //order the returned results by the year column
        $seasons = Season::orderBy('year', 'desc')->get();

        //return to the 'manage.games.create' view with the returned results
        return view('manage.games.create')->with('seasons', $seasons);
    }


    /*  MANAGE.GAMES.STORE
     *  this function will return the "manage.games.create" 
     *  view with the season details in SEASON model.
     */

    public function store(Request $request)
    {
        
        $this->validate($request, [
            'gameSeason' => 'required',
            'gameType' => 'required|in:pos,reg,pre',
            'gameWeek' => 'required|numeric',
            'team.0.team' => 'required|different:team.1.team|numeric',
            'team.1.team' => 'required|numeric',
            'team.*.teamScore' => 'required|numeric',
            'team.*.teamBonus' => 'required|numeric',
            'team.*.teamTotal' => 'required|numeric',
            'team.*.teamOutcome' => 'required|in:win,lose,draw,forfeit',
            'team.*.teamDiff' => 'required|numeric'
        ]);

        //create a new GAME model and insert a new record into the GAMES table
        $game = new Game();
        $game->seasonID = $request->gameSeason;
        $game->week = $request->gameWeek;
        $game->gameType = $request->gameType;
        $game->save();

        $teamATotalScore = $request->team[0]['teamScore'] + $request->team[0]['teamBonus'];
        $teamBTotalScore = $request->team[1]['teamScore'] + $request->team[1]['teamBonus'];

        function checkOutcome($team1, $team2){
            switch(true){
                case ($team1 == 0 && $team2 != 0):
                    return "forfeit";
                case ($team1 > $team2):
                    return "win";
                case ($team1 < $team2):
                    return "lose";
                case ($team1 != 0 && $team1 === $team2):
                    return "draw";
                default:
                    return "forfeit";
            }
        }

        //store the values for team A in an array
        $teamAValues = array(

            'teamID' => $request->team[0]['team'],
            'seasonID' => $request->gameSeason,
            'baseScore' => $request->team[0]['teamScore'],
            'bonusScore' => $request->team[0]['teamBonus'],
            'totalScore' => $teamATotalScore,
            'margin' => $teamBTotalScore,
            'outcome' => checkOutcome($teamATotalScore, $teamBTotalScore)

        );

        //store the values for team B in an array
        $teamBValues = array(

            'teamID' => $request->team[1]['team'],
            'seasonID' => $request->gameSeason,
            'baseScore' => $request->team[1]['teamScore'],
            'bonusScore' => $request->team[1]['teamBonus'],
            'totalScore' => $teamBTotalScore,
            'margin' => $teamATotalScore,
            'outcome' => checkOutcome($teamBTotalScore, $teamATotalScore)

        );

        //insert team A and B records into the pivot table GAME_TEAM using
        //attach allows you to store the foreign key (gameID) from the
        //parent model GAME into the the related model GAME_TEAM
        $game->teams()->attach(1, $teamAValues);
        $game->teams()->attach(1, $teamBValues);

        $formMsg = "Game Created!";
        $request->session()->flash('formMsg', $formMsg);

        return redirect()->back()->withInput($request->input());

    }


    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        //
    }
}
