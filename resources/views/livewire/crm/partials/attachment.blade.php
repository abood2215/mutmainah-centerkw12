@php $type = $att['file_type'] ?? ''; $url = $att['data_url'] ?? $att['file_url'] ?? '#'; @endphp
@if($type === 'image')
    <a href="{{ $url }}" target="_blank" class="block mt-2">
        <img src="{{ $att['thumb_url'] ?? $url }}" class="rounded-xl max-w-full max-h-52 object-cover border border-white/20" alt="صورة">
    </a>
@elseif($type === 'audio')
    <audio controls class="mt-2 w-full max-w-xs rounded-lg" style="height:36px;">
        <source src="{{ $url }}">
    </audio>
@elseif($type === 'video')
    <video controls class="mt-2 rounded-xl max-w-full max-h-48">
        <source src="{{ $url }}">
    </video>
@elseif($type === 'contact')
    <div class="mt-2 bg-white/10 rounded-xl p-2.5 border border-white/20 flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-bold truncate">{{ $att['file_name'] ?? 'جهة اتصال' }}</p>
            @if($att['phone_number'] ?? '') <p class="text-[10px] opacity-70" dir="ltr">{{ $att['phone_number'] }}</p> @endif
        </div>
    </div>
@elseif(in_array($type, ['file', 'document', '']))
    @if($url !== '#')
    <a href="{{ $url }}" target="_blank"
       class="mt-2 flex items-center gap-2 bg-white/10 rounded-xl p-2.5 border border-white/20 hover:bg-white/20 transition-colors">
        <svg class="w-5 h-5 flex-shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
        <span class="text-xs font-semibold truncate">{{ $att['file_name'] ?? 'ملف' }}</span>
        <svg class="w-3.5 h-3.5 opacity-60 mr-auto flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
    </a>
    @endif
@endif
