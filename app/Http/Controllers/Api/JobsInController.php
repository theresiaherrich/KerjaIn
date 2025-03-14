<?php

namespace App\Http\Controllers\Api;

use App\Models\jobsIn;
use App\Models\Company;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Type;
use App\Models\Disability;
use App\Models\Policy;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\jobsInResource;
use Illuminate\Support\Facades\Validator;
use Exception;
class JobsInController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
    }

        public function index(Request $request)
        {
            try{
                if ($request->has('search') && !empty($request->search)) {
                    $jobIds = jobsIn::search($request->search)->keys();
                    $jobsIn = jobsIn::whereIn('id', $jobIds);
                } else {
                    $jobsIn = jobsIn::query();
                }

                if ($request->has('locations') && !empty($request->locations)) {
                    $jobsIn = $jobsIn->whereIn('location', $request->locations);
                }
                if ($request->has('min_salary') && !empty($request->min_salary)) {
                    $jobsIn = $jobsIn->where('salary', '>=', $request->min_salary);
                }
                if ($request->has('max_salary') && !empty($request->max_salary)) {
                    $jobsIn = $jobsIn->where('salary', '<=', $request->max_salary);
                }
                if ($request->has('company_names') && !empty($request->company_names)) {
                    $jobsIn = $jobsIn->whereIn('company_name', $request->company_names);
                }
                if ($request->has('disability_types') && !empty($request->disability_types)) {
                    $jobsIn = $jobsIn->whereIn('disability_type', $request->disability_types);
                }
                if ($request->has('education_levels') && !empty($request->education_levels)) {
                    $jobsIn = $jobsIn->whereIn('education_level', $request->education_levels);
                }
                if ($request->has('experience_durations') && !empty($request->experience_durations)) {
                    $jobsIn = $jobsIn->whereIn('experience_id', $request->experience_durations);
                }
                if ($request->has('type_durations') && !empty($request->type_durations)) {
                    $jobsIn = $jobsIn->whereIn('type_duration', $request->type_durations);
                }
                if ($request->has('policy_locations') && !empty($request->policy_locations)) {
                    $jobsIn = $jobsIn->whereIn('policy_location', $request->policy_locations);
                }

                $jobsIn = $jobsIn->with(['company','disability','education', 'experience','type','policy'])
                                ->latest()
                                ->paginate(5);

                return new jobsInResource('List Data jobsIn', $jobsIn);
            } catch (Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

    public function store(Request $request)
    {
        try{
        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'description'   => 'required',
            'salary'        => 'required',
            'location'      => 'required',
            'company_name'  => 'required|exists:companies,name',
            'disability_type' => 'required|exists:disabilities,type',
            'education_level'  => 'required|exists:educations,level',
            'experience_duration' => 'required|exists:experiences,duration',
            'type_duration'       => 'required|exists:types,duration',
            'policy_location'     => 'required|exists:policies,location',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $company = Company::where('name', $request->company_name)->first();
        $disability = Disability::where('type', $request->disability_type)->first();
        $education = Education::where('level', $request->education_level)->first();
        $experience = Experience::where('duration', $request->experience_duration)->first();
        $type = Type::where('duration', $request->type_duration)->first();
        $policy = Policy::where('location', $request->policy_location)->first();

        $jobsIn = JobsIn::create([
            'name'          => $request->name,
            'description'   => $request->description,
            'salary'        => $request->salary,
            'location'      => $request->location,
            'company_name'  => $company->id,
            'disability_type' => $disability->id,
            'education_level'  => $education->id,
            'experience_duration' => $experience->id,
            'type_duration'       => $type->id,
            'policy_location'     => $policy->id,
        ]);


        return new jobsInResource('Data jobsIn Berhasil Ditambahkan!', $jobsIn);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
    }


    public function show($id)
    {
        try{
        $jobsIn = jobsIn::with(['company', 'disability', 'education', 'experience', 'type', 'policy'])->find($id);


        if (!$jobsIn) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        return response()->json([
            'message'       => 'Detail Data jobsIn!',
            'data'          => [
                'name'          => $jobsIn->name,
                'description'   => $jobsIn->description,
                'salary'        => $jobsIn->salary,
                'location'      => $jobsIn->location,
                'company_name'  => $jobsIn->company ? $jobsIn->company->name : null,
                'disability_type' => $jobsIn->disability ? $jobsIn->disability->type : null,
                'education_level'  => $jobsIn->education ? $jobsIn->education->level : null,
                'experience_duration' => $jobsIn->experience ? $jobsIn->experience->duration : null,
                'type_duration'       => $jobsIn->type ? $jobsIn->type->duration : null,
                'policy_location'     => $jobsIn->policy ? $jobsIn->policy->location : null,
            ]
        ]);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
    }

    public function update(Request $request, $id)
    {
        try{
        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'description'   => 'required',
            'salary'        => 'required',
            'location'      => 'required',
            'company_name'  => 'required|exists:companies,name',
            'disability_type' => 'required|exists:disabilities,type',
            'education_level'  => 'required|exists:educations,level',
            'experience_duration' => 'required|exists:experiences,duration',
            'type_duration'       => 'required|exists:types,duration',
            'policy_location'     => 'required|exists:policies,location',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $jobsIn = JobsIn::find($id);

        if (!$jobsIn) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        $company = Company::where('name', $request->company_name)->first();
        $disability = Disability::where('type', $request->disability_type)->first();
        $education = Education::where('level', $request->education_level)->first();
        $experience = Experience::where('duration', $request->experience_duration)->first();
        $type = Type::where('duration', $request->type_duration)->first();
        $policy = Policy::where('location', $request->policy_location)->first();

        $jobsIn->update([
            'name'          => $request->name,
            'description'   => $request->description,
            'salary'        => $request->salary,
            'location'      => $request->location,
            'company_name'  => $company->id,
            'disability_type' => $disability->id,
            'education_level'  => $education->id,
            'experience_duration' => $experience->id,
            'type_duration'       => $type->id,
            'policy_location'     => $policy->id,
        ]);

        return new jobsInResource('Data jobsIn Berhasil Diubah!', $jobsIn);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
    }


    public function destroy($id)
    {
        try{
            $jobsIn = jobsIn::find($id);

            if (!$jobsIn) {
                return response()->json(['message' => 'Job not found'], 404);
            }

            $jobsIn->delete();

            return new jobsInResource('Data jobsIn Berhasil Dihapus!', null);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
