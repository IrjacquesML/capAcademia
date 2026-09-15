<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Option;
use App\Models\Promotion;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Request $request): View
    {
        $users = User::query()
            ->manageableBy($request->user())
            ->with(['faculty:id,name', 'option:id,name', 'promotion:id,name'])
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(Request $request): View
    {
        return view('admin.users.form', $this->formData($request->user(), new User(['role' => UserRole::Student])));
    }

    public function store(Request $request): RedirectResponse
    {
        $actor = $request->user();
        $data = $this->validated($request, $actor);

        $created = User::query()->create($data);

        $this->audit->record($created, AuditAction::UserCreated, [
            'role' => $created->roleLabel(),
            'email' => $created->email,
        ], $actor);

        return redirect()->route('admin.users.index')->with('status', 'Utilisateur créé.');
    }

    public function edit(Request $request, User $user): View
    {
        abort_unless($request->user()->canManageUser($user), 403);

        return view('admin.users.form', $this->formData($request->user(), $user));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->canManageUser($user), 403);

        $data = $this->validated($request, $request->user(), $user);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->fill($data);
        $changed = collect($user->getDirty())->except(['password', 'remember_token'])->keys()->all();
        $user->save();

        $this->audit->record($user, AuditAction::UserUpdated, [
            'fields' => $changed,
        ], $request->user());

        return redirect()->route('admin.users.index')->with('status', 'Utilisateur mis à jour.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->canManageUser($user), 403);
        abort_if($user->is($request->user()), 403, 'Vous ne pouvez pas supprimer votre propre compte.');

        $this->audit->record($request->user(), AuditAction::UserDeleted, [
            'deleted_user_id' => $user->id,
            'deleted_name' => $user->name,
            'deleted_email' => $user->email,
        ], $request->user());

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Utilisateur supprimé.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(User $actor, User $user): array
    {
        $faculties = Faculty::query()->visibleToStaff($actor)->with('options:id,faculty_id,name')->orderBy('name')->get();

        return [
            'user' => $user,
            'actor' => $actor,
            'roles' => $actor->assignableRoles(),
            'faculties' => $faculties,
            'promotions' => Promotion::query()->orderBy('level')->get(),
            'optionsJson' => $faculties->mapWithKeys(
                fn (Faculty $faculty) => [
                    $faculty->id => $faculty->options->map(fn (Option $option) => [
                        'id' => $option->id,
                        'name' => $option->name,
                    ])->values(),
                ],
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, User $actor, ?User $existing = null): array
    {
        $allowedRoles = array_map(fn (UserRole $role) => $role->value, $actor->assignableRoles());

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($existing?->id)],
            'password' => [$existing ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ['required', Rule::in($allowedRoles)],
            'faculty_id' => ['nullable', 'exists:faculties,id'],
            'option_id' => ['nullable', 'exists:options,id'],
            'promotion_id' => ['nullable', 'exists:promotions,id'],
        ]);

        $role = UserRole::from($data['role']);
        $data['faculty_id'] = $data['faculty_id'] ?? null;
        $data['option_id'] = $data['option_id'] ?? null;
        $data['promotion_id'] = $data['promotion_id'] ?? null;

        if ($actor->isAdmin() && $actor->faculty_id) {
            $data['faculty_id'] = $actor->faculty_id;
        }

        if ($role === UserRole::SuperAdmin) {
            $data['faculty_id'] = null;
            $data['option_id'] = null;
            $data['promotion_id'] = null;
        } elseif ($role === UserRole::Admin) {
            $data['option_id'] = null;
            $data['promotion_id'] = null;
        } elseif ($role === UserRole::Teacher) {
            $request->validate(['faculty_id' => ['required', 'exists:faculties,id']]);
            $data['faculty_id'] = (int) ($data['faculty_id'] ?? $actor->faculty_id);
            $data['promotion_id'] = $data['promotion_id'] ?: null;
        } else {
            $request->validate([
                'faculty_id' => ['required', 'exists:faculties,id'],
                'option_id' => ['required', 'exists:options,id'],
                'promotion_id' => ['required', 'exists:promotions,id'],
            ]);
        }

        if ($actor->isAdmin() && $actor->faculty_id && $data['faculty_id'] && (int) $data['faculty_id'] !== (int) $actor->faculty_id) {
            abort(403, 'Vous ne pouvez pas affecter un utilisateur hors de votre faculté.');
        }

        $data['faculty_id'] = $data['faculty_id'] ?: null;
        $data['option_id'] = $data['option_id'] ?: null;
        $data['promotion_id'] = $data['promotion_id'] ?: null;

        if ($data['option_id'] && $data['faculty_id']) {
            $optionFacultyId = Option::query()->whereKey($data['option_id'])->value('faculty_id');
            abort_unless((int) $optionFacultyId === (int) $data['faculty_id'], 422, 'L’option ne correspond pas à la faculté.');
        }

        return $data;
    }
}
