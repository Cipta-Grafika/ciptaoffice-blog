@extends('layouts.cms')

@section('title', 'Pengguna')

@section('content')
    <div class="cms-page">
        <x-cms-page-header eyebrow="Akses CMS" title="Pengguna"
            description="Kelola akun, peran, dan akses tim editorial CiptaOffice.">
            <x-slot:actions><a class="btn btn-primary" href="{{ route('cms.users.create') }}"><i class="bi bi-person-plus"></i><span class="cms-action-label--compact">Tambah pengguna</span></a></x-slot:actions>
        </x-cms-page-header>
        <section class="cms-surface" aria-label="Daftar pengguna">
            <div class="cms-list-toolbar"><p class="cms-surface-kicker mb-0">Tim editorial</p><p class="cms-record-count mb-0">{{ $users->total() }} pengguna</p></div>
            <div class="table-responsive">
                <table class="table table-hover cms-table mb-0">
                    <thead><tr><th>Nama</th><th>Role</th><th>Status</th><th class="text-end">Tindakan</th></tr></thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td><span class="cms-table-primary">{{ $user->name }}</span><span class="cms-table-secondary">{{ $user->email }}</span></td>
                                <td>{{ $user->role->label() }}</td>
                                <td><span class="cms-status {{ $user->is_active ? 'cms-status--success' : '' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td><div class="cms-table-actions"><a class="btn btn-sm btn-outline-primary" href="{{ route('cms.users.edit', $user) }}">Edit</a></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><div class="cms-empty-state"><span class="cms-empty-state-icon"><i class="bi bi-people"></i></span><h2>Belum ada pengguna</h2><p class="mb-0">Tambahkan anggota tim untuk memberi akses CMS.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <div class="cms-pagination">{{ $users->links() }}</div>
    </div>
@endsection
