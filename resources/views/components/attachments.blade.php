@props(['size' => 'normal', 'attachments' => [], 'placeholder' => 'Tidak ada'])

<div @if ($size === 'small') class="text-xs" @endif>
  @php /** @var \App\Models\Attachment $attachment */ @endphp
  @forelse($attachments as $attachment)
    <div>
      <a href="{{ $attachment->url }}" target="_blank" class="inline-flex">
        <x-heroicon-o-document @class([
          'w-5 h-5' => $size !== 'small',
          'w-3 h-3' => $size === 'small',
        ]) />
        {{ $attachment->name }}
      </a>
    </div>
  @empty
    <div class="fi-in-placeholder text-sm leading-6 text-gray-400 dark:text-gray-500">
      {{ $placeholder }}
    </div>
  @endforelse
</div>
