<?php

namespace App\Helpers;

class VietQRHelper
{
    /**
     * Generate VietQR URL
     * Format: https://img.vietqr.io/image/{bankCode}-{accountNumber}-compact.jpg?amount={amount}&addInfo={description}&accountName={accountHolder}
     * 
     * @param string $bankCode Bank code (VCB, TCB, BID, etc.) - lowercase
     * @param string $accountNumber Account number
     * @param int $amount Amount in VND
     * @param string $description Description/Content (nội dung chuyển khoản)
     * @param string $accountHolder Account holder name (tên chủ tài khoản)
     * @return string VietQR URL
     */
    public static function generateQRUrl($bankCode, $accountNumber, $amount, $description = null, $accountHolder = null)
    {
        // Base URL for VietQR API
        $baseUrl = 'https://img.vietqr.io/image/';
        
        // Convert bank code to lowercase
        $bankCodeLower = strtolower($bankCode);
        
        // Build URL: baseUrl + bankCode-accountNumber-compact.jpg
        $url = $baseUrl . $bankCodeLower . '-' . $accountNumber . '-compact.jpg';
        
        // Build query parameters
        $params = [];
        
        if ($amount !== null) {
            $params['amount'] = $amount;
        }
        
        if ($description !== null && $description !== '') {
            $params['addInfo'] = $description;
        }
        
        if ($accountHolder !== null && $accountHolder !== '') {
            $params['accountName'] = $accountHolder;
        }
        
        // Add query string if there are parameters
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
}
