{{-- resources/views/student/exam/result/index.blade.php --}}
@extends('student.exam.layouts.exam')

@section('title', 'Hasil Tes - ' . $package->title)

@section('content')
    <div class="exam-result-wrapper">
        @if ($attempt->is_flagged)
            @include('student.exam.result._flagged')
        @else
            @include('student.exam.result._completed')
        @endif
    </div>
@endsection
