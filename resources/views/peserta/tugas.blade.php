<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Riwayat Tugas
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            <a href="/peserta/dashboard"
               class="inline-block mb-4 text-blue-600 hover:text-blue-800">
                ← Kembali ke Dashboard
            </a>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">

                <div class="p-6 border-b">
                    <h3 class="text-xl font-semibold">
                        Riwayat Tugas
                    </h3>
                </div>

                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-gray-200">

                        <thead>
<tr>
    <th>Judul</th>
    <th>Minggu</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>
</thead>

<tbody>

@foreach($tugas as $item)

<tr>

<td>{{ $item->judul }}</td>

<td>Minggu {{ $item->minggu }}</td>

<td>

@if($item->status == 'Selesai')

<span class="px-3 py-1 bg-green-100 text-green-700 rounded-full">
    Selesai
</span>

@elseif($item->status == 'Revisi')

<span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full">
    Revisi
</span>

@else

<span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full">
    Belum Diperiksa
</span>

@endif

</td>

<td>

<div class="flex gap-2">

<a
href="{{ route('peserta.tugas.edit',$item->id) }}"
class="bg-blue-500 text-white px-3 py-1 rounded">

Edit

</a>

<form
action="{{ route('peserta.tugas.destroy',$item->id) }}"
method="POST">

@csrf
@method('DELETE')

<button
onclick="return confirm('Yakin ingin menghapus tugas ini?')"
class="bg-red-500 text-white px-3 py-1 rounded">

Hapus

</button>

</form>

</div>

</td>

</tr>

@endforeach

                        <tbody class="bg-white divide-y divide-gray-200">

                            @forelse($tugas as $item)

                            <tr>

                                <td class="px-6 py-4">
                                    {{ $item->judul }}
                                </td>

                                <td class="px-6 py-4">
                                    Minggu {{ $item->minggu }}
                                </td>

                                <td class="px-6 py-4">

                                    @if($item->status == 'Selesai')

                                        <span class="px-3 py-1 rounded-full text-sm bg-green-100 text-green-700">
                                            Selesai
                                        </span>

                                    @elseif($item->status == 'Revisi')

                                        <span class="px-3 py-1 rounded-full text-sm bg-yellow-100 text-yellow-700">
                                            Revisi
                                        </span>

                                    @else

                                        <span class="px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-700">
                                            Belum Diperiksa
                                        </span>

                                    @endif

                                </td>

                            </tr>

                            @empty

                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                    Belum ada tugas yang diupload.
                                </td>
                            </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>
    </div>

</x-app-layout>