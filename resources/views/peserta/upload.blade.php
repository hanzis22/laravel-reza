<x-app-layout>
    <x-slot name="header">
        <h2>
            Upload Tugas
        </h2>
    </x-slot>

    <div class="p-6">

    <a
    href="/peserta/dashboard"
    class="btn btn-secondary mb-3"
>
    ← Kembali
</a>

        <form action="/peserta/upload"
      method="POST"
      enctype="multipart/form-data">

            @csrf

            <div>
                <label>Judul Tugas</label>
                <input
                    type="text"
                    name="judul"
                    class="border p-2 w-full">
            </div>

            <br>

            <div>
                <label>Minggu</label>

                <select
                    name="minggu"
                    class="border p-2 w-full">

                    <option value="1">Minggu 1</option>
                    <option value="2">Minggu 2</option>
                    <option value="3">Minggu 3</option>
                    <option value="4">Minggu 4</option>

                </select>
            </div>

            <br>

            <div>
                <label>File Tugas</label>

                <input
                    type="file"
                    name="file"
                    class="border p-2 w-full">
            </div>

            <br>

            <button
                type="submit"
                class="bg-blue-500 text-white px-4 py-2">

                Upload

            </button>

        </form>

    </div>
</x-app-layout>