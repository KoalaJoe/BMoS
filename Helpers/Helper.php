<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

/*
|--------------------------------------------------------------------------
| Helper Classes
|--------------------------------------------------------------------------
|
| Here is where you can create your own custom helpers.
| To use them just call ClassName:FunctionName for
| Example: ... Helper::sortStandings
|
*/

class Helper
{
    public static function sortStandings($a, $b){
       
    	if($a['points'] == $b['points']){
            if($a['percent'] == $b['percent']){
            	return ($a['difference'] < $b['difference'] ? 1 : -1);
            }else{
                return ($a['percent'] < $b['percent'] ? 1 : -1);
            }
        }            
        return ($a['points'] < $b['points'] ? 1 : -1);
    }

}