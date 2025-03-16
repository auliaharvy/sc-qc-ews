<?php

use App\Models\Navigation;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\Part;
use App\Models\NgType;

if (!function_exists('getMenus')) {
    function getMenus()
    {
        return Navigation::with('subMenus')->orderBy('sort', 'desc')->get();
    }
}

if (!function_exists('getParentMenus')) {
    function getParentMenus($url)
    {
        $menu = Navigation::where('url', $url)->first();
        if ($menu) {
            $parentMenu = Navigation::select('name')->where('id', $menu->main_menu)->first();
            return $parentMenu->name ?? '';
        }
        return '';
    }
}

if (!function_exists('getRoles')) {
    function getRoles()
    {
        return Role::where('name', '!=', 'admin')->get();
    }
}

if (!function_exists('getSupplier')) {
    function getSupplier()
    {
        return Supplier::get();
    }
}

if (!function_exists('getNgTypes')) {
    function getNgTypes()
    {
        return NgType::get();
    }
}

if (!function_exists('getPart')) {
    function getPart()
    {
        $userRole = auth()->user()->roles()->first()->name;
        $supplierId = auth()->user()->supplier_id;
        $userRole = auth()->user()->roles()->first()->name;
        if ($userRole == 'Admin Supplier') {
            return Part::where('supplier_id', $supplierId)->get();
        } else {
            return Part::get();
        }
    }
}
