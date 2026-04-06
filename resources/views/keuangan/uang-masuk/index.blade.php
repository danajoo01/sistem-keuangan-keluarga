@extends('layouts.master')

@section('title', $page['title'])

@php
$isEdit = $selectedIncome && $mode === 'edit';
$isView = $selectedIncome && $mode === 'view';
$isCreate = ! $selectedIncome || $mode === 'create';
$disableEmptyForm = ! $canCreate && ! $selectedIncome;
$formAction = $isEdit ? $updateUrl : $storeUrl;
$statusBadgeMap = [
'approved' => ['bg-soft-success', 'text-success'],
'rejected' => ['bg-soft-danger', 'text-danger'],
'pending' => ['bg-soft-warning', 'text-warning'],
];
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendors/css/dataTables.bs5.min.css') }}">
@endpush

@section('content')
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">{{ $page['heading'] }}</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Keuangan</li>
            <li class="breadcrumb-item">{{ $page['heading'] }}</li>
        </ul>
    </div>
</div>

<div class="main-content">
    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card stretch stretch-full">
                <div class="card-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div>
                        <h5 class="card-title mb-1">List {{ $page['heading'] }}</h5>
                        <p class="text-muted mb-0 fs-12">{{ $page['description'] }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        @if($canCreate)
                        <a href="{{ $indexUrl }}" class="btn btn-primary btn-sm">Tambah Baru</a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle w-100" id="income-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jumlah</th>
                                    <th>Deskripsi</th>
                                    <th>Sumber</th>
                                    <th>Pemohon</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card stretch stretch-full">
                <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                    <h5 class="card-title mb-0">
                        @if($isCreate)
                        Form {{ $page['heading'] }}
                        @elseif($isEdit)
                        Edit {{ $page['heading'] }}
                        @else
                        Detail {{ $page['heading'] }}
                        @endif
                    </h5>

                    @if($selectedIncome)
                    @php([$statusBg, $statusText] = $statusBadgeMap[$selectedIncome->status] ?? ['bg-soft-secondary', 'text-muted'])
                    <div class="d-flex gap-2">
                        <span class="badge {{ $statusBg }} {{ $statusText }}">{{ ucfirst($selectedIncome->status) }}</span>
                        <span class="badge bg-soft-primary text-primary">{{ $selectedIncome->isSubmission() ? 'Pengajuan Dana' : 'Input Admin' }}</span>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    @if($selectedIncome)
                    <div class="alert alert-light border mb-4">
                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <div>
                                <div class="fs-12 text-muted">Dibuat oleh</div>
                                <div class="fw-semibold">{{ $selectedIncome->creator?->name ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="fs-12 text-muted">Diupdate</div>
                                <div class="fw-semibold">{{ $selectedIncome->updated_at?->format('d M Y H:i') }}</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <form method="POST" action="{{ $formAction }}">
                        @csrf
                        @if($isEdit)
                        @method('PATCH')
                        @endif

                        @if($disableEmptyForm)
                        <div class="alert alert-light border mb-4">
                            Pilih data dari tabel untuk melihat detail atau memproses approval.
                        </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="jumlah" class="form-label">Jumlah</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="jumlah"
                                    id="jumlah"
                                    value="{{ old('jumlah', $selectedIncome?->jumlah) }}"
                                    class="form-control @error('jumlah') is-invalid @enderror"
                                    @disabled($isView || $isStatusOnlyEdit || $disableEmptyForm)>
                                @error('jumlah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea
                                    name="deskripsi"
                                    id="deskripsi"
                                    rows="4"
                                    class="form-control @error('deskripsi') is-invalid @enderror"
                                    @disabled($isView || $isStatusOnlyEdit || $disableEmptyForm)>{{ old('deskripsi', $selectedIncome?->deskripsi) }}</textarea>
                                @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label for="tanggal" class="form-label">Tanggal</label>
                                <input
                                    type="date"
                                    name="tanggal"
                                    id="tanggal"
                                    value="{{ old('tanggal', $selectedIncome?->tanggal?->format('Y-m-d')) }}"
                                    class="form-control @error('tanggal') is-invalid @enderror"
                                    @disabled($isView || $isStatusOnlyEdit || $disableEmptyForm)>
                                @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            @if($selectedIncome && $selectedIncome->isSubmission() && $context === 'approval')
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                @if($isEdit)
                                <select
                                    name="status"
                                    id="status"
                                    class="form-select @error('status') is-invalid @enderror">
                                    <option value="approved" @selected(old('status', $selectedIncome->status) === 'approved')>Approve</option>
                                    <option value="rejected" @selected(old('status', $selectedIncome->status) === 'rejected')>Reject</option>
                                </select>
                                @else
                                <input type="text" value="{{ ucfirst($selectedIncome->status) }}" class="form-control" disabled>
                                @endif
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Approved Oleh</label>
                                <input type="text" value="{{ $selectedIncome->approver?->name ?? '-' }}" class="form-control" disabled>
                            </div>
                            <div class="col-12">
                                <label for="approval_note" class="form-label">Keterangan Approval</label>
                                <textarea
                                    name="approval_note"
                                    id="approval_note"
                                    rows="3"
                                    class="form-control @error('approval_note') is-invalid @enderror"
                                    @disabled(! $isEdit)>{{ old('approval_note', $selectedIncome->approval_note) }}</textarea>
                                @error('approval_note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            @elseif($selectedIncome && $selectedIncome->approval_note)
                            <div class="col-12">
                                <label class="form-label">Keterangan Approval</label>
                                <textarea class="form-control" rows="3" disabled>{{ $selectedIncome->approval_note }}</textarea>
                            </div>
                            @elseif($selectedIncome)
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <input type="text" value="{{ ucfirst($selectedIncome->status) }}" class="form-control" disabled>
                            </div>
                            @endif

                            @if($showSendEmailOption)
                            <div class="col-12">
                                <div class="alert alert-light border mb-0">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="send_email" name="send_email" @checked(old('send_email'))>
                                        <label class="form-check-label fw-semibold" for="send_email">
                                            Kirim notifikasi email
                                        </label>
                                    </div>
                                    <div class="fs-12 text-muted mt-2">Jika ingin mailing pastikan user email aktif agar ada notifikasi email.</div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="mt-4 d-flex justify-content-between align-items-center gap-2 flex-wrap">
                            @if($canDelete)
                            <button type="submit" form="delete-income-form" class="btn btn-outline-danger" onclick="return confirm('Hapus data pemasukan ini?');">Hapus</button>
                            @else
                            <span class="text-muted fs-12">
                                @if($isView)
                                Mode view only.
                                @elseif($isStatusOnlyEdit)
                                Field inti dikunci, hanya status approval yang bisa diubah.
                                @else
                                Lengkapi field wajib sebelum menyimpan.
                                @endif
                            </span>
                            @endif

                            @if(! $isView && ($canCreate || $isEdit))
                            <button type="submit" class="btn btn-primary">{{ $page['submitLabel'] }}</button>
                            @endif
                        </div>
                    </form>

                    @if($canDelete)
                    <form id="delete-income-form" method="POST" action="{{ $deleteUrl }}">
                        @csrf
                        @method('DELETE')
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="{{ asset('assets/vendors/js/dataTables.min.js') }}"></script>
<script src="{{ asset('assets/vendors/js/dataTables.bs5.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#income-table').DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            ajax: '{{ $tableAjaxUrl }}',
            order: [
                [0, 'desc']
            ],
            columns: [{
                    data: 'tanggal',
                    name: 'tanggal'
                },
                {
                    data: 'jumlah',
                    name: 'jumlah'
                },
                {
                    data: 'deskripsi',
                    name: 'deskripsi'
                },
                {
                    data: 'sumber',
                    name: 'sumber',
                    orderable: false
                },
                {
                    data: 'pemohon',
                    name: 'pemohon',
                    orderable: false
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'aksi',
                    name: 'aksi',
                    orderable: false,
                    searchable: false,
                    className: 'text-end'
                },
            ],
            language: {
                processing: 'Memuat data...',
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                zeroRecords: 'Data tidak ditemukan',
                paginate: {
                    previous: 'Sebelumnya',
                    next: 'Berikutnya'
                }
            }
        });
    });
</script>
@endpush