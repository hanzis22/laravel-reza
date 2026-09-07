<x-guest-layout>

    <div class="w-full max-w-md mx-auto">

        <div class="bg-white shadow-lg rounded-xl p-8">

            <div class="text-center mb-6">

                <div class="flex justify-center mb-4">
                    <img
                        src="{{ asset('images/logo.jpg') }}"
                        alt="Dieng Cyber"
                        class="h-24">
                </div>

                <p class="text-center text-gray-600 mt-2">
                    Sistem Monitoring Tugas Magang
                </p>
                
                <p class="text-center text-sm text-gray-500">
                    PT Dieng Cyber Indonesia
                </p>

            </div>

            <h2 class="text-xl font-semibold text-center mb-6">
                Login Peserta / Admin
            </h2>

            <x-auth-session-status
                class="mb-4"
                :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-4">
                    <label class="block mb-2">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="w-full border rounded-lg px-4 py-2">
                </div>

                <div class="mb-4">
                    <label class="block mb-2">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        required
                        class="w-full border rounded-lg px-4 py-2">
                </div>

                <div class="mb-4 flex items-center">

                    <input
                        type="checkbox"
                        name="remember"
                        id="remember">

                    <label
                        for="remember"
                        class="ml-2 text-sm text-gray-600">

                        Ingat Saya

                    </label>

                </div>

                <button
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg">

                    Login

                </button>

                <div class="mt-4 text-center">

    <a
        href="{{ route('register') }}"
        class="text-blue-600 hover:text-blue-800">

        Belum punya akun? Register

    </a>

</div>

            </form>

        </div>

    </div>

</x-guest-layout>