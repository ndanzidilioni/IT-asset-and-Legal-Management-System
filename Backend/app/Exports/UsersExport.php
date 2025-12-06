<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::select([
            'id', 'fname', 'mname', 'lname', 'email', 'username', 
            'role', 'status', 'privileges', 'failed_login_attempts', 
            'locked_until', 'password_changed_at', 'created_at', 'updated_at'
        ])->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'First Name',
            'Middle Name',
            'Last Name',
            'Email',
            'Username',
            'Role',
            'Status',
            'Privileges',
            'Failed Login Attempts',
            'Locked Until',
            'Password Changed At',
            'Created At',
            'Updated At'
        ];
    }

    /**
     * @param User $user
     */
    public function map($user): array
    {
        return [
            $user->id,
            $user->fname,
            $user->mname,
            $user->lname,
            $user->email,
            $user->username,
            ucfirst($user->role),
            ucfirst($user->status),
            $user->privileges ? implode(', ', $user->privileges) : 'None',
            $user->failed_login_attempts ?? 0,
            $user->locked_until ? $user->locked_until->format('Y-m-d H:i:s') : 'Not Locked',
            $user->password_changed_at ? $user->password_changed_at->format('Y-m-d H:i:s') : 'Never',
            $user->created_at->format('Y-m-d H:i:s'),
            $user->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
}
