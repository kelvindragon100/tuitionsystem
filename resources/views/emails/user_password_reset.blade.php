@component('mail::message')
# Password Reset

Hello {{ $user->name }},

Your account password has been reset by the administrator.

**New Password:** `{{ $plainPassword }}`

Please log in and change your password immediately.

Thanks,<br>
Smart Tuition Team
@endcomponent
