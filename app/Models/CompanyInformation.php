<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyInformation extends Model
{
    protected $fillable = [
        'company_name',
        'company_tax_code',
        'company_tax_code_issue_date',
        'company_tax_code_issue_place',
        'company_address',
        'company_phone',
        'company_email',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
    ];

    protected $casts = [
        'company_tax_code_issue_date' => 'date',
    ];

    /**
     * Get the single company information record
     * Since there should only be one record, we'll use a singleton pattern
     */
    public static function getInstance()
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'THANG LONG STAY',
                'company_tax_code' => null,
                'company_tax_code_issue_date' => null,
                'company_tax_code_issue_place' => null,
                'company_address' => null,
                'company_phone' => null,
                'company_email' => null,
            ]
        );
    }
}
