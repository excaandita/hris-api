<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateResponsibilityRequest;
use App\Models\Responsibility;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResponsibilityController extends Controller
{
    public function fetch(Request $request) {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $responsibilityQuery = Responsibility::query();

        if($id) {
            $responsibility = $responsibilityQuery->find($id);

            if($responsibility) {
                return ResponseFormatter::success($responsibility, 'Responsibility Found');
            }

            return ResponseFormatter::error('Responsibility not found', 404);
        }

        $responsibilities = $responsibilityQuery->where('role_id', $request->role_id);

        if($name) {
            $responsibilities->where('name', 'like', '%'.$name.'%');
        }

        return ResponseFormatter::success(
            $responsibilities->paginate($limit),
            'Responsibilities Found'
        );
    }

    public function create(CreateResponsibilityRequest $request) {
        try {
            $responsibility = Responsibility::create([
                'name' => $request->name,
                'role_id' => $request->role_id,
            ]);

            if(!$responsibility) {
                throw new Exception('Responsibility not created');
            }

            return ResponseFormatter::success($responsibility, 'Responsibility Created');
        } catch (Exception $th) {
            return ResponseFormatter::error($th->getMessage(), 500);
        }
    }

    public function destroy($id) {
        try {
            $responsibility = Responsibility::find($id);

            if(!$responsibility) {
                throw new Exception('Responsibility not found');
            }

            $responsibility->delete();

            return ResponseFormatter::success('Responsibilities deleted');
        } catch (Exception $th) {
            return ResponseFormatter::error($th->getMessage(), 500);
        }
    }
}
