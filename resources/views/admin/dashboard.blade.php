<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            Dashboard Admin DCTMS
        </h2>
    </x-slot>

    <div class="py-8">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

    <div class="card shadow-sm">

        <div class="bg-white shadow-sm rounded-lg p-6">

    <h3 class="text-2xl font-bold mb-4">
        Selamat datang Admin,
        {{ auth()->user()->name }}
    </h3>

    <a href="{{ route('admin.tugas.index') }}"
       class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Kelola Tugas Peserta
    </a>

</div>

    </div>

    </div>
</div>
</x-app-layout>