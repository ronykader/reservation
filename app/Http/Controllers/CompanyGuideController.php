<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\StoreGuideRequest;
use App\Http\Requests\UpdateGuideRequest;
use App\Mail\UserRegistrationInvite;
use App\Models\Company;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CompanyGuideController extends Controller
{
    public function index(Company $company): View
    {
        $this->authorize('viewAny', $company);
        $guides = $company->users()->where('role_id', Role::GUIDE->value)->get();
        return view('companies.guides.index', compact('company', 'guides'));
    }

    public function create(Company $company): View
    {
        $this->authorize('create', $company);
        return view('companies.guides.create', compact('company'));
    }

    public function store(StoreGuideRequest $request, Company $company): RedirectResponse
    {
        $this->authorize('create', $company);
        $invitation = UserInvitation::create([
            'email' => $request->input('email'),
            'token' => Str::uuid(),
            'company_id' => $company->id,
            'role_id' => Role::GUIDE->value,
        ]);

        Mail::to($request->input('email'))->send(new UserRegistrationInvite($invitation));

        return to_route('companies.guides.index', $company);
    }

    public function edit(Company $company, User $guide)
    {
        $this->authorize('update', $company);
        return view('companies.guides.edit', compact('company', 'guide'));
    }

    public function update(UpdateGuideRequest $request, Company $company, User $guide): RedirectResponse
    {
        $this->authorize('update', $company);
        $guide->update($request->validated());
        return to_route('companies.guides.index', $company);
    }

    public function destroy(Company $company, User $guide): RedirectResponse
    {
        $guide->delete();
        return to_route('companies.guides.index', $company);
    }
}
