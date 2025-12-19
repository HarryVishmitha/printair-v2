@component('mail::message')
# New Contact Message

**Name:** {{ $p['name'] ?? '-' }}  
**Email:** {{ $p['email'] ?? '-' }}  
**Phone:** {{ $p['phone'] ?? '-' }}  
**Subject:** {{ $p['subject'] ?? '-' }}

---

{{ $p['message'] ?? '' }}

---

**IP:** {{ $p['ip'] ?? '-' }}  
**User Agent:** {{ $p['ua'] ?? '-' }}

Thanks,  
{{ config('app.name') }}
@endcomponent

