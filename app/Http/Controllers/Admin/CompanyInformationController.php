<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyInformation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyInformationController extends Controller
{
    /**
     * Display company information page
     */
    public function index()
    {
        $company = CompanyInformation::getInstance();

        return Inertia::render('Admin/CompanyInformation/Index', [
            'company' => [
                'id' => $company->id,
                'company_name' => $company->company_name,
                'company_tax_code' => $company->company_tax_code,
                'company_tax_code_issue_date' => $company->company_tax_code_issue_date?->format('Y-m-d'),
                'company_tax_code_issue_place' => $company->company_tax_code_issue_place,
                'company_address' => $company->company_address,
                'company_phone' => $company->company_phone,
                'company_email' => $company->company_email,
                'bank_name' => $company->bank_name,
                'bank_account_number' => $company->bank_account_number,
                'bank_account_holder' => $company->bank_account_holder,
            ],
        ]);
    }

    /**
     * Update company information
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_tax_code' => 'nullable|string|max:50',
            'company_tax_code_issue_date' => 'nullable|date',
            'company_tax_code_issue_place' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|email|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_holder' => 'nullable|string|max:255',
        ]);

        $company = CompanyInformation::getInstance();
        $company->update($validated);

        return back()->with('success', 'Thông tin doanh nghiệp đã được cập nhật thành công!');
    }
}
