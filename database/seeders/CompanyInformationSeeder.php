<?php

namespace Database\Seeders;

use App\Models\CompanyInformation;
use Illuminate\Database\Seeder;

class CompanyInformationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CompanyInformation::updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'Công ty TNHH Thăng Long Stay',
                'company_tax_code' => '0101234567',
                'company_tax_code_issue_date' => '2020-01-15',
                'company_tax_code_issue_place' => 'Cục Thuế thành phố Hà Nội',
                'company_address' => 'Trường Đại học Thăng Long, Nghiêm Xuân Yêm, Hoàng Mai, Hà Nội',
                'company_phone' => '+84 123 456 789',
                'company_email' => 'contact@tlstay.com',
                'bank_name' => 'Ngân hàng Quốc tế Việt Nam',
                'bank_code' => 'VIB',
                'bank_account_number' => '028092005',
                'bank_account_holder' => 'CONG TY TNHH THANG LONG STAY',
            ]
        );
    }
}
