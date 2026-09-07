<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Tugas
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white p-6 rounded-lg shadow">

                <a href="/peserta/tugas"
                   class="text-blue-600 hover:text-blue-800">
                    ← Kembali
                </a>

                <h3 class="text-2xl font-bold mt-4 mb-6">
                    Edit Tugas
                </h3>

                <form action="{{ route('peserta.tugas.update', $tugas->id) }}"
                      method="POST"
                      enctype="multipart/form-data">

                    @csrf
                    @method('PUT')

                    {{-- Judul --}}
                    <div class="mb-4">
                        <label class="block mb-2">
                            Judul Tugas
                        </label>

                        <input
                            type="text"
                            name="judul"
                            value="{{ old('judul', $tugas->judul) }}"
                            class="w-full border rounded-lg px-4 py-2"
                            required>
                    </div>

                    {{-- Minggu --}}
                    <div class="mb-4">
                        <label class="block mb-2">
                            Minggu
                        </label>

                        <select
                            name="minggu"
                            class="w-full border rounded-lg px-4 py-2"
                            required>

                            @for($i = 1; $i <= 16; $i++)

                                <option
                                    value="{{ $i }}"
                                    {{ $tugas->minggu == $i ? 'selected' : '' }}>

                                    Minggu {{ $i }}

                                </option>

                            @endfor

                        </select>
                    </div>

                    {{-- Ganti File --}}
                    <div class="mb-4">
                        <label class="block mb-2">
                            Ganti File PDF (Opsional)
                        </label>

                        <input
                            type="file"
                            name="file"
                            class="w-full border rounded-lg p-3">

                        <p class="text-sm text-gray-500 mt-2">
                            Kosongkan jika tidak ingin mengganti file.
                        </p>
                    </div>

                    <button
                        type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg">

                        Simpan Perubahan

                    </button>

                </form>

            </div>

        </div>
    </div>

</x-app-layout>