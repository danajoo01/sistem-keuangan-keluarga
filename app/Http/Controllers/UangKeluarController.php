<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUangKeluarRequest;
use App\Http\Requests\UpdateUangKeluarRequest;
use App\Models\UangKeluar;
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
            abort_unless($this->canEdit($expense, $context), 403);
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

        return redirect()
            ->route('keuangan.pengeluaran.show', $expense)
            ->with('success', 'Data pengeluaran berhasil disimpan dan menunggu approval.');
    }

    public function update(UpdateUangKeluarRequest $request, UangKeluar $expense): RedirectResponse
    {
        $context = $this->resolveContext($request);

        abort_unless($this->canEdit($expense, $context), 403);

        $validated = $request->validated();

        $expense->update([
            'status' => $validated['status'],
            'approval_note' => $validated['approval_note'] ?? null,
            'approved_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('keuangan.approval-pengeluaran.show', ['expense' => $expense, 'mode' => 'view'])
            ->with('success', 'Status pengeluaran berhasil diperbarui.');
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
                    '<a href="%s" target="_blank" class="btn btn-light btn-sm">Lihat Bukti</a>',
                    Storage::url($expense->bukti_path)
                ),
                'status' => $this->statusBadge($expense->status),
                'aksi' => $this->actionButtons($expense, $context, $config['showRoute']),
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

        return view('keuangan.uang-keluar.index', [
            'context' => $context,
            'page' => $config,
            'selectedExpense' => $expense,
            'mode' => $expense ? $mode : 'create',
            'tableAjaxUrl' => route($config['dataRoute']),
            'indexUrl' => route($config['indexRoute']),
            'storeUrl' => route($config['storeRoute']),
            'canCreate' => $context === 'user',
            'canEditRecord' => $expense ? $this->canEdit($expense, $context) : false,
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

    private function canEdit(UangKeluar $expense, string $context): bool
    {
        return $context === 'approval' && $expense->exists;
    }

    private function actionButtons(UangKeluar $expense, string $context, string $showRoute): string
    {
        $buttons = [
            sprintf(
                '<a href="%s" class="btn btn-light btn-sm">View</a>',
                route($showRoute, $expense)
            ),
        ];

        if ($this->canEdit($expense, $context)) {
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
}
