# Password Policy Guide

## Password Requirements

All passwords in the system **MUST** include:

1. ✅ **At least 8 characters** long
2. ✅ **One uppercase letter** (A-Z)
3. ✅ **One lowercase letter** (a-z)
4. ✅ **One number** (0-9)
5. ✅ **One special character** from: `!@#$%^&*(),.?":{}|<>`

## ✅ Valid Password Examples

These passwords meet ALL requirements:

| Password | Why it's valid |
|----------|----------------|
| `Admin123!` | Has uppercase, lowercase, number, and special char |
| `Password123!` | Has uppercase, lowercase, number, and special char |
| `Test1234!` | Has uppercase, lowercase, number, and special char |
| `MyP@ssw0rd` | Has uppercase, lowercase, number, and special char |
| `Secure@Pass1` | Has uppercase, lowercase, number, and special char |
| `Welcome2024!` | Has uppercase, lowercase, number, and special char |
| `NewUser#123` | Has uppercase, lowercase, number, and special char |

## ❌ Invalid Password Examples

These passwords DO NOT meet requirements:

| Password | Why it's invalid |
|----------|------------------|
| `admin123` | ❌ Missing uppercase letter and special character |
| `Admin123` | ❌ Missing special character |
| `admin123!` | ❌ Missing uppercase letter |
| `ADMIN123!` | ❌ Missing lowercase letter |
| `Admin!` | ❌ Too short (less than 8 characters) |
| `AdminTest` | ❌ Missing number and special character |
| `12345678!` | ❌ Missing uppercase and lowercase letters |

## Current User Passwords

Based on the system setup:

| Username | Current Password | Notes |
|----------|-----------------|-------|
| `admin` | `admin123` | ⚠️ Does not meet policy! Use `Admin123!` instead |
| `developer` | Unknown | May need reset |
| `Mwaki` | Unknown | May need reset |
| `Lupha` | Unknown | May need reset |

## How to Change Password

### Via Web Interface:

1. **Login** to the system
2. If prompted with "must change password" modal:
   - Enter your **current password**
   - Enter a **new password** that meets ALL requirements
   - **Confirm** the new password
   - Click **"Change Password"**

### Examples of Strong Passwords:

You can use any of these patterns:
- `[Word][Number][Special]` → `Welcome2024!`
- `[Name][Number][Special]` → `John1234!`
- `[Word][Special][Word][Number]` → `My@Pass123`
- `[Capital][lowercase][Number][Special]` → `Test123!`

### Tips for Creating Strong Passwords:

1. **Start with a word** (capitalize first letter): `Password`
2. **Add numbers**: `Password123`
3. **Add a special character**: `Password123!`
4. **Make it personal but secure**: `MyOffice2024!`

## Password Change Checklist

When changing your password, ensure:

- [ ] Password is at least 8 characters
- [ ] Contains at least one UPPERCASE letter
- [ ] Contains at least one lowercase letter
- [ ] Contains at least one number (0-9)
- [ ] Contains at least one special character (!@#$%^&*)
- [ ] New password and confirmation match
- [ ] Current password is correct

## Common Issues

### "Password does not meet requirements"

**Solution:** Your password is missing one or more requirements. Check that it has:
- ✅ Uppercase letter
- ✅ Lowercase letter
- ✅ Number
- ✅ Special character
- ✅ At least 8 characters

**Quick fix:** Use one of the example passwords like `Admin123!` or `Password123!`

### "Current password is incorrect"

**Solution:** The current password you entered doesn't match your account.
- Double-check you're typing it correctly
- Make sure Caps Lock is off
- Contact an administrator if you've forgotten your password

### "New passwords do not match"

**Solution:** The password and confirmation fields don't match.
- Type carefully in both fields
- Copy/paste if needed to ensure they match

## Resetting a Password (Admin)

If you're an administrator and need to reset a user's password:

```bash
cd Backend
php artisan tinker
```

Then in tinker:
```php
$user = App\Models\User::where('username', 'USERNAME')->first();
$user->password = Hash::make('Admin123!');  // Use a valid password
$user->must_change_password = true;  // Force user to change on next login
$user->save();
exit
```

Replace `USERNAME` with the actual username and `Admin123!` with your chosen password.

## Security Best Practices

1. **Don't reuse passwords** across different systems
2. **Change passwords regularly** (every 90 days recommended)
3. **Don't share passwords** with others
4. **Use unique passwords** for each account
5. **Don't write passwords down** in plain text

## Need Help?

If you're having trouble with passwords:
1. Try one of the **example passwords** listed above
2. Check the **password requirements** carefully
3. Contact your system administrator
4. Review the **common issues** section

## Password Policy Enforcement

The password policy is enforced:
- ✅ During user registration
- ✅ When changing passwords
- ✅ When resetting passwords
- ✅ For all users (including admins)

There are **no exceptions** to the password policy for security reasons.
