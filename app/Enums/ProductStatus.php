<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';     // menunggu verifikasi
    case Approved = 'approved';   // tampil di katalog
    case Rejected = 'rejected';
    case Inactive = 'inactive';   // dinonaktifkan penjual/admin
}
