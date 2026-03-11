{{-- resources/views/student/test/start.blade.php --}}
@extends('layouts.student')

@section('title', 'Mulai Tes - ' . $package->title)

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-5 text-center">
                        <i class="bi bi-file-earmark-text text-primary" style="font-size: 4rem;"></i>
                        <h2 class="mt-3 mb-4">{{ $package->title }}</h2>

                        @if ($package->description)
                            <p class="text-muted mb-4">{{ $package->description }}</p>
                        @endif

                        <div class="row text-start mb-4">
                            <div class="col-md-6 mb-3">
                                <div class="bg-light p-3 rounded">
                                    <h6 class="text-muted mb-2"><i class="bi bi-question-circle"></i> Total Soal</h6>
                                    <h4 class="mb-0">{{ $package->total_questions }} soal</h4>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="bg-light p-3 rounded">
                                    <h6 class="text-muted mb-2"><i class="bi bi-clock"></i> Durasi</h6>
                                    <h4 class="mb-0">{{ $package->duration }} menit</h4>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning text-start">
                            <h6 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Perhatian!</h6>
                            <ul class="mb-0 ps-3">
                                <li>Pastikan koneksi internet Anda stabil</li>
                                <li>Tes akan dimulai setelah Anda klik tombol "Mulai Tes"</li>
                                <li>Timer akan berjalan otomatis dan tidak bisa di-pause</li>
                                <li>Jawaban Anda akan tersimpan otomatis</li>
                                <li>Tes akan ter-submit otomatis jika waktu habis</li>
                                <li>Kerjakan dengan jujur dan tidak menyontek</li>
                            </ul>
                        </div>

                        <form method="POST" action="{{ route('student.test.work', ['attempt' => 'new']) }}" id="startForm">
                            @csrf
                            <input type="hidden" name="package_id" value="{{ $package->id }}">

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="agree" required>
                                <label class="form-check-label" for="agree">
                                    Saya setuju untuk mengerjakan tes dengan jujur dan mematuhi aturan yang berlaku
                                </label>
                            </div>

                            <button type="button" class="btn btn-primary btn-lg px-5" onclick="confirmStart()">
                                <i class="bi bi-play-circle"></i> Saya Siap, Mulai Tes!
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function confirmStart() {
                if (!document.getElementById('agree').checked) {
                    alert('Anda harus menyetujui pernyataan terlebih dahulu');
                    return;
                }

                if (confirm('Yakin ingin memulai tes? Timer akan langsung berjalan setelah Anda mengklik OK.')) {
                    // Create attempt via AJAX first
                    fetch('{{ route('student.test.create-attempt') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                package_id: {{ $package->id }}
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.attempt_id) {
                                window.location.href = '/student/test/' + data.attempt_id + '/work';
                            }
                        })
                        .catch(err => {
                            alert('Gagal memulai tes. Silakan coba lagi.');
                        });
                }
            }
        </script>
    @endpush
@endsection
