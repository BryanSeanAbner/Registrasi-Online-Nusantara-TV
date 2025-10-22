@extends('public.shared.layout')

@section('content')
<section class="relative px-6 py-16 sm:py-24 lg:px-8 bg-white">
  <div class="mx-auto max-w-3xl">
    <div class="overflow-hidden rounded-2xl bg-white p-8 shadow-xl ring-1 ring-gray-200 md:p-10">
      <div class="flex flex-col items-center text-center">
        <img id="success-animation" src="/api/check-animation" alt="Success" class="h-32 w-32" />

        <h1 class="text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl mb-4 mt-6">
          Terima kasih telah mendaftar!
        </h1>
        <p class="text-lg text-gray-600 mb-8">
          Mohon tunggu konfirmasi selanjutnya melalui WhatsApp.
        </p>

        <div class="mt-4">
          <a href="/" class="inline-flex items-center gap-x-2 rounded-2xl bg-blue-600 px-8 py-5 text-sm font-medium text-white shadow transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
            </svg>
            Kembali ke Beranda
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Memastikan animasi dimuat dengan timestamp untuk mencegah cache
  const animationImg = document.getElementById('success-animation');
  if (animationImg) {
    const timestamp = new Date().getTime();
    animationImg.src = `/api/check-animation?t=${timestamp}`;
  }
});
</script>
@endpush


