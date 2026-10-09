<?php

namespace App\Enums;

enum WithdrawalStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';   // sudah dicairkan oleh verifikator
    case Rejected = 'rejected';
}
