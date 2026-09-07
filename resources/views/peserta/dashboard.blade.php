<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Peserta
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-2xl font-bold mb-2">
                    Selamat Datang, {{ Auth::user()->name }}
                </h3>

                <p class="text-gray-600">
                    Kelola tugas mingguan magang Anda.
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-6">

                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h4 class="text-lg font-semibold mb-2">
                        Upload Tugas
                    </h4>

                    <p class="text-gray-600 mb-4">
                        Upload laporan mingguan magang.
                    </p>

                    <a href="/peserta/upload"
                       class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Upload Tugas
                    </a>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h4 class="text-lg font-semibold mb-2">
                        Riwayat Tugas
                    </h4>

                    <p class="text-gray-600 mb-4">
                        Lihat status dan komentar mentor.
                    </p>

                    <a href="/peserta/tugas"
                       class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        Lihat Riwayat
                    </a>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>