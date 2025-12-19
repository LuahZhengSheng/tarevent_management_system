<?php
/**
 * Author: Tang Lit Xuan
 * 
 * Clear existing permissions data
 * This migration clears all existing permission data to prepare for the new permission system
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clear all existing permissions for admin users (not super_admin)
        // Super admin should keep null permissions (they have all permissions by default)
        DB::table('users')
            ->where('role', 'admin')
            ->whereNotNull('permissions')
            ->update(['permissions' => null]);
    }

    /**
     * Reverse the migrations.
     * 
     * Note: This cannot be reversed as we don't know what the original permissions were
     */
    public function down(): void
    {
        // Cannot reverse - permissions data is lost
        // This is intentional as we're migrating to a new permission system
    }
};
