@extends('layouts.cms')

@section('title', 'Pengguna')

@section('content')
    <div class="cms-page">
        <x-cms-page-header eyebrow="Akses CMS" title="Pengguna"
            description="Kelola akun, peran, dan akses tim editorial CiptaOffice.">
            <x-slot:actions><a class="btn btn-primary" href="{{ route('cms.users.create') }}"><i class="bi bi-person-plus"></i><span class="cms-action-label--compact">Tambah pengguna</span></a></x-slot:actions>
        </x-cms-page-header>
        <section class="cms-surface" aria-label="Daftar pengguna">
            <x-cms-table kicker="Tim editorial" record-label="pengguna" :paginator="$users" :column-count="4" :empty="$users->isEmpty()" empty-icon="people"
                empty-title="Belum ada pengguna" empty-description="Tambahkan anggota tim untuk memberi akses CMS.">
                <x-slot:head>
                    <th>Nama</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th class="text-end">Tindakan</th>
                </x-slot:head>
                @foreach ($users as $user)
                    <tr>
                        <td><span class="cms-table-primary">{{ $user->name }}</span><span class="cms-table-secondary">{{ $user->email }}</span></td>
                        <td>{{ $user->role->label() }}</td>
                        <td><span class="cms-status {{ $user->is_active ? 'cms-status--success' : '' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td><div class="cms-table-actions"><a class="btn btn-sm btn-outline-primary" href="{{ route('cms.users.edit', $user) }}"><i class="bi bi-pencil-square" aria-hidden="true"></i>Edit</a></div></td>
                    </tr>
                @endforeach
            </x-cms-table>
        </section>
    </div>
@endsection
