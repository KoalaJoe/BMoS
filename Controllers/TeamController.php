<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Team;
use App\Season;

class TeamController extends Controller
{
    public function index(){
        
        return view('manage/teams/index');
    
    }

    public function create(){
        $seasons = Season::orderBy('year', 'desc')->get();
        return view('manage.teams.create')->with('seasons', $seasons);
    }

    public function store(Request $request){
    	

        $this->validate($request, [

            'seasonID' => 'required|numeric',
            'teamName' => 'required|max:25',
            'teamAbbr' => 'required|size:4',
            'teamNote' => 'max:50',
            'teamStatus' => 'required|in:1,0'

        ]);

        $values = array(
            'seasonID' => $request->seasonID,
            'teamName' => $request->teamName,
            'teamAbbr' => $request->teamAbbr,
            'teamDesc' => $request->teamNote,
            'status' => 1
        );

        Team::insert($values);

        $formMsg = "Team successfully created!";
        $request->session()->flash('formMsg', $formMsg);
        return redirect()->back();

    }

    public function show($id){
    	
        $teams = Team::where('seasonID', $id)->orderBy('teamName', 'asc')->get();
        return view('manage/teams/index')->with('teams', $teams);

    }

    public function edit($id){

    }

}
