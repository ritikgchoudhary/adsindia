<?php

namespace App\Http\Controllers;

use Laramin\Utility\Onumoti;

abstract class Controller
{
    public function __construct()
    {
        $className = get_called_class();
        Onumoti::mySite($this,$className);
    }

    public static function middleware()
    {
        return [];
    }

    /**
     * Check if current admin has a specific permission.
     */
    protected function checkPermission($key)
    {
        $admin = auth()->user();
        if (!$admin) return false;
        if (isset($admin->is_super_admin) && $admin->is_super_admin) return true;
        
        $perms = $admin->permissions;
        if (!$perms) return false;

        // permissions is cast as object/array in Admin model
        return (bool) ($perms->$key ?? false);
    }

}
