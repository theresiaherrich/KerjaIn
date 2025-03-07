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

class JobsInController extends Controller
{

    public function index(Request $request)
    {

        if ($request->has('search') && !empty($request->search)) {
            $jobsIn = jobsIn::search($request->search);
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
        if ($request->has('company_ids') && !empty($request->company_ids)) {
            $jobsIn = $jobsIn->whereIn('company_id', $request->company_ids);
        }
        if ($request->has('disability_ids') && !empty($request->disability_ids)) {
            $jobsIn = $jobsIn->whereIn('disability_id', $request->disability_ids);
        }
        if ($request->has('education_ids') && !empty($request->education_ids)) {
            $jobsIn = $jobsIn->whereIn('education_id', $request->education_ids);
        }
        if ($request->has('experience_ids') && !empty($request->experience_ids)) {
            $jobsIn = $jobsIn->whereIn('experience_id', $request->experience_ids);
        }
        if ($request->has('type_ids') && !empty($request->type_ids)) {
            $jobsIn = $jobsIn->whereIn('type_id', $request->type_ids);
        }
        if ($request->has('policy_ids') && !empty($request->policy_ids)) {
            $jobsIn = $jobsIn->whereIn('policy_id', $request->policy_ids);
        }

        $jobsIn = $jobsIn->with(['company','disability','education', 'experience','type','policy',])->latest()->paginate(5);

        return new jobsInResource('List Data jobsIn', $jobsIn);
    }


    public function store(Request $request)
    {
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
            'company_id'    => $company->id,
            'disability_id' => $disability->id,
            'education_id'  => $education->id,
            'experience_id' => $experience->id,
            'type_id'       => $type->id,
            'policy_id'     => $policy->id,
        ]);

        return new jobsInResource('Data jobsIn Berhasil Ditambahkan!', $jobsIn);
    }


    public function show($id)
    {
        $jobsIn = jobsIn::find($id);

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
    }

    public function update(Request $request, $id)
    {
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
            'company_id'    => $company->id,
            'disability_id' => $disability->id,
            'education_id'  => $education->id,
            'experience_id' => $experience->id,
            'type_id'       => $type->id,
            'policy_id'     => $policy->id,
        ]);

        return new jobsInResource('Data jobsIn Berhasil Diubah!', $jobsIn);
    }


    public function destroy($id)
    {
        $jobsIn = jobsIn::find($id);

        if (!$jobsIn) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        $jobsIn->delete();

        return new jobsInResource('Data jobsIn Berhasil Dihapus!', null);
    }
}
