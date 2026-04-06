<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUangKeluarRequest;
use App\Http\Requests\UpdateUangKeluarRequest;
use App\Models\UangKeluar;
use App\Support\AttachmentUrl;
use App\Support\FinanceNotifier;
use App\Support\MailConfiguration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UangKeluarController extends Controller
{
    public function index(Request $request): View
    {
        $context = $this->resolveContext($request);

        return $this->renderPage($request, $context);
    }

    public function show(Request $request, UangKeluar $expense): View
    {
        $context = $this->resolveContext($request);

        abort_unless($this->canView($request, $expense, $context), 403);

        $mode = $request->query('mode', 'view');
        $mode = in_array($mode, ['view', 'edit'], true) ? $mode : 'view';

        if ($mode === 'edit') {
            abort_unless($this->canEdit($request, $expense, $context), 403);
        }

        return $this->renderPage($request, $context, $expense, $mode);
    }

    public function store(StoreUangKeluarRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $file = $request->file('bukti');
        $path = $file->store('bukti-pengeluaran', 'public');

        $expense = UangKeluar::query()->create([
            'created_by' => $request->user()->id,
            'jumlah' => $validated['jumlah'],
            'deskripsi' => $validated['deskripsi'],
            'tanggal' => $validated['tanggal'],
            'bukti_path' => $path,
            'bukti_original_name' => $file->getClientOriginalName(),
            'status' => 'pending',
        ]);

        FinanceNotifier::notifyAdminsForExpense($expense, $request->boolean('send_email'));

        return redirect()
            ->route('keuangan.pengeluaran.show', $expense)
            ->with('success', 'Data pengeluaran berhasil disimpan dan menunggu approval.');
    }

    public function update(UpdateUangKeluarRequest $request, UangKeluar $expense): RedirectResponse
    {
        $context = $this->resolveContext($request);

        abort_unless($this->canEdit($request, $expense, $context), 403);

        $validated = $request->validated();

        if ($context === 'approval') {
            $expense->update([
                'status' => $validated['status'],
                'approval_note' => $validated['approval_note'] ?? null,
                'approved_by' => $request->user()->id,
            ]);

            FinanceNotifier::notifyUserForExpenseStatus($expense->fresh(['creator']), $request->boolean('send_email'));

            return redirect()
                ->route('keuangan.approval-pengeluaran.show', ['expense' => $expense, 'mode' => 'view'])
                ->with('success', 'Status pengeluaran berhasil diperbarui.');
        }

        $payload = [
            'jumlah' => $validated['jumlah'],
            'deskripsi' => $validated['deskripsi'],
            'tanggal' => $validated['tanggal'],
        ];

        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');

            if ($expense->bukti_path && Storage::disk('public')->exists($expense->bukti_path)) {
                Storage::disk('public')->delete($expense->bukti_path);
            }

            $payload['bukti_path'] = $file->store('bukti-pengeluaran', 'public');
            $payload['bukti_original_name'] = $file->getClientOriginalName();
        }

        $expense->update($payload);

        return redirect()
            ->route('keuangan.pengeluaran.show', ['expense' => $expense, 'mode' => 'view'])
            ->with('success', 'Data pengeluaran berhasil diperbarui.');
    }

    public function destroy(Request $request, UangKeluar $expense): RedirectResponse
    {
        $context = $this->resolveContext($request);

        abort_unless($this->canDelete($request, $expense, $context), 403);

        if ($expense->bukti_path && Storage::disk('public')->exists($expense->bukti_path)) {
            Storage::disk('public')->delete($expense->bukti_path);
        }

        $expense->delete();

        return redirect()
            ->route($this->contextConfig($context)['indexRoute'])
            ->with('success', 'Data pengeluaran berhasil dihapus.');
    }

    public function data(Request $request): JsonResponse
    {
        $context = $this->resolveContext($request);
        $config = $this->contextConfig($context);

        $baseQuery = UangKeluar::query()->with(['creator', 'approver']);

        $query = $context === 'approval'
            ? $baseQuery
            : $baseQuery->where('created_by', $request->user()->id);

        $recordsTotal = (clone $query)->count();
        $this->applySearch($query, (string) $request->input('search.value', ''));
        $recordsFiltered = (clone $query)->count();

        $columns = ['tanggal', 'jumlah', 'deskripsi', 'created_by', 'created_at', 'status', 'created_at'];
        $orderColumn = $columns[(int) $request->input('order.0.column', 4)] ?? 'created_at';
        $orderDirection = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $rows = $query
            ->orderBy($orderColumn, $orderDirection)
            ->skip((int) $request->integer('start'))
            ->take((int) $request->integer('length', 10))
            ->get()
            ->map(fn(UangKeluar $expense) => [
                'tanggal' => $expense->tanggal?->format('d/m/Y'),
                'jumlah' => 'Rp ' . number_format((float) $expense->jumlah, 0, ',', '.'),
                'deskripsi' => e($expense->deskripsi),
                'pemohon' => e($expense->creator?->name ?? '-'),
                'bukti' => sprintf(
                    '<div class="d-flex gap-2"><a href="%s" target="_blank" class="btn btn-light btn-sm">Lihat Bukti</a><a href="%s" class="btn btn-outline-secondary btn-sm">Download</a></div>',
                    AttachmentUrl::preview('public', $expense->bukti_path, $expense->bukti_original_name, $expense->created_by),
                    AttachmentUrl::download('public', $expense->bukti_path, $expense->bukti_original_name, $expense->created_by)
                ),
                'status' => $this->statusBadge($expense->status),
                'aksi' => $this->actionButtons($expense, $context, $config['showRoute'], $request),
            ]);

        return response()->json([
            'draw' => (int) $request->integer('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }

    private function renderPage(Request $request, string $context, ?UangKeluar $expense = null, string $mode = 'create'): View
    {
        $config = $this->contextConfig($context);
        $showSendEmailOption = ($context === 'user' && $mode === 'create')
            || ($context === 'approval' && $expense && $mode === 'edit');

        return view('keuangan.uang-keluar.index', [
            'context' => $context,
            'page' => $config,
            'selectedExpense' => $expense,
            'mode' => $expense ? $mode : 'create',
            'tableAjaxUrl' => route($config['dataRoute']),
            'indexUrl' => route($config['indexRoute']),
            'storeUrl' => route($config['storeRoute']),
            'updateUrl' => $expense ? $this->resolveUpdateUrl($context, $expense) : route($config['storeRoute']),
            'deleteUrl' => $expense ? $this->resolveDeleteUrl($context, $expense) : null,
            'canCreate' => $context === 'user',
            'canEditRecord' => $expense ? $this->canEdit($request, $expense, $context) : false,
            'canDelete' => $expense ? $this->canDelete($request, $expense, $context) : false,
            'mailAvailable' => $showSendEmailOption ? MailConfiguration::isConfigured() : false,
            'showSendEmailOption' => $showSendEmailOption && MailConfiguration::isConfigured(),
        ]);
    }

    private function resolveContext(Request $request): string
    {
        $routeName = (string) $request->route()?->getName();

        return str_starts_with($routeName, 'keuangan.approval-pengeluaran.') ? 'approval' : 'user';
    }

    /**
     * @return array<string, string>
     */
    private function contextConfig(string $context): array
    {
        return $context === 'approval'
            ? [
                'title' => 'Approval Pengeluaran',
                'heading' => 'Approval Pengeluaran',
                'description' => 'Lihat seluruh data pengeluaran dan perbarui status approval.',
                'indexRoute' => 'keuangan.approval-pengeluaran.index',
                'dataRoute' => 'keuangan.approval-pengeluaran.data',
                'storeRoute' => 'keuangan.approval-pengeluaran.index',
                'showRoute' => 'keuangan.approval-pengeluaran.show',
                'submitLabel' => 'Simpan Status',
            ]
            : [
                'title' => 'Data Pengeluaran',
                'heading' => 'Data Pengeluaran',
                'description' => 'Tambahkan pengeluaran baru dan pantau hasil approval admin.',
                'indexRoute' => 'keuangan.pengeluaran.index',
                'dataRoute' => 'keuangan.pengeluaran.data',
                'storeRoute' => 'keuangan.pengeluaran.store',
                'showRoute' => 'keuangan.pengeluaran.show',
                'submitLabel' => 'Simpan Pengeluaran',
            ];
    }

    private function applySearch(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $builder) use ($search) {
            $builder
                ->where('deskripsi', 'like', '%' . $search . '%')
                ->orWhere('status', 'like', '%' . $search . '%')
                ->orWhereHas('creator', fn(Builder $creator) => $creator->where('name', 'like', '%' . $search . '%'));
        });
    }

    private function canView(Request $request, UangKeluar $expense, string $context): bool
    {
        if ($context === 'approval') {
            return true;
        }

        return (int) $expense->created_by === (int) $request->user()->id;
    }

    private function canEdit(Request $request, UangKeluar $expense, string $context): bool
    {
        if ($context === 'approval') {
            return $expense->exists;
        }

        return $expense->exists
            && (int) $expense->created_by === (int) $request->user()->id
            && $expense->status === 'pending';
    }

    private function canDelete(Request $request, UangKeluar $expense, string $context): bool
    {
        if ($request->user()->isAdmin()) {
            return true;
        }

        return $context === 'user'
            && (int) $expense->created_by === (int) $request->user()->id
            && $expense->status === 'pending';
    }
    private function actionButtons(UangKeluar $expense, string $context, string $showRoute, Request $request): string
    {
        $buttons = [
            sprintf(
                '<a href="%s" class="btn btn-light btn-sm">View</a>',
                route($showRoute, $expense)
            ),
        ];

        if ($this->canEdit($request, $expense, $context)) {
            $buttons[] = sprintf(
                '<a href="%s" class="btn btn-primary btn-sm">Edit</a>',
                route($showRoute, ['expense' => $expense, 'mode' => 'edit'])
            );
        }

        return '<div class="d-flex justify-content-end gap-2">' . implode('', $buttons) . '</div>';
    }

    private function statusBadge(string $status): string
    {
        $classes = match ($status) {
            'approved' => 'bg-soft-success text-success',
            'rejected' => 'bg-soft-danger text-danger',
            default => 'bg-soft-warning text-warning',
        };

        return sprintf('<span class="badge %s">%s</span>', $classes, ucfirst($status));
    }

    private function resolveUpdateUrl(string $context, UangKeluar $expense): string
    {
        return route($context === 'approval' ? 'keuangan.approval-pengeluaran.update' : 'keuangan.pengeluaran.update', $expense);
    }

    private function resolveDeleteUrl(string $context, UangKeluar $expense): string
    {
        return route($context === 'approval' ? 'keuangan.approval-pengeluaran.destroy' : 'keuangan.pengeluaran.destroy', $expense);
    }
}
