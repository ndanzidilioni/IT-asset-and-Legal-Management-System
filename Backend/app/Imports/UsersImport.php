<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements ToCollection, WithHeadingRow
{
    private $errors = [];
    private $successCount = 0;
    private $skipCount = 0;

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            try {
                // Skip empty rows
                if (empty($row['first_name']) && empty($row['email'])) {
                    $this->skipCount++;
                    continue;
                }

                // Validate required fields
                $validator = Validator::make($row->toArray(), [
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'username' => 'required|string|max:255|unique:users,username',
                    'role' => 'required|in:admin,user,developer,client',
                    'status' => 'sometimes|in:active,inactive',
                ]);

                if ($validator->fails()) {
                    $this->errors[] = [
                        'row' => $row,
                        'errors' => $validator->errors()->all()
                    ];
                    continue;
                }

                // Generate default password if not provided
                $password = $row['password'] ?? $this->generateDefaultPassword();
                
                // Validate password policy if provided
                if (isset($row['password'])) {
                    $passwordErrors = User::validatePasswordPolicy($row['password']);
                    if (!empty($passwordErrors)) {
                        $this->errors[] = [
                            'row' => $row,
                            'errors' => $passwordErrors
                        ];
                        continue;
                    }
                }

                // Parse privileges
                $privileges = [];
                if (!empty($row['privileges'])) {
                    $privilegesList = explode(',', $row['privileges']);
                    $availablePrivileges = array_keys(User::getAvailablePrivileges());
                    foreach ($privilegesList as $privilege) {
                        $privilege = trim($privilege);
                        if (in_array($privilege, $availablePrivileges)) {
                            $privileges[] = $privilege;
                        }
                    }
                }

                // Create user
                User::create([
                    'fname' => $row['first_name'],
                    'mname' => $row['middle_name'] ?? null,
                    'lname' => $row['last_name'],
                    'email' => $row['email'],
                    'username' => $row['username'],
                    'password' => Hash::make($password),
                    'role' => $row['role'],
                    'status' => $row['status'] ?? 'active',
                    'privileges' => $privileges,
                    'password_changed_at' => now(),
                    'must_change_password' => isset($row['password']) ? false : true, // Force change if default password
                ]);

                $this->successCount++;

            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $row,
                    'errors' => [$e->getMessage()]
                ];
            }
        }
    }

    /**
     * Generate a default password
     */
    private function generateDefaultPassword(): string
    {
        return 'TempPass123!';
    }

    /**
     * Get import results
     */
    public function getResults(): array
    {
        return [
            'success_count' => $this->successCount,
            'skip_count' => $this->skipCount,
            'error_count' => count($this->errors),
            'errors' => $this->errors
        ];
    }
}
