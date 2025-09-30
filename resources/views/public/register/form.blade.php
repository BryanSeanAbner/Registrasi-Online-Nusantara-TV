@extends('public.shared.layout')

@section('content')
  <section class="px-4 py-12">
    <div class="mx-auto w-full max-w-md rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
      <h1 class="mb-8 text-2xl font-bold text-gray-900">Daftar: {{ $event->title }}</h1>

      <form method="post" class="space-y-6">
        @csrf

        <div>
          <label for="name" class="mb-2 block text-sm font-medium text-gray-700">Nama</label>
          <input id="name" name="name" type="text" required
                 class="w-full rounded-lg border-gray-300 px-4 py-3 text-base shadow-sm focus:border-blue-500 focus:ring-blue-500" />
        </div>

        <div>
          <label for="phone" class="mb-2 block text-sm font-medium text-gray-700">No. WhatsApp</label>
          <input id="phone" name="phone" type="text" required
                 class="w-full rounded-lg border-gray-300 px-4 py-3 text-base shadow-sm focus:border-blue-500 focus:ring-blue-500" />
        </div>

        <div>
          <label for="email" class="mb-2 block text-sm font-medium text-gray-700">Email <span class="text-gray-400">(opsional)</span></label>
          <input id="email" name="email" type="email"
                 class="w-full rounded-lg border-gray-300 px-4 py-3 text-base shadow-sm focus:border-blue-500 focus:ring-blue-500" />
        </div>

        <div>
          <label for="company" class="mb-2 block text-sm font-medium text-gray-700">Perusahaan <span class="text-gray-400">(opsional)</span></label>
          <input id="company" name="company" type="text"
                 class="w-full rounded-lg border-gray-300 px-4 py-3 text-base shadow-sm focus:border-blue-500 focus:ring-blue-500" />
        </div>

        <div class="pt-4">
          <button type="submit"
                  class="w-full rounded-lg bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
            Daftar Sekarang
          </button>
        </div>
      </form>
    </div>
  </section>
@endsection
