<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use App\Models\Employee;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function create(CreateTeamRequest $request) {
        try {
            if($request->hasFile('icon')){
                $path = $request->file('icon')->store('public/icons');
            }

            $team = Team::create([
                'name' => $request->name,
                'icon' => isset($path) ? $path : '',
                'company_id' => $request->company_id,
            ]);

            if(!$team) {
                throw new Exception('Team not created');
            }

            return ResponseFormatter::success($team, 'Team Created');
        } catch (Exception $th) {
            return ResponseFormatter::error($th->getMessage(), 500);
        }
    }

    public function update(UpdateTeamRequest $request, $id) {
        try {
            $team = Team::find($id);

            if(!$team) {
                throw new Exception('Team not found');
            }

            if($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            $team->update([
                'name' => $request->name,
                'icon' => isset($path) ? $path : $team->icon,
                'company_id' => $request->company_id,
            ]);

            return ResponseFormatter::success($team, 'Team Updated');
        } catch (Exception $th) {
            return ResponseFormatter::error($th->getMessage(), 500);
        }
    }

    public function fetch(Request $request) {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $teamQuery = Team::withCount('employees');

        if($id) {
            $team = $teamQuery->find($id);

            if($team) {
                return ResponseFormatter::success($team, 'Team Found');
            }

            return ResponseFormatter::error('Team not found', 404);
        }

        $teams = $teamQuery->where('company_id', $request->company_id);

        if($name) {
            $teams->where('name', 'like', '%'.$name.'%');
        }

        return ResponseFormatter::success(
            $teams->paginate($limit),
            'Teams Found'
        );
    }

    public function destroy($id) {
        try {
            $team = Team::find($id);

            if(!$team) {
                throw new Exception('Team not found');
            }

            $team->delete();

            return ResponseFormatter::success('Teams deleted');
        } catch (Exception $th) {
            return ResponseFormatter::error($th->getMessage(), 500);
        }
    }

    public function teamDetail($id) {
        try {
            //declare var
            $employees = [];
            $message = 'Team Detail Not Found';

            $teamQuery = Team::withCount('employees')->where('id', $id)->first();

            if($teamQuery) {
                $employees = Employee::with('role')->where('team_id', $teamQuery->id)->get();
                $message = 'Teams Detail Found';
            }

            $result = [
                'team' => $teamQuery,
                'employees' => $employees
            ];

            return ResponseFormatter::success(
                $result,
                $message
            );
        } catch (Exception $th) {
            return ResponseFormatter::error($th->getMessage(), 500);
        }
    }
}
