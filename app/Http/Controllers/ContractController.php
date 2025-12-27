<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;

class ContractController extends Controller
{
    /**
     * Generate and display contract PDF for a booking in browser
     */
    public function download(Request $request, Booking $booking)
    {
        // Check if user is authorized to view this contract
        if (Auth::check() && Auth::id() !== $booking->user_id && !Auth::user()->isManager()) {
            abort(403, 'Bạn không có quyền xem hợp đồng này.');
        }

        // Check if booking is paid
        if ($booking->payment_status !== 'paid') {
            abort(404, 'Hợp đồng chỉ có thể được tạo sau khi thanh toán thành công.');
        }

        // Load relationships
        $booking->load(['user', 'house', 'room', 'house.owner']);

        // Get company information
        $company = \App\Models\CompanyInformation::getInstance();

        // Prepare contract data
        $contractDate = now();
        $contractDateDay = $contractDate->day;
        $contractDateMonth = $contractDate->month;
        $contractDateYear = $contractDate->year;
        $contractPlace = $company->company_address ?? 'Hà Nội';

        // Company information
        $companyName = $company->company_name ?? 'THANG LONG STAY';
        $companyTaxCode = $company->company_tax_code ?? '';
        $companyTaxCodeIssueDate = $company->company_tax_code_issue_date ? $company->company_tax_code_issue_date->format('d/m/Y') : '';
        $companyTaxCodeIssuePlace = $company->company_tax_code_issue_place ?? '';
        $companyAddress = $company->company_address ?? '';
        $companyPhone = $company->company_phone ?? '';
        $companyEmail = $company->company_email ?? '';

        // Company representative (owner of the house)
        $owner = $booking->house->owner;
        $companyRepresentativeName = $owner->name ?? '';
        $companyRepresentativePosition = 'Quản lý nhà trọ';
        $companyRepresentativeSignature = $owner->signature ?? null;

        // User information
        $user = $booking->user;
        $userName = $user->name ?? '';
        $userBirthYear = $user->date_of_birth ? $user->date_of_birth->format('Y') : '';
        $userIdNumber = $user->id_card_number ?? '';
        $userIdIssueDate = $user->id_card_issue_date ? $user->id_card_issue_date->format('d/m/Y') : '';
        $userIdIssuePlace = $user->id_card_issue_place ?? '';
        $userPermanentAddress = $user->permanent_address ?? '';
        $userPhone = $user->phone ?? '';

        // House and room information
        $house = $booking->house;
        $room = $booking->room;
        $houseAddress = $house->address ?? '';
        $roomNumber = $room ? ($room->room_number ?? 'N/A') : 'N/A';
        $roomArea = $room ? ($room->area ?? '') : '';
        $rentalPurpose = 'Ở trọ';

        // Booking dates
        $startDate = $booking->start_date->format('d/m/Y');
        $endDate = $booking->end_date->format('d/m/Y');

        // Price information
        $totalPrice = $booking->total_price;
        $totalPriceInWords = $this->numberToWords($totalPrice);
        $paymentMethod = $booking->payment_method === 'vnpay' ? 'Chuyển khoản qua VNPay' : 'Tiền mặt';

        // Bank information from company
        $bankAccountOwner = $company->bank_account_holder ?? '';
        $bankAccountNumber = $company->bank_account_number ?? '';
        $bankName = $company->bank_name ?? '';
        $bankCode = $company->bank_code ?? '';
        
        // Format bank name with code if both exist
        $bankDisplayName = $bankName;
        if ($bankName && $bankCode) {
            $bankDisplayName = "{$bankName} ({$bankCode})";
        } elseif ($bankCode && !$bankName) {
            $bankDisplayName = $bankCode;
        }

        // Signature information
        $userSignature = $booking->user_signature ?? null;
        $signedAt = $booking->signed_at ? $booking->signed_at->format('d/m/Y H:i') : null;

        $filename = "hop-dong-thue-tro-{$booking->id}.pdf";

        // Generate PDF directly without caching
        $pdf = Pdf::setOption([
            'defaultFont' => 'DejaVu Serif',
            'isRemoteEnabled' => true, // Enable to load base64 images
            'isHtml5ParserEnabled' => true,
        ])->loadView('contracts.rental_contract', compact(
            'contractDateDay',
            'contractDateMonth',
            'contractDateYear',
            'contractPlace',
            'companyName',
            'companyTaxCode',
            'companyTaxCodeIssueDate',
            'companyTaxCodeIssuePlace',
            'companyAddress',
            'companyRepresentativeName',
            'companyRepresentativePosition',
            'companyRepresentativeSignature',
            'companyPhone',
            'companyEmail',
            'userName',
            'userBirthYear',
            'userIdNumber',
            'userIdIssueDate',
            'userIdIssuePlace',
            'userPermanentAddress',
            'userPhone',
            'houseAddress',
            'roomNumber',
            'roomArea',
            'rentalPurpose',
            'startDate',
            'endDate',
            'totalPrice',
            'totalPriceInWords',
            'paymentMethod',
            'bankAccountOwner',
            'bankAccountNumber',
            'bankName',
            'bankCode',
            'bankDisplayName',
            'userSignature',
            'signedAt'
        ));

        // Stream PDF directly without caching
        // Check if this is an embed request (for PDF viewer)
        if ($request->query('embed') === 'true') {
            return $pdf->stream($filename, [
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        }

        return $pdf->stream($filename, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Get contract PDF URL for embedding (direct generation, no cache)
     */
    public function preview(Booking $booking)
    {
        // Check if user is authorized to view this contract
        if (Auth::check() && Auth::id() !== $booking->user_id && !Auth::user()->isManager()) {
            abort(403, 'Bạn không có quyền xem hợp đồng này.');
        }

        // Check if booking is paid
        if ($booking->payment_status !== 'paid') {
            abort(404, 'Hợp đồng chỉ có thể được tạo sau khi thanh toán thành công.');
        }

        // Redirect to download which will generate PDF directly
        return redirect()->route('contract.download', ['booking' => $booking->id, 'embed' => 'true']);
    }

    /**
     * Convert number to Vietnamese words
     */
    private function numberToWords($number)
    {
        $ones = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
        $tens = ['', 'mười', 'hai mươi', 'ba mươi', 'bốn mươi', 'năm mươi', 'sáu mươi', 'bảy mươi', 'tám mươi', 'chín mươi'];
        $hundreds = ['', 'một trăm', 'hai trăm', 'ba trăm', 'bốn trăm', 'năm trăm', 'sáu trăm', 'bảy trăm', 'tám trăm', 'chín trăm'];

        if ($number == 0) {
            return 'không';
        }

        $result = '';
        $number = (int) $number;

        // Handle millions
        if ($number >= 1000000) {
            $millions = floor($number / 1000000);
            $result .= $this->convertThreeDigits($millions, $ones, $tens, $hundreds) . ' triệu ';
            $number %= 1000000;
        }

        // Handle thousands
        if ($number >= 1000) {
            $thousands = floor($number / 1000);
            $result .= $this->convertThreeDigits($thousands, $ones, $tens, $hundreds) . ' nghìn ';
            $number %= 1000;
        }

        // Handle remaining
        if ($number > 0) {
            $result .= $this->convertThreeDigits($number, $ones, $tens, $hundreds);
        }

        return trim($result) . ' đồng';
    }

    /**
     * Convert three digits to words
     */
    private function convertThreeDigits($number, $ones, $tens, $hundreds)
    {
        $result = '';
        $hundred = floor($number / 100);
        $remainder = $number % 100;
        $ten = floor($remainder / 10);
        $one = $remainder % 10;

        if ($hundred > 0) {
            $result .= $hundreds[$hundred] . ' ';
        }

        if ($ten > 0) {
            if ($ten == 1 && $one > 0) {
                $result .= 'mười ' . ($one == 5 ? 'lăm' : ($one == 1 ? 'một' : $ones[$one])) . ' ';
            } else {
                $result .= $tens[$ten] . ' ';
                if ($one > 0) {
                    $result .= ($one == 5 ? 'lăm' : ($one == 1 ? 'một' : $ones[$one])) . ' ';
                }
            }
        } elseif ($one > 0) {
            $result .= $ones[$one] . ' ';
        }

        return trim($result);
    }

    /**
     * Show sign contract page
     */
    public function showSign(Booking $booking)
    {
        // Check if user is authorized
        if (Auth::id() !== $booking->user_id) {
            abort(403, 'Bạn không có quyền ký hợp đồng này.');
        }

        // Check if booking is paid
        if ($booking->payment_status !== 'paid') {
            return redirect()->route('history.index')
                ->with('error', 'Hợp đồng chỉ có thể được ký sau khi thanh toán thành công.');
        }

        // Check if already signed
        if ($booking->contract_signed) {
            return redirect()->route('booking.success', $booking->id)
                ->with('info', 'Hợp đồng này đã được ký.');
        }

        // Load relationships
        $booking->load(['house', 'room']);

        $bookingData = [
            'id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'house_name' => $booking->house->name,
            'room_number' => $booking->room->room_number,
            'floor' => $booking->room->floor,
            'start_date' => $booking->start_date->format('Y-m-d'),
            'end_date' => $booking->end_date->format('Y-m-d'),
        ];

        return Inertia::render('SignContract', [
            'booking' => $bookingData,
        ]);
    }

    /**
     * Handle contract signing
     */
    public function sign(Request $request, Booking $booking)
    {
        // Check if user is authorized
        if (Auth::id() !== $booking->user_id) {
            abort(403, 'Bạn không có quyền ký hợp đồng này.');
        }

        // Check if booking is paid
        if ($booking->payment_status !== 'paid') {
            return back()->withErrors([
                'message' => 'Hợp đồng chỉ có thể được ký sau khi thanh toán thành công.'
            ]);
        }

        // Check if already signed
        if ($booking->contract_signed) {
            return back()->withErrors([
                'message' => 'Hợp đồng này đã được ký.'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'signature' => 'required|string',
            'agree' => 'required|accepted',
        ], [
            'signature.required' => 'Vui lòng vẽ chữ ký của bạn.',
            'agree.required' => 'Vui lòng đồng ý với các điều khoản hợp đồng.',
            'agree.accepted' => 'Bạn phải đồng ý với các điều khoản hợp đồng.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validate signature is base64 image
        $signature = $request->signature;
        if (!preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $signature)) {
            return back()->withErrors([
                'signature' => 'Chữ ký không hợp lệ. Vui lòng vẽ lại chữ ký.'
            ])->withInput();
        }

        // Update booking with signature
        $booking->update([
            'user_signature' => $signature,
            'signed_at' => now(),
            'contract_signed' => true,
        ]);

        // Generate URL with query parameter
        $url = route('booking.success', $booking->id) . '?signed=1';
        
        return redirect($url)
            ->with('success', 'Bạn đã ký hợp đồng thành công!');
    }
}
