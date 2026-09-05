<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $items = Company::where('tenant_id', $request->user()->tenant_id)->paginate(20);
        return view('companies.index', compact('items'));
    }

    public function show(Company $company)
    {
        return view('companies.index', ['items' => collect([$company])]);
    }
}
