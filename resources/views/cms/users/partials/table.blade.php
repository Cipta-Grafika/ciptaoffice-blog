<x-cms-table kicker="Tim editorial" record-label="pengguna" :paginator="$users" :column-count="4" :empty="$users->isEmpty()" empty-icon="people"
    empty-title="Belum ada pengguna" empty-description="Tambahkan anggota tim untuk memberi akses CMS.">
    <x-slot:filters>
        <div class="d-flex align-items-center gap-2">
            <form class="cms-filter-form mb-0" method="get">
                <select class="form-select form-select-sm" name="status" aria-label="Filter status">
                    <option value="">Semua status</option>
                    <option value="1" @selected(request('status') === '1')>Aktif</option>
                    <option value="0" @selected(request('status') === '0')>Nonaktif</option>
                </select>
            </form>
            <a class="btn btn-sm btn-outline-secondary" href="#">
                <i class="bi bi-upload" aria-hidden="true"></i><span class="cms-action-label--compact ms-1">Import</span>
            </a>
            <a class="btn btn-sm btn-primary" href="{{ route('cms.users.create') }}">
                <i class="bi bi-person-plus" aria-hidden="true"></i><span class="cms-action-label--compact ms-1">Create</span>
            </a>
        </div>
    </x-slot:filters>
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
