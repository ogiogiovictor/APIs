<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ECMIPayment;

class CollectionSummary extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $previousMonth = $this->BillMonth - 1;
        $newpayment = new ECMIPayment();

       $ecmiPayments =  $newpayment->whereYear('TransactionDateTime', '=', $this->BillYear)->whereMonth('TransactionDateTime', '=', $previousMonth)->sum("Amount");

        return [
            "BillYear" => $this->BillYear,
            "BillMonth" => ((int)$this->BillMonth - 1),
            "Bills" => number_format($this->Bills, 2),
            "Payment" => number_format($this->Payment, 2),
            "ecmiAmount" => number_format($ecmiPayments, 2),
            'totalCollection' => number_format((int)$this->Payment +  (int)$ecmiPayments, 2),
        ];
        //return parent::toArray($request);
    }
}

// $ecmi_payment_lastMonth = $newpayment->whereYear('TransactionDateTime', '=', now()->year)
// ->whereMonth('TransactionDateTime', '=', now()->subMonth()->month)
// ->sum('Amount');