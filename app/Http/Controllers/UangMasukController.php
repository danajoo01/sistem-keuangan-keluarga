<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUangMasukRequest;
use App\Http\Requests\UpdateUangMasukRequest;
use App\Models\UangMasuk;
use App\Support\FinanceNotifier;
use App\Support\MailConfiguration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UangMasukController extends Controller
{
    public function index(Request $request): View
    {
        $context = $this->resolveContext($request);

        return $this->renderPage($request, $context);
    }

    public function show(Request $request, UangMasuk $income): View
    {
        $context = $this->resolveContext($request);

        abort_unless($this->canView($request, $income, $context), 403);

        $mode = $request->query('mode', 'view');
        $mode = in_array($mode, ['view', 'edit'], true) ? $mode : 'view';

        if ($mode === 'edit') {
            abort_unless($this->canEdit($request, $income, $context), 403);
        }

        return $this->renderPage($request, $context, $income, $mode);
    }

    public function store(StoreUangMasukRequest $request): RedirectResponse
    {
        $context = $this->resolveContext($request);
        $validated = $request->validated();

        $income = UangMasuk::query()->create([
            'created_by' => $request->user()->id,
            'approved_by' => $context === 'master' ? $request->user()->id : null,
            'source' => $context === 'submission' ? 'pengajuan' : 'admin',
            'jumlah' => $validated['jumlah'],
            'deskripsi' => $validated['deskripsi'],
            'tanggal' => $validated['tanggal'],
            'status' => $context === 'master' ? 'approved' : 'pending',
        ]);

        if ($context === 'submission') {
            FinanceNotifier::notifyAdminsForSubmission($income, $request->boolean('send_email'));
        }

        return redirect()
            ->route($this->contextConfig($context)['showRoute'], $income)
            ->with('success', $context === 'master'
                ? 'Data pemasukan berhasil disimpan.'
                : 'Pengajuan dana berhasil disimpan dan menunggu approval.');
    }

    public function update(UpdateUangMasukRequest $request, UangMasuk $income): RedirectResponse
    {
        $context = $this->resolveContext($request);

        abort_unless($this->canEdit($request, $income, $context), 403);

        $validated = $request->validated();

        if ($context === 'approval' && $income->isSubmission()) {
            $income->update([
                'status' => $validated['status'],
                'approval_note' => $validated['approval_note'] ?? null,
                'approved_by' => $request->user()->id,
            ]);

            FinanceNotifier::notifyUserForSubmissionStatus($income->fresh(['creator']), $request->boolean('send_email'));

            $message = 'Status pengajuan dana berhasil diperbarui.';
        } elseif ($context === 'submission' && $income->isSubmission()) {
            $income->update([
                'jumlah' => $validated['jumlah'],
                'deskripsi' => $validated['deskripsi'],
                'tanggal' => $validated['tanggal'],
                'status' => 'pending',
                'approved_by' => null,
            ]);

            $message = 'Pengajuan dana berhasil diperbarui.';
        } else {
            $income->update([
                'jumlah' => $validated['jumlah'],
                'deskripsi' => $validated['deskripsi'],
                'tanggal' => $validated['tanggal'],
                'status' => 'approved',
                'approved_by' => $request->user()->id,
            ]);

            $message = 'Data pemasukan berhasil diperbarui.';
        }

        return redirect()
            ->route($this->contextConfig($context)['showRoute'], ['income' => $income, 'mode' => 'view'])
            ->with('success', $message);
    }

    public function destroy(Request $request, UangMasuk $income): RedirectResponse
    {
        $context = $this->resolveContext($request);

        abort_unless($this->canDelete($request, $income, $context), 403);

        $income->delete();

        return redirect()
            ->route($this->contextConfig($context)['indexRoute'])
            ->with('success', 'Data pemasukan berhasil dihapus.');
    }

    public function data(Request $request): JsonResponse
    {
        $context = $this->resolveContext($request);
        $config = $this->contextConfig($context);

        $baseQuery = UangMasuk::query()->with(['creator', 'approver']);

        $query = match ($context) {
            'submission' => $baseQuery
                ->where('source', 'pengajuan')
                ->where('created_by', $request->user()->id),
            'approval' => $baseQuery->where('source', 'pengajuan'),
            default => $baseQuery,
        };

        $recordsTotal = (clone $query)->count();
        $this->applySearch($query, (string) $request->input('search.value', ''));
        $recordsFiltered = (clone $query)->count();

        $columns = ['tanggal', 'jumlah', 'deskripsi', 'source', 'created_by', 'status', 'created_at'];
        $orderColumn = $columns[(int) $request->input('order.0.column', 5)] ?? 'created_at';
        $orderDirection = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $rows = $query
            ->orderBy($orderColumn, $orderDirection)
            ->skip((int) $request->integer('start'))
            ->take((int) $request->integer('length', 10))
            ->get()
            ->map(fn(UangMasuk $income) => [
                'tanggal' => $income->tanggal?->format('d/m/Y'),
                'jumlah' => 'Rp ' . number_format((float) $income->jumlah, 0, ',', '.'),
                'deskripsi' => e($income->deskripsi),
                'sumber' => $income->isSubmission() ? 'Pengajuan Dana' : 'Input Admin',
                'pemohon' => e($income->creator?->name ?? '-'),
                'status' => $this->statusBadge($income->status),
                'aksi' => $this->actionButtons($income, $context, $config['showRoute'], $request),
            ]);

        return response()->json([
            'draw' => (int) $request->integer('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }

    private function renderPage(Request $request, string $context, ?UangMasuk $income = null, string $mode = 'create'): View
    {
        $config = $this->contextConfig($context);

        return view('keuangan.uang-masuk.index', [
            'context' => $context,
            'page' => $config,
            'selectedIncome' => $income,
            'mode' => $income ? $mode : 'create',
            'tableAjaxUrl' => route($config['dataRoute']),
            'indexUrl' => route($config['indexRoute']),
            'storeUrl' => route($config['storeRoute']),
            'updateUrl' => $income ? $this->resolveUpdateUrl($context, $income) : route($config['storeRoute']),
            'deleteUrl' => $income ? $this->resolveDeleteUrl($context, $income) : null,
            'canCreate' => $context !== 'approval',
            'canDelete' => $income ? $this->canDelete($request, $income, $context) : false,
            'canEditRecord' => $income ? $this->canEdit($request, $income, $context) : false,
            'isStatusOnlyEdit' => $context === 'approval' && $income?->isSubmission() && $mode === 'edit',
            'mailAvailable' => MailConfiguration::isConfigured(),
            'showSendEmailOption' => MailConfiguration::isConfigured()
                && (($context === 'submission' && $mode === 'create')
                    || ($context === 'approval' && $income?->isSubmission() && $mode === 'edit')),
        ]);
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
                ->orWhere('source', 'like', '%' . $search . '%')
                ->orWhereHas('creator', fn(Builder $creator) => $creator->where('name', 'like', '%' . $search . '%'));
        });
    }

    private function resolveContext(Request $request): string
    {
        $routeName = (string) $request->route()?->getName();

        return match (true) {
            str_starts_with($routeName, 'keuangan.pengajuan-dana.') => 'submission',
            str_starts_with($routeName, 'keuangan.approval-pengajuan.') => 'approval',
            default => 'master',
        };
    }

    /**
     * @return array<string, string>
     */
    private function contextConfig(string $context): array
    {
        return match ($context) {
            'submission' => [
                'title' => 'Pengajuan Dana',
                'heading' => 'Pengajuan Dana',
                'description' => 'Ajukan dana baru dan pantau status approval dari admin.',
                'indexRoute' => 'keuangan.pengajuan-dana.index',
                'dataRoute' => 'keuangan.pengajuan-dana.data',
                'storeRoute' => 'keuangan.pengajuan-dana.store',
                'showRoute' => 'keuangan.pengajuan-dana.show',
                'submitLabel' => 'Kirim Pengajuan',
            ],
            'approval' => [
                'title' => 'Approval Pengajuan Dana',
                'heading' => 'Approval Pengajuan Dana',
                'description' => 'Tinjau pengajuan dana user lalu approve atau reject.',
                'indexRoute' => 'keuangan.approval-pengajuan.index',
                'dataRoute' => 'keuangan.approval-pengajuan.data',
                'storeRoute' => 'keuangan.approval-pengajuan.index',
                'showRoute' => 'keuangan.approval-pengajuan.show',
                'submitLabel' => 'Simpan Status',
            ],
            default => [
                'title' => 'Data Pemasukan',
                'heading' => 'Data Pemasukan',
                'description' => 'Kelola pemasukan langsung dan pantau seluruh pengajuan dana.',
                'indexRoute' => 'keuangan.pemasukan.index',
                'dataRoute' => 'keuangan.pemasukan.data',
                'storeRoute' => 'keuangan.pemasukan.store',
                'showRoute' => 'keuangan.pemasukan.show',
                'submitLabel' => 'Simpan Data',
            ],
        };
    }

    private function canView(Request $request, UangMasuk $income, string $context): bool
    {
        return match ($context) {
            'submission' => $income->isSubmission() && (int) $income->created_by === (int) $request->user()->id,
            'approval' => $income->isSubmission(),
            default => true,
        };
    }

    private function canEdit(Request $request, UangMasuk $income, string $context): bool
    {
        if ($context === 'submission') {
            return $income->isSubmission()
                && (int) $income->created_by === (int) $request->user()->id
                && $income->status === 'pending';
        }

        if ($context === 'approval') {
            return $income->isSubmission();
        }

        return true;
    }

    private function canDelete(Request $request, UangMasuk $income, string $context): bool
    {
        if ($request->user()->isAdmin()) {
            return true;
        }

        return $context === 'submission'
            && $income->isSubmission()
            && (int) $income->created_by === (int) $request->user()->id
            && $income->status === 'pending';
    }

    private function actionButtons(UangMasuk $income, string $context, string $showRoute, Request $request): string
    {
        $buttons = [
            sprintf(
                '<a href="%s" class="btn btn-light btn-sm">View</a>',
                route($showRoute, $income)
            ),
        ];

        if ($this->canEdit($request, $income, $context)) {
            $buttons[] = sprintf(
                '<a href="%s" class="btn btn-primary btn-sm">Edit</a>',
                route($showRoute, ['income' => $income, 'mode' => 'edit'])
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

    private function resolveUpdateUrl(string $context, UangMasuk $income): string
    {
        return route(match ($context) {
            'submission' => 'keuangan.pengajuan-dana.update',
            'approval' => 'keuangan.approval-pengajuan.update',
            default => 'keuangan.pemasukan.update',
        }, $income);
    }

    private function resolveDeleteUrl(string $context, UangMasuk $income): string
    {
        return route(match ($context) {
            'submission' => 'keuangan.pengajuan-dana.destroy',
            'approval' => 'keuangan.approval-pengajuan.destroy',
            default => 'keuangan.pemasukan.destroy',
        }, $income);
    }
}
