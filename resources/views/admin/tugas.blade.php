<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Data Tugas Peserta
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <a href="/admin/dashboard"
               class="inline-block mb-4 text-blue-600 hover:text-blue-800">
                ← Kembali ke Dashboard
            </a>

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg overflow-hidden">

                <div class="p-6 border-b">
                    <h3 class="text-2xl font-bold">
                        Data Tugas Peserta
                    </h3>
                </div>

                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-gray-200">

                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">No</th>
                                <th class="px-4 py-3 text-left">Nama Peserta</th>
                                <th class="px-4 py-3 text-left">Judul</th>
                                <th class="px-4 py-3 text-left">Minggu</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Komentar Mentor</th>
                                <th class="px-4 py-3 text-left">File</th>
                                <th class="px-4 py-3 text-left">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">

                        @forelse($tugas as $item)

                            <tr class="hover:bg-gray-50">

                                <td class="px-4 py-4">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="px-4 py-4">
                                    {{ $item->user->name }}
                                </td>

                                <td class="px-4 py-4">
                                    {{ $item->judul }}
                                </td>

                                <td class="px-4 py-4">
                                    Minggu {{ $item->minggu }}
                                </td>

                                <td class="px-4 py-4">

                                    @if($item->status == 'Diterima')

                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">
                                            Diterima
                                        </span>

                                    @elseif($item->status == 'Revisi')

                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">
                                            Revisi
                                        </span>

                                    @else

                                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">
                                            Belum Diperiksa
                                        </span>

                                    @endif

                                </td>

                                <td class="px-4 py-4">

                                    <div class="mb-3">
                                        <strong>Komentar Mentor:</strong><br>
                                        {{ $item->komentar ?? '-' }}
                                    </div>

                                    @if($item->ai_review)

                                        <div class="mt-3">
                                            <strong>Review AI:</strong>

                                            <div class="mt-2 p-3 bg-green-50 border-l-4 border-green-500 rounded text-sm max-h-48 overflow-y-auto">
                                                {!! nl2br(e(Str::limit($item->ai_review, 500))) !!}
                                            </div>
                                        </div>

                                    @endif

                                </td>

                                <td class="px-4 py-4">

                                    <a
                                        href="{{ route('admin.tugas.download', $item->id) }}"
                                        class="text-blue-600 hover:underline">
                                        Download
                                    </a>

                                </td>

                                <td class="px-4 py-4">

                                    <a
                                        href="{{ route('admin.tugas.ai', $item->id) }}"
                                        class="inline-block mb-3 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                                        Analisis AI
                                    </a>

                                    <form
                                        action="{{ route('admin.tugas.updateStatus', $item->id) }}"
                                        method="POST">

                                        @csrf
                                        @method('PUT')

                                        <select
                                            name="status"
                                            class="w-full border rounded px-3 py-2 mb-2">

                                            <option value="Belum Diperiksa"
                                                {{ $item->status == 'Belum Diperiksa' ? 'selected' : '' }}>
                                                Belum Diperiksa
                                            </option>

                                            <option value="Revisi"
                                                {{ $item->status == 'Revisi' ? 'selected' : '' }}>
                                                Revisi
                                            </option>

                                            <option value="Selesai"
                                                {{ $item->status == 'Selesai' ? 'selected' : '' }}>
                                                Selesai
                                            </option>

                                        </select>

                                        <textarea
                                            name="komentar"
                                            rows="3"
                                            class="w-full border rounded px-3 py-2"
                                            placeholder="Masukkan komentar mentor...">{{ $item->komentar }}</textarea>

                                        <button
                                            type="submit"
                                            class="mt-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                                            Simpan
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="8" class="text-center py-6 text-gray-500">
                                    Belum ada data tugas.
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