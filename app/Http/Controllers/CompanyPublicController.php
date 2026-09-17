<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyPublicController extends Controller
{
    public function __invoke(Request $request, string $slug)
    {
        $company = Company::where('slug', $slug)
            ->whereIn('subscription_status', ['active', 'trialing'])
            ->first();

        if (!$company) {
            abort(404);
        }

        return view('company.public', ['company' => $company]);
    }
}