{{-- resources/views/student/exam/start.blade.php --}}
@extends('student.exam.layouts.exam')

@section('title', 'Mulai Tes - ' . $package->title)

@section('content')
    <div class="exam-start-wrapper">
        <div class="exam-start-card">
            <div class="exam-start-header">
                <i class="bi bi-file-earmark-text"></i>
                <h2>{{ $package->title }}</h2>
                @if ($package->description)
                    <p class="mb-0 opacity-75">{{ $package->description }}</p>
                @endif
            </div>

            <div class="exam-start-body">
                <div class="exam-info-grid">
                    <div class="exam-info-item">
                        {{-- <i class="bi bi-question-circle"></i> --}}
                        <h4>{{ $package->total_questions }}</h4>
                        <span>Soal</span>
                    </div>
                    <div class="exam-info-item">
                        {{-- <i class="bi bi-clock"></i> --}}
                        <h4>{{ $package->duration }}</h4>
                        <span>Menit</span>
                    </div>
                </div>

                <div class="alert alert-warning mb-4">
                    <h6 class="alert-heading mb-2"><i class="bi bi-exclamation-triangle me-2"></i>Perhatian!</h6>
                    <ul class="mb-0 ps-3" style="font-size: 0.9rem;">
                        <li>Pastikan koneksi internet Anda stabil</li>
                        <li>Tes akan dimulai setelah Anda klik tombol "Mulai Tes"</li>
                        <li>Timer akan berjalan otomatis dan tidak bisa di-pause</li>
                        <li>Jawaban Anda akan tersimpan otomatis</li>
                        <li>Tes akan ter-submit otomatis jika waktu habis</li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('student.test.work', ['attempt' => 'new']) }}" id="startForm">
                    @csrf
                    <input type="hidden" name="package_id" value="{{ $package->id }}">

                    <div class="form-check mb-4 p-3" style="background: var(--bg-main); border-radius: var(--radius-md);">
                        <input class="form-check-input" type="checkbox" id="agree" required>
                        <label class="form-check-label" for="agree" style="font-size: 0.9rem;">
                            Saya setuju untuk mengerjakan tes dengan jujur dan mematuhi aturan yang berlaku
                        </label>
                    </div>

                    <button type="button" class="btn btn-start btn-lg w-100 py-3" onclick="confirmStart()">
                        <i class="bi bi-play-circle me-2"></i>Saya Siap, Mulai Tes!
                    </button>
                </form>
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
