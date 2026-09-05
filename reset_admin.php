<?php
// Reset super admin password + role - run via php artisan tinker
$u = \App\Models\User::find(1);
$u->password = 'Admin123!';
$u->role = 1;
$u->save();

// Pastikan role super-admin ada dan terpasang
$sa = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super-admin']);
$u->assignRole('super-admin');

echo 'OK: ' . $u->email . ' password reset, role=' . $u->role . PHP_EOL;
echo 'Hash check: ' . (\Illuminate\Support\Facades\Hash::check('Admin123!', $u->password) ? 'MATCH' : 'FAIL') . PHP_EOL;
