@extends('errors::minimal')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message')
    @auth
        @if(auth()->user()->role !== 'admin')
            <div style="max-width:520px;margin:0 auto;text-align:center;">
                <p style="font-size:15px;font-weight:600;color:#1f2937;margin-bottom:8px;">Bạn không có quyền truy cập trang quản trị</p>
                <p style="font-size:13px;color:#6b7280;margin-bottom:16px;">
                    Tài khoản hiện tại <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }}) có role <code style="background:#f3f4f6;padding:2px 6px;border-radius:4px;">{{ auth()->user()->role }}</code> không được phép vào <code>/admin</code>.<br>
                    Chỉ tài khoản có <strong>role = admin</strong> mới vào được.
                </p>
                <p style="font-size:13px;color:#374151;margin-bottom:16px;">Bạn có muốn đăng xuất để đăng nhập tài khoản khác không?</p>
                <div style="display:flex;gap:10px;justify-content:center;">
                    <button onclick="fetch('{{ url('/api/auth/logout') }}', {method:'POST', credentials:'include', headers:{'Accept':'application/json'}}).finally(()=>{ window.location.href='/'; })" style="background:#111827;color:#fff;border:none;padding:8px 16px;border-radius:6px;font-size:13px;cursor:pointer;">Đăng xuất</button>
                    <a href="/" style="background:#f3f4f6;color:#111827;text-decoration:none;padding:8px 16px;border-radius:6px;font-size:13px;border:1px solid #e5e7eb;">Về trang chủ</a>
                </div>
                <p style="font-size:11px;color:#9ca3af;margin-top:14px;">Gợi ý: đăng nhập bằng <code>admin / admin123</code></p>
            </div>
        @else
            {{ $exception->getMessage() ?: 'Forbidden' }}
        @endif
    @else
        {{ $exception->getMessage() ?: 'Forbidden' }}
    @endauth
@endsection
