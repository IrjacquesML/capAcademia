<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserAuditReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserAuditController extends Controller
{
    public function show(Request $request, User $user, UserAuditReportService $reports): View
    {
        abort_unless($request->user()->canManageUser($user), 403);

        return view('admin.users.audit', $reports->build($user));
    }
}
