<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCompanyRequets;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $companyQuery = Company::with(['users'])->whereHas('users', function ($query) {
            $query->where('user_id', Auth::id());
        });

        // Get single data
        if ($id) {
            $company = $companyQuery->find($id);

            if ($company) {
                return ResponseFormatter::success($company, 'company found');
            }

            return ResponseFormatter::error('company not found', 404);
        }

        // Get multiple data
        $companies = $companyQuery;

        // powerhuman.com/api/company?id=1
        if ($name) {
            $companies->where('name', 'like', '%' . $name . '%');
        }

        // company::with(['users'])->where('name', 'like', '%' . $name . '%')->paginate(10);
        return ResponseFormatter::success(
            $companies->paginate($limit),
            'Companies found'
        );
    }
    
    public function create(CreateCompanyRequets $request)
    {
        try {
            // Upload logo
            if($request->hasFile('logo')){
                $path =$request->file('logo')->store('pubic/logos');
            }
            
            //Create company
            $company = company::create([
                'name'=> $request->name,
                'logo' => $path
            ]);

            if(!$company)
            {
                throw new Exception('company not created');
            }

            // Attach company to user
            $user = User::find(Auth::id());
            $user->companies()->attach($company->id);

            // Load users at company
            $company->load('users');
    
            return ResponseFormatter::success($company, 'Company created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateCompanyRequest $request, $id)
    {
        try {
            // Get team
            $company = Company::find($id);

            // Check if team exists
            if(!$company)
            {
                throw new Exception('Company not found');
            }

            // Upload logo
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }

            // Update team
            $company->update([
                'name'=> $request->name,
                'logo' => isset($path) ? $path : $company->logo,
            ]);
    
            return ResponseFormatter::success($company, 'Company updated');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
