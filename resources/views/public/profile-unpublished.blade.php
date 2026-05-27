@extends('layouts.public', ['profile' => $profile, 'showPlatformBranding' => false])

@section('content')
    <div class="max-w-md mx-auto px-4 py-12 pb-28 text-center">
        <div class="mx-auto mb-6 h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center text-3xl text-gray-600">
            ●
        </div>
        <h1 class="text-3xl font-bold tracking-tight">{{ __('Seite nicht veröffentlicht') }}</h1>
        <p class="mt-4 text-base leading-relaxed text-gray-600">
            {{ __('Diese Seite ist noch nicht öffentlich freigegeben. Bitte später erneut versuchen.') }}
        </p>
    </div>
@endsection
