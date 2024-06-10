@props(['size' => 'normal', 'attachments' => [], 'placeholder' => 'Tidak ada'])

<div @if ($size === 'small') class="text-xs" @endif>
  @php /** @var \App\Models\Attachment $attachment */ @endphp
  @forelse($attachments as $attachment)
    @php $icon = match (str($attachment->name)->afterLast('.')->lower()->toString()) {
      'pdf', 'txt', 'doc', 'docx', 'docm' => 'lucide-file-text',
      'png', 'jpg', 'jpeg', 'gif', 'webp' => 'lucide-file-image',
      'xlsx', 'xlsm', 'xlsb', 'xltx', 'csv', 'tsv' => 'lucide-file-spreadsheet',
      'pptx', 'pptm', 'potx' => 'lucide-projector',
      default => 'lucide-file',
    } @endphp
    <div>
      <a href="{{ $attachment->url }}" target="_blank" class="inline-flex">
        @svg($icon, $size === 'small' ? 'w-3 h-3' : 'w-5 h-5')
        {{ $attachment->name }}
      </a>
    </div>
  @empty
    <div class="fi-in-placeholder text-sm leading-6 text-gray-400 dark:text-gray-500">
      {{ $placeholder }}
    </div>
  @endforelse
</div>
