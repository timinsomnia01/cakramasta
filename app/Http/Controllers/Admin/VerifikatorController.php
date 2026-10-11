<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Teams\CreateTeam;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveVerifikatorRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class VerifikatorController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('q', ''));

        return Inertia::render('admin/verifikators/index', [
            'verifikators' => User::query()
                ->role(UserRole::Verifikator)
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")))
                ->latest()
                ->paginate(15)
                ->withQueryString()
                ->through(fn (User $user) => $this->row($user)),
            'filters' => ['q' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/verifikators/form', ['verifikator' => null]);
    }

    public function store(SaveVerifikatorRequest $request, CreateTeam $createTeam): RedirectResponse
    {
        DB::transaction(function () use ($request, $createTeam) {
            $user = new User($request->safe()->only(['name', 'email', 'phone', 'password']));
            $user->forceFill([
                'role' => UserRole::Verifikator,
                'is_active' => $request->boolean('is_active'),
                'email_verified_at' => now(),
            ])->save();

            // Setiap akun memiliki tim pribadi, sama seperti pendaftaran biasa.
            $createTeam->handle($user, $user->name."'s Team", isPersonal: true);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Verifikator ditambahkan.')]);

        return to_route('admin.verifikators.index');
    }

    public function edit(User $verifikator): Response
    {
        $this->ensureVerifikator($verifikator);

        return Inertia::render('admin/verifikators/form', ['verifikator' => $this->row($verifikator)]);
    }

    public function update(SaveVerifikatorRequest $request, User $verifikator): RedirectResponse
    {
        $this->ensureVerifikator($verifikator);

        $data = $request->safe()->only(['name', 'email', 'phone']);

        if ($request->filled('password')) {
            $data['password'] = $request->validated('password');
        }

        $verifikator->fill($data)->forceFill(['is_active' => $request->boolean('is_active')])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Verifikator diperbarui.')]);

        return to_route('admin.verifikators.index');
    }

    /**
     * Hapus akun verifikator. Riwayat verifikasi tetap ada (kolom verified_by menjadi kosong).
     */
    public function destroy(User $verifikator): RedirectResponse
    {
        $this->ensureVerifikator($verifikator);

        $verifikator->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Verifikator dihapus.')]);

        return to_route('admin.verifikators.index');
    }

    private function ensureVerifikator(User $user): void
    {
        abort_unless($user->role === UserRole::Verifikator, 404);
    }

    /**
     * @return array<string, mixed>
     */
    private function row(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'isActive' => (bool) $user->is_active,
            'createdAt' => $user->created_at->toISOString(),
        ];
    }
}
